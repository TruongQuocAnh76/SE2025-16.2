export default defineNuxtConfig({
  extends: [
    './app/domains/landing',
    './app/domains/auth',
    './app/domains/courses',
    './app/base'
  ],

  modules: ['@nuxtjs/tailwindcss'],

  runtimeConfig: {
    public: {
      backendUrl: process.env.BACKEND_URL || 'http://localhost:8000',
      awsEndpoint: process.env.AWS_ENDPOINT || 'http://localhost:4566',
      awsBucket: process.env.AWS_BUCKET || 'certchain-dev'
    }
  },

  components: [
    { path: './app/domains/landing/components', pathPrefix: false },
    { path: './app/domains/auth/components', pathPrefix: false },
    { path: './app/domains/courses/components', pathPrefix: false },
    { path: './app/base/components', pathPrefix: false }
  ],

  devServer: {
    host: '0.0.0.0',
    port: 3000
  },

  vite: {
    server: {
      hmr: { host: '0.0.0.0' }
    }
  },

  compatibilityDate: '2025-10-18'
})
