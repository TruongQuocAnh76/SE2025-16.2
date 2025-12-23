import { defineNuxtRouteMiddleware } from '#app'

export default defineNuxtRouteMiddleware((to) => {
  // Public routes that should not require auth
  const publicRoutes = [
    '/',
    '/auth/login',
    '/auth/signin',
    '/auth/signup',
    '/auth/register',
    '/auth/forgot-password',
    '/login',
    '/signup',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/auth/oauth-callback',
    '/auth/reset-password',
    '/privacy',
    '/terms'
  ]

  // Regex patterns for public routes that have dynamic segments or need specific matching
  const publicPatterns = [
    /^\/courses\/?$/,                 // Course catalog
    /^\/courses\/[a-f0-9-]{36}\/?$/,  // Course detail (UUID only)
    /^\/verify-certificate\/?$/       // Certificate verification
  ]

  const isPublic = publicRoutes.some(route => to.path === route || to.path.startsWith(route + '/')) ||
    publicPatterns.some(pattern => pattern.test(to.path))

  if (isPublic) return

  // Check token exists
  const token = useCookie('auth_token').value
  if (!token) {
    return navigateTo('/auth/login')
  }

  // Check if user is loaded (plugin should have done this)
  const auth = useAuth()
  if (!auth.user?.value) {
    // No user loaded yet - redirect to login
    // Plugin will load user on next page load
    return navigateTo('/auth/login')
  }

  // Role-based guard for admin pages
  if (to.path.startsWith('/admin')) {
    const user = auth.user?.value
    if (!user || user.role !== 'ADMIN') {
      return navigateTo('/')
    }
  }

  // User is authenticated, allow navigation
})