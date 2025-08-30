<template>
  <div class="profile-container">
    <div v-if="user.username" class="profile-content">
      <!-- Header Section -->
      <a-card class="profile-header" :bordered="false">
        <div class="header-content">
          <div class="avatar-section">
            <a-avatar 
              :size="120" 
              :style="{ 
                backgroundColor: getAvatarColor(user.username),
                fontSize: '48px',
                border: '4px solid white',
                boxShadow: '0 8px 24px rgba(0,0,0,0.12)'
              }"
            >
              {{ getInitials(user.fullName) }}
            </a-avatar>
            <a-button 
              type="text" 
              class="change-avatar-btn"
              @click="showAvatarModal = true"
            >
              <template #icon>
                <CameraOutlined />
              </template>
              Change Avatar
            </a-button>
          </div>
          <div class="user-info">
            <h1 class="user-name">{{ user.fullName }}</h1>
            <p class="user-title">{{ user.jobName }} • {{ user.location }}</p>
            <div class="user-badges">
              <a-tag 
                :color="getRoleColor(user.role)" 
                class="role-tag"
              >
                {{ user.role }}
              </a-tag>
              <a-tag 
                :color="user.isOnline ? 'green' : 'default'" 
                class="status-tag"
              >
                {{ user.isOnline ? 'Online' : 'Offline' }}
              </a-tag>
            </div>
          </div>
          <div class="header-actions">
            <a-button 
              type="primary" 
              @click="handleSaveChanges"
              v-if="editMode"
            >
              <template #icon>
                <CheckOutlined />
              </template>
              Save Changes
            </a-button>
            <a-button 
              type="primary" 
              @click="editMode = !editMode"
              v-else
            >
              <template #icon>
                <EditOutlined />
              </template>
              Edit Profile
            </a-button>
            <a-dropdown>
              <a-button>
                More Actions 
                <DownOutlined />
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item @click="showSecurityModal = true">
                    <SafetyOutlined />
                    Security Settings
                  </a-menu-item>
                  <a-menu-item @click="exportProfile">
                    <DownloadOutlined />
                    Export Profile
                  </a-menu-item>
                  <a-menu-divider />
                  <a-menu-item danger>
                    <DeleteOutlined />
                    Deactivate Account
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>
      </a-card>

      <a-row :gutter="24" class="profile-body">
        <!-- Main Information -->
        <a-col :span="16">
          <a-card title="Personal Information" class="info-card">
            <a-form :model="formData" layout="vertical" :disabled="!editMode">
              <a-row :gutter="16">
                <a-col :span="12">
                  <a-form-item label="Employee ID" name="jobId">
                    <a-input 
                      v-model:value="formData.jobId" 
                      :disabled="true"
                      prefix="ID:"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="Username" name="username">
                    <a-input 
                      v-model:value="formData.username" 
                      :disabled="true"
                      prefix="@"
                    />
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="16">
                <a-col :span="12">
                  <a-form-item label="Full Name" name="fullName">
                    <a-input 
                      v-model:value="formData.fullName"
                      placeholder="Enter full name"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="Email Address" name="email">
                    <a-input 
                      v-model:value="formData.email"
                      placeholder="Enter email address"
                      type="email"
                    />
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="16">
                <a-col :span="12">
                  <a-form-item label="Phone Number" name="phone">
                    <a-input 
                      v-model:value="formData.phone"
                      placeholder="Enter phone number"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="Location" name="location">
                    <a-input 
                      v-model:value="formData.location"
                      placeholder="Enter work location"
                    />
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="16">
                <a-col :span="12">
                  <a-form-item label="Job Title" name="jobName">
                    <a-input 
                      v-model:value="formData.jobName"
                      placeholder="Enter job title"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="Department" name="department">
                    <a-input 
                      v-model:value="formData.department"
                      placeholder="Enter department"
                    />
                  </a-form-item>
                </a-col>
              </a-row>

              <a-form-item label="Bio" name="bio">
                <a-textarea 
                  v-model:value="formData.bio"
                  placeholder="Tell us about yourself..."
                  :rows="3"
                />
              </a-form-item>
            </a-form>
          </a-card>
        </a-col>

        <!-- Sidebar -->
        <a-col :span="8">
          <!-- Security & Settings -->
          <a-card title="Security & Settings" class="security-card">
            <div class="security-item">
              <div class="security-info">
                <h4>Two-Factor Authentication</h4>
                <p>{{ user.twoFactorEnabled ? 'Enabled' : 'Disabled' }}</p>
              </div>
              <a-switch 
                v-model:checked="formData.twoFactorEnabled"
                :disabled="!editMode"
                @change="toggle2FA"
              />
            </div>

            <a-divider />

            <div class="security-item">
              <div class="security-info">
                <h4>Email Notifications</h4>
                <p>Maintenance alerts & updates</p>
              </div>
              <a-switch 
                v-model:checked="formData.emailNotifications"
                :disabled="!editMode"
              />
            </div>

            <a-divider />

            <div class="security-item">
              <div class="security-info">
                <h4>Password</h4>
                <p>Last changed 30 days ago</p>
              </div>
              <a-button 
                type="link" 
                @click="showPasswordModal = true"
                :disabled="!editMode"
              >
                Change
              </a-button>
            </div>

            <a-divider />

            <div class="security-actions">
              <a-button block @click="showSecurityModal = true">
                <SafetyOutlined />
                Security Settings
              </a-button>
              <a-button block type="dashed" style="margin-top: 8px;">
                <DownloadOutlined />
                Download Data
              </a-button>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <!-- Loading State -->
    <div v-else class="loading-container">
      <a-spin size="large">
        <template #indicator>
          <LoadingOutlined style="font-size: 48px;" spin />
        </template>
      </a-spin>
      <p class="loading-text">Loading your profile...</p>
    </div>

    <!-- Modals -->
    <!-- Password Change Modal -->
    <a-modal
      v-model:open="showPasswordModal"
      title="Change Password"
      @ok="changePassword"
      @cancel="resetPasswordForm"
    >
      <a-form :model="passwordForm" layout="vertical">
        <a-form-item label="Current Password" required>
          <a-input-password v-model:value="passwordForm.current" />
        </a-form-item>
        <a-form-item label="New Password" required>
          <a-input-password v-model:value="passwordForm.new" />
        </a-form-item>
        <a-form-item label="Confirm New Password" required>
          <a-input-password v-model:value="passwordForm.confirm" />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- Security Settings Modal -->
    <a-modal
      v-model:open="showSecurityModal"
      title="Security Settings"
      width="800px"
      :footer="null"
    >
      <a-tabs>
        <a-tab-pane key="2fa" tab="Two-Factor Authentication">
          <div class="security-section">
            <div v-if="!user.twoFactorEnabled" class="setup-2fa">
              <h3>Setup Two-Factor Authentication</h3>
              <p>Enhance your account security with 2FA using an authenticator app</p>
              
              <a-steps :current="current2FAStep" style="margin: 24px 0;">
                <a-step title="Install App" description="Download authenticator app" />
                <a-step title="Scan QR Code" description="Add account to app" />
                <a-step title="Verify Code" description="Confirm setup" />
                <a-step title="Backup Codes" description="Save recovery codes" />
              </a-steps>

              <div v-if="current2FAStep === 0" class="step-content">
                <h4>Step 1: Install Authenticator App</h4>
                <p>Download one of these apps on your mobile device:</p>
                <div class="app-recommendations">
                  <div class="app-item">
                    <strong>Google Authenticator</strong>
                    <p>Free, reliable, works offline</p>
                  </div>
                  <div class="app-item">
                    <strong>Microsoft Authenticator</strong>
                    <p>Supports backup and cloud sync</p>
                  </div>
                  <div class="app-item">
                    <strong>Authy</strong>
                    <p>Multi-device support with backup</p>
                  </div>
                </div>
                <a-button type="primary" @click="current2FAStep = 1">
                  Continue
                </a-button>
              </div>

              <div v-if="current2FAStep === 1" class="step-content">
                <h4>Step 2: Scan QR Code</h4>
                <p>Open your authenticator app and scan this QR code:</p>
                
                <div class="qr-section">
                  <div class="qr-code-container">
                    <canvas ref="qrCanvas" width="200" height="200"></canvas>
                  </div>
                  
                  <div class="manual-entry">
                    <h5>Can't scan? Enter manually:</h5>
                    <div class="secret-key-display">
                      <strong>Account:</strong> {{ user.username }}@CMMS<br>
                      <strong>Secret Key:</strong>
                      <a-input 
                        :value="secretKey" 
                        readonly 
                        style="margin-top: 8px;"
                        suffix-icon="copy"
                        @click="copySecretKey"
                      />
                      <a-button 
                        type="link" 
                        size="small" 
                        @click="copySecretKey"
                        style="padding: 0; margin-top: 4px;"
                      >
                        <CopyOutlined /> Copy Secret Key
                      </a-button>
                    </div>
                  </div>
                </div>

                <div class="step-actions">
                  <a-button @click="current2FAStep = 0">Back</a-button>
                  <a-button type="primary" @click="current2FAStep = 2">
                    Continue
                  </a-button>
                </div>
              </div>

              <div v-if="current2FAStep === 2" class="step-content">
                <h4>Step 3: Verify Setup</h4>
                <p>Enter the 6-digit code from your authenticator app:</p>
                
                <a-form layout="vertical" style="max-width: 300px;">
                  <a-form-item label="Verification Code">
                    <a-input 
                      v-model:value="verificationCode" 
                      placeholder="000000"
                      maxlength="6"
                      style="text-align: center; font-size: 18px; letter-spacing: 4px;"
                    />
                  </a-form-item>
                </a-form>

                <div class="step-actions">
                  <a-button @click="current2FAStep = 1">Back</a-button>
                  <a-button 
                    type="primary" 
                    @click="verify2FACode"
                    :disabled="verificationCode.length !== 6"
                  >
                    Verify & Enable 2FA
                  </a-button>
                </div>
              </div>

              <div v-if="current2FAStep === 3" class="step-content">
                <h4>Step 4: Save Backup Codes</h4>
                <p>Save these backup codes in a secure place. You can use them to access your account if you lose your phone:</p>
                
                <div class="backup-codes-section">
                  <div class="backup-codes">
                    <div v-for="code in backupCodes" :key="code" class="backup-code">
                      {{ code }}
                    </div>
                  </div>
                  
                  <div class="backup-actions">
                    <a-button @click="downloadBackupCodes" type="primary">
                      <DownloadOutlined /> Download Backup Codes
                    </a-button>
                    <a-button @click="copyBackupCodes" style="margin-left: 8px;">
                      <CopyOutlined /> Copy to Clipboard
                    </a-button>
                  </div>
                  
                  <a-alert 
                    message="Important" 
                    description="These codes will only be shown once. Make sure to save them in a secure location." 
                    type="warning" 
                    show-icon 
                    style="margin-top: 16px;"
                  />
                </div>

                <div class="step-actions">
                  <a-button type="primary" @click="complete2FASetup">
                    Complete Setup
                  </a-button>
                </div>
              </div>
            </div>

            <!-- When 2FA is already enabled -->
            <div v-else class="manage-2fa">
              <h3>Two-Factor Authentication Enabled</h3>
              <a-alert 
                message="Your account is protected with 2FA" 
                description="Two-factor authentication is currently active on your account." 
                type="success" 
                show-icon 
                style="margin-bottom: 24px;"
              />

              <div class="current-2fa-info">
                <h4>Current Setup Information</h4>
                <div class="info-grid">
                  <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="value">
                      <a-tag color="green">Active</a-tag>
                    </span>
                  </div>
                  <div class="info-item">
                    <span class="label">Enabled on:</span>
                    <span class="value">January 15, 2025</span>
                  </div>
                  <div class="info-item">
                    <span class="label">Last used:</span>
                    <span class="value">Today at 9:30 AM</span>
                  </div>
                  <div class="info-item">
                    <span class="label">Backup codes:</span>
                    <span class="value">8 remaining</span>
                  </div>
                </div>
              </div>

              <div class="manage-actions">
                <h4>Manage Your 2FA</h4>
                <div class="action-buttons">
                  <a-button @click="showRegenerateBackup = true">
                    <ReloadOutlined /> Generate New Backup Codes
                  </a-button>
                  <a-button @click="showReset2FA = true" style="margin-left: 8px;">
                    <SettingOutlined /> Reset 2FA
                  </a-button>
                  <a-button danger @click="showDisable2FA = true" style="margin-left: 8px;">
                    <CloseOutlined /> Disable 2FA
                  </a-button>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <a-tab-pane key="sessions" tab="Active Sessions">
          <a-list 
            :data-source="activeSessions"
            item-layout="horizontal"
          >
            <template #renderItem="{ item }">
              <a-list-item>
                <a-list-item-meta
                  :title="item.device"
                  :description="item.location + ' • ' + item.lastActive"
                />
                <template #actions>
                  <a v-if="!item.current" @click="terminateSession(item.id)">Terminate</a>
                  <span v-else style="color: green;">Current</span>
                </template>
              </a-list-item>
            </template>
          </a-list>
        </a-tab-pane>

        <a-tab-pane key="backup-codes" tab="Backup Codes">
          <div class="backup-codes-tab">
            <h3>Recovery Backup Codes</h3>
            <p>Use these codes to access your account if you lose your authenticator device.</p>
            
            <a-alert 
              message="Keep these codes safe" 
              description="Each backup code can only be used once. Generate new codes if you're running low." 
              type="info" 
              show-icon 
              style="margin-bottom: 24px;"
            />

            <div class="current-backup-status">
              <div class="status-item">
                <span class="label">Total codes:</span>
                <span class="value">10</span>
              </div>
              <div class="status-item">
                <span class="label">Used:</span>
                <span class="value">2</span>
              </div>
              <div class="status-item">
                <span class="label">Remaining:</span>
                <span class="value">8</span>
              </div>
            </div>

            <div class="backup-actions">
              <a-button type="primary" @click="generateNewBackupCodes">
                <ReloadOutlined /> Generate New Codes
              </a-button>
              <a-button @click="downloadCurrentBackupCodes" style="margin-left: 8px;">
                <DownloadOutlined /> Download Current Codes
              </a-button>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-modal>

    <!-- Backup Code Generation Modal -->
    <a-modal
      v-model:open="showRegenerateBackup"
      title="Generate New Backup Codes"
      @ok="regenerateBackupCodes"
      @cancel="showRegenerateBackup = false"
    >
      <a-alert 
        message="Warning" 
        description="Generating new backup codes will invalidate all existing backup codes. Make sure to save the new codes." 
        type="warning" 
        show-icon 
        style="margin-bottom: 16px;"
      />
      <p>Are you sure you want to generate new backup codes?</p>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { 
  CameraOutlined, 
  EditOutlined, 
  CheckOutlined,
  DownOutlined,
  DownloadOutlined,
  DeleteOutlined,
  LoadingOutlined,
  CopyOutlined,
  ReloadOutlined,
  SettingOutlined,
  SafetyOutlined,
  CloseOutlined
} from '@ant-design/icons-vue'
import { useUserStore } from '@/stores/user'
import { message } from 'ant-design-vue'

