<template>
  <div>
    <HeroSection/>
    <HowItWorksSection/>
    <HowItWorksProcessSection/>
    <TestimonialsSection/>
    <PricingSection/>
    
    <!-- Call to Action Section -->
    <section class="py-12">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <!-- Section Header -->
        <h2 class="text-h2 text-neutral-0 mb-4 font-inter">
          Ready to Start Learning?
        </h2>
        <p class="text-body text-neutral-100 mb-8 font-inter">
          Join thousands of learners earning verified certificates today.
        </p>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <button class="bg-neutral-0 text-primary-500 px-8 py-4 rounded-xl font-inter font-medium hover:bg-neutral-50 transition-all duration-200 shadow-lg hover:shadow-xl">
            Create Account
          </button>
          <button class="bg-transparent border-2 border-neutral-0 text-neutral-0 px-8 py-4 rounded-xl font-inter font-medium hover:bg-neutral-0 hover:text-primary-500 transition-all duration-200">
            Browse Courses
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import HeroSection from '../components/ui/HeroSection.vue';
import HowItWorksSection from '../components/ui/WhyChoose.vue';
import HowItWorksProcessSection from '../components/ui/HowItWorksProcessSection.vue';
import TestimonialsSection from '../components/ui/TestimonialsSection.vue';
import PricingSection from '../components/ui/PricingSection.vue';
    
// Landing page
useHead({
  title: 'Certchain - Blockchain-Verified Learning Platform',
  meta: [
    { name: 'description', content: 'Learn, earn and verify certificates on blockchain. Secure, transparent, and trusted certificate verification.' }
  ]
})
</script>
