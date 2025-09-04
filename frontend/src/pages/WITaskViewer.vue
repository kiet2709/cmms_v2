<template>
  <div class="viewer-container">
    <!-- Header Section -->
<div class="header-section" style="max-width:800px; margin:0 auto; display:flex; flex-direction:column; align-items:center; text-align:center; margin-bottom: 30px;">
  
  <div class="header-content" style="display:flex; flex-direction:row; align-items:center; justify-content:center; gap:12px;">
    <div class="form-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <div class="header-text">
      <h1 class="form-title" style="margin:0;">{{ WICode || 'Working Instruction Form' }}</h1>
      <p class="form-subtitle" style="margin:0;">Please complete all sections below</p>
    </div>
  </div>

  <div class="progress-indicator" style="width:100%; margin-top:16px; text-align:center;">
    <div class="progress-bar" style="width:100%; height:8px; background:#eee; border-radius:4px; overflow:hidden;">
      <div class="progress-fill" :style="{ width: progressPercentage + '%', background:'#4caf50', height:'100%' }"></div>
    </div>
    <span class="progress-text" style="display:block; margin-top:4px;">{{ completedItems }} of {{ totalItems }} completed</span>
  </div>
</div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading form...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="formItems.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h3>No Form Available</h3>
      <p>There are no saved forms for this working instruction yet.</p>
    </div>

    <!-- Form Content -->
    <div v-else class="form-content">
      <div
        v-for="(item, index) in formItems"
        :key="index"
        class="form-section"
        :class="getItemClasses(item)"
      >
        <!-- Section Number -->
        <div v-if="item.type !== 'label'" class="section-number">
          {{ getSectionNumber(index) }}
        </div>

        <!-- Render Label -->
        <component
          v-if="item.type === 'label'"
          :is="item.heading || 'h3'"
          class="form-label"
          :style="{
            fontWeight: item.bold ? '700' : '600',
            fontStyle: item.italic ? 'italic' : 'normal',
            textDecoration: item.underline ? 'underline' : 'none'
          }"
        >
          {{ item.text }}
        </component>

        <!-- Render Yes/No -->
        <div v-else-if="item.type === 'yesno'" class="question-container">
          <h4 class="question-text">{{ item.question }}</h4>
          <div class="radio-group">
            <label class="radio-option" :class="{ 'selected': selectedAnswers['yn' + index] === 'Yes' }">
              <input 
                type="radio" 
                :name="'yn' + index" 
                value="Yes" 
                v-model="selectedAnswers['yn' + index]"
                @change="updateProgress"
              />
              <span class="radio-custom"></span>
              <span class="option-text">Yes</span>
              <div class="option-icon success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </label>
            <label class="radio-option" :class="{ 'selected': selectedAnswers['yn' + index] === 'No' }">
              <input 
                type="radio" 
                :name="'yn' + index" 
                value="No" 
                v-model="selectedAnswers['yn' + index]"
                @change="updateProgress"
              />
              <span class="radio-custom"></span>
              <span class="option-text">No</span>
              <div class="option-icon error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </label>
          </div>
        </div>

        <!-- Render Multiple Choice -->
        <div v-else-if="item.type === 'multiple'" class="question-container">
          <h4 class="question-text">{{ item.question }}</h4>
          <div class="checkbox-group">
            <label 
              v-for="(opt, i) in item.options.split(',')" 
              :key="i" 
              class="checkbox-option"
              :class="{ 'selected': isMultipleSelected(index, i) }"
            >
              <input 
                type="checkbox" 
                v-model="selectedAnswers['multiple' + index]"
                :value="opt.trim()"
                @change="updateProgress"
              />
              <span class="checkbox-custom">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <span class="option-text">{{ opt.trim() }}</span>
            </label>
          </div>
        </div>

        <!-- Render Single Choice -->
        <div v-else-if="item.type === 'single'" class="question-container">
          <h4 class="question-text">{{ item.question }}</h4>
          <div class="radio-group">
            <label 
              v-for="(opt, i) in item.options.split(',')" 
              :key="i" 
              class="radio-option"
              :class="{ 'selected': selectedAnswers['single' + index] === opt.trim() }"
            >
              <input 
                type="radio" 
                :name="'single' + index" 
                :value="opt.trim()"
                v-model="selectedAnswers['single' + index]"
                @change="updateProgress"
              />
              <span class="radio-custom"></span>
              <span class="option-text">{{ opt.trim() }}</span>
            </label>
          </div>
        </div>

        <!-- Render Static Image -->
        <div v-else-if="item.type === 'staticImage'" class="image-container">
          <div class="image-wrapper">
            <img v-if="item.imageUrl" :src="item.imageUrl" class="static-image" />
            <div class="image-overlay">
              <button class="image-expand" @click="openImageModal(item.imageUrl)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Render User Image Upload -->
        <div v-else-if="item.type === 'userImage'" class="upload-container">
          <h4 class="question-text">Upload Image</h4>
          <div class="upload-area" :class="{ 'has-file': uploadedFiles[index] }">
            <input 
              type="file" 
              :id="'file-' + index"
              accept="image/*"
              @change="handleFileUpload($event, index)"
              style="display: none;"
            />
            <label :for="'file-' + index" class="upload-label">
              <div v-if="!uploadedFiles[index]" class="upload-placeholder">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p>Click to upload image</p>
                <span>PNG, JPG, JPEG up to 10MB</span>
              </div>
              <div v-else class="upload-preview">
                <img :src="uploadedFiles[index]" alt="Uploaded image" />
                <div class="file-info">
                  <p>Image uploaded successfully</p>
                  <button type="button" @click.prevent="removeFile(index)" class="remove-file">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                      <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button class="btn btn-secondary" @click="resetForm">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M1 4v6h6M23 20v-6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Reset Form
        </button>
        <button class="btn btn-primary" @click="submitForm" :disabled="!isFormValid">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Submit Form
        </button>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="modal-overlay" @click="closeImageModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close" @click="closeImageModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <img :src="modalImageUrl" alt="Expanded image" class="modal-image" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from "vue"
