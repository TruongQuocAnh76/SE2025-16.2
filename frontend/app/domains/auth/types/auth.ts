export interface User {
  id: string
  email: string
  username: string
  first_name: string
  last_name: string
  // Add other fields as needed
}

export interface LoginRequest {
  login: string
  password: string
}

export interface RegisterRequest {
  email: string
  username: string
  password: string
}

export interface User {
  id: string
  email: string
  username: string
  first_name: string
  last_name: string
  // Add other fields as needed
}

export interface LoginRequest {
  login: string
  password: string
}

export interface RegisterRequest {
  email: string
  username: string
  password: string
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
