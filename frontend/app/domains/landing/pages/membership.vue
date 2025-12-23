<template>
  <div class="min-h-screen bg-white">
    <!-- Success Message -->
    <transition name="fade">
      <div v-if="showSuccessMessage" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3">
          <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="font-medium">{{ upgradeMessage }}</p>
        </div>
      </div>
    </transition>
    
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
            :disabled="plan.disabled"
            :on-click="plan.onClick"
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
import { ref, onMounted, computed } from 'vue'
import PricingCard from '../components/membership/ui/PricingCard.vue'
import BlockchainSection from '../components/membership/ui/BlockchainSection.vue'
import FAQSection from '../components/membership/ui/FAQSection.vue'
import TestimonialsCarousel from '../components/membership/ui/TestimonialsCarousel.vue'
import CourseCards from '../components/membership/ui/CourseCards.vue'

const config = useRuntimeConfig()
const router = useRouter()
const route = useRoute()

const currentUser = ref(null)
const upgrading = ref(false)
const upgradeMessage = ref('')
const showSuccessMessage = ref(false)
const isLoadingUser = ref(false)

useHead({
  title: 'Membership Plans - Certchain',
  meta: [
    { name: 'description', content: 'Choose the perfect membership plan for your learning journey. Free, Premium, and Enterprise options available.' }
  ]
})

// Fetch current user data
const fetchCurrentUser = async (showLoading = true) => {
  try {
    if (showLoading) isLoadingUser.value = true
    
    const token = useCookie('auth_token').value
    if (!token) {
      if (showLoading) isLoadingUser.value = false
      return
    }

    const user = await $fetch('/api/auth/me', {
      baseURL: config.public.backendUrl,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    currentUser.value = user
    console.log('User data loaded:', { 
      membership_tier: user.membership_tier, 
      membership_expires_at: user.membership_expires_at 
    })
  } catch (error) {
    console.error('Failed to fetch user:', error)
  } finally {
    if (showLoading) isLoadingUser.value = false
  }
}

// Check if user has active Premium membership
const isPremium = computed(() => {
  if (!currentUser.value) return false
  if (currentUser.value.membership_tier !== 'PREMIUM') return false
  
  // Check if not expired
  if (!currentUser.value.membership_expires_at) return true // No expiry = lifetime
  
  const expiryDate = new Date(currentUser.value.membership_expires_at)
  return expiryDate > new Date()
})

// Get days remaining for Premium
const daysRemaining = computed(() => {
  if (!isPremium.value || !currentUser.value?.membership_expires_at) return null
  
  const expiryDate = new Date(currentUser.value.membership_expires_at)
  const today = new Date()
  const diff = expiryDate.getTime() - today.getTime()
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
})

// Handle Premium upgrade - redirect to payment
const handleUpgradePremium = () => {
  const token = useCookie('auth_token').value
  if (!token) {
    router.push('/login?redirect=/membership')
    return
  }

  // Redirect to payment page for Premium membership - use window.location.href for clean navigation
  window.location.href = '/payment?type=MEMBERSHIP'
}

const pricingPlans = computed(() => [
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
    description: isPremium.value 
      ? `✨ Premium Active - ${daysRemaining.value} days remaining` 
      : 'Best for serious learners',
    price: 24,
    features: [
      'All free features',
      'Unlimited premium courses (FREE!)',
      'Priority email support',
      'Advanced analytics dashboard',
      'Downloadable course materials',
      'Certificate customization'
    ],
    buttonText: isLoadingUser.value 
      ? '⏳ Updating...' 
      : (isPremium.value ? `✓ Premium Active (${daysRemaining.value} days left)` : 'Upgrade Now'),
    buttonClass: (isPremium.value || isLoadingUser.value)
      ? 'bg-gradient-to-r from-green-500 to-green-600 text-white cursor-not-allowed opacity-75' 
      : 'bg-teal-400 text-white hover:bg-teal-500',
    buttonLink: '', // Will use click handler
    disabled: isPremium.value || isLoadingUser.value,
    onClick: (isPremium.value || isLoadingUser.value) ? null : handleUpgradePremium
  }
])

onMounted(async () => {
  await fetchCurrentUser()
  
  // Check if redirected from successful payment
  if (route.query.payment_success === 'true') {
    showSuccessMessage.value = true
    upgradeMessage.value = '🎉 Premium membership activated successfully! Enjoy unlimited access to all courses.'
    
    // Remove query param from URL
    router.replace({ query: {} })
    
    // Fetch user data multiple times to ensure we get the updated status
    const maxRetries = 5
    let retryCount = 0
    
    const fetchWithRetry = async () => {
      await fetchCurrentUser(false)
      retryCount++
      
      // If still not Premium and haven't reached max retries, try again
      if (!isPremium.value && retryCount < maxRetries) {
        setTimeout(fetchWithRetry, 500) // Retry every 500ms
      }
    }
    
    // Start fetching immediately
    setTimeout(fetchWithRetry, 500)
    
    // Hide message after 5 seconds
    setTimeout(() => {
      showSuccessMessage.value = false
      upgradeMessage.value = ''
    }, 5000)
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: all 0.5s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translate(-50%, -20px);
}

.fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
}
</style>
