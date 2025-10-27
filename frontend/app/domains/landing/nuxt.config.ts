// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  // Landing domain mini app configuration
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  components: [
    { path: './components', pathPrefix: false },
    { path: '../base/components', pathPrefix: false }
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