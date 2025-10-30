<template>
  <div class="k-editor-preview-object">
    <iframe :src="url" />
  </div>
</template>

<script>
export default {
  props: {
    url: { type: String, default: () => null },
  },
};
</script>

<style lang="scss">
.k-editor-preview-object {
  height: 100%;
  > iframe {
    height: 100%;
    width: 100%;
  }
}
</style>
