<template>
  <input
    v-bind="$attrs"
    :class="computedClass"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ variant: { type: String, default: 'default' }, size: { type: String, default: 'md' }, modelValue: { type: [String, Number], default: '' } })
const emit = defineEmits(['update:modelValue'])

const computedClass = computed(() => {
  const base = 'rounded-md border px-3 py-2 transition focus:ring-1'
  const sizes = { sm: 'text-xs', md: 'text-sm', lg: 'text-base' }
  return ['shadcn-input', base, sizes[props.size] || sizes.md].join(' ')
})
</script>
