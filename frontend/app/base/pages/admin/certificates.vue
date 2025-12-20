<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-h2 font-bold text-text-dark">All Certificates</h1>
          <p class="text-body text-text-muted mt-1">View all issued certificates</p>
        </div>
        <div class="text-body text-text-muted">
          Total: <span class="font-semibold text-text-dark">{{ total }}</span>
        </div>
      </div>

      <!-- Certificates Table -->
      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Certificate #</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Student</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Course</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Issued</th>
                <th class="px-6 py-4 text-left text-caption font-semibold text-text-dark uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="cert in certificates" :key="cert.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-body font-mono text-text-dark">{{ cert.certificate_number }}</td>
                <td class="px-6 py-4 text-body text-text-dark">{{ cert.student?.first_name }} {{ cert.student?.last_name }}</td>
                <td class="px-6 py-4 text-body text-text-muted">{{ cert.course?.title }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="px-3 py-1 text-caption font-medium rounded-full"
                    :class="{
                      'bg-brand-primary/10 text-brand-primary': cert.status === 'ISSUED',
                      'bg-accent-orange/10 text-accent-orange': cert.status === 'PENDING',
                      'bg-accent-red/10 text-accent-red': cert.status === 'REVOKED'
                    }"
                  >
                    {{ cert.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-body text-text-muted">{{ formatDate(cert.issued_at) }}</td>
                <td class="px-6 py-4">
                  <button 
                    @click="openCertModal(cert)"
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
            Showing {{ from }} to {{ to }} of {{ total }} certificates
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

    <!-- Certificate Detail Modal -->
    <CertificateDetailModal
      :isOpen="certModalOpen"
      :certificate="selectedCert"
      @close="closeCertModal"
    />
  </div>
</template>

<script setup lang="ts">
import { useAdminList } from '../../composables/useAdminList'
import CertificateDetailModal from '../../components/ui/CertificateDetailModal.vue'

const { listCertificates } = useAdminList()

const certificates = ref<any[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)
const loading = ref(true)

const certModalOpen = ref(false)
const selectedCert = ref<any>(null)

const openCertModal = (cert: any) => {
  selectedCert.value = cert
  certModalOpen.value = true
}

const closeCertModal = () => {
  certModalOpen.value = false
  selectedCert.value = null
}

const fetchCertificates = async (page = 1) => {
  loading.value = true
  const response = await listCertificates(page, 20)
  if (response) {
    certificates.value = response.data
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
    fetchCertificates(page)
  }
}

const formatDate = (dateStr: string | null): string => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(() => {
  fetchCertificates()
})
</script>
