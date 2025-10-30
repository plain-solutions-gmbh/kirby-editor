<?php

namespace Plain\Editor;

/**
 * @package   Kirby Editor
 * @author    Roman Gsponer <support@plain-solutions.net>
 * @link      https://plain-solutions.net/
 * @copyright Roman Gsponer
 * @license   https://plain-solutions.net/terms/ 
 */

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\File as KirbyFile;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

class Item extends KirbyFile{

    private string $value;
    private static array $options;
    private static null|string $base = null;
    private static null|bool $access = null;
    private null|array $option = null;
    private null|string $current = null;
    private null|string $content = null;
    private bool $onlydir = false;

    /**
     * Cache and return the root path if the editor
     */
    public static function getRoot() {
        if (static::$base !== null) {
            return static::$base;
        }
        $path = App::instance()->root() . '/' . App::instance()->option('plain.editor.root');
        return static::$base = Str::rtrim(Str::replace($path, ['//', '///'], '/'));
    }

    public function __construct(
        array|string|null $props = null,
        string|null $url = null
    ) {

        static::$access ??= in_array(
            App::instance()->user()?->role()->name(),
            A::wrap(App::instance()->option('plain.editor.access'))
        );

        if (static::$access === false) {
            throw new Exception(key: "plain.editor.access");
        }

        if (is_array($props) === false) {
            $this->root = $props ?? '';
            $this->value = Str::trim($this->root(), static::getRoot());
        }

        $this->current = $props['current'] ?? null;
        $this->onlydir = $props['onlydir'] ?? false;
        $this->value ??= $props['path'] ?? '';

        $this->root ??= static::getRoot() . $this->value;

        if (
            F::exists($this->root) === false && 
            Dir::exists($this->root) === false
        ) {
            throw new NotFoundException(
                key: 'plain.editor.' . $this->itemType() . '.exists',
                data: ['name' => $this->name()]
            );
        }

        //Ignore model and url
        $this->url = $this->model = null;

        static::$options ??= App::instance()->option('plain.editor');
        
    }

    /**
     * Create a new self
     * 
     * @param mixed ...$params 
     */
    public static function factory(...$params): self
    {
        return new self(...$params);
    }

    /**
     * Return an array with content to send content partial
     * 
     * @param int $index 
     */
    public function content($index = 0): array
    {

        $chunkSize = 1024 * 1024;

        try {

            if ($this->type() === 'folder') {
                $folder = $this->folder();
                $data  = base64_encode(Json::encode($folder));
            } else {
                //Read partial of fiel
                $handle = fopen($this->root(), "rb");
                fseek($handle, $index * $chunkSize);
                $content = fread($handle, $chunkSize);
                $data = base64_encode($content);
                fclose($handle);
            }

            return  [
                'modified' => $this->modified(),
                'index'    => $index,
                'parts'     => round($this->size() / $chunkSize, 0, PHP_ROUND_HALF_UP),
                'data'     => $data,
                'eof'      => (($index + 1) * $chunkSize >= $this->size())
            ];

        } catch (\Throwable $th) {

            throw new NotFoundException(
                key: 'plain.editor.loading',
                data: ['message' => $th->getMessage()]
            );
        };
    }

    /** 
     * Returns the current mime type
     * 
     */
    public function mime(): ?string
    {
        return static::$options['extensions'][$this->extension()] ?? Parent::mime();
    }

    private function folder() {

        $children = A::map(Dir::read($this->root, absolute:true), function ($file) {
            $item = new static($file);

            return [
                'text' => $item->filename(),
                'info' => $item->niceSize(),
                'value' => $item->toArray(),
                'image' => [
                    'icon' => $item->icon(),
                    'src' => $item->preview()
                ]
            ];
        });
        return $children;
    }

    /** 
     * Return the type of the current item
     * 
     */
    private function itemType(): string
    {
        return ($this->isFile()) ? 'file' : 'folder';
    }

    /**
     * Creates a new file
     * 
     * @param string $name 
     * @param string $type 
     */
    public function create(string $name, string $type): self
    {

        if ($this->isFile()) {
            $target = $this->parent(true) . '/' . $name;
        }

        $target ??= $this->root() . '/'. $name;

        match ($type) {
            'file' => F::write($target, ''),
            'folder' => Dir::make($target),
        };

        return new static($target);

    }

