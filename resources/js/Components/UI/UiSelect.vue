<template>
  <select v-bind="$attrs" :class="['shadcn-input', $attrs.class]">
    <slot />
  </select>
</template>

<script setup>
// Simple select wrapper to keep class usage consistent
</script>
