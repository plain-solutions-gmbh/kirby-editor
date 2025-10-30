<template>
  <k-header class="k-editor-header">
    <!-- mobile menu opener -->
    <k-button
      icon="bars"
      class="k-panel-menu-proxy"
      @click="$panel.menu.toggle()"
    />

    <k-button
      :icon="treeOpen ? 'angle-left' : 'angle-right'"
      :title="treeOpen ? $t('collapse') : $t('expand')"
      @click="$emit('toggleTree')"
    />

    <span class="k-editor-filename-wrapper">
      <span class="k-editor-filename">{{ title }}</span>
    </span>

    <!-- Notifications -->
    <k-button
      v-if="notification"
      slot="buttons"
      :icon="notification.icon"
      :text="notification.message"
      :theme="notification.theme"
      variant="filled"
      @click="notification.close()"
    />

    <k-button-group v-else slot="buttons">
      <k-button
        v-for="button in changeButtons"
        :key="button.text"
        v-bind="button"
        variant="filled"
        size="sm"
      />
      <k-button
        v-if="allowPreviewSelector"
        variant="filled"
        theme="blue"
        size="sm"
        :text="$t(nextPreview + '.label')"
        @click="toggleNextPreview"
      />
      <k-button
        icon="dots"
        size="xs"
        class="k-file-preview-frame-dropdown-toggle"
        @click="$refs.dropdown.toggle()"
      />
      <k-dropdown-content ref="dropdown">
        <k-dropdown-item icon="file-add" @click="create('file')">{{
          $t("plain.editor.file.new")
        }}</k-dropdown-item>
        <k-dropdown-item icon="folder-add" @click="create('folder')">{{
          $t("plain.editor.folder.new")
        }}</k-dropdown-item>
        <hr v-if="current && hasChanges === false" />
        <k-dropdown-item
          v-if="current && hasChanges === false"
          icon="title"
          @click="rename()"
          >{{ $t("rename") }}</k-dropdown-item
        >
        <k-dropdown-item
          v-if="current && hasChanges === false"
          icon="parent"
          @click="move()"
          >{{ $t("move") }}</k-dropdown-item
        >
        <k-dropdown-item
          v-if="current && current.isFile && hasChanges === false"
          icon="download"
          @click="$emit('download')"
          >{{ $t("download") }}</k-dropdown-item
        >
        <hr v-if="current" />
        <k-dropdown-item
          v-if="current"
          icon="trash"
          style="--button-color-text: var(--color-negative)"
          @click="remove()"
          >{{ $t("delete") }}</k-dropdown-item
        >
      </k-dropdown-content>
    </k-button-group>
  </k-header>
</template>

