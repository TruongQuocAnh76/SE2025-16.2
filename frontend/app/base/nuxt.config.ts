export default defineNuxtConfig({
  // Base layer configuration
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  components: [
    { path: './components', pathPrefix: false }
  ],
  css: [
    './assets/css/base.css'
  ],
  devServer: {
    host: '0.0.0.0',
    port: 3002
  }
})
