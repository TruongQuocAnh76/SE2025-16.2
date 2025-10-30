export interface Course {
  id: string
  title: string
  slug: string
  description: string
  thumbnail?: string
  level: 'BEGINNER' | 'INTERMEDIATE' | 'ADVANCED' | 'EXPERT'
  price?: number
  duration?: number
  status: 'DRAFT' | 'PUBLISHED' | 'ARCHIVED'
  teacher_id: string
  passing_score: number
  created_at?: string
  updated_at?: string
  teacher?: User
  modules?: Module[]
  enrollments?: Enrollment[]
  reviews?: Review[]
  quizzes?: Quiz[]
}

export interface Module {
  id: string
  title: string
  order_index: number
  course_id: string
  created_at?: string
  updated_at?: string
  lessons?: Lesson[]
  course?: Course
}

export interface Lesson {
  id: string
  title: string
  content_type: string
  content_url?: string
  duration?: number
  order_index: number
  is_free: boolean
  module_id: string
  created_at?: string
  updated_at?: string
  module?: Module
  progresses?: Progress[]
}

export interface User {
  id: string
  first_name: string
  last_name: string
  email: string
  username: string
  role: string
  bio?: string
  avatar?: string
  is_active: boolean
  created_at?: string
  updated_at?: string
}

export interface Enrollment {
  id: string
  student_id: string
  course_id: string
  enrolled_at: string
  completed_at?: string
  progress_percentage: number
  student?: User
  course?: Course
}

export interface Review {
  id: string
  student_id: string
  course_id: string
  rating: number
  comment?: string
  created_at: string
  student?: User
  course?: Course
}

export interface Quiz {
  id: string
  course_id: string
  title: string
  description?: string
  duration?: number
  passing_score: number
  max_attempts: number
  is_active: boolean
  created_at?: string
  updated_at?: string
  course?: Course
}

export interface Progress {
  id: string
  student_id: string
  lesson_id: string
  completed: boolean
  time_spent: number
  last_accessed_at: string
  student?: User
  lesson?: Lesson
}

export interface CreateCourseData {
  title: string
  description: string
  thumbnail?: string
  level: 'BEGINNER' | 'INTERMEDIATE' | 'ADVANCED' | 'EXPERT'
  price?: number
  duration?: number
  passing_score: number
}