const userStore = useUserStore()
const editMode = ref(false)
const showPasswordModal = ref(false)
const showSecurityModal = ref(false)
const showAvatarModal = ref(false)
const showRegenerateBackup = ref(false)
const showReset2FA = ref(false)
const showDisable2FA = ref(false)

// 2FA Setup
const current2FAStep = ref(0)
const verificationCode = ref('')
const secretKey = ref('JBSWY3DPEHPK3PXP')
const qrCanvas = ref(null)

// Form data
const formData = reactive({
  jobId: '',
  username: '',
  fullName: '',
  email: '',
  phone: '',
  location: '',
  jobName: '',
  department: '',
  bio: '',
  twoFactorEnabled: false,
  emailNotifications: true
})

// Password form
const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

// Backup codes
const backupCodes = ref([
  '1a2b-3c4d-5e6f',
  '2b3c-4d5e-6f7g',
  '3c4d-5e6f-7g8h',
  '4d5e-6f7g-8h9i',
  '5e6f-7g8h-9i0j',
  '6f7g-8h9i-0j1k',
  '7g8h-9i0j-1k2l',
  '8h9i-0j1k-2l3m',
  '9i0j-1k2l-3m4n',
  '0j1k-2l3m-4n5o'
])

// Computed user data
const user = computed(() => {
  if (!userStore.rawUser?.user) return {}
  
  const userData = userStore.rawUser.user
  return {
    jobId: userData.job_id || 'N/A',
    username: userData.username || 'N/A',
    fullName: userData.job_name || userData.username || 'Unknown User',
    location: userData.location || 'Not specified',
    role: userData.role?.name || 'User',
    email: userData.email || '',
    phone: userData.phone || '',
    jobName: userData.job_name || 'Not specified',
    department: userData.department || 'Not specified',
    twoFactorEnabled: userData.two_factor_enabled || false,
    isOnline: true,
    bio: userData.bio || ''
  }
})

