export default defineNuxtRouteMiddleware(async (to) => {
  // Define public routes that don't require authentication
  const publicRoutes = [
    '/',                 // Landing page
    '/login',            // Login page
    '/signup',           // Signup page
    '/register',         // Alternative signup route
    '/forgot-password',  // Forgot password page
    '/reset-password',   // Reset password page
    '/auth/login',       // Auth login route
    '/auth/register',    // Auth register route
    '/auth/forgot-password'  // Auth forgot password route
  ]
  
  // Check if current route is public
  const isPublicRoute = publicRoutes.some(route => {
    // Exact match or starts with the route (for dynamic routes)
    return to.path === route || to.path.startsWith(route + '/')
  })
  
  // Skip auth check for public routes
  if (isPublicRoute) {
    return
  }

  // Get auth token from cookie
  const authToken = useCookie('auth_token')
  
  // If no auth token exists, redirect to home
  if (!authToken.value) {
    return navigateTo('/')
  }

  // Validate token with backend
  try {
    const config = useRuntimeConfig()
    const response = await $fetch('/api/auth/me', {
      baseURL: config.public.apiBase || 'http://localhost:8000',
      headers: {
        'Authorization': `Bearer ${authToken.value}`,
        'Accept': 'application/json'
      }
    })

    // If validation successful, store user data
    const userData = useCookie('user_data')
    userData.value = response

  } catch (error: any) {
    console.error('Token validation failed:', error)
    
    // Clear invalid token and user data
    authToken.value = null
    const userData = useCookie('user_data')
    userData.value = null
    
    // Redirect to home
    return navigateTo('/')
  }
})
