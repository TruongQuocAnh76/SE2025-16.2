import type { UploadedFile } from '../types'

export const useFileUpload = () => {
  const uploadedFile = ref<UploadedFile | null>(null)
  const isDragOver = ref(false)
  const error = ref<string | null>(null)

  // Validate file
  const validateFile = (file: File): boolean => {
    error.value = null
    
    // Check file type
    if (file.type !== 'application/pdf') {
      error.value = 'Please select a PDF file only'
      return false
    }

    // Check file size (max 10MB)
    const maxSize = 10 * 1024 * 1024 // 10MB
    if (file.size > maxSize) {
      error.value = 'File size must be less than 10MB'
      return false
    }

    return true
  }

  // Handle file selection
  const handleFileSelect = (file: File) => {
    if (!validateFile(file)) {
      return
    }

    uploadedFile.value = {
      file,
      name: file.name,
      size: file.size
    }
  }

  // Handle file input change
  const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const files = target.files
    
    if (files && files.length > 0 && files[0]) {
      handleFileSelect(files[0])
    }
  }

  // Handle drag and drop
  const onDragOver = (event: DragEvent) => {
    event.preventDefault()
    isDragOver.value = true
  }

  const onDragLeave = (event: DragEvent) => {
    event.preventDefault()
    isDragOver.value = false
  }

  const onDrop = (event: DragEvent) => {
    event.preventDefault()
    isDragOver.value = false
    
    const files = event.dataTransfer?.files
    if (files && files.length > 0 && files[0]) {
      handleFileSelect(files[0])
    }
  }

  // Clear uploaded file
  const clearFile = () => {
    uploadedFile.value = null
    error.value = null
  }

  // Format file size
  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'
    
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  }

  return {
    // State
    uploadedFile: readonly(uploadedFile),
    isDragOver: readonly(isDragOver),
    error: readonly(error),

    // Actions
    onFileChange,
    onDragOver,
    onDragLeave,
    onDrop,
    clearFile,

    // Computed
    hasFile: computed(() => uploadedFile.value !== null),
    fileSize: computed(() => uploadedFile.value ? formatFileSize(uploadedFile.value.size) : ''),
    
    // Utilities
    formatFileSize
  }
}