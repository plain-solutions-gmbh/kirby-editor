<template>
  <div class="k-editor-preview-blueprint">
    <!-- eslint-disable-next-line vue/no-v-html -->
    <p v-if="missing" v-html="missing"></p>
    <component :is="'k-designer-designer'" v-else v-bind="$props" />
  </div>
</template>

<script>
export default {
  props: {
    content: { type: String, default: () => null },
    draft: { type: String, default: () => null },
    item: { type: Object, default: () => null },
  },
  computed: {
    missing() {
      if (this.$helper.isComponent("k-designer-designer") === false) {
        return this.$t("plain.editor.blueprint.missing", {
          link: "https://github.com/plain-solutions-gmbh/kirby-blueprint-editor",
        });
      }
      return false;
    },
  },
};
</script>

<style lang="scss">
.k-editor-preview-blueprint {
  display: flex;
  height: 100%;
  justify-content: center;
  align-items: center;
  a {
    color: var(--color-blue-300);
    text-decoration: underline;
  }
}
</style>