const activeSessions = ref([
  {
    id: 1,
    device: 'Chrome on Windows',
    location: 'Ho Chi Minh City, Vietnam',
    lastActive: '5 minutes ago',
    current: true
  },
  {
    id: 2,
    device: 'Mobile App',
    location: 'Ho Chi Minh City, Vietnam',
    lastActive: '2 hours ago',
    current: false
  }
])

// Methods
const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const getAvatarColor = (username) => {
  const colors = ['#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1', '#13c2c2']
  const index = username ? username.length % colors.length : 0
  return colors[index]
}

const getRoleColor = (role) => {
  const roleColors = {
    'Admin': 'red',
    'Manager': 'blue',
    'Technician': 'green',
    'User': 'default'
  }
  return roleColors[role] || 'default'
}

const handleSaveChanges = () => {
  // API call to save profile changes
  message.success('Profile updated successfully')
  editMode.value = false
}

const toggle2FA = (checked) => {
  if (checked) {
    current2FAStep.value = 0
    showSecurityModal.value = true
  } else {
    showDisable2FA.value = true
  }
}

const generateQRCode = () => {
  nextTick(() => {
    if (qrCanvas.value) {
      const canvas = qrCanvas.value
      const ctx = canvas.getContext('2d')
      
      // Simple QR code simulation (in real app, use QR code library)
      ctx.fillStyle = '#000000'
      ctx.fillRect(0, 0, 200, 200)
      ctx.fillStyle = '#ffffff'
      ctx.fillRect(10, 10, 180, 180)
      
      // Draw QR pattern simulation
      ctx.fillStyle = '#000000'
      for (let i = 0; i < 15; i++) {
        for (let j = 0; j < 15; j++) {
          if ((i + j) % 2 === 0) {
            ctx.fillRect(20 + i * 11, 20 + j * 11, 10, 10)
          }
        }
      }
      
      // Add text
      ctx.fillStyle = '#666666'
      ctx.font = '12px Arial'
      ctx.textAlign = 'center'
      ctx.fillText('CMMS 2FA Setup', 100, 210)
    }
  })
}

