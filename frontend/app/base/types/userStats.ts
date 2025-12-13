export interface User {
  id: string
  first_name: string
  last_name: string
  email: string
  password?: string | null
  avatar?: string | null
  bio?: string | null
  role: 'STUDENT' | 'TEACHER' | 'ADMIN'
  auth_provider: 'EMAIL' | 'GOOGLE' | 'FACEBOOK' | 'GITHUB'
  is_email_verified: boolean
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface Enrollment {
  id: string
  student_id: string
  course_id: string
  status: 'ACTIVE' | 'COMPLETED' | 'DROPPED'
  progress: number
  completed_at: string | null
  enrolled_at: string
  course: {
    id: string
    title: string
    description: string
    price: number
    duration: number
    level: string
    category: string
    status: string
    teacher_id: string
    teacher: {
      id: string
      first_name: string
      last_name: string
    }
  }
}

export interface Certificate {
  id: string
  certificate_number: string
  student_id: string
  course_id: string
  status: 'PENDING' | 'ISSUED' | 'FAILED' | 'REVOKED'
  final_score: number
  pdf_url?: string
  pdf_hash?: string
  blockchain_transaction_hash?: string
  issued_at: string
  course: {
    id: string
    title: string
  }
}

export interface CourseTimeSpent {
  course_id: string
  course_title: string
  total_time_spent: number
  enrollment_status: string
  enrolled_at: string
}

export interface CourseProgress {
  course: {
    id: string
    title: string
  }
  enrollment: {
    progress: number
    status: string
  }
  modules: Array<{
    id: string
    title: string
    lessons: Array<{
      id: string
      title: string
      is_completed: boolean
      time_spent: number
    }>
  }>
}
