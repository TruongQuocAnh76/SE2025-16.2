<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Simple header -->
     <TopBar/>

    <!-- Main Content -->
    <main class="flex-1 pt-16">
      <slot />
    </main>

    <!-- Simple footer -->
     <Footer/>

    <!-- Toast Notifications -->
    <Toast />

    <!-- AI Chatbot - Always visible in bottom right corner -->
    <AIChatbot />
  </div>
</template>

<script setup lang="ts">
// Explicitly import components from base layer
import TopBar from '../components/ui/TopBar.vue'
import Footer from '../components/ui/Footer.vue'
import Toast from '../components/ui/Toast.vue'
import AIChatbot from '~/components/AIChatbot.vue'
</script>