<script>
export default {
  props: {
    current: { type: Object, default: () => null },
    treeOpen: { type: Boolean, default: () => false },
  },
  data() {
    return {
      previewComponents: [],
      previewNames: [],
      previewLabels: [],
      hasChanges: false,
      previewIndex: 0,
    };
  },
  computed: {
    title() {
      return this.$helper.string.ltrim(this.current?.value ?? "", "/");
    },
    allowPreviewSelector() {
      return this.previewComponents.length > 1;
    },
    notification() {
      if (
        window.panel.notification.context === "view" &&
        !window.panel.notification.isFatal
      ) {
        return window.panel.notification;
      }

      return null;
    },
    nextPreview() {
      const next = (this.previewIndex + 1) % this.previewNames.length;
      return this.previewNames[next];
    },
    changeButtons() {
      if (this.hasChanges === true) {
        return [
          {
            theme: "notice",
            text: this.$t("discard"),
            icon: "undo",
            responsive: true,
            click: () => this.$emit("discard"),
          },
          {
            theme: "notice",
            text: this.$t("save"),
            icon: "check",
            click: () => this.$emit("save"),
          },
        ];
      }
      return [];
    },
  },
  watch: {
    current(current) {
      const previews = current?.components ?? {};

      this.previewComponents = Object.keys(previews);
      this.previewNames = Object.values(previews);

      this.updatePreview();
    },
  },
  mounted() {
    window.addEventListener("keydown", this.saveHandler);
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.saveHandler);
  },
  methods: {
    toggleNextPreview() {
      this.previewIndex = (this.previewIndex + 1) % this.previewNames.length;
      this.setPreview();
    },
    updatePreview() {
      const preview = new URL(window.location.href).searchParams.get("preview");
      this.previewIndex = Math.max(this.previewNames.indexOf(preview), 0);
    },
    setPreview() {
      if (this.allowPreviewSelector) {
        this.$emit("previewSelect", this.previewNames[this.previewIndex]);
      } else {
        this.$emit("previewSelect", null);
      }
    },
    async action(method, args = {}) {
      this.$api
        .get(`plain/editor/get/${method}`, {
          path: this.current?.value ?? "",
          params: JSON.stringify(args),
        })
        .then((newItem) => {
          window.panel.dialog.close();
          this.$emit("reload", newItem);
        })
        .catch((res) => {
          window.panel.notification.open({
            theme: "danger",
            timeout: 30000,
            message: res.message,
          });
        });
    },
    create(type) {
      window.panel.dialog.open({
        component: "k-form-dialog",
        props: {
          fields: {
            filename: {
              label: this.$t("name"),
              type: "text",
              allow: "a-z0-9@._-",
            },
          },
          submitButton: this.$t("create"),
        },
        on: {
          submit: (value) => {
            this.action("create", {
              name: value.filename,
              type: type,
            });
          },
        },
      });
    },
    rename() {
      window.panel.dialog.open({
        component: "k-form-dialog",
        props: {
          fields: {
            filename: {
              label: this.$t("plain.editor.file.rename") + ":",
              type: "text",
              allow: "a-z0-9@._-",
              after: "." + this.current.extension,
            },
          },
          value: {
            filename: this.current.value,
          },
          submitButton: this.$t("rename"),
        },
        on: {
          submit: (value) => {
            this.action("rename", {
              newName: value.filename,
            });
          },
        },
      });
    },
    move() {
      window.panel.dialog.open({
        component: "k-form-dialog",
        props: {
          fields: {
            location: {
              type: "filetree",
              onlydir: true,
              required: false,
              height: "22rem",
            },
          },
          submitButton: this.$t("move"),
        },
        on: {
          submit: (value) => {
            const newRoot = value.location
              ? value.location + "/" + this.current.filename
              : null;
            this.action("move", {
              newRoot: newRoot,
            });
          },
        },
      });
    },
    remove() {
      window.panel.dialog.open({
        component: "k-remove-dialog",
        props: {
          text: this.$t("file.delete.confirm", {
            filename: this.current.filename,
          }),
        },
        on: {
          submit: () => {
            this.action("remove");
          },
        },
      });
    },
    saveHandler(event) {
      if (
        (event.ctrlKey || event.metaKey) &&
        event.key.toLowerCase() === "s" &&
        this.hasChanges
      ) {
        event.preventDefault();
        this.$emit("save");
      }
    },
  },
};
</script>

<style lang="scss">
.k-editor-header {
  display: flex;
  flex-wrap: nowrap;
  overflow: hidden;
  align-items: center;
  border-bottom: 1px solid var(--color-border);
  padding: var(--spacing-2) var(--spacing-2);
  margin-bottom: 0;
  > .k-header-buttons,
  .k-button,
  .k-header-title {
    white-space: nowrap;
    text-overflow: ellipsis;
    margin-bottom: 0;
  }
  .k-header-title-text {
    display: flex;
    overflow: hidden;
    align-items: center;
  }
  .k-editor-filename-wrapper {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    direction: rtl;
    line-height: 1;
    text-align: left;
    font-size: 0.45em;
    > .k-editor-filename {
      direction: rtl;
      text-align: left;
      &:after {
        content: "/";
      }
    }
  }
}
</style>