const copySecretKey = () => {
  navigator.clipboard.writeText(secretKey.value)
  message.success('Secret key copied to clipboard')
}

const verify2FACode = () => {
  // In real app, verify with backend
  if (verificationCode.value === '123456') {
    current2FAStep.value = 3
    message.success('2FA verification successful')
  } else {
    message.error('Invalid verification code')
  }
}

const complete2FASetup = () => {
  formData.twoFactorEnabled = true
  showSecurityModal.value = false
  current2FAStep.value = 0
  message.success('Two-factor authentication has been enabled successfully')
}

const downloadBackupCodes = () => {
  const codesText = backupCodes.value.join('\n')
  const blob = new Blob([`CMMS Backup Codes\nGenerated: ${new Date().toLocaleDateString()}\n\n${codesText}\n\nKeep these codes safe and secure!`], 
    { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `cmms-backup-codes-${Date.now()}.txt`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
  message.success('Backup codes downloaded successfully')
}

const copyBackupCodes = () => {
  const codesText = backupCodes.value.join('\n')
  navigator.clipboard.writeText(codesText)
  message.success('Backup codes copied to clipboard')
}

const regenerateBackupCodes = () => {
  // Generate new backup codes
  const newCodes = []
  for (let i = 0; i < 10; i++) {
    const code = Math.random().toString(36).substr(2, 4) + '-' +
                  Math.random().toString(36).substr(2, 4) + '-' +
                  Math.random().toString(36).substr(2, 4)
    newCodes.push(code.toUpperCase())
  }
  backupCodes.value = newCodes
  showRegenerateBackup.value = false
  message.success('New backup codes generated successfully')
}

const generateNewBackupCodes = () => {
  showRegenerateBackup.value = true
}

const downloadCurrentBackupCodes = () => {
  downloadBackupCodes()
}

const changePassword = () => {
  if (passwordForm.new !== passwordForm.confirm) {
    message.error('New passwords do not match')
    return
  }
  message.success('Password changed successfully')
  showPasswordModal.value = false
  resetPasswordForm()
}

const resetPasswordForm = () => {
  passwordForm.current = ''
  passwordForm.new = ''
  passwordForm.confirm = ''
}

const terminateSession = (sessionId) => {
  message.success('Session terminated successfully')
}

const exportProfile = () => {
  message.success('Profile data exported successfully')
}

// Watch for 2FA step changes to generate QR code
const watchStep = () => {
  if (current2FAStep.value === 1) {
    generateQRCode()
  }
}

// Initialize form data when user data is loaded
const initializeFormData = () => {
  if (user.value.username) {
    Object.assign(formData, user.value)
  }
}

onMounted(async () => {
  await userStore.fetchUser()
  initializeFormData()
})

// Watch for step changes
import { watch } from 'vue'
watch(() => current2FAStep.value, watchStep)
</script>

<style scoped>
.profile-container {
  padding: 24px;
  background: #f0f2f5;
  min-height: 100vh;
}

.profile-content {
  max-width: 1400px;
  margin: 0 auto;
}

/* Header Styles */
.profile-header {
  margin-bottom: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 24px;
}

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.change-avatar-btn {
  color: #1890ff;
  padding: 4px 8px;
  font-size: 12px;
}

.user-info {
  flex: 1;
}

.user-name {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 600;
  color: #1f1f1f;
}

.user-title {
  margin: 0 0 16px 0;
  font-size: 16px;
  color: #666;
}

.user-badges {
  display: flex;
  gap: 8px;
}

.role-tag, .status-tag {
  font-size: 12px;
  font-weight: 500;
  border-radius: 6px;
}

.header-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Body Styles */
.profile-body {
  margin-top: 24px;
}

.info-card, .security-card {
  margin-bottom: 24px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.info-card .ant-card-head-title,
.security-card .ant-card-head-title {
  font-weight: 600;
  font-size: 16px;
}

/* Security Card */
.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.security-info h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 500;
}

.security-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

.security-actions {
  margin-top: 16px;
}

/* 2FA Setup Styles */
.setup-2fa, .manage-2fa {
  padding: 16px 0;
}

.setup-2fa h3, .manage-2fa h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  color: #1f1f1f;
}

.step-content {
  margin: 24px 0;
}

.step-content h4 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 500;
}