import axiosClient from "../utils/axiosClient"

// Props
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
})

// Reactive data
const formItems = ref([])
const loading = ref(false)
const WICode = ref('')
const selectedAnswers = ref({})
const uploadedFiles = ref({})
const showImageModal = ref(false)
const modalImageUrl = ref('')

// Computed properties
const totalItems = computed(() => {
  return formItems.value.filter(item => 
    ['yesno', 'multiple', 'single', 'userImage'].includes(item.type)
  ).length
})

const completedItems = computed(() => {
  let completed = 0
  formItems.value.forEach((item, index) => {
    if (item.type === 'yesno' && selectedAnswers.value['yn' + index]) completed++
    if (item.type === 'multiple' && selectedAnswers.value['multiple' + index]?.length > 0) completed++
    if (item.type === 'single' && selectedAnswers.value['single' + index]) completed++
    if (item.type === 'userImage' && uploadedFiles.value[index]) completed++
  })
  console.log(completed);
  
  return completed
})

const progressPercentage = computed(() => {
  return totalItems.value > 0 ? Math.round((completedItems.value / totalItems.value) * 100) : 0
})

const isFormValid = computed(() => {
  return completedItems.value === totalItems.value
})

// Methods
const fetchForm = async (id) => {
  try {
    loading.value = true
    const res = await axiosClient.get("", {
      params: {
        c: "DailyTaskController",
        m: "getWiById",
        id
      }
    })

    console.log(res.data);
    

    WICode.value = res.data.data[0].code;

    if (res.data.status === "success" && res.data.data.length > 0) {
      const schema = res.data.data[0].schema
      formItems.value = JSON.parse(schema)
      initializeAnswers()
    } else {
      formItems.value = []
    }
  } catch (err) {
    console.error("Error fetching form:", err)
    formItems.value = []
  } finally {
    loading.value = false
  }
}

const initializeAnswers = () => {
  selectedAnswers.value = {}
  uploadedFiles.value = {}

  formItems.value.forEach((item, index) => {
    if (item.type === 'multiple') {
      // nếu có answer thì gán, không thì mảng rỗng
      selectedAnswers.value['multiple' + index] = item.answer || []
    }
    if (item.type === 'yesno') {
      selectedAnswers.value['yn' + index] = item.answer || null
    }
    if (item.type === 'single') {
      selectedAnswers.value['single' + index] = item.answer || null
    }
    if (item.type === 'userImage') {
      uploadedFiles.value[index] = item.answer || null
    }
  })
}
const resetAnswers = () => {
  selectedAnswers.value = {}
  uploadedFiles.value = {}
  formItems.value.forEach((item, index) => {
    if (item.type === 'multiple') {
      selectedAnswers.value['multiple' + index] = []
    }
  })
}

const getItemClasses = (item) => {
  return {
    'label-section': item.type === 'label',
    'question-section': ['yesno', 'multiple', 'single'].includes(item.type),
    'image-section': item.type === 'staticImage',
    'upload-section': item.type === 'userImage'
  }
}

const getSectionNumber = (index) => {
  let sectionCount = 0
  for (let i = 0; i <= index; i++) {
    if (formItems.value[i].type !== 'label') {
      sectionCount++
    }
  }
  return sectionCount
}

