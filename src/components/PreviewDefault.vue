<template>
  <div class="k-editor-preview-default">
    <div class="k-editor-preview-default-preview">
      <k-button
        v-if="isLarge && !content && progress === false"
        variant="filled"
        icon="preview"
        @click="loadFile()"
        >Load preview</k-button
      >

      <k-progress v-if="progress !== false && !content" :value="progress" />

      <k-file-preview-frame v-if="content">
        <img v-if="type === 'image'" :src="url" />
        <video
          v-else-if="type === 'video'"
          :src="url"
          preload="metadata"
          controls
        />
        <audio
          v-else-if="type === 'audio'"
          controls
          preload="metadata"
          :src="url"
        />
        <k-icon v-else :type="item.icon" />
      </k-file-preview-frame>
    </div>

    <k-file-preview-details :details="details" />
  </div>
</template>

<script>
export default {
  props: {
    content: { type: String, default: () => null },
    item: { type: Object, default: () => null },
    url: { type: String, default: () => null },
  },
  data() {
    return {
      progress: false,
    };
  },

  computed: {
    isLarge() {
      return this.item.size > 5 * 1024 * 1024;
    },
    details() {
      return [
        { title: this.$t("title"), text: this.item.filename },
        { title: this.$t("mime"), text: this.item.mime },
        { title: this.$t("plain.editor.modified"), text: this.item.modified },
        { title: this.$t("size"), text: this.item.niceSize },
      ];
    },
    type() {
      return this.item.type ?? this.item.mime.split("/")[0];
    },
  },
  methods: {
    onLoad(value) {
      this.progress = value;
    },
    async loadFile() {
      await this.$options.mixins[0].methods.fetchFile.call(this);
    },
    async fetchFile() {
      if (!this.isLarge) {
        await this.$options.mixins[0].methods.fetchFile.call(this);
      }
    },
  },
};
</script>

<style lang="scss">
.k-editor-preview-default-preview {
  flex: 1 1 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  > .k-file-preview-frame-column {
    height: 100%;
    width: 100%;
    > .k-file-preview-frame {
      --icon-size: 50px;
    }
  }
  > .k-progress {
    max-width: 150px;
  }
}

.k-editor-preview-default {
  height: 100%;
  display: flex;
  flex-direction: column;
}
</style>
