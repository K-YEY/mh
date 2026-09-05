<template>
  <button
    v-bind="$attrs"
    :class="computedClass"
    :disabled="$attrs.disabled"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ variant: { type: String, default: 'primary' }, size: { type: String, default: 'md' } })

const computedClass = computed(() => {
  const base = 'inline-flex items-center justify-center rounded-md font-medium transition-colors'
  const variants = {
    primary: 'shadcn-btn-primary',
    secondary: 'shadcn-btn-secondary',
    ghost: 'shadcn-btn-ghost',
    outline: 'shadcn-btn-outline',
  }
  const sizes = {
    sm: 'h-7 px-2.5 text-xs',
    md: 'h-10 px-4 text-sm',
    lg: 'h-12 px-6 text-base',
  }
  return [base, variants[props.variant] || variants.primary, sizes[props.size] || sizes.md].join(' ')
})
</script>
