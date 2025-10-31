<template>
  <section class="py-16 bg-white">
    <div class="container mx-auto px-4 max-w-3xl">
      <h2 class="text-3xl font-bold text-center mb-12">
        Frequently asked questions about CertChain
      </h2>
      <div class="space-y-3">
        <div v-for="(faq, index) in faqs" :key="index" class="border border-gray-200 rounded-lg">
          <button 
            @click="toggleFaq(index)"
            class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-colors"
          >
            <span class="font-medium text-left">{{ faq.question }}</span>
            <svg 
              class="w-5 h-5 transition-transform" 
              :class="{ 'rotate-180': faq.open }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div v-if="faq.open" class="px-6 pb-4 text-gray-600">
            {{ faq.answer }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const faqs = ref([
  {
    question: 'What is CertChain?',
    answer: 'CertChain is a blockchain-based learning platform that provides verified certificates for completed courses.',
    open: false
  },
  {
    question: 'How do blockchain certificates work?',
    answer: 'Each certificate is recorded on the blockchain, making it permanently verifiable and tamper-proof.',
    open: false
  },
  {
    question: 'Can I change my plan later?',
    answer: 'Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.',
    open: false
  },
  {
    question: 'What payment methods do you accept?',
    answer: 'We accept all major credit cards, PayPal, and cryptocurrency payments.',
    open: false
  }
])

const toggleFaq = (index) => {
  faqs.value[index].open = !faqs.value[index].open
}
</script>
