import { defineNuxtPlugin } from '#app'

export default defineNuxtPlugin({
  name: 'auth-init',
  enforce: 'pre', // Run before other plugins and middleware
  async setup() {
    try {
      const auth = useAuth()
      
      // On server: only restore from cookie, no API call
      // On client: full init (reads cookie first, then API if needed)
      if (import.meta.server) {
        // Server-side: restore user from user_data cookie only
        const userDataCookie = useCookie('user_data').value
        const token = useCookie('auth_token').value
        
        if (token && userDataCookie) {
          try {
            const cachedUser = typeof userDataCookie === 'string' 
              ? JSON.parse(userDataCookie) 
              : userDataCookie
            if (cachedUser && cachedUser.id) {
              auth.user.value = cachedUser
            }
          } catch (e) {
            // Cookie parse failed
          }
        }
        auth.ready.value = true
      } else {
        // Client-side: full init (may call API if cookie missing)
        await auth.init()
      }
      
      // Sync auth user to userStats for components that use useUserStats
      if (auth.user?.value) {
        const userStats = useUserStats()
        userStats.setCurrentUser(auth.user.value)
      }
    } catch (err) {
      console.error('auth-init plugin error:', err)
    }
  }
})
