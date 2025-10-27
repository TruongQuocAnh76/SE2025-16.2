<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Simple header -->
     <TopBar/>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Simple footer -->
     <Footer/>
  </div>
</template>

<script setup lang="ts">
// Explicitly import components from base layer
import TopBar from '../../../base/components/ui/TopBar.vue'
import Footer from '../../../base/components/ui/Footer.vue'
</script>