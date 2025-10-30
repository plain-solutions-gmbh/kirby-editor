<template>
  <div class="k-editor-preview-fonts">
    <div>
      <p v-for="size in sizes" :key="size" :style="styleFor(size)">
        {{ sampleText }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    url: { type: String, default: () => null },
    family: { type: String, default: "CustomFont" },
    sampleText: {
      type: String,
      default: "The quick brown fox jumps over the lazy dog. 0123456789",
    },
    sizes: {
      type: Array,
      default: () => [16, 24, 32, 48, 64],
    },
  },
  watch: {
    async url(url) {
      try {
        const fontFace = new FontFace(this.family, `url(${url})`);
        await fontFace.load();
        document.fonts.add(fontFace);
      } catch (err) {
        this.$emit("error", "Font loading failed: " + err);
      }
    },
  },
  methods: {
    styleFor(size) {
      return {
        fontFamily: `${this.family}, sans-serif`,
        fontSize: size + "px",
        lineHeight: 1.3,
      };
    },
  },
};
</script>

<style lang="scss">
.k-editor-preview-fonts {
  display: grid;
  gap: 1rem;
  height: 100%;
  > div {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 0.5rem;
    > p {
      flex: 0 0 auto; /* verhindert, dass Elemente schrumpfen */
      white-space: nowrap;
    }
  }
}
</style>
