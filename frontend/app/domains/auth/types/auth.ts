export interface User {
  id: string
  email: string
  username: string
  first_name: string
  last_name: string
  avatar?: string
  bio?: string
  role: 'STUDENT' | 'TEACHER' | 'ADMIN'
  auth_provider: 'EMAIL' | 'GOOGLE' | 'FACEBOOK' | 'GITHUB'
  is_email_verified: boolean
  is_active: boolean
}

export interface LoginRequest {
  login: string
  password: string
}

export interface RegisterRequest {
  email: string
  username: string
  password: string
  first_name: string
  last_name: string
}

export interface LoginResponse {
  message: string
  access_token: string
  token_type: string
  user: User
}

export interface RegisterResponse {
  message: string
  user: User
}

export interface ValidationError {
  field: string
  message: string
}

export interface AuthError {
  message: string
  errors?: ValidationError[]
}
