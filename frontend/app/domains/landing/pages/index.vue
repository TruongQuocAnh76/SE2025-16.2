<template>
  <div>
    <HeroSection/>
    <OverviewSection/>
    <FeaturesSection/>
    <TestimonialsSection/>
    
  </div>
</template>

<script setup>
import HeroSection from '../components/ui/HeroSection.vue';
import TestimonialsSection from '../components/ui/TestimonialsSection.vue';
import PricingSection from '../components/ui/PricingSection.vue';
import OverviewSection from '../components/ui/OverviewSection.vue';
import FeaturesSection from '../components/ui/FeaturesSection.vue';
    
// Landing page
useHead({
  title: 'Certchain - Blockchain-Verified Learning Platform',
  meta: [
    { name: 'description', content: 'Learn, earn and verify certificates on blockchain. Secure, transparent, and trusted certificate verification.' }
  ]
})
</script>
