<script>
export default {
  extends: "k-tree",
  inheritAttrs: false,
  props: {
    onlydir: {
      type: Boolean,
      default: () => false,
    },
    readPathFromUrl: {
      type: Boolean,
      default: () => false,
    },
  },
  data() {
    return {
      state: [],
      item: null,
    };
  },
  computed: {
    viewurl() {
      return this.$panel.view.url();
    },
    pathFromUrl() {
      if (this.readPathFromUrl) {
        return new URL(window.location.href).searchParams.get("path") || "/";
      }
      return null;
    },
  },
  mounted() {
    this.init();
  },
  methods: {
    async init(item) {
      if (item) {
        this.state = [];
        this.current = item.value;
      }

      if (this.items) {
        this.state = this.items;
      } else {
        const items = await this.load(null);
        this.state = items;
        this.update(item?.value);
        this.$emit("loaded");
      }
    },
    async load(path) {
      return await this.$api.get("plain/editor/tree", {
        path: path ?? "",
        current: this.current ?? this.pathFromUrl,
        onlydir: this.onlydir ? 1 : 0,
      });
    },
    loadItem(item) {
      if (item.hasChildren && Array.isArray(item.children) === false) {
        this.load(item.children).then((children) => (item.children = children));
      }

      this.current = item?.value ?? null;
    },
    update(value) {
      //Try get from query
      if (this.readPathFromUrl) {
        value ??= this.pathFromUrl;
      }
      value ??= this.current;

      const item = this.findItem(value);
      this.current = item?.value;

      this.$emit("update", item);
    },
    select(item) {
      this.current = item?.value ?? null;
      this.$emit("select", item);
    },
    async open(item) {
      if (!item) {
        return;
      }

      if (item.hasChildren === false) {
        return false;
      }

      // children have not been loaded yet
      if (typeof item.children === "string") {
        this.$set(item, "loading", true);
        item.children = await this.load(item.children);
        this.$set(item, "loading", false);
      }

      this.$set(item, "open", true);
    },
    findItem(current, parent) {
      parent ??= this.state;
      current ??= this.current;

      for (const item of parent) {
        if (item.value === current) return item;

        if (Array.isArray(item.children) && item.children.length > 0) {
          const result = this.findItem(current, item.children);
          if (result) return result;
        }
      }

      return null;
    },
    isItem(item, target) {
      return item.value === target;
    },
  },
};
</script>
