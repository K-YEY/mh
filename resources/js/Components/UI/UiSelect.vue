<template>
  <select
    v-bind="$attrs"
    :class="['shadcn-input', $attrs.class]"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
  >
    <slot />
  </select>
</template>

<script setup>
const props = defineProps({ modelValue: { type: [String, Number], default: '' } })
const emit = defineEmits(['update:modelValue'])
</script>
