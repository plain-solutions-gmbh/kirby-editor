<!-- eslint-disable vue/no-v-html -->
<template>
  <div
    name="markdown"
    class="k-editor-preview-markdown"
    :theme="theme"
    v-html="compiled"
  ></div>
</template>

<script>
import { marked } from "marked";

export default {
  props: {
    content: { type: String, default: () => null },
    draft: { type: String, default: () => null },
  },
  //Maybe watch
  computed: {
    compiled() {
      return marked.parse(this.draft ?? this.content);
    },
    theme() {
      return this.$panel.theme?.current ?? "";
    },
  },
};
</script>

<style>
.k-editor-preview-markdown {
  /* Light Mode Variablen */
  --bg: #ffffff;
  --text: #222222;
  --heading: #111111;
  --muted: #555555;
  --code-bg: #f5f5f5;
  --code-text: #d6336c;
  --blockquote-bg: #fafafa;
  --blockquote-border: #ddd;

  background: var(--bg);
  color: var(--text);
  line-height: 1.65;
  font-size: 1rem;
  padding: 1.5rem;
  overflow-x: auto;
  transition: background 0.3s, color 0.3s;
}

/* Dark Mode Variablen */
.k-editor-preview-markdown[theme="dark"] {
  --bg: #1e1e1e;
  --text: #e0e0e0;
  --heading: #ffffff;
  --muted: #999999;
  --code-bg: #2b2b2b;
  --code-text: #ffb454;
  --blockquote-bg: #2a2a2a;
  --blockquote-border: #444;
}

/* Headings */
.k-editor-preview-markdown h1,
.k-editor-preview-markdown h2,
.k-editor-preview-markdown h3,
.k-editor-preview-markdown h4,
.k-editor-preview-markdown h5,
.k-editor-preview-markdown h6 {
  font-weight: 600;
  margin: 1.4em 0 0.6em;
  color: var(--heading);
  line-height: 1.25;
}

.k-editor-preview-markdown h1 {
  font-size: 2rem;
  border-bottom: 2px solid var(--blockquote-border);
  padding-bottom: 0.3em;
}
.k-editor-preview-markdown h2 {
  font-size: 1.6rem;
  border-bottom: 1px solid var(--blockquote-border);
  padding-bottom: 0.2em;
}
.k-editor-preview-markdown h3 {
  font-size: 1.3rem;
}

/* Paragraphs */
.k-editor-preview-markdown p {
  margin: 0.8em 0;
}

/* Links */
.k-editor-preview-markdown a {
  color: #3b82f6;
  text-decoration: none;
}
.k-editor-preview-markdown a:hover {
  text-decoration: underline;
}

/* Inline code */
.k-editor-preview-markdown code {
  background: var(--code-bg);
  color: var(--code-text);
  padding: 0.2em 0.4em;
  border-radius: 6px;
  font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
  font-size: 0.9em;
}

/* Code blocks */
.k-editor-preview-markdown pre code {
  display: block;
  padding: 1em;
  border-radius: 8px;
  overflow-x: auto;
  font-size: 0.9em;
  line-height: 1.4;
}

/* Lists */
.k-editor-preview-markdown ul,
.k-editor-preview-markdown ol {
  padding-left: 1.5em;
  margin: 0.8em 0;
}

.k-editor-preview-markdown li {
  margin: 0.3em 0;
}

/* Blockquote */
.k-editor-preview-markdown blockquote {
  border-left: 4px solid var(--blockquote-border);
  background: var(--blockquote-bg);
  padding: 0.8em 1em;
  margin: 1em 0;
  border-radius: 6px;
  color: var(--muted);
}

/* Tables */
.k-editor-preview-markdown table {
  width: 100%;
  border-collapse: collapse;
  margin: 1em 0;
  font-size: 0.95em;
}
.k-editor-preview-markdown th,
.k-editor-preview-markdown td {
  border: 1px solid var(--blockquote-border);
  padding: 0.5em 0.8em;
}
.k-editor-preview-markdown th {
  background: var(--blockquote-bg);
  text-align: left;
}
</style>
