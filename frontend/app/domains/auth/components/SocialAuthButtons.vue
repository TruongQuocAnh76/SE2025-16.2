<template>
  <div class="social-auth-section">
    <!-- Divider với chữ "OR" -->
    <div class="divider">
      <span>OR</span>
    </div>
    
    <!-- Social Login Buttons -->
    <div class="social-buttons">
      <!-- Google Button -->
      <button 
        @click="loginWithGoogle"
        class="social-button google-button"
        type="button"
      >
        <img 
          src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" 
          alt="Google" 
          class="social-icon"
        />
        <span>{{ buttonText }} with Google</span>
      </button>

      <!-- Facebook Button -->
      <button 
        @click="loginWithFacebook"
        class="social-button facebook-button"
        type="button"
      >
        <svg class="social-icon" fill="currentColor" viewBox="0 0 24 24">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>{{ buttonText }} with Facebook</span>
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  buttonText: {
    type: String,
    default: 'Login'
  }
})

const config = useRuntimeConfig()
const backendUrl = config.public.backendUrl || 'http://localhost:8000'

const loginWithGoogle = () => {
  // Redirect to backend OAuth endpoint
  window.location.href = `${backendUrl}/api/auth/google`
}

const loginWithFacebook = () => {
  // Redirect to backend OAuth endpoint
  window.location.href = `${backendUrl}/api/auth/facebook`
}
</script>

<style scoped>
.social-auth-section {
  margin-top: 1.5rem;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.5rem 0;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e5e7eb;
}

.divider span {
  padding: 0 1rem;
  color: #9ca3af;
  font-size: 0.875rem;
  font-weight: 500;
}

.social-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.social-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.social-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.social-button:active {
  transform: translateY(0);
}

.social-icon {
  width: 20px;
  height: 20px;
}

/* Google Button - Blue background */
.google-button {
  background-color: #4285f4;
  color: white;
}

.google-button:hover {
  background-color: #357ae8;
}

/* Facebook Button - Blue background (same as Google) */
.facebook-button {
  background-color: #4285f4;
  color: white;
}

.facebook-button:hover {
  background-color: #357ae8;
}
</style>
