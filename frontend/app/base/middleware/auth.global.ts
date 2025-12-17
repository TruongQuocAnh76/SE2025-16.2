import { defineNuxtRouteMiddleware } from '#app'

export default defineNuxtRouteMiddleware(async (to) => {
  // Distinct logs for server and client so you can find them easily
  if (process.server) {
    console.log(`[middleware][server] auth.global running for ${to.path}`)
  }
  if (process.client) {
    console.log(`[middleware][client] auth.global running for ${to.path}`)
  }
  try {
    // Debug: show cookie and auth state
    const rawToken = useCookie('auth_token').value
    if (process.server) {
      console.log('[middleware][server] auth_token cookie:', rawToken)
    }
    if (process.client) {
      console.log('[middleware][client] auth_token cookie:', rawToken)
    }

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

    const auth = useAuth()
    const userStats = useUserStats()

    // If there's a token but auth user isn't loaded yet, try to fetch once
    const token = rawToken
    if (!auth.user?.value && token) {
      try {
        await auth.getUser()
      } catch (e) {
        // ignore - will handle redirect below
      }
    }

    // If user loaded, propagate to userStats and allow navigation
    if (auth.user?.value) {
      if (!userStats.currentUser?.value) {
        userStats.setCurrentUser(auth.user.value)
      }
      return
    }

    // Not authenticated -> redirect to login
    return navigateTo('/auth/login')
  } catch (err) {
    // Don't block navigation on middleware errors; redirect to login as fallback
    console.error('auth.global middleware error:', err)
    return navigateTo('/auth/login')
  }
})