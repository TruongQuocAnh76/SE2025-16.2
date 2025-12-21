<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Account Settings
          </h2>
        </div>
      </div>

      <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <!-- Sidebar Navigation -->
        <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
          <nav class="space-y-1">
            <button
              v-for="item in navigation"
              :key="item.name"
              @click="handleTabChange(item)"
              :class="[
                currentTab === item.id
                  ? 'bg-gray-50 text-brand-primary hover:bg-white'
                  : 'text-gray-900 hover:text-gray-900 hover:bg-gray-50',
                'group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors duration-150 ease-in-out'
              ]"
              :aria-current="currentTab === item.id ? 'page' : undefined"
            >
              <i
                :class="[
                  item.icon,
                  currentTab === item.id
                    ? 'text-brand-primary'
                    : 'text-gray-400 group-hover:text-gray-500',
                  'flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-center pt-1'
                ]"
                aria-hidden="true"
              ></i>
              <span class="truncate">{{ item.name }}</span>
            </button>
          </nav>
        </aside>

        <!-- Content Area -->
        <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
          <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
            mode="out-in"
          >
            <component :is="activeComponent" />
          </Transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuth } from '~/domains/auth/composables/useAuth'
import GeneralSettings from '../../components/settings/GeneralSettings.vue'
import SecuritySettings from '../../components/settings/SecuritySettings.vue'

const { user } = useAuth()
const navigation = [
  { name: 'Profile', id: 'general', icon: 'fas fa-user-circle' },
  { name: 'Security', id: 'security', icon: 'fas fa-key' },
  { name: 'My Dashboard', id: 'dashboard', icon: 'fas fa-chart-line' },
]

const currentTab = ref('general')

const handleTabChange = (item: any) => {
  if (item.id === 'dashboard') {
    if (user.value?.username) {
      navigateTo(`/s/${user.value.username}`)
    } else {
      navigateTo('/')
    }
    return
  }
  currentTab.value = item.id
}

const activeComponent = computed(() => {
  switch (currentTab.value) {
    case 'general':
      return GeneralSettings
    case 'security':
      return SecuritySettings
    default:
      return GeneralSettings
  }
})
</script>
