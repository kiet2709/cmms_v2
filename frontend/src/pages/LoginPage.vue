<template>
  <div class="login-container">
    <!-- Background Elements -->
    <div class="bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <!-- Loading Overlay -->
    <a-spin
      :spinning="loading"
      size="large"
      tip="Signing you in..."
    />

    <!-- Main Content -->
    <div class="login-content">
      <div class="login-card">
        <!-- Logo Section -->
        <div class="logo-section">
          <div class="logo-container">
            <img :src="logo" alt="CMMS Logo" class="logo-image" />
            <h1 class="logo-text">CMMS</h1>
          </div>
          <p class="logo-subtitle">Computerized Maintenance Management System</p>
        </div>

        <!-- Form Section -->
        <div class="form-section">


          <a-form
            :model="form"
            @finish="handleSubmit"
            layout="vertical"
            class="login-form"
            :disabled="loading"
          >
            <!-- Username Field -->
            <a-form-item
              name="username"
              :rules="usernameRules"
              class="form-item"
            >
              <template #label>
                <span class="field-label">Username</span>
              </template>
              <a-input
                v-model:value="form.username"
                placeholder="Enter your username"
                size="large"
                class="custom-input"
                :prefix="usernameIcon"
              />
            </a-form-item>

            <!-- Password Field -->
            <a-form-item
              name="password"
              :rules="passwordRules"
              class="form-item"
            >
              <template #label>
                <span class="field-label">Password</span>
              </template>
              <a-input-password
                v-model:value="form.password"
                placeholder="Enter your password"
                size="large"
                class="custom-input"
                :prefix="passwordIcon"
              />
            </a-form-item>

            <!-- Error Message -->
            <a-alert
              v-if="errorMessage"
              :message="errorMessage"
              type="error"
              show-icon
              class="error-alert"
              closable
              @close="errorMessage = ''"
            />


            <!-- Submit Button -->
            <a-button
              type="primary"
              html-type="submit"
              size="large"
              block
              class="submit-button"
              :loading="loading"
            >
              <template v-if="!loading">
                Sign In
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </template>
            </a-button>
          </a-form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
          <p>&copy; 2025 CMMS. All rights reserved.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, h } from 'vue'
import { useRouter } from 'vue-router'
import { message } from 'ant-design-vue'
import { UserOutlined, LockOutlined } from '@ant-design/icons-vue'
import axiosClient from '@/utils/axiosClient'
import logo from "@/assets/logo.png";

const router = useRouter()

// Reactive form data
const form = reactive({
  username: '',
  password: ''
})

// Reactive state
const errorMessage = ref('')
const loading = ref(false)
const rememberMe = ref(false)

// Form validation rules
const usernameRules = [
  { required: true, message: 'Please enter your username', trigger: 'blur' },
  { min: 3, message: 'Username must be at least 3 characters', trigger: 'blur' }
]

const passwordRules = [
  { required: true, message: 'Please enter your password', trigger: 'blur' },
  { min: 6, message: 'Password must be at least 6 characters', trigger: 'blur' }
]

// Icons
const usernameIcon = h(UserOutlined, { style: { color: '#8c8c8c' } })
const passwordIcon = h(LockOutlined, { style: { color: '#8c8c8c' } })

// Handle form submission
const handleSubmit = async (values) => {
  errorMessage.value = ''
  loading.value = true

  try {
    const res = await axiosClient.post('', values, {
      params: {
        c: 'AuthController',
        m: 'login'
      }
    })

    // Store token
    localStorage.setItem('accessToken', res.data.accessToken)
    
    // Store remember me preference
    if (rememberMe.value) {
      localStorage.setItem('rememberMe', 'true')
      localStorage.setItem('lastUsername', values.username)
    } else {
      localStorage.removeItem('rememberMe')
      localStorage.removeItem('lastUsername')
    }

    // Success message and redirect
    message.success('Welcome back! Redirecting...')
    
    setTimeout(() => {
      router.push('/dashboard/equipments')
    }, 1000)

  } catch (error) {
    console.error('Login error:', error)
    
    const errorMsg = error.response?.data?.message || 
                    'Invalid username or password. Please try again.'
    
    errorMessage.value = errorMsg
    message.error(errorMsg)
    
    // Clear password on error
    form.password = ''
  } finally {
    loading.value = false
  }
}

