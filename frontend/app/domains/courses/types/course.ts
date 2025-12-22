export interface Tag {
  id: string;
  name: string;
}

export interface Course {
  id: string
  title: string
  slug: string
  description: string
  thumbnail?: string
  category?: string
  language?: string
  discount?: number
  level: 'BEGINNER' | 'INTERMEDIATE' | 'ADVANCED' | 'EXPERT'
  price?: number
  originalPrice?: number
  duration?: number
  status: 'DRAFT' | 'PUBLISHED' | 'ARCHIVED'
  teacher_id: string
  passing_score: number
  tags?: Tag[]
  created_at?: string
  updated_at?: string
  teacher?: User
  modules?: Module[]
  enrollments?: Enrollment[]
  reviews?: Review[]
  quizzes?: Quiz[]
  rating_counts?: { [key: number]: number };
  average_rating?: number
  review_count?: number
}

export interface Module {
  id: string
  title: string
  description?: string
  order_index: number
  course_id: string
  created_at?: string
  updated_at?: string
  lessons?: Lesson[]
  quizzes?: Quiz[]
  course?: Course
}

export interface Lesson {
  id: string
  title: string
  description?: string
  text_content?: string
  content_type: string
  content_url?: string
  duration?: number
  order_index: number
  is_free: boolean
  module_id: string
  completed?: boolean
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
  time_limit?: number
  quiz_type: string
  passing_score: number
  max_attempts: number
  order_index?: number
  total_questions?: number
  total_points?: number
  is_active: boolean
  created_at?: string
  updated_at?: string
  course?: Course
  questions?: Question[]
}

export interface QuizAttempt {
  id: string
  quiz_id: string
  student_id: string
  started_at: string
  submitted_at?: string
  score?: number
  passed?: boolean
  time_spent?: number
  answers?: QuizAnswer[]
}

export interface QuizAnswer {
  id: string
  question_id: string
  answer_text: string
  is_correct?: boolean
  points_earned?: number
}

export interface QuizStats {
  total_attempts: number
  best_score: number
  average_score: number
  last_attempt?: QuizAttempt
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

  long_description?: string
  category?: string
  language?: string
  discount?: number

  tags?: string[];
}

export interface Question {
  id?: string
  question_text: string
  question_type: 'MULTIPLE_CHOICE' | 'CHECKBOX' | 'SHORT_ANSWER'
  points: number
  order_index: number
  options?: string[]
  correct_answer: string
  explanation?: string
  quiz_id?: string
}

export interface CreateModuleData {
  id?: string
  title: string
  description?: string
  order_index: number
  lessons: CreateLessonData[]
  quizzes: CreateQuizData[]
}

export interface CreateLessonData {
  id?: string
  title: string
  content_type: 'VIDEO'
  content_url?: string
  video_file?: File
  duration?: number
  order_index: number
  is_free: boolean
}

export interface CreateQuizData {
  id?: string
  title: string
  description?: string
  quiz_type: 'PRACTICE' | 'GRADED' | 'FINAL'
  time_limit?: number
  passing_score: number
  max_attempts?: number
  order_index: number
  is_active: boolean
  questions: CreateQuestionData[]
}

export interface CreateQuestionData {
  id?: string
  question_text: string
  question_type: 'MULTIPLE_CHOICE' | 'CHECKBOX' | 'SHORT_ANSWER'
  points: number
  order_index: number
  options?: string[]
  correct_answer: string
  explanation?: string
}

export interface CreateCourseWithModulesData extends CreateCourseData {
  modules: CreateModuleData[]
}

export interface CreateReviewData {
  rating: number
  comment?: string
}

export interface CreateCourseResponse {
  message: string
  course: Course
  thumbnail_upload_url?: string
  video_upload_urls?: {
    [key: string]: {
      upload_url: string
      lesson_id: string
      original_video_path: string
      hls_base_path: string
    }
  }
}

export interface CourseDetailsResponse {
  course: Course;
  rating_counts: { [key: number]: number };
}

export interface AddReviewResponse {
  message: string;
  review: Review;
  course_stats: {
    average_rating: number;
    review_count: number;
    rating_counts: { [key: number]: number };
  };
}
