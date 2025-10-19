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
</script>
