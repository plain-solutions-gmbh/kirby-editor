<template>
  <k-field
    name="filetree"
    type="filetree"
    :style="{ height: height }"
    v-bind="$props"
  >
    <k-file-tree
      ref="filetree"
      :onlydir="onlydir"
      :current="value"
      :read-path-from-url="readPathFromUrl"
      @loaded="loading = false"
      @update="$emit('update', $event)"
      @select="input($event)"
    />
    <div v-if="loading" class="k-tree-loader">
      <k-icon type="loader"></k-icon>
    </div>
  </k-field>
</template>

<script>
export default {
  extend: "k-field",
  props: {
    height: {
      type: String,
      default: "20rem",
    },
    onlydir: {
      type: Boolean,
      default: false,
    },
    required: {
      type: Boolean,
      default: () => false,
    },
    value: {
      type: String,
      default: () => null,
    },
    save: {
      type: String,
      default: () => "value",
    },
    readPathFromUrl: {
      type: Boolean,
      default: () => false,
    },
  },
  data() {
    return {
      loading: true,
    };
  },
  methods: {
    init(reset) {
      this.$refs.filetree.init(reset);
    },
    input(item) {
      //Diselect
      if (this.required === false && this.value === item.value) {
        item = null;
      }

      this.$emit("input", this.save ? item?.[this.save] : item);
    },
    select(item) {
      this.$refs.filetree.select(item);
    },
    update(value) {
      this.$refs.filetree.update(value);
    },
  },
};
</script>

<style lang="scss">
.k-tree-loader {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.k-field-type-filetree {
  position: relative;
  overflow-y: hidden;
  overflow: auto;
  padding: var(--spacing-1);
  --tree-branch-color-back: var(--item-color-back);
  background: var(--item-color-back);
}
</style>
