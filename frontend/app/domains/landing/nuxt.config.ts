// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  // Landing domain mini app configuration
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  pages: true,
  components: [
    { path: './app/domains/landing/components', pathPrefix: false },
    { path: './app/base/components', pathPrefix: false }
  ],
  css: [
    './app/domains/landing/assets/css/main.css',
    './app/base/assets/css/base.css'
  ],
  devServer: {
    host: '0.0.0.0',
    port: 3001
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