<template>
  <div v-bind="$attrs" :class="['shadcn-card', 'rounded-md', 'shadow-sm', $attrs.class]">
    <slot />
  </div>
</template>

<script setup>
// Minimal Card wrapper to standardize card usage across the app
</script>