.step-content p {
  margin: 0 0 16px 0;
  color: #666;
}

.app-recommendations {
  display: flex;
  gap: 16px;
  margin: 16px 0 24px 0;
}

.app-item {
  flex: 1;
  padding: 16px;
  border: 1px solid #e8e8e8;
  border-radius: 8px;
  background: #fafafa;
}

.app-item strong {
  display: block;
  margin-bottom: 4px;
  color: #1f1f1f;
}

.app-item p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

/* QR Code Section */
.qr-section {
  display: flex;
  gap: 32px;
  align-items: flex-start;
  margin: 24px 0;
}

.qr-code-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.qr-code-container canvas {
  border: 2px solid #e8e8e8;
  border-radius: 8px;
}

.manual-entry {
  flex: 1;
}

.manual-entry h5 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 500;
}

.secret-key-display {
  padding: 16px;
  background: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e8e8e8;
}

.secret-key-display strong {
  color: #1f1f1f;
}

/* Backup Codes */
.backup-codes-section {
  margin: 24px 0;
}

.backup-codes {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin: 16px 0;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e8e8e8;
}

.backup-code {
  font-family: 'Courier New', monospace;
  font-size: 14px;
  font-weight: 500;
  padding: 8px 12px;
  background: white;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  text-align: center;
}

