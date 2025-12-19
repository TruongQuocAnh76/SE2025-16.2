<template>
  <main class="max-w-7xl mx-auto px-4 py-16">
    <h1 class="text-4xl font-extrabold mb-6">About CertChain</h1>

    <p class="text-lg text-gray-700 mb-6">CertChain provides a secure, developer-friendly platform for issuing and verifying digital credentials. We focus on making the end-to-end certification workflow reliable, auditable and easy to integrate with existing learning systems.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-2">Our Mission</h2>
        <p class="text-gray-600">Empower organizations to mint verifiable credentials that learners can carry across platforms and employers can trust.</p>
      </div>

      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-2">Platform</h2>
        <p class="text-gray-600">Issue signed certificates, automate bulk issuance, and verify records with a lightweight REST API and public verifier endpoints.</p>
      </div>

      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-2">Partnerships</h2>
        <p class="text-gray-600">We partner with training providers and assessment platforms to simplify credential workflows and reduce verification friction.</p>
      </div>
    </div>

    <section class="mt-12">
      <h3 class="text-2xl font-bold mb-4">Why organizations choose CertChain</h3>
      <ul class="list-disc pl-6 text-gray-600 space-y-2">
        <li>Cryptographic signatures ensure certificates are tamper-evident and verifiable.</li>
        <li>Simple REST API and web hooks for seamless integration with LMS and HR systems.</li>
        <li>Privacy controls: issuers decide what metadata to share during verification.</li>
      </ul>
    </section>

    <section class="mt-12">
      <h3 class="text-2xl font-bold mb-4">Get in touch</h3>
      <p class="text-gray-600">For partnerships, integrations or enterprise licensing, reach out at <a class="text-accent-star" href="mailto:partnerships@certchain.example">partnerships@certchain.example</a>.</p>
    </section>
  </main>
</template>

<script setup lang="ts">
// Static page — no script required
</script>

<style scoped></style>