// Initialize form with remembered data
const initializeForm = () => {
  const remembered = localStorage.getItem('rememberMe')
  const lastUsername = localStorage.getItem('lastUsername')
  
  if (remembered === 'true' && lastUsername) {
    form.username = lastUsername
    rememberMe.value = true
  }
}

// Initialize on component mount
initializeForm()
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.bg-shapes {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 200px;
  height: 200px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

.loading-overlay {
  position: fixed !important;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.login-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 440px;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.logo-section {
  text-align: center;
  margin-bottom: 40px;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 12px;
}

.logo-image {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  object-fit: contain;
  background: rgba(255, 255, 255, 0.7);
  padding: 8px;
}


.logo-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.logo-text {
  font-size: 36px;
  font-weight: 900;
  letter-spacing: 4px;
  margin: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-subtitle {
  color: #666;
  font-size: 14px;
  margin: 0;
  font-weight: 500;
}

.form-section {
  margin-bottom: 30px;
}

.form-header {
  text-align: center;
  margin-bottom: 32px;
}

.form-header h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 8px 0;
}

.form-header p {
  color: #666;
  font-size: 16px;
  margin: 0;
}

.login-form {
  width: 100%;
}

.form-item {
  margin-bottom: 24px;
}

.field-label {
  font-weight: 600;
  color: #333;
  font-size: 15px;
}

:deep(.custom-input) {
  border-radius: 12px !important;
  border: 2px solid #e8e8e8 !important;
  padding: 12px 16px !important;
  font-size: 16px !important;
  transition: all 0.3s ease !important;
}

:deep(.custom-input:hover) {
  border-color: #667eea !important;
}

:deep(.custom-input:focus) {
  border-color: #667eea !important;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

:deep(.ant-input-affix-wrapper) {
  border-radius: 12px !important;
  border: 2px solid #e8e8e8 !important;
  padding: 12px 16px !important;
  transition: all 0.3s ease !important;
}

:deep(.ant-input-affix-wrapper:hover) {
  border-color: #667eea !important;
}

:deep(.ant-input-affix-wrapper:focus-within) {
  border-color: #667eea !important;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.error-alert {
  margin-bottom: 20px;
  border-radius: 12px;
  border: none;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.remember-checkbox {
  color: #666;
  font-size: 14px;
}

.forgot-link {
  color: #667eea;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #764ba2;
}

.submit-button {
  height: 50px !important;
  border-radius: 12px !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  border: none !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 8px !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
}

.submit-button:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
}

.submit-button:active {
  transform: translateY(0) !important;
}

.login-footer {
  text-align: center;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e8e8e8;
}

.login-footer p {
  color: #999;
  font-size: 13px;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 480px) {
  .login-card {
    padding: 30px 20px;
    margin: 10px;
    border-radius: 20px;
  }
  
  .logo-text {
    font-size: 28px;
    letter-spacing: 2px;
  }
  
  .form-header h2 {
    font-size: 24px;
  }
  
  .logo-container {
    flex-direction: column;
    gap: 12px;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .login-card {
    background: rgba(30, 30, 30, 0.95);
    color: #fff;
  }
  
  .form-header h2 {
    color: #fff;
  }
  
  .field-label {
    color: #ddd;
  }
  
  .logo-subtitle {
    color: #aaa;
  }
  
  .login-footer p {
    color: #666;
  }

  .logo-text {
    font-size: 36px;
    font-weight: 900;
    letter-spacing: 4px;
    margin: 0;
    background: white;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
}
</style>