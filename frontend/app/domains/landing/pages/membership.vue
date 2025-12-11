<template>
  <div class="min-h-screen bg-white">
    <!-- Header Section -->
    <section class="bg-gradient-to-b from-cyan-50 to-white py-20">
      <div class="container mx-auto px-4">
        <h1 class="text-5xl font-bold text-center mb-4 text-[#4FD1C5]">
          Affordable pricing
        </h1>
      </div>
    </section>

    <!-- Pricing Cards -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          <PricingCard 
            v-for="plan in pricingPlans" 
            :key="plan.title"
            :title="plan.title"
            :description="plan.description"
            :price="plan.price"
            :features="plan.features"
            :button-text="plan.buttonText"
            :button-class="plan.buttonClass"
            :button-link="plan.buttonLink"
          />
        </div>
      </div>
    </section>

    <!-- Blockchain Certificates Section -->
    <BlockchainSection />

    <!-- FAQ Section -->
    <FAQSection />

    <!-- Testimonials Section -->
    <TestimonialsCarousel />


    <!-- Course Cards Section -->
    <CourseCards />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PricingCard from '../components/membership/ui/PricingCard.vue'
import BlockchainSection from '../components/membership/ui/BlockchainSection.vue'
import FAQSection from '../components/membership/ui/FAQSection.vue'
import TestimonialsCarousel from '../components/membership/ui/TestimonialsCarousel.vue'
import CourseCards from '../components/membership/ui/CourseCards.vue'

useHead({
  title: 'Membership Plans - Certchain',
  meta: [
    { name: 'description', content: 'Choose the perfect membership plan for your learning journey. Free, Premium, and Enterprise options available.' }
  ]
})

const pricingPlans = ref([
  {
    title: 'Free',
    description: 'Perfect for getting started',
    price: 0,
    features: [
      'Access to free courses',
      'Basic blockchain certificates',
      'Community forum access',
      'Course completion tracking'
    ],
    buttonText: 'Get Started',
    buttonClass: 'border-2 border-teal-400 text-teal-600 hover:bg-teal-50',
    buttonLink: '/courses'
  },
  {
    title: 'Premium',
    description: 'Best for serious learners',
    price: 24,
    features: [
      'All free features',
      'Unlimited premium courses',
      'Priority email support',
      'Advanced analytics dashboard',
      'Downloadable course materials',
      'Certificate customization'
    ],
    buttonText: 'Upgrade Now',
    buttonClass: 'bg-teal-400 text-white hover:bg-teal-500',
    buttonLink: '/payment' // TODO: Design payment page later
  }
])
</script>