const isMultipleSelected = (itemIndex, optionIndex) => {
  const answers = selectedAnswers.value['multiple' + itemIndex] || []
  const option = formItems.value[itemIndex].options.split(',')[optionIndex].trim()
  return answers.includes(option)
}

const handleFileUpload = (event, index) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      uploadedFiles.value[index] = e.target.result
      updateProgress()
    }
    reader.readAsDataURL(file)
  }
}

const removeFile = (index) => {
  delete uploadedFiles.value[index]
  updateProgress()
}

const openImageModal = (imageUrl) => {
  modalImageUrl.value = imageUrl
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  modalImageUrl.value = ''
}

const updateProgress = () => {
  // This method is called whenever an answer changes to update the progress
}

const resetForm = () => {
  if (confirm('Are you sure you want to reset all answers?')) {
    resetAnswers();
  }
}

const submitForm = () => {
  if (isFormValid.value) {
    // clone schema gốc
    const updatedSchema = JSON.parse(JSON.stringify(formItems.value))

    // gắn câu trả lời vào schema mới
    updatedSchema.forEach((item, index) => {
      if (item.type === 'yesno') {
        item.answer = selectedAnswers.value['yn' + index] || null
      }
      if (item.type === 'multiple') {
        item.answer = selectedAnswers.value['multiple' + index] || []
      }
      if (item.type === 'single') {
        item.answer = selectedAnswers.value['single' + index] || null
      }
      if (item.type === 'userImage') {
        item.answer = uploadedFiles.value[index] || null
      }
    })

    // gói thông tin
    const formData = {
      uuid: props.id,
      wiCode: WICode.value,
      schema: updatedSchema,
      inspectorId: userStore.rawUser.user.uuid,
    }

    console.log("📌 Schema sau khi user submit:")
    console.log(JSON.stringify(formData, null, 2))
    console.log('--------');
    console.log(WICode);
    save(formData);
    
    emit('submitted')

  };
    
}

const emit = defineEmits(['submitted'])

const save = async (formData) => {
    try {
      await axiosClient.post('', formData, {
        params: { c: 'DailyTaskController', m: 'doDailyTask' },
        headers: { 'Content-Type': 'application/json' },
      });
      alert("Submit successfully!");
    } catch (err) {
      console.error("Save failed:", err);
      alert("Save failed! Please try again.");
    } 
}


import { useUserStore } from '@/stores/user'
import { onMounted } from 'vue'
const userStore = useUserStore()

onMounted(() => {
  userStore.fetchUser()
})


// Watch for prop changes
watch(
  () => props.id,
  (newId) => {
    if (newId) fetchForm(newId)
  },
  { immediate: true }
)
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.viewer-container {
  min-height: 100vh;
  background: rgb(245, 245, 245);
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Header Section */
.header-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 24px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
}

.form-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.form-title {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.form-subtitle {
  margin: 4px 0 0 0;
  font-size: 16px;
  color: #718096;
  font-weight: 400;
}

.progress-indicator {
  display: flex;
  align-items: center;
  gap: 16px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 14px;
  font-weight: 600;
  color: #4a5568;
  white-space: nowrap;
}

/* Loading State */
.loading-container {
  text-align: center;
  padding: 80px 20px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-text {
  font-size: 18px;
  color: #4a5568;
  margin: 0;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  color: #cbd5e0;
}

.empty-state h3 {
  font-size: 24px;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 8px;
}

.empty-state p {
  font-size: 16px;
  color: #718096;
  margin: 0;
}

/* Form Content */
.form-content {
  max-width: 800px;
  margin: 0 auto;
}

.form-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}

.form-section:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.form-section.label-section {
  padding: 24px 32px;
  text-align: center;
  background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
}

.form-section.question-section,
.form-section.upload-section {
  padding: 32px;
}

.form-section.image-section {
  padding: 20px;
}

.section-number {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 600;
}

.form-label {
  color: #2d3748;
  margin: 0;
  line-height: 1.4;
}

.question-container {
  margin-top: 8px;
}

.question-text {
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 20px 0;
  line-height: 1.4;
}

/* Radio and Checkbox Groups */
.radio-group,
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.radio-option,
.checkbox-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #f8fafc;
}

.radio-option:hover,
.checkbox-option:hover {
  border-color: #cbd5e0;
  background: #ffffff;
  transform: translateX(4px);
}

.radio-option.selected,
.checkbox-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #ebf4ff 0%, #e6fffa 100%);
  transform: translateX(4px);
}

.radio-option input[type="radio"],
.checkbox-option input[type="checkbox"] {
  display: none;
}

.radio-custom,
.checkbox-custom {
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: white;
}

.checkbox-custom {
  border-radius: 6px;
}

