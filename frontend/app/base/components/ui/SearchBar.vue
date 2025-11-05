<template>
  <div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <svg
        class="w-5 h-5 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        />
      </svg>
    </div>
    <input
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="inputClasses"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      @keydown.enter="$emit('search', modelValue)"
      type="text"
    />
    <button
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-medium text-blue-600 hover:underline"
      :disabled="disabled"
      @click="goToCourses"
    >
      Courses
    </button>
  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue?: string
  placeholder?: string
  disabled?: boolean
  size?: 'sm' | 'md' | 'lg'
  variant?: 'default' | 'outline'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Search...',
  disabled: false,
  size: 'md',
  variant: 'default'
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'search': [query: string]
}>()

const router = useRouter()

function goToCourses() {
  if (props.disabled) return
  router.push('/courses')
}

const inputClasses = computed(() => {
  const baseClasses = [
    'w-full',
    'border',
    'rounded-lg',
    'focus:outline-none',
    'focus:ring-2',
    'transition-colors',
    'pl-10', // Left padding for the icon
    'pr-4'   // Right padding
  ]

  // Size classes
  const sizeClasses = {
    sm: ['py-2', 'text-sm'],
    md: ['py-3', 'text-base'],
    lg: ['py-4', 'text-lg']
  }

  // Variant classes
  const variantClasses = {
    default: [
      'border-gray-300',
      'bg-white',
      'text-gray-900',
      'placeholder-gray-500',
      'focus:border-blue-500',
      'focus:ring-blue-500'
    ],
    outline: [
      'border-gray-400',
      'bg-transparent',
      'text-gray-700',
      'placeholder-gray-400',
      'focus:border-blue-500',
      'focus:ring-blue-500'
    ]
  }

  // Disabled classes
  const disabledClasses = props.disabled ? [
    'bg-gray-100',
    'text-gray-500',
    'cursor-not-allowed',
    'border-gray-200'
  ] : []

  return [
    ...baseClasses,
    ...sizeClasses[props.size],
    ...variantClasses[props.variant],
    ...disabledClasses
  ].join(' ')
})
</script>
