<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-h2 font-bold text-text-dark">All Users</h1>
          <p class="text-body text-text-muted mt-1">Manage all users in the system</p>
        </div>
        <div class="text-body text-text-muted">
          Total: <span class="font-semibold text-text-dark">{{ total }}</span>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">User</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Joined</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                      <img v-if="user.avatar" :src="user.avatar" :alt="user.username" class="w-full h-full object-cover" />
                      <span v-else class="text-body-sm font-semibold text-text-dark">{{ getInitials(user.first_name, user.last_name) }}</span>
                    </div>
                    <div>
                      <p class="text-body font-medium text-text-dark">{{ user.first_name }} {{ user.last_name }}</p>
                      <p class="text-caption text-text-muted">@{{ user.username }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-body text-text-muted">{{ user.email }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="px-3 py-1 text-caption font-medium rounded-full"
                    :class="{
                      'bg-accent-purple/10 text-accent-purple': user.role === 'ADMIN',
                      'bg-accent-blue/10 text-accent-blue': user.role === 'TEACHER',
                      'bg-gray-100 text-text-muted': user.role === 'STUDENT'
                    }"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-3 py-1 text-caption font-medium rounded-full"
                    :class="user.is_active ? 'bg-brand-primary/10 text-brand-primary' : 'bg-accent-red/10 text-accent-red'"
                  >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-body text-text-muted">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4">
                  <button 
                    @click="openUserModal(user)"
                    class="text-brand-primary hover:text-teal-600 text-body-sm font-medium transition-colors"
                  >
                    View Details
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="text-body-sm text-text-muted">
            Showing {{ from }} to {{ to }} of {{ total }} users
          </div>
          <div class="flex items-center gap-2">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="px-4 py-2 text-body-sm font-medium rounded-lg border transition-colors"
              :class="currentPage === 1 ? 'border-gray-200 text-text-muted cursor-not-allowed' : 'border-gray-300 text-text-dark hover:bg-gray-100'"
            >
              Previous
            </button>
            <span class="text-body-sm text-text-dark">Page {{ currentPage }} of {{ lastPage }}</span>
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === lastPage"
              class="px-4 py-2 text-body-sm font-medium rounded-lg border transition-colors"
              :class="currentPage === lastPage ? 'border-gray-200 text-text-muted cursor-not-allowed' : 'border-gray-300 text-text-dark hover:bg-gray-100'"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- User Detail Modal -->
    <UserDetailModal
      :isOpen="userModalOpen"
      :user="selectedUser"
      @close="closeUserModal"
    />
  </div>
</template>

<script setup lang="ts">
import { useAdminList } from '../../composables/useAdminList'
import type { User } from '../../types/user'
import UserDetailModal from '../../components/ui/UserDetailModal.vue'

const { listUsers } = useAdminList()

const users = ref<User[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)
const loading = ref(true)

const userModalOpen = ref(false)
const selectedUser = ref<User | null>(null)

const openUserModal = (user: User) => {
  selectedUser.value = user
  userModalOpen.value = true
}

const closeUserModal = () => {
  userModalOpen.value = false
  selectedUser.value = null
}

const fetchUsers = async (page = 1) => {
  loading.value = true
  const response = await listUsers(page, 20)
  if (response) {
    users.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
    total.value = response.total
    from.value = response.from
    to.value = response.to
  }
  loading.value = false
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchUsers(page)
  }
}

const getInitials = (firstName?: string, lastName?: string): string => {
  const first = firstName?.[0] || ''
  const last = lastName?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(() => {
  fetchUsers()
})
</script>