.radio-option.selected .radio-custom,
.checkbox-option.selected .checkbox-custom {
  border-color: #667eea;
  background: #667eea;
  color: white;
}

.radio-custom::after {
  content: '';
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: transparent;
  transition: background 0.2s ease;
}

.radio-option.selected .radio-custom::after {
  background: white;
}

.checkbox-custom svg {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.checkbox-option.selected .checkbox-custom svg {
  opacity: 1;
}

.option-text {
  flex: 1;
  font-size: 16px;
  font-weight: 500;
  color: #4a5568;
}

.option-icon {
  width: 20px;
  height: 20px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.radio-option.selected .option-icon {
  opacity: 1;
}

.option-icon.success {
  color: #38a169;
}

.option-icon.error {
  color: #e53e3e;
}

/* Image Styles */
.image-container {
  text-align: center;
}

.image-wrapper {
  position: relative;
  display: inline-block;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.static-image {
  max-width: 100%;
  height: auto;
  display: block;
  transition: transform 0.3s ease;
}

.image-overlay {
  position: absolute;
  top: 12px;
  right: 12px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.image-wrapper:hover .image-overlay {
  opacity: 1;
}

.image-wrapper:hover .static-image {
  transform: scale(1.02);
}

.image-expand {
  width: 40px;
  height: 40px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease;
}

.image-expand:hover {
  background: rgba(0, 0, 0, 0.9);
}

/* Upload Styles */
.upload-area {
  border: 2px dashed #cbd5e0;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  transition: all 0.2s ease;
  background: #f8fafc;
}

.upload-area:hover {
  border-color: #667eea;
  background: #ebf4ff;
}

.upload-area.has-file {
  border-color: #38a169;
  background: #f0fff4;
}

.upload-label {
  display: block;
  cursor: pointer;
}

.upload-placeholder {
  color: #718096;
}

.upload-placeholder svg {
  color: #a0aec0;
  margin-bottom: 16px;
}

.upload-placeholder p {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #4a5568;
}

.upload-placeholder span {
  font-size: 14px;
  color: #718096;
}

.upload-preview {
  display: flex;
  align-items: center;
  gap: 16px;
  text-align: left;
}

.upload-preview img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.file-info {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.file-info p {
  font-size: 16px;
  font-weight: 600;
  color: #38a169;
  margin: 0;
}

.remove-file {
  width: 32px;
  height: 32px;
  background: #fed7d7;
  color: #e53e3e;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-file:hover {
  background: #feb2b2;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 40px;
  padding: 32px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

.btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  min-width: 140px;
  justify-content: center;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
}

.btn-secondary {
  background: #f7fafc;
  color: #4a5568;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #edf2f7;
  border-color: #cbd5e0;
  transform: translateY(-1px);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(8px);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from { 
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to { 
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 40px;
  height: 40px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1001;
  transition: background 0.2s ease;
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.9);
}

.modal-image {
  width: 100%;
  height: auto;
  display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
  .viewer-container {
    padding: 12px;
  }
  
  .header-section {
    padding: 24px 20px;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .form-title {
    font-size: 24px;
  }
  
  .progress-indicator {
    flex-direction: column;
    gap: 12px;
  }
  
  .progress-text {
    text-align: center;
  }
  
  .form-section.question-section,
  .form-section.upload-section {
    padding: 24px 20px;
  }
  
  .section-number {
    position: static;
    margin: 0 auto 16px;
  }
  
  .action-buttons {
    flex-direction: column;
    padding: 24px 20px;
  }
  
  .btn {
    width: 100%;
  }
  
  .radio-group,
  .checkbox-group {
    gap: 8px;
  }
  
  .radio-option,
  .checkbox-option {
    padding: 12px;
  }
  
  .question-text {
    font-size: 16px;
  }
  
  .upload-area {
    padding: 24px 16px;
  }
  
  .upload-preview {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .form-title {
    font-size: 20px;
  }
  
  .form-subtitle {
    font-size: 14px;
  }
  
  .form-section {
    margin-bottom: 16px;
  }
  
  .radio-option:hover,
  .checkbox-option:hover,
  .radio-option.selected,
  .checkbox-option.selected {
    transform: none;
  }
}

/* Print Styles */
@media print {
  .viewer-container {
    background: white;
    padding: 0;
  }
  
  .header-section {
    background: white;
    box-shadow: none;
    border-bottom: 2px solid #e2e8f0;
  }
  
  .progress-indicator,
  .action-buttons {
    display: none;
  }
  
  .form-section {
    background: white;
    box-shadow: none;
    border: 1px solid #e2e8f0;
    break-inside: avoid;
  }
  
  .modal-overlay {
    display: none;
  }
}

</style>