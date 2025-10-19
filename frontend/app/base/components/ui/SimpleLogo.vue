<template>
  <div :class="logoClasses">
    <!-- Simple Chain + C Symbol -->
    <svg
      :width="size"
      :height="size"
      viewBox="0 0 32 32"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="flex-shrink-0 transition-transform duration-200 hover:rotate-12 hover:scale-105"
    >
      <!-- Letter C with chain pattern -->
      <path
        d="M16 4C21.5228 4 26 8.47715 26 14C26 19.5228 21.5228 24 16 24"
        :stroke="primaryColor"
        stroke-width="3"
        stroke-linecap="round"
        fill="none"
      />
      
      <!-- Chain links integrated into C -->
      <circle
        cx="12"
        cy="10"
        r="2"
        :stroke="secondaryColor"
        stroke-width="2"
        fill="none"
      />
      <circle
        cx="8"
        cy="16"
        r="2"
        :stroke="secondaryColor"
        stroke-width="2"
        fill="none"
      />
      <circle
        cx="12"
        cy="22"
        r="2"
        :stroke="secondaryColor"
        stroke-width="2"
        fill="none"
      />
      
      <!-- Connecting lines -->
      <line
        x1="10"
        y1="12"
        x2="8"
        y2="14"
        :stroke="secondaryColor"
        stroke-width="1.5"
      />
      <line
        x1="8"
        y1="18"
        x2="10"
        y2="20"
        :stroke="secondaryColor"
        stroke-width="1.5"
      />
    </svg>

    <!-- Text Logo -->
    <div v-if="showText" :class="textClasses">
      <span>{{ brandName }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  size?: number | string
  primaryColor?: string
  secondaryColor?: string
  showText?: boolean
  brandName?: string
  variant?: 'horizontal' | 'vertical' | 'icon-only'
}

const props = withDefaults(defineProps<Props>(), {
  size: 32,
  primaryColor: '#2563eb',
  secondaryColor: '#64748b',
  showText: true,
  brandName: 'Certchain',
  variant: 'horizontal'
})

const logoClasses = computed(() => [
  'flex items-center select-none',
  {
    'flex-col gap-1': props.variant === 'vertical',
    'gap-2': props.variant === 'horizontal'
  }
])

const textClasses = computed(() => [
  'font-inter font-semibold leading-none text-gray-800',
  {
    'text-xl': props.variant === 'horizontal',
    'text-base': props.variant === 'vertical'
  }
])
</script>

<style scoped>
/* Subtle animation */
:deep(svg circle) {
  animation: linkFloat 2s ease-in-out infinite;
}

:deep(svg circle:nth-child(2)) {
  animation-delay: 0.3s;
}

:deep(svg circle:nth-child(3)) {
  animation-delay: 0.6s;
}

@keyframes linkFloat {
  0%, 100% { 
    opacity: 1;
    transform: scale(1);
  }
  50% { 
    opacity: 0.7;
    transform: scale(1.1);
  }
}
</style>
