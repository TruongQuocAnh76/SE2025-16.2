<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
if (import.meta.client) {
  // Facebook appends #_=_ to the return URL, which causes Vue Router to throw a warning
  // and can break scrolling/routing. We need to remove it immediately.
  if (window.location.hash === '#_=_') {
    history.replaceState(
      null, 
      '', 
      window.location.href.split('#')[0]
    )
  }
}
</script>
