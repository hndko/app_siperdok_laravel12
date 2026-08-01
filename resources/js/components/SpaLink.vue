<template>
  <RouterLink :to="href" custom v-slot="{ href: resolvedHref, navigate, isActive }">
    <a
      :href="resolvedHref"
      :class="[{ active: isActive }, $attrs.class]"
      @click="navigate"
    >
      <slot></slot>
    </a>
  </RouterLink>
</template>

<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
  href: { type: String, required: true },
});
</script>
