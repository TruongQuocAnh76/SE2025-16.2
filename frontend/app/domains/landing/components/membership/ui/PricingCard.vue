<template>
  <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow flex flex-col h-full">
    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ title }}</h3>
    <p class="text-sm text-gray-500 mb-4">{{ description }}</p>
    <div class="mb-4">
      <span class="text-3xl font-bold text-gray-900">${{ price }}</span>
      <span class="text-gray-500 text-sm">/month</span>
    </div>
    <ul class="space-y-2 mb-6 text-sm flex-grow">
      <li v-for="(feature, index) in features" :key="index" class="flex items-center text-gray-700">
        <svg class="w-4 h-4 text-teal-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        <span>{{ feature }}</span>
      </li>
    </ul>
    <!-- Success Message -->
    <div v-if="$parent.upgradeMessage && title === 'Premium'" class="mb-3 p-2 bg-green-100 text-green-700 text-sm rounded">
      {{ $parent.upgradeMessage }}
    </div>

    <NuxtLink 
      v-if="buttonLink"
      :to="buttonLink"
      :class="buttonClass"
      class="w-full py-2 rounded-lg font-medium transition-colors mt-auto text-center block"
    >
      {{ buttonText }}
    </NuxtLink>
    <button 
      v-else
      :class="buttonClass"
      :disabled="disabled"
      @click="onClick"
      class="w-full py-2 rounded-lg font-medium transition-colors mt-auto disabled:opacity-60"
    >
      {{ buttonText }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: 'Lorem ipsum'
  },
  price: {
    type: Number,
    required: true
  },
  features: {
    type: Array,
    required: true
  },
  buttonText: {
    type: String,
    default: 'Choose Plan'
  },
  buttonClass: {
    type: String,
    default: 'border-2 border-teal-400 text-teal-600 hover:bg-teal-50'
  },
  buttonLink: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  onClick: {
    type: Function,
    default: null
  }
})
</script>
