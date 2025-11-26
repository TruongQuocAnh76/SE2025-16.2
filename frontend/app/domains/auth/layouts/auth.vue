<template>
  <div class="min-h-screen bg-gray-50">
    <main class="flex-1">
      <slot />
    </main>

    <!-- Toast Notifications -->
    <Toast />
  </div>
</template>
<script lang="ts" setup>
import Toast from '~/base/components/ui/Toast.vue'
</script>

<style>

</style>