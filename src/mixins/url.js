export default {
  data() {
    return {
      paramValue: null,
    };
  },
  mounted() {
    window.addEventListener("refreshUrl", this.popState);
    this.refreshUrl();
  },
  beforeDestroy() {
    window.removeEventListener("refreshUrl", this.popState);
  },
  methods: {
    refreshUrl() {
      const value = this.$panel.view.url().searchParams.get(this.name);

      if (value !== this.paramValue) {
        this.paramValue = value;
      }
    },
    updateParam(value) {
      // Get URL params
      const url = this.$panel.view.url();
      const searchParams = url.searchParams;

      if (value === null || value === undefined || value === "") {
        // Remove Param
        searchParams.delete(this.name);
      } else {
        // Set/Replace param
        searchParams.set(this.name, value);
      }

      const paramString = searchParams.toString()
        ? "?" + searchParams.toString()
        : "";

      const newUrl =
        url.pathname + (paramString ? "?" + paramString : "") + url.hash;

      window.history.pushState({}, "", newUrl);
    },
  },
};
