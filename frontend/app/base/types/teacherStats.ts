import type { User } from './userStats'

export interface TeacherCourse {
  id: string
  title: string
  slug: string
  description: string
  thumbnail: string | null
  status: 'DRAFT' | 'PENDING' | 'PUBLISHED' | 'ARCHIVED'
  level: string
  price: number
  duration: number
  passing_score: number
  created_at: string
  published_at: string | null
  enrollments_count: number
  reviews_avg_rating: number | null
}

export interface TeacherCoursesResponse {
  teacher: {
    id: string
    first_name: string
    last_name: string
    email: string
  }
  courses: TeacherCourse[]
  total_courses: number
  total_enrollments: number
}

export interface StudentEnrollment {
  enrollment_id: string
  course_id: string
  course_title: string
  status: string
  progress: number
  enrolled_at: string
  completed_at: string | null
}

export interface TeacherStudent {
  student_id: string
  first_name: string
  last_name: string
  email: string
  enrollments: StudentEnrollment[]
  total_courses_enrolled: number
}

export interface TeacherStudentsResponse {
  teacher: {
    id: string
    first_name: string
    last_name: string
    email: string
  }
  students: TeacherStudent[]
  total_students: number
  total_enrollments: number
}

export interface TeacherStatistics {
  total_courses: number
  published_courses: number
  draft_courses: number
  total_enrollments: number
  unique_students: number
  average_rating: number
  total_reviews: number
}

export interface TeacherStatisticsResponse {
  teacher: {
    id: string
    first_name: string
    last_name: string
    email: string
  }
  statistics: TeacherStatistics
}

export interface PendingSubmission {
  id: string
  student_id: string
  student_name: string
  student_avatar: string | null
  course_title: string
  assignment_title: string
  submitted_at: string
  status: 'PENDING' | 'REVIEWED' | 'GRADED'
}

export interface PendingCertificate {
  id: string
  student_id: string
  student_name: string
  course_title: string
  completed_at: string
  final_score: number
}
