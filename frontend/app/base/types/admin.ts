export interface DashboardStats {
  total_users: number
  total_courses: number
  certificates_issued: number
  pending_actions: number
}

export interface TeacherApplication {
  id: string
  user_id: string
  status: 'PENDING' | 'APPROVED' | 'REJECTED'
  // Personal Information from application
  full_name: string
  email: string
  bio: string | null
  gender: 'MALE' | 'FEMALE' | 'OTHER' | null
  phone: string | null
  date_of_birth: string | null
  country: string | null
  avatar_url: string | null
  // Certificate Information
  certificate_title: string
  issuer: string
  issue_date: string
  expiry_date: string | null
  certificate_file_path: string | null
  // Review Information
  reviewed_by: string | null
  reviewed_at: string | null
  rejection_reason: string | null
  // Timestamps
  created_at: string
  updated_at: string
  // User info from relationship
  user?: {
    id: string
    first_name: string
    last_name: string
    username: string
    email: string
    role: string
  }
}

export interface CourseApplication {
  id: string
  title: string
  teacher_name: string
  category: string
  level: string
  duration: number
  submitted_at: string
}

export interface CertificatesOverview {
  issued: number
  verified: number
  pending: number
}

export interface RecentCertificate {
  id: string
  certificate_number: string
  student_name: string
  student_avatar: string | null
  course_name: string
  instructor_name: string
  issued_at: string
  is_verified: boolean
}

export interface SystemLogEntry {
  id: string
  level: string
  message: string
  context: Record<string, any>
  action_by: string
  timestamp: string
}
