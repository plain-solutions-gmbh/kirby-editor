<template>
  <div class="k-editor-preview-text"></div>
</template>

<script>
import loader from "@monaco-editor/loader";

export default {
  props: {
    content: { type: String, default: () => null },
    draft: { type: String, default: () => null },
    item: { type: Object, default: () => null },
  },
  data() {
    return {
      value: this.draft ?? this.content,
      debounceTimer: null,
    };
  },
  computed: {
    lang() {
      return this.item?.language ?? this.item.mime.split("/").pop();
    },
    options() {
      return this.item?.options ?? {};
    },
    theme() {
      return this.$panel.theme?.current ?? "";
    },
  },
  watch: {
    content() {
      this.value = this.draft ?? this.content;
    },
    draft(draft) {
      if (draft === null) {
        this.value = this.content;
      }
    },
  },
  mounted() {
    //Loading external source to format language
    loader.config({
      paths: {
        vs: this.$panel.urls.site + "/media/plugins/plain/editor/vs",
      },
    });

    loader.init().then((monaco) => {
      //Insert Monaco editor instance
      const editor = monaco.editor.create(this.$el, {
        value: this.value,
        language: this.lang,
        theme: this.theme === "dark" ? "vs-dark" : "vs",
        automaticLayout: true,
        ...this.options,
      });

      //Add change listener
      editor.getModel().onDidChangeContent(() => {
        this.value = editor.getValue();
        this.input();
      });
    });
  },
  methods: {
    input() {
      //Wait half second before update
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(
        () => this.$emit("update", this.value),
        500,
      );
    },
  },
};
</script>

<style lang="scss">
.k-editor-preview-text {
  width: 100%;
  height: 100%;
}
</style>
