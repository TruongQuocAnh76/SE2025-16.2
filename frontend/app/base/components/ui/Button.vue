<template>
  <button
    :class="buttonClasses"
    :disabled="disabled"
    @click="handleClick"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'transparent' | 'outline'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
}

interface Emits {
  (e: 'click', event: Event): void
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false
})

const emit = defineEmits<Emits>()

const buttonClasses = computed(() => [
  'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
  {
    // Primary variant
    'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500': props.variant === 'primary',

    // Secondary variant
    'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-500': props.variant === 'secondary',

    // Transparent variant (main request)
    'bg-transparent text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:ring-gray-500': props.variant === 'transparent',

    // Outline variant
    'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-500': props.variant === 'outline',

    // Size variants
    'px-3 py-1.5 text-sm': props.size === 'sm',
    'px-4 py-2 text-base': props.size === 'md',
    'px-6 py-3 text-lg': props.size === 'lg',
  }
])

const handleClick = (event: Event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>
