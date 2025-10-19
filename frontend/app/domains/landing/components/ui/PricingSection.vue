<template>
  <section class="bg-neutral-50 py-12">
    <div class="max-w-7xl mx-auto px-6">
      <!-- Section Header -->
      <div class="text-center mb-12">
        <h2 class="text-h2 text-neutral-900 mb-4 font-inter">
          Choose Your Learning Path
        </h2>
        <p class="text-body text-neutral-600 max-w-3xl mx-auto font-inter">
          Start free or unlock premium features to accelerate your blockchain education journey.
        </p>
      </div>

      <!-- Pricing Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Pricing Card -->
        <div 
          v-for="tier in pricingTiers" 
          :key="tier.id"
          class="bg-neutral-0 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 relative"
          :class="{ 'border-2 border-primary-500 transform scale-105': tier.featured }"
        >
          <!-- Featured Badge -->
          <div 
            v-if="tier.featured" 
            class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-primary-500 text-neutral-0 px-6 py-2 rounded-full text-body-sm font-inter font-medium"
          >
            Most Popular
          </div>

          <!-- Plan Name -->
          <div class="text-center mb-6">
            <h3 class="text-h3 text-neutral-900 mb-2 font-inter">
              {{ tier.name }}
            </h3>
            <div class="mb-4">
              <span class="text-h1 text-neutral-900 font-inter font-bold">
                {{ tier.price }}
              </span>
              <span v-if="tier.period" class="text-body text-neutral-600 font-inter">
                /{{ tier.period }}
              </span>
            </div>
            <p class="text-body text-neutral-600 font-inter">
              {{ tier.description }}
            </p>
          </div>

          <!-- Features List -->
          <div class="mb-8">
            <ul class="space-y-4">
              <li 
                v-for="feature in tier.features" 
                :key="feature"
                class="flex items-start space-x-3"
              >
                <svg class="w-5 h-5 text-success-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="text-body text-neutral-700 font-inter">
                  {{ feature }}
                </span>
              </li>
            </ul>
          </div>

          <!-- CTA Button -->
          <div class="text-center">
            <button 
              class="w-full py-4 px-6 rounded-xl font-inter font-medium transition-all duration-200"
              :class="tier.featured 
                ? 'bg-primary-500 text-neutral-0 hover:bg-primary-600 shadow-lg hover:shadow-xl' 
                : 'bg-neutral-100 text-neutral-900 hover:bg-neutral-200'"
            >
              {{ tier.cta }}
            </button>
          </div>
        </div>
      </div>

      <!-- Additional Info -->
      <div class="text-center mt-12">
        <p class="text-body-sm text-neutral-600 font-inter">
          All plans include blockchain-verified certificates and lifetime access to completed courses.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Pricing tiers data
const pricingTiers = [
  {
    id: 1,
    name: "Student",
    price: "Free",
    period: null,
    description: "Start learning today",
    featured: false,
    features: [
      "Access to free courses",
      "Blockchain-verified certificates",
      "Course progress tracking",
      "Community access"
    ],
    cta: "Get Started Free"
  },
  {
    id: 2,
    name: "Pro Learner",
    price: "$29",
    period: "month",
    description: "For serious learners",
    featured: true,
    features: [
      "Access to all courses",
      "Unlimited certificates",
      "Priority support",
      "Downloadable resources",
      "Course recommendations"
    ],
    cta: "Start Free Trial"
  },
  {
    id: 3,
    name: "Educator",
    price: "$99",
    period: "month",
    description: "Teach and earn",
    featured: false,
    features: [
      "Create unlimited courses",
      "Student analytics",
      "Revenue sharing (70%)",
      "Marketing support",
      "Advanced course tools"
    ],
    cta: "Start Teaching"
  }
]
</script>
