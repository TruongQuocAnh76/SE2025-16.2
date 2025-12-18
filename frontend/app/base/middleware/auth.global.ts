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
    '/reset-password'
  ]

  const isPublic = publicRoutes.some(route => to.path === route || to.path.startsWith(route + '/'))
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

  // User is authenticated, allow navigation
})