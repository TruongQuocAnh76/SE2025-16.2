export interface DashboardStats {
  total_users: number
  total_courses: number
  certificates_issued: number
  pending_actions: number
}

export interface TeacherApplication {
  id: string
  user_id: string
  user_name: string
  first_name: string
  last_name: string
  user_email: string
  username: string
  avatar: string | null
  bio: string | null
  current_role: string
  requested_role: string
  certificate_title: string
  issuer: string
  issue_date: string | null
  expiry_date: string | null
  submitted_at: string
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
