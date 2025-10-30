<?php

namespace Plain\Editor;

/**
 * @package   Kirby Editor
 * @author    Roman Gsponer <support@plain-solutions.net>
 * @link      https://plain-solutions.net/
 * @copyright Roman Gsponer
 * @license   https://plain-solutions.net/terms/ 
 */

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

class Tree
{

    private string $root;
    private array $data;
    private array $options;

    /**
     * @param string $path Current start path
     * @param null|string $current Importent Item -> to load all children above him.
     * @param int $onlydir 
     */
    public function __construct(
        private string $path = '/',
        private ?string $current = null,
        private int $onlydir = 0
    ) {

        $this->root = Item::getRoot() . $path;
        $this->options = App::instance()->option('plain.editor');
        $this->data = $this->parse('dirs');

        if ($this->onlydir === 0) {
            $this->data = A::merge(
                $this->data,
                $this->parse('files'),
            );
        }
    }


    /**
     * Walk through 'files' or 'folders'
     * 
     * @param string $fnc 
     * @return array 
     */
    private function parse(string $fnc): array
    {
        return A::map(
            Dir::$fnc($this->root),
            function ($item) {

                $relative = $this->path . '/' .  $item;

                return Item::factory([
                    'path'  => $relative,
                    'current'   => $this->current,
                    'onlydir'   => $this->onlydir
                ]);
            }
        );
    }

    /**
     * Returns a new self
     * 
     * @param mixed ...$params 
     */
    public static function factory(...$params): self
    {
        return new self(...$params);
    }

    /** 
     * Returns parse data and returns an array
     */
    public function toArray(): array
    {
        return A::map(
            $this->data,
            function ($item) {

                $array = $item->toArray();
                $array['label'] ??= $item->filename();
                $extend = $this->options['extensions'][$item->extension()] ?? null;

                if ($extend === null) {
                    $mime = $item->mime();
                    $extend = $this->options['mimes'][$mime] ?? null;
                    $extend ??= $this->options['mimes'][Str::before($mime, '/') . '/*'] ?? null;
                }

                if (is_array($extend)) {
                    return A::merge($array, $extend);
                }

                if (is_callable($extend)) {
                    return $extend($array);
                }

                return $array;
            }
        );
    }
}
