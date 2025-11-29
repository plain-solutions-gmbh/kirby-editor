<template>
  <k-panel class="k-editor-view">
    <main class="k-panel-main">
      <k-panel-menu />

      <k-editor-header
        ref="header"
        :current="current"
        :tree-open="treeOpen"
        @reload="onFileChange($event)"
        @discard="$refs.preview.update(null)"
        @download="$refs.preview.download()"
        @previewSelect="onPreviewSet"
        @toggleTree="treeOpen = !treeOpen"
        @save="$refs.preview.save()"
      />

      <div class="k-editor-main">
        <div
          class="k-editor-tree"
          :style="{ width: (treeOpen ? treeWidth : 0) + 'px' }"
        >
          <k-filetree-field
            ref="filetree"
            height="100%"
            :required="true"
            :save="null"
            :read-path-from-url="true"
            @input="onSelect($event)"
            @update="onFiletreeUpdate($event)"
          />

          <div
            v-if="treeOpen"
            class="resize-handle"
            @mousedown="startResizing"
          ></div>
        </div>

        <k-editor-preview
          ref="preview"
          :current="current"
          @hasChanges="$refs.header.hasChanges = $event"
          @select="$refs.filetree.select($event)"
          @update="current = $event"
        />
      </div>
    </main>
  </k-panel>
</template>

<script>
export default {
  data() {
    return {
      current: null,
      error: null,
      treeOpen: true,
      treeWidth: 200,
      resizing: false,
    };
  },
  mounted() {
    this.$panel.menu.close();

    //Show warning: Know what you're doing!
    if (localStorage.getItem("plain.editor.notify.risk") !== "disabled") {
      this.notify_risk();
    }

    window.addEventListener("popstate", this.onUrlUpdate);
  },
  beforeDestroy() {
    window.removeEventListener("popstate", this.onUrlUpdate);
  },
  methods: {
    updateParam(path, preview) {
      path ??= this.current.value;

      const url = new URL(window.location.href);
      const query = { path, ...(preview && { preview }) };

      window.history.pushState(
        {},
        "",
        url.pathname + "?" + this.$helper.url.buildQuery(query) + url.hash,
      );
    },
    onUrlUpdate() {
      //Check if item exists -> Filetree sets the current
      this.$refs.filetree.update();
      //To set the right preview component
      this.$refs.preview.updatePreview();
      //Set preview selector to the right pointer
      this.$refs.header.updatePreview();
    },
    //Filetree is the one who set the current
    async onFiletreeUpdate(item) {
      //No item -> get root
      this.current =
        item ??
        (await this.$api.get("plain/editor/get/toArray", {
          path: "",
        }));
    },
    onPreviewSet(componentName) {
      if (componentName) {
        this.updateParam(null, componentName);
      }

      this.$refs.preview.updatePreview(componentName);
    },
    onFileChange(item) {
      this.$refs.filetree.init(item);
      //this.onSelect(item)
    },
    onSelect(current) {
      this.error = null;

      if (current !== null) {
        this.updateParam(current ? current.value : "/");
        this.current = current;
      }
    },
    startResizing(e) {
      this.resizing = true;
      this.startX = e.clientX;
      this.startWidth = this.treeWidth;
      document.addEventListener("mousemove", this.resize);
      document.addEventListener("mouseup", this.stopResizing);
    },
    resize(e) {
      if (this.resizing) {
        const minWidth = 100;
        const maxWidth = 500;
        const delta = e.clientX - this.startX;
        this.treeWidth = Math.min(
          maxWidth,
          Math.max(minWidth, this.startWidth + delta),
        );
        if (this.$refs?.preview) {
          this.$refs.preview.$emit("resize");
        }
      }
    },
    toggleTree(state) {
      this.treeOpen = state;
    },
    stopResizing() {
      this.resizing = false;
      document.removeEventListener("mousemove", this.resize);
      document.removeEventListener("mouseup", this.stopResizing);
    },
    notify_risk() {
      window.panel.dialog.open({
        component: "k-editor-notify-risk",
        on: {
          submit: () => {
            //Set flag
            localStorage.setItem("plain.editor.notify.risk", "disabled");
            window.panel.dialog.close();
          },
          cancel: () => {
            window.history.back();
          },
        },
      });
    },
  },
};
</script>

<style lang="scss">
.k-editor-view > .k-panel-main {
  padding: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;

  .k-editor-main {
    display: flex;
    flex: 1 1 100%;
    min-height: calc(100% - 50px);
  }

  .k-editor-tree {
    position: relative;
    height: 100%;
    box-shadow: var(--shadow-md);
    z-index: 1;
  }

  .resize-handle {
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
    background: transparent;
  }
}
</style>
