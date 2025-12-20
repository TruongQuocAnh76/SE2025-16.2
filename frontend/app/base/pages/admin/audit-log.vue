<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-h2 font-bold text-text-dark">Audit Log</h1>
          <p class="text-body text-text-muted mt-1">System activity and audit trail</p>
        </div>
        <div class="text-body text-text-muted">
          Total: <span class="font-semibold text-text-dark">{{ total }}</span>
        </div>
      </div>

      <!-- Logs List -->
      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="divide-y divide-gray-100">
          <div 
            v-for="log in logs" 
            :key="log.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span 
                    class="px-2 py-1 text-caption font-medium rounded-full"
                    :class="{
                      'bg-accent-red/10 text-accent-red': log.level === 'ERROR',
                      'bg-accent-orange/10 text-accent-orange': log.level === 'WARNING',
                      'bg-accent-blue/10 text-accent-blue': log.level === 'INFO',
                      'bg-gray-100 text-text-muted': log.level === 'DEBUG'
                    }"
                  >
                    {{ log.level }}
                  </span>
                  <span class="text-caption text-text-muted">{{ formatDateTime(log.created_at) }}</span>
                </div>
                <p class="text-body font-medium text-text-dark mb-2">{{ log.message }}</p>
                <div class="text-caption text-text-muted space-y-1">
                  <p v-if="log.user">Action by: <span class="font-medium text-text-dark">{{ log.user.first_name }} {{ log.user.last_name }}</span></p>
                  <p v-if="log.ip_address">IP: <span class="font-mono">{{ log.ip_address }}</span></p>
                  <div v-if="log.context && Object.keys(log.context).length > 0" class="mt-2">
                    <details class="text-caption">
                      <summary class="cursor-pointer text-brand-primary hover:text-teal-600">View Context</summary>
                      <pre class="mt-2 p-3 bg-gray-50 rounded-lg overflow-auto text-xs">{{ JSON.stringify(log.context, null, 2) }}</pre>
                    </details>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="text-body-sm text-text-muted">
            Showing {{ from }} to {{ to }} of {{ total }} logs
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
  </div>
</template>

<script setup lang="ts">
import { useAdminList } from '../../composables/useAdminList'

const { listLogs } = useAdminList()

const logs = ref<any[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)
const loading = ref(true)

const fetchLogs = async (page = 1) => {
  loading.value = true
  const response = await listLogs(page, 50)
  if (response) {
    logs.value = response.data
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
    fetchLogs(page)
  }
}

const formatDateTime = (dateStr: string): string => {
  const date = new Date(dateStr)
  return date.toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchLogs()
})
</script>
