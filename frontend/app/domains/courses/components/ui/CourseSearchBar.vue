<template>
  <div
    class="relative bg-cover bg-center bg-no-repeat min-h-[400px] flex items-center justify-center"
    style="background-image: url('/hero.png')"
  >
    <!-- Overlay for better text readability -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- Content -->
    <div class="relative z-10 text-center px-4 max-w-2xl mx-auto p-6">
      <!-- Search Bar -->
      <div class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search for courses..."
          class="flex-1 px-6 py-3 rounded-lg border-0 focus:ring-2 focus:ring-brand-primary focus:outline-none text-gray-900 placeholder-gray-500"
          @keyup.enter="handleSearch"
        />
        <button
          @click="handleSearch"
          class="px-8 py-3 bg-brand-primary hover:bg-brand-secondary text-white font-semibold rounded-lg transition-colors duration-200 focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 focus:outline-none"
        >
          Search
        </button>
      </div>

      <!-- Popular Searches -->
      <div class="mt-6">
        <p class="text-gray-300 text-sm mb-2">Popular searches:</p>
        <div class="flex flex-wrap justify-center gap-2">
          <button
            v-for="term in popularSearches"
            :key="term"
            @click="searchQuery = term; handleSearch()"
            class="px-3 py-1 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm rounded-full transition-colors duration-200"
          >
            {{ term }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const searchQuery = ref('')
const popularSearches = [
  'JavaScript',
  'Python',
  'React',
  'Machine Learning',
  'Web Development',
  'Data Science'
]

const emit = defineEmits<{
  search: [query: string]
}>()

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    emit('search', searchQuery.value.trim())
  }
}

// Page meta
definePageMeta({
  layout: 'default'
})
</script>

<style scoped>
/* Additional custom styles if needed */
</style>