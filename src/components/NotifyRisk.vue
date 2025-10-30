<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    size="large"
    :submit-button="$t('plain.editor.notify.risk.submit')"
    @cancel="$emit('cancel')"
    @submit="$emit('submit')"
  >
    <slot>
      <div class="k-editor-notify-risk">
        <k-icon theme="negative" type="alert" />
        <p>{{ $t("plain.editor.notify.risk.title") }}</p>
        <i>{{ $t("plain.editor.notify.risk.desc") }}</i>
      </div>
    </slot>
  </k-dialog>
</template>

<script>
export default {
  extends: "k-dialog",
};
</script>

<style lang="scss">
.k-editor-notify-risk {
  text-align: center;
  padding-bottom: var(--dialog-padding);
  p {
    font-size: 1.5rem;
    line-height: 1;
    padding-bottom: 1rem;
  }
  svg {
    color: var(--color-red);
    width: 3rem;
    height: 3rem;
    margin: 1rem auto;
  }
}
</style>
