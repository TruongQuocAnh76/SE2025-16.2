<template>
  <div :class="logoClasses">
    <!-- Chain Link Symbol + Graduation Cap -->
    <svg
      :width="size"
      :height="size"
      viewBox="0 0 40 40"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="flex-shrink-0 transition-transform duration-200 hover:scale-105"
    >
      <!-- Blockchain Chain Links -->
      <g class="chain-links">
        <!-- First Link -->
        <path
          d="M8 12C8 10.8954 8.89543 10 10 10H14C15.1046 10 16 10.8954 16 12V16C16 17.1046 15.1046 18 14 18H10C8.89543 18 8 17.1046 8 16V12Z"
          :stroke="color"
          stroke-width="2"
          fill="none"
        />
        <!-- Second Link -->
        <path
          d="M14 18C14 19.1046 14.8954 20 16 20H20C21.1046 20 22 20.8954 22 22V26C22 27.1046 21.1046 28 20 28H16C14.8954 28 14 27.1046 14 26V22C14 20.8954 14.8954 20 16 20Z"
          :stroke="color"
          stroke-width="2"
          fill="none"
        />
        <!-- Third Link -->
        <path
          d="M22 14C22 12.8954 22.8954 12 24 12H28C29.1046 12 30 12.8954 30 14V18C30 19.1046 29.1046 20 28 20H24C22.8954 20 22 19.1046 22 18V14Z"
          :stroke="color"
          stroke-width="2"
          fill="none"
        />
      </g>
      
      <!-- Graduation Cap -->
      <g class="graduation-cap">
        <!-- Cap base -->
        <path
          d="M10 6L20 2L30 6L20 10L10 6Z"
          :fill="color"
          opacity="0.8"
        />
        <!-- Cap top -->
        <path
          d="M14 8V12C14 13.1046 16.6863 14 20 14C23.3137 14 26 13.1046 26 12V8"
          :stroke="color"
          stroke-width="1.5"
          fill="none"
        />
        <!-- Tassel -->
        <line
          x1="30"
          y1="6"
          x2="32"
          y2="4"
          :stroke="color"
          stroke-width="1.5"
        />
        <circle
          cx="32"
          cy="4"
          r="1"
          :fill="color"
        />
      </g>

      <!-- Certificate Symbol -->
      <g class="certificate" opacity="0.6">
        <rect
          x="26"
          y="24"
          width="8"
          height="6"
          rx="1"
          :stroke="color"
          stroke-width="1"
          fill="none"
        />
        <line
          x1="28"
          y1="26"
          x2="32"
          y2="26"
          :stroke="color"
          stroke-width="0.5"
        />
        <line
          x1="28"
          y1="28"
          x2="31"
          y2="28"
          :stroke="color"
          stroke-width="0.5"
        />
      </g>
    </svg>

    <!-- Text Logo -->
    <div v-if="showText" :class="textClasses">
      <span :style="{ color }" class="font-bold">Cert</span>
      <span :class="chainTextColor">chain</span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  size?: number | string
  color?: string
  showText?: boolean
  variant?: 'horizontal' | 'vertical' | 'icon-only'
  theme?: 'light' | 'dark'
}

const props = withDefaults(defineProps<Props>(), {
  size: 40,
  color: '#2563eb',
  showText: true,
  variant: 'horizontal',
  theme: 'light'
})

const logoClasses = computed(() => [
  'flex items-center select-none',
  {
    'flex-col gap-2': props.variant === 'vertical',
    'gap-3': props.variant === 'horizontal'
  }
])

const textClasses = computed(() => [
  'font-inter font-bold leading-none tracking-tight',
  {
    'text-2xl': props.variant === 'horizontal',
    'text-xl text-center': props.variant === 'vertical'
  }
])

const chainTextColor = computed(() => 
  props.theme === 'dark' ? 'text-slate-400' : 'text-slate-500'
)
</script>

<style scoped>
/* Animation for chain links */
.chain-links {
  animation: chainPulse 3s ease-in-out infinite;
}

@keyframes chainPulse {
  0%, 100% { 
    opacity: 1;
    transform: translateY(0);
  }
  50% { 
    opacity: 0.8;
    transform: translateY(-1px);
  }
}

/* Hover effects */
:deep(.certchain-logo:hover .graduation-cap) {
  animation: capBounce 0.6s ease-in-out;
}

@keyframes capBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-2px); }
}

@media (max-width: 640px) {
  :deep(.text-2xl) {
    font-size: 1.25rem;
  }
  
  :deep(.text-xl) {
    font-size: 1rem;
  }
}
</style>
