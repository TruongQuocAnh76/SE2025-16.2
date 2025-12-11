export interface ValidationResult {
  isValid: boolean
  message?: string
}

export const validateEmail = (email: string): ValidationResult => {
  if (!email) {
    return { isValid: false, message: 'Email is required' }
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) {
    return { isValid: false, message: 'Please enter a valid email address' }
  }

  return { isValid: true }
}

export const validateUsername = (username: string): ValidationResult => {
  if (!username) {
    return { isValid: false, message: 'Username is required' }
  }

  if (username.length < 3) {
    return { isValid: false, message: 'Username must be at least 3 characters' }
  }

  if (username.length > 20) {
    return { isValid: false, message: 'Username must not exceed 20 characters' }
  }

  const usernameRegex = /^[a-zA-Z0-9_]+$/
  if (!usernameRegex.test(username)) {
    return { isValid: false, message: 'Username can only contain letters, numbers, and underscores' }
  }

  return { isValid: true }
}

export const validatePassword = (password: string): ValidationResult => {
  if (!password) {
    return { isValid: false, message: 'Password is required' }
  }

  if (password.length < 6) {
    return { isValid: false, message: 'Password must be at least 6 characters' }
  }

  if (password.length > 100) {
    return { isValid: false, message: 'Password is too long' }
  }

  return { isValid: true }
}

export const validateConfirmPassword = (password: string, confirmPassword: string): ValidationResult => {
  if (!confirmPassword) {
    return { isValid: false, message: 'Please confirm your password' }
  }

  if (password !== confirmPassword) {
    return { isValid: false, message: 'Passwords do not match' }
  }

  return { isValid: true }
}

export const validateName = (name: string, fieldName: string = 'Name'): ValidationResult => {
  if (!name) {
    return { isValid: false, message: `${fieldName} is required` }
  }

  if (name.length < 2) {
    return { isValid: false, message: `${fieldName} must be at least 2 characters` }
  }

  if (name.length > 50) {
    return { isValid: false, message: `${fieldName} must not exceed 50 characters` }
  }

  return { isValid: true }
}

export const validateLoginForm = (login: string, password: string): { isValid: boolean; errors: Record<string, string> } => {
  const errors: Record<string, string> = {}

  if (!login) {
    errors.login = 'Username or email is required'
  }

  const passwordValidation = validatePassword(password)
  if (!passwordValidation.isValid) {
    errors.password = passwordValidation.message || 'Invalid password'
  }

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  }
}

export const validateRegisterForm = (
  email: string,
  username: string,
  password: string,
  confirmPassword: string,
  firstName: string,
  lastName: string
): { isValid: boolean; errors: Record<string, string> } => {
  const errors: Record<string, string> = {}

  const emailValidation = validateEmail(email)
  if (!emailValidation.isValid) {
    errors.email = emailValidation.message || 'Invalid email'
  }

  const usernameValidation = validateUsername(username)
  if (!usernameValidation.isValid) {
    errors.username = usernameValidation.message || 'Invalid username'
  }

  const passwordValidation = validatePassword(password)
  if (!passwordValidation.isValid) {
    errors.password = passwordValidation.message || 'Invalid password'
  }

  const confirmPasswordValidation = validateConfirmPassword(password, confirmPassword)
  if (!confirmPasswordValidation.isValid) {
    errors.confirmPassword = confirmPasswordValidation.message || 'Passwords do not match'
  }

  const firstNameValidation = validateName(firstName, 'First name')
  if (!firstNameValidation.isValid) {
    errors.firstName = firstNameValidation.message || 'Invalid first name'
  }

  const lastNameValidation = validateName(lastName, 'Last name')
  if (!lastNameValidation.isValid) {
    errors.lastName = lastNameValidation.message || 'Invalid last name'
  }

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  }
}