.backup-actions {
  margin: 16px 0;
  display: flex;
  gap: 8px;
}

/* Step Actions */
.step-actions {
  margin-top: 24px;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

/* Manage 2FA */
.current-2fa-info {
  margin: 24px 0;
  padding: 16px;
  background: #f6ffed;
  border: 1px solid #b7eb8f;
  border-radius: 8px;
}

.current-2fa-info h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 500;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-item .label {
  font-size: 12px;
  color: #666;
}

.info-item .value {
  font-size: 12px;
  font-weight: 500;
  color: #1f1f1f;
}

.manage-actions {
  margin: 24px 0;
}

.manage-actions h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 500;
}

.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Backup Codes Tab */
.backup-codes-tab h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
}

.current-backup-status {
  display: flex;
  gap: 24px;
  margin: 24px 0;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 8px;
}

.status-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.status-item .label {
  font-size: 12px;
  color: #666;
}

.status-item .value {
  font-size: 18px;
  font-weight: 600;
  color: #1890ff;
}

.backup-actions {
  margin: 24px 0;
  display: flex;
  gap: 8px;
}

/* Security Modal */
.security-section {
  padding: 16px 0;
}

.security-section h3 {
  margin: 0 0 8px 0;
  font-size: 16px;
}

.security-section p {
  margin: 0 0 16px 0;
  color: #666;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 60vh;
  gap: 16px;
}

