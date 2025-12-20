<template>
  <main class="max-w-7xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold mb-6">Careers</h1>
    <p class="text-gray-700 mb-6">Join a small, product-focused team building secure credentialing infrastructure used by educators and employers. We prioritize impact, reliability and developer experience.</p>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="text-xl font-semibold">Frontend Engineer</h3>
        <p class="text-gray-600 mt-2">Build user-facing features, improve developer tooling, and collaborate with design to ship polished UIs. Experience with Vue/Nuxt and TypeScript preferred.</p>
        <ul class="mt-3 list-disc pl-6 text-gray-600">
          <li>Work with Nuxt 3 and TypeScript</li>
          <li>Own features end-to-end, from design to deployment</li>
          <li>Collaborate with backend engineers on API design</li>
        </ul>
        <div class="mt-4">
          <a class="inline-block px-4 py-2 bg-accent-star text-text-dark rounded" href="mailto:careers@certchain.example?subject=Frontend%20Engineer%20Application">Apply</a>
        </div>
      </div>

      <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="text-xl font-semibold">Backend Engineer</h3>
        <p class="text-gray-600 mt-2">Design and maintain APIs, scale background jobs, and own security-critical systems. Experience with PHP/Laravel or Node.js is a plus.</p>
        <ul class="mt-3 list-disc pl-6 text-gray-600">
          <li>Design resilient APIs and background workers</li>
          <li>Improve observability and performance</li>
          <li>Contribute to security and privacy practices</li>
        </ul>
        <div class="mt-4">
          <a class="inline-block px-4 py-2 bg-accent-star text-text-dark rounded" href="mailto:careers@certchain.example?subject=Backend%20Engineer%20Application">Apply</a>
        </div>
      </div>
    </section>

    <section class="mt-12">
      <h3 class="text-2xl font-bold mb-4">Why work with us</h3>
      <ul class="list-disc pl-6 text-gray-600 space-y-2">
        <li>Remote-first, flexible schedule.</li>
        <li>Competitive compensation and learning budget.</li>
        <li>Opportunity to shape product and infrastructure from the ground up.</li>
      </ul>
    </section>
  </main>
</template>

<script setup lang="ts">
// Static page
</script>

<style scoped></style>
