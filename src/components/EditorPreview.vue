<template>
  <div class="k-editor-preview">
    <!-- Error message -->
    <div v-if="error" class="k-editor-preview-overlay">
      <k-empty icon="cancel">{{ error }}</k-empty>
    </div>

    <!-- Loader -->
    <div v-if="loader" class="k-editor-preview-overlay">
      <k-icon type="loader"></k-icon>
      <!-- Progressbar -->
      <k-progress v-if="typeof loader === 'number'" :value="loader" />
    </div>

    <!-- Preview component -->
    <component
      :is="component"
      v-if="component && content !== null"
      ref="preview"
      :content="content"
      :value="value"
      :url="url"
      :item="current"
      :draft="draft"
      @select="$emit('select', $event)"
      @update="update($event)"
      @error="error = $event"
      @onload="loader = $event"
    />
  </div>
</template>

<script>
export default {
  props: {
    current: { type: Object, default: () => null },
  },
  data() {
    return {
      content: null,
      component: null,
      draft: null,
      modified: null,
      url: null,
      blob: null,
      error: null,
      db: null,
      loader: false,
    };
  },
  watch: {
    async current(current) {
      await this.load(current);
      this.updatePreview();
    },
    draft(draft) {
      this.$emit("hasChanges", draft !== null);
    },
    blob: {
      immediate: true,
      handler(newBlob) {
        if (newBlob) {
          newBlob.text().then((text) => {
            this.content = text;
            this.url = URL.createObjectURL(this.blob);
          });
        } else {
          this.content = null;
        }
      },
    },
  },
  created() {
    this.initDb();
  },
  methods: {
    async load(item) {
      //Clear content. No old stuff will shown in current preview
      this.draft = this.content = this.modified = this.component = null;

      //File from db
      if (await this.getFromDb(item)) {
        const modified = await this.$api.get("plain/editor/get/modified", {
          path: item.value,
        });

        //File on Server is newer -> get from server (or notify if draft)
        if (this.modified < modified.result) {
          this.draft === null
            ? await this.getFromServer()
            : window.panel.notification.open({
                theme: "warning",
                icon: "alert",
                timeout: 30000,
                message: this.$t("plain.editor.notify.deprecated.text"),
              });
        }
        return;
      }

      //Entry not found -> get from server
      await this.getFromServer();
    },
    updatePreview(componentName) {
      //Get single component or default
      let component = this.current?.component ?? "k-editor-preview-default";
      const components = this.current?.components;

      if (this.$helper.object.isObject(components)) {
        //Try get from url query
        componentName ??= new URL(window.location.href).searchParams.get(
          "preview",
        );
        //Select first preview
        componentName ??= Object.values(components)[0];
        //Overwrite with current preview
        component = Object.keys(components).find(
          (key) => components[key] === componentName,
        );
      }

      this.component = component;
    },
    download() {
      //Create a dummy and fire it
      const downloadLink = document.createElement("a");
      downloadLink.href = this.url;
      downloadLink.download = this.current.filename;
      document.body.appendChild(downloadLink);
      downloadLink.click();
    },
    update(value) {
      this.draft = value === this.content ? null : value;
      this.saveToDb();
    },
    save(force = false) {
      this.$api
        .post("plain/editor/save", {
          path: this.current.value,
          content: this.draft,
          modified: this.modified,
          force: force,
        })
        .then((res) => {
          if (res.status === "outdated") {
            this.deprecatedVersion();
          } else {
            this.update(null);
            this.getFromServer();
          }
        })
        .catch((res) => {
          window.panel.notification.open({
            theme: res.status,
            timeout: 30000,
            message: res.message,
          });
        });
    },

    async getFromServer() {
      //Show progress
      this.loader = 0;
      this.modified = null;
      this.draft = null;

      //Init params
      let index = 0;
      let parts = [];
      let total = null;
      let eof = false;

      //Download file partial
      while (!eof) {
        const res = await this.$api.get("plain/editor/get/content", {
          path: this.current.value,
          params: JSON.stringify({ index: index }),
        });

        if (res.error) {
          this.error = res.error;
          this.loader = false;
          return;
        }

        total ??= res.parts;
        this.modified ??= res.modified;

        //File changed during download
        if (res.modified !== this.modified) {
          parts = [];
          index = 0;
          continue;
        }

        // Base64 → Bytes
        parts.push(this.base64ToBytes(res.data));

        eof = res.eof;
        index++;

        //Set progress percentage
        this.loader = Math.round((index / total) * 100);
      }

      //Create blob
      this.blob = new Blob(parts, {
        type: this.current.mime ?? "application/octet-stream",
      });

      this.saveToDb();

      //Hide spinner
      this.loader = false;

      return parts;
    },
    base64ToBytes(base64String) {
      const bin = atob(base64String);
      const bytes = new Uint8Array(bin.length);
      for (let i = 0; i < bin.length; i++) {
        bytes[i] = bin.charCodeAt(i);
      }
      return bytes;
    },
    deprecatedVersion() {
      window.panel.dialog.open({
        component: "k-text-dialog",
        props: {
          text: this.$t("plain.editor.notify.deprecated.text"),
          submitButton: {
            icon: "undo",
            theme: "warning",
            text: this.$t("plain.editor.notify.deprecated.submit"),
          },
        },
        on: {
          submit: () => {
            window.panel.dialog.close();
            this.save(true);
          },
        },
      });
    },

    /* Database methods */
    async initDb() {
      //Init Database
      this.db = await new Promise((resolve, reject) => {
        //Open Database
        const request = indexedDB.open("kirby-editor", 1);

        //Create Store
        request.onupgradeneeded = function (event) {
          const db = event.target.result;
          if (!db.objectStoreNames.contains("files")) {
            db.createObjectStore("files");
          }
        };

        //Set Database
        request.onsuccess = (event) => {
          resolve(event.target.result);
        };

        request.onerror = () => reject(null);
      });
    },
    getFromDb(item) {
      return new Promise((resolve) => {
        const tx = this.db.transaction("files", "readonly");
        const store = tx.objectStore("files");
        const request = store.get(item.value);
        request.onsuccess = (e) => {
          const data = e.target.result;
          if (data) {
            this.blob = data.blob;
            this.draft = data.draft;
            this.modified = data.modified;
            resolve(true);
          } else {
            resolve(false);
          }
        };
        request.onerror = () => {
          resolve(false);
        };
      });
    },
    saveToDb() {
      const tx = this.db.transaction("files", "readwrite");
      const store = tx.objectStore("files");
      store.put(
        {
          blob: this.blob,
          draft: this.draft,
          modified: this.modified,
        },
        this.current.value,
      );
    },
  },
};
</script>

<style lang="scss">
.k-editor-preview {
  position: relative;
  height: 100%;
  overflow: scroll;
  flex: 1 1 auto;
}

.k-editor-preview-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  justify-content: center;
  align-items: center;
  padding: var(--spacing-12);
  background: var(--panel-color-back);
  z-index: 1;
  & > .k-box {
    width: auto;
  }
  & > .k-progress {
    max-width: 150px;
  }
}
</style>