.loading-text {
  font-size: 16px;
  color: #666;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .header-actions {
    flex-direction: row;
  }
  
  .profile-body .ant-col {
    margin-bottom: 16px;
  }

  .qr-section {
    flex-direction: column;
    gap: 16px;
  }

  .app-recommendations {
    flex-direction: column;
  }

  .backup-codes {
    grid-template-columns: 1fr;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .current-backup-status {
    flex-direction: column;
    gap: 12px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .backup-actions {
    flex-direction: column;
  }
}

/* Form Styling */
.ant-form-item-label > label {
  font-weight: 500;
  color: #262626;
}

.ant-input:focus,
.ant-input-focused {
  border-color: #1890ff;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

/* Custom card styling */
.ant-card-head {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-bottom: none;
}

.ant-card-head-title {
  color: white;
}

/* Hover effects */
.ant-card {
  transition: all 0.3s ease;
}

.ant-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Custom Steps */
.ant-steps-item-title {
  font-size: 12px !important;
}

.ant-steps-item-description {
  font-size: 11px !important;
}

/* Verification Code Input */
.ant-input[maxlength="6"] {
  text-align: center;
  font-size: 18px;
  letter-spacing: 4px;
  font-weight: 500;
}

/* Custom Alert Styling */
.ant-alert {
  border-radius: 8px;
}

/* Tab Content */
.ant-tabs-content {
  padding-top: 16px;
}

</style>