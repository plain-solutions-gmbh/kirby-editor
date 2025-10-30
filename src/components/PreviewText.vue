<template>
  <MonacoEditor
    ref="monaco"
    v-model="value"
    :options="options"
    :language="lang"
    :theme="theme === 'dark' ? 'vs-dark' : 'vs'"
    style="height: 100%"
    @change="input()"
  />
</template>

<script>
import MonacoEditor, {
  loader as MonacoLoader,
} from "@guolao/vue-monaco-editor";

export default {
  components: {
    MonacoEditor,
  },
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
  created() {
    MonacoLoader.config({
      paths: {
        vs: this.$panel.urls.site + "/media/plugins/plain/editor/vs",
      },
    });
  },
  methods: {
    input() {
      //Wait half second before update
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(
        () => this.$emit("update", this.value),
        500
      );
    },
  },
};
</script>
