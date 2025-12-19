import type { RouterConfig } from '@nuxt/schema'

// https://router.vuejs.org/api/interfaces/routeroptions.html
export default <RouterConfig>{
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        }

        // Facebook appends #_=_ to the URL which causes a Vue Router warning
        // and can break page verification. We explicitly ignore it.
        if (to.hash === '#_=_') {
            return { top: 0 }
        }

        if (to.hash) {
            return {
                el: to.hash,
                top: 30,
                behavior: 'smooth',
            }
        }

        return { top: 0 }
    }
}
