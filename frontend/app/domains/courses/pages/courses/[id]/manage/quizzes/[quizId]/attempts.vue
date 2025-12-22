<template>
  <div class="min-h-screen bg-background py-12">
    <div class="max-w-7xl mx-auto px-4">
      <div class="mb-6 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-text-dark">Attempt Management</h1>
          <p class="text-gray-500 mt-2" v-if="currentQuiz">
            Quiz: {{ currentQuiz.title }}
          </p>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Student
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Submitted At
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Score
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  Loading attempts...
                </td>
              </tr>
            </template>
            <template v-else-if="attempts.length === 0">
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No attempts found for this quiz.
                </td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="attempt in attempts" :key="attempt.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">
                      {{ attempt.student ? `${attempt.student.first_name} ${attempt.student.last_name}` : 'Unknown Student' }}
                    </div>
                    <div class="text-sm text-gray-500 ml-2">
                      ({{ attempt.student?.email || 'N/A' }})
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(attempt.submitted_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                  {{ attempt.score }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                      attempt.grading_status === 'pending_manual'
                        ? 'bg-yellow-100 text-yellow-800'
                        : (attempt.is_passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')
                    ]"
                  >
                    {{ attempt.grading_status === 'pending_manual' ? 'Pending Grading' : (attempt.is_passed ? 'Passed' : 'Failed') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <NuxtLink
                    :to="`/courses/${courseId}/manage/quizzes/${quizId}/grade/${attempt.id}`"
                    class="text-teal-600 hover:text-teal-900 bg-teal-50 px-3 py-1 rounded"
                  >
                    Grade / Review
                  </NuxtLink>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const courseId = route.params.id as string
const quizId = route.params.quizId as string

const { getQuizById, fetchAllAttempts, attempts, currentQuiz, loading } = useQuizzes()

onMounted(async () => {
  await getQuizById(quizId)
  await fetchAllAttempts(quizId)
})

const formatDate = (dateString?: string) => {
  if (!dateString) return 'Not submitted'
  return new Date(dateString).toLocaleString()
}
</script>
