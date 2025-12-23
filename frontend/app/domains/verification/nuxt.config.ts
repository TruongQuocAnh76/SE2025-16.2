// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  // Verification domain mini app configuration
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  components: [
    { path: './components', pathPrefix: false },
    { path: '../base/components', pathPrefix: false }
  ],
  runtimeConfig: {
    public: {
      backendUrl: process.env.BACKEND_URL,
      awsEndpoint: process.env.AWS_ENDPOINT,
      awsBucket: process.env.AWS_BUCKET || 'certchain-dev',
      stripePublishableKey: process.env.STRIPE_PUBLISHABLE_KEY || ''
    }
  },
  devServer: {
    host: '0.0.0.0',
    port: 3005
  },
  vite: {
    server: {
      hmr: {
        host: '0.0.0.0'
      }
    }
  },
  compatibilityDate: '2025-10-18'
}) 