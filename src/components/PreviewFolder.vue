<template>
  <div class="k-editor-preview-folder">
    <k-items
      v-if="items.length > 0"
      :items="items"
      size="small"
      layout="cards"
      @item="select($event)"
    />
    <div v-else class="k-editor-preview-overlay">
      <k-empty icon="cancel">{{ $t("plain.editor.folder.empty") }}</k-empty>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    content: { type: String, default: () => null },
  },
  computed: {
    items() {
      return JSON.parse(this.content);
    },
  },
  methods: {
    select(item) {
      this.$emit("select", item.value);
    },
  },
};
</script>

<style>
.k-editor-preview-folder {
  margin: var(--spacing-5);
}
</style>