    /**
     * Write string to file
     * 
     * @param mixed $content 
     * @param mixed $modified To check file on server is newer
     * @param bool $force Ignore if file on server is newer
     */
    public function save($content, $modified, $force = false): array
    {

        //Check modification of the file
        if ($force === false && $this->modified() !== $modified) {
            return [
                'status' => 'outdated',
            ];
        };

        $this->write($content);

        return [
            'status' => 'ok',
            'item' => self::factory($this->root)->toArray()
        ];

    }

    /**
     * Delete the current file
     */
    public function remove(): self
    {

        //Need to get parent folder before the file disapper
        $newLocation = $this->parent(true);

        $result = ($this->isFile()) ? F::remove($this->root) : Dir::remove($this->root);

        if ($result === false) {
            throw new NotFoundException(
                key: 'plain.editor.' . $this->itemType() . '.deleted',
                data: ['name' => $this->name()]
            );
        }

        return new static($newLocation);
    }

    /**
     * Move the current file
     * 
     * @param null|string $newRoot Relative path of the destination
     * @param bool $overwrite 
     */
    public function move(null|string $newRoot, bool $overwrite = false): static
    {
        $newRoot ??= '/' . $this->filename();
        return Parent::move(static::getRoot() . $newRoot, $overwrite);
    }

    /**
     * Get the option for the current mime. Either the whole option or by key
     * 
     * @param string|null $key 
     * @param mixed $fallback 
     */
    private function option(string|null $key = null, $fallback = null): string|array
    {
        //Load once
        if ($this->option === null) {

            $mime = $this->mime();
            $mime_option = static::$options['mimes'];

            $this->option = A::merge(
                //Get mime wildchars
                $mime_option[Str::before($mime, '/') . '/*'] ?? [],
                //Get mime
                $mime_option[$mime] ?? []
            );
            
        }

        if ($key) {
            return $this->option[$key] ?? $fallback;
        }

        return $this->option ?? [];

    }

    /**
     * Check if item is a file
     */
    private function isFile() {
        return F::exists($this->root);
    }

    /**
     * Get icon from mime options
     */
    private function icon() {
        return $this->option(
            'icon',
            $this->itemType()
        );
    }

    /**
     * Get images base64 content (null if not an image)
     */
    private function preview(): null|string
    {
        if ($this->type() === 'image') {
            return $this->dataUri();
        }
        return null;
    }

    /**
     * Check if folder has an subfolder 
     * 
     */
    private function hasChildren(): null|bool
    {
        if ($this->isFile()) {
            return null;
        }
        return !Dir::isEmpty($this->root);
    }

    /**
     * Return 
     * 
     * @param bool $absolute 
     */
    private function parent(bool $absolute = false)
    {
        return F::dirname($absolute ? $this->root : $this->value);
    }

    /**
     * Check if item is a part of current
     */
    private function isOpen(): bool
    {
        if ($this->current === null) {
            return false;
        }

        return Str::startsWith(
            $this->current,
            $this->value
        );
    }

    /**
     * Returns the file type.
     */
    public function type(): string|null
    {
        if ($this->isFile()) {
            return Parent::type();
        }
        return 'folder';
    }

    /**
     * Returns children as tree array
     */
    private function children()
    {
        if ($this->isFile()) {
            return null;
        }

        if ($this->isOpen()) {
            return Tree::factory(
                $this->value,
                $this->current,
                $this->onlydir
            )->toArray();
        }
        return $this->value;
    }

    /** 
     * Returns the relative path
     * 
     */
    public function value() {
        return $this->value;
    }

    /**
     * Returns an array of all important values
     */
    public function toArray(): array
    {
        return A::merge(
            Parent::toArray(),
            [
                'label'     => $this->filename(),
                'icon'      => $this->icon(),
                'children'  => $this->children(),
                'isFile'    => $this->isFile(),
                'open'      => $this->isOpen(),
                'hasChildren' => $this->hasChildren(),
                'modified'  => $this->modified('Y-m-d H:i:s'),
                'parent'    => $this->parent(),
                'value'     => $this->value(),
                'content'   => $this->content
            ],
            //Override by user options
            $this->option()
        );
    }

}

?>