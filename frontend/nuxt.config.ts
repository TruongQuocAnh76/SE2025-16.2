// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  devServer: {
    host: '0.0.0.0',
    port: 3000
  },
  css: [
    './app/base/assets/css/base.css'
  ],
  vite: {
    server: {
      hmr: {
        host: '0.0.0.0'
      }
    }
  },
  ignore: [
  'node_modules/**'
  ],
  extends: [
    './app/base',
    './app/domains/landing'
  ],
})
