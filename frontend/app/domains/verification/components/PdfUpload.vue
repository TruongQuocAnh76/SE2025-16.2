<template>
  <div class="w-full">
    <!-- File Drop Zone -->
    <div
      class="relative border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200 hover:bg-gray-50"
      :class="{
        'border-brand-primary bg-brand-primary/5': isDragOver && !error && !hasFile,
        'border-red-300 bg-red-50': error,
        'border-green-300 bg-green-50': hasFile && !error,
        'border-gray-300': !isDragOver && !error && !hasFile
      }"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
    >
      <!-- File Input -->
      <input
        ref="fileInput"
        type="file"
        accept=".pdf"
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        @change="onFileChange"
      />

      <!-- Drop Zone Content -->
      <div v-if="!hasFile" class="space-y-4">
        <div class="flex justify-center">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
        </div>
        <div>
          <p class="text-lg font-medium text-text-dark">
            Drop your certificate PDF here
          </p>
          <p class="text-text-muted mt-1">
            or <span class="text-brand-primary hover:text-brand-secondary cursor-pointer font-medium">browse files</span>
          </p>
        </div>
        <p class="text-sm text-text-muted">
          Supports PDF files up to 10MB
        </p>
      </div>

      <!-- Selected File Display -->
      <div v-else class="space-y-4">
        <div class="flex justify-center">
          <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-lg font-medium text-text-dark">
            {{ uploadedFile?.name }}
          </p>
          <p class="text-text-muted">
            {{ fileSize }}
          </p>
        </div>
        <button
          @click="clearFile"
          class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Remove
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-red-700 font-medium">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useFileUpload } from '../composables/useFileUpload'

const {
  uploadedFile,
  isDragOver,
  error,
  hasFile,
  fileSize,
  onFileChange,
  onDragOver,
  onDragLeave,
  onDrop,
  clearFile
} = useFileUpload()

// Expose file data to parent component
defineExpose({
  uploadedFile,
  hasFile,
  clearFile
})
</script>