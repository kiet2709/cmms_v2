<template>
  <div class="form-wrapper">
    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <p>Loading...</p>
    </div>
    <!-- Empty Form -->
    <div v-else-if="steps.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <polyline points="14,2 14,8 20,8"
            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h3>No Form Available</h3>
      <p>There are no saved forms for this working instruction yet.</p>
    </div>

    <!-- Form Content -->
    <div v-else class="form-content">
      <!-- Header -->
      <!-- <div class="header">
        <div class="header-left">
          <h2 class="title">{{ WICode || 'Working Instruction' }}</h2>
          <div class="subtitle">Step {{ currentStep + 1 }} / {{ steps.length }}</div>
        </div>
        <div class="progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
          </div>
          <div class="progress-text">
            {{ completedItems }} / {{ totalItems }} completed ({{ progressPercentage }}%)
          </div>
        </div>
      </div> -->

      <!-- Step Items -->
      <div v-if="currentStepObj && currentStepObj.formItems && currentStepObj.formItems.length > 0" class="step-items">
        <div
          v-for="(item, idx) in currentStepObj.formItems"
          :key="item.id || idx"
          class="form-section"
          :class="getItemClasses(item)"
        >
          <!-- LABEL -->
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

          <!-- YES/NO -->
          <div v-else-if="item.type === 'yesno'" class="question">
            <div class="question-text">{{ item.question }}</div>
            <div class="radio-group">
              <label class="radio-option" :class="{ selected: selectedAnswers[item.id] === 'Yes' }">
                <input
                  type="radio"
                  :name="'yn-' + item.id"
                  value="Yes"
                  v-model="selectedAnswers[item.id]"
                  @change="updateProgress"
                />
                <span class="radio-mark"></span>
                <span>Yes</span>
              </label>

              <label class="radio-option" :class="{ selected: selectedAnswers[item.id] === 'No' }">
                <input
                  type="radio"
                  :name="'yn-' + item.id"
                  value="No"
                  v-model="selectedAnswers[item.id]"
                  @change="updateProgress"
                />
                <span class="radio-mark"></span>
                <span>No</span>
              </label>
            </div>
          </div>

          <!-- MULTIPLE -->
          <div v-else-if="item.type === 'multiple'" class="question">
            <div class="question-text">{{ item.question }}</div>
            <div class="checkbox-group">
              <label
                v-for="(opt, i) in getOptions(item)"
                :key="i"
                class="checkbox-option"
                :class="{ selected: (selectedAnswers[item.id] || []).includes(opt) }"
              >
                <input
                  type="checkbox"
                  :value="opt"
                  v-model="selectedAnswers[item.id]"
                  @change="updateProgress"
                />
                <span class="checkbox-mark">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
                <span>{{ opt }}</span>
              </label>
            </div>
          </div>

          <!-- SINGLE -->
          <div v-else-if="item.type === 'single'" class="question">
            <div class="question-text">{{ item.question }}</div>
            <div class="radio-group">
              <label
                v-for="(opt, i) in getOptions(item)"
                :key="i"
                class="radio-option"
                :class="{ selected: selectedAnswers[item.id] === opt }"
              >
                <input
                  type="radio"
                  :name="'single-' + item.id"
                  :value="opt"
                  v-model="selectedAnswers[item.id]"
                  @change="updateProgress"
                />
                <span class="radio-mark"></span>
                <span>{{ opt }}</span>
              </label>
            </div>
          </div>

          <!-- STATIC IMAGE -->
          <div v-else-if="item.type === 'staticImage'" class="image-container">
          <!-- Nếu có nhiều ảnh -->
          <template v-if="Array.isArray(item.imageUrls) && item.imageUrls.length > 0">
            
            <!-- 1 ảnh -->
            <div v-if="item.imageUrls.length === 1" class="image-single">
              <img 
                :src="item.imageUrls[0]" 
                alt="Static Image" 
                class="static-image" 
                @click="openImageModal(item.imageUrls[0])" 
              />
            </div>

            <!-- 2 ảnh -->
            <div v-else-if="item.imageUrls.length === 2" class="image-double">
              <div 
                v-for="(src, i) in item.imageUrls" 
                :key="i" 
                class="image-wrapper-large"
                @click="openImageModal(src)"
              >
                <img :src="src" alt="Static Image" class="static-image" />
              </div>
            </div>

            <!-- 3 ảnh -->
            <div v-else-if="item.imageUrls.length === 3" class="image-triple">
              <div 
                v-for="(src, i) in item.imageUrls" 
                :key="i" 
                class="image-wrapper-large"
                @click="openImageModal(src)"
              >
                <img :src="src" alt="Static Image" class="static-image" />
              </div>
            </div>

            <!-- 4 ảnh trở lên -->
            <div v-else class="image-gallery">
              <div 
                v-for="(src, i) in item.imageUrls" 
                :key="i" 
                class="image-wrapper"
                @click="openImageModal(src)"
              >
                <img :src="src" alt="Static Image" class="static-image" />
              </div>
            </div>
          </template>

          <!-- Nếu chỉ có 1 ảnh cũ -->
          <div v-else-if="item.imageUrl" class="image-single">
            <img 
              :src="item.imageUrl" 
              alt="Static Image" 
              class="static-image" 
              @click="openImageModal(item.imageUrl)" 
            />
          </div>
        </div>

          <!-- USER IMAGE -->
          <div v-else-if="item.type === 'userImage'" class="upload-container">
            <div class="question-text">Upload Image</div>
            <input
              class="file-input"
              type="file"
              accept="image/*"
              :id="'file-' + item.id"
              @change="handleFileUpload($event, item.id)"
            />
            <div v-if="uploadedFiles[item.id]" class="upload-preview">
              <img :src="uploadedFiles[item.id]" alt="Uploaded"/>
              <button class="remove-file" @click.prevent="removeFile(item.id)">Remove</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty Step -->
      <div v-else class="empty-state">
        <h3>No Items in This Step</h3>
        <p>This step does not contain any form fields.</p>
      </div>

      <!-- Navigation -->
      <!-- <div class="navigation">
        
      </div> -->

      <!-- Image Modal -->
      <div v-if="showImageModal" class="modal" @click="closeImageModal">
        <div class="modal-inner" @click.stop>
          <button class="modal-close" @click="closeImageModal">✕</button>
          <img :src="modalImageUrl" alt="Preview"/>
        </div>
      </div>
    </div>
    <div class="form-footer">
      <button  :style="{ visibility: currentStep === 0 ? 'hidden' : 'visible' }" class="btn btn-secondary" @click="prevStep" >
        Previous
      </button>

      <div class="step-indicator">
        Step {{ currentStep + 1 }} / {{ steps.length }}
      </div>

      <div class="footer-right">
        <button
          :style="{ visibility: currentStep < steps.length - 1 ? 'visible' : 'hidden' }"
          class="btn btn-primary"
          v-if="currentStep < steps.length"
          @click="nextStep"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from "vue"
import { Modal } from "ant-design-vue"
import axiosClient from "../utils/axiosClient"
import { useUserStore } from '@/stores/user'
import { onMounted } from 'vue'
const userStore = useUserStore()

onMounted(() => {
  userStore.fetchUser()
})

// Props
const props = defineProps({
  id: { type: [String, Number], required: true }
})

// State
const steps = ref([])                 // [{ stepIndex, formItems: [...] }]
const currentStep = ref(0)
const loading = ref(false)
const WICode = ref('')
const selectedAnswers = ref({})       // key = item.id -> value
const uploadedFiles = ref({})         // key = item.id -> dataURL
const showImageModal = ref(false)
const modalImageUrl = ref('')
const formItems = ref([])

// Helpers
const ANSWER_TYPES = ['yesno', 'multiple', 'single', 'userImage']

const currentStepObj = computed(() => steps.value[currentStep.value])

const flatItems = computed(() =>
  steps.value.flatMap(s => Array.isArray(s.formItems) ? s.formItems : [])
)

const totalItems = computed(() =>
  flatItems.value.filter(it => ANSWER_TYPES.includes(it.type)).length
)

const completedItems = computed(() => {
  let done = 0
  flatItems.value.forEach(it => {
    if (!ANSWER_TYPES.includes(it.type)) return
    const val = selectedAnswers.value[it.id]
    if (it.type === 'yesno' && (val === 'Yes' || val === 'No')) done++
    if (it.type === 'single' && !!val) done++
    if (it.type === 'multiple' && Array.isArray(val) && val.length > 0) done++
    if (it.type === 'userImage' && uploadedFiles.value[it.id]) done++
  })
  return done
})

const isFormValid = computed(() => completedItems.value === totalItems.value)

const getOptions = (item) =>
  (item?.options || '')
    .split(',')
    .map(s => s.trim())
    .filter(Boolean)

// Fetch
const fetchForm = async (id) => {
  try {
    loading.value = true

    const res = await axiosClient.get("", {
      params: { c: "WorkingInstructionController", m: "getWiById", id }
    })

    const ok = res?.data?.status === "success" && Array.isArray(res?.data?.data) && res.data.data.length > 0

    if (!ok) {
      steps.value = []
      return
    }

    const row = res.data.data[0]
    console.log(row);
    
    WICode.value = row?.code || ''

    let parsed
    try {
      parsed = JSON.parse(row.schema || '[]')
    } catch (e) {
      console.error("Schema JSON parse error:", e)
      parsed = []
    }

    // Chuẩn hóa: items -> formItems
    const normalized = Array.isArray(parsed)
      ? parsed.map((s, idx) => ({
          stepIndex: Number(s?.stepIndex) || idx + 1,
          formItems: Array.isArray(s?.items) ? s.items : []
        }))
      : []

    steps.value = normalized
    currentStep.value = 0
    initializeAnswers()
  } catch (err) {
    console.error("Error fetching form:", err)
    steps.value = []
  } finally {
    loading.value = false
  }
}

// Init answers
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

// UI class
const getItemClasses = (item) => ({
  'label-section': item.type === 'label',
  'question-section': ['yesno', 'multiple', 'single'].includes(item.type),
  'image-section': item.type === 'staticImage',
  'upload-section': item.type === 'userImage'
})

const handleFileUpload = (event, itemId) => {
  const file = event.target.files?.[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (e) => {
    uploadedFiles.value[itemId] = e.target.result
    updateProgress()
  }
  reader.readAsDataURL(file)
}

const removeFile = (itemId) => {
  delete uploadedFiles.value[itemId]
  updateProgress()
}

const openImageModal = (src) => {
  modalImageUrl.value = src
  showImageModal.value = true
}
const closeImageModal = () => {
  showImageModal.value = false
  modalImageUrl.value = ''
}

const updateProgress = () => {
  // trigger computed reactivity if needed
}

// Nav
const nextStep = () => { if (currentStep.value < steps.value.length - 1) currentStep.value++ }
const prevStep = () => {
  if (currentStep.value > 0) currentStep.value--
}

const emit = defineEmits(['submitted'])

// Watch id
  watch(
    () => props.id,
    (newId) => {
      if (newId !== undefined && newId !== null && newId !== '') {
        fetchForm(newId)
      }
    },
    { immediate: true }
  )
</script>

<style scoped>
.form-wrapper {
  max-width: 900px;
  height: 600px;
  margin: 0 auto;
  padding: 5px;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  border-top: 2px solid #c3c5c9;

  display: flex;
  flex-direction: column; /* để content và footer xếp dọc */
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  border: 1px dashed #e2e8f0;
  border-radius: 12px;
  background: #fff;
}

.form-content {
  flex: 1;                /* chiếm hết chiều cao còn lại */
  overflow-y: auto;       /* cuộn khi tràn */
  padding-bottom: 60px;   /* chừa chỗ cho footer */
  display: grid;
  gap: 8px;
}

.header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  align-items: center;
  background: #fff;
  border-radius: 12px;
  padding: 16px 20px;
  border: 1px solid #e2e8f0;
}

.title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
}

.subtitle {
  color: #6b7280;
  font-size: 14px;
  margin-top: 4px;
}

.progress {
  display: grid;
  gap: 8px;
}

.progress-bar {
  height: 8px;
  background: #eef2f7;
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  transition: width 0.2s ease;
}

.progress-text {
  font-size: 13px;
  color: #374151;
  text-align: right;
}

.step-items {
  /* display: grid; */
  /* gap: 16px; */
}

.form-section {
  background: #fff;
  /* border: 1px solid #e5e7eb; */
  border-radius: 12px;
  padding: 5px;
}

.form-label {
  margin: 0;
  color: #1f2937;
}

.question {
  display: grid;
  gap: 5px;
}

.question-text {
  font-size:medium;
  font-weight: 600;
  color: #1f2937;
}

.radio-group
.checkbox-group {
  justify-content: center;
  display: flex;              /* xếp ngang */
  flex-wrap: wrap;            /* xuống dòng nếu quá dài */
  gap: 150px;                  /* khoảng cách giữa các option */
  margin-top: 8px;
}

.radio-option,
.checkbox-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px;
  padding-right: 90px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: #fafafa;
  cursor: pointer;
  transition: all 0.2s ease;
   margin-top: 8px;
}

.radio-option.selected,
.checkbox-option.selected {
  border-color: #667eea;
  background: #eef2ff;
}

.radio-option input,
.checkbox-option input {
  display: none;
}

.radio-mark {
  width: 15px;
  height: 15px;
  border: 2px solid #9ca3af;
  border-radius: 999px;
  background: #fff;
}

.radio-option.selected .radio-mark {
  border-color: #667eea;
  background: #667eea;
} 

.radio-mark {
  width: 15px;
  height: 15px;
  border: 2px solid #9ca3af;
  border-radius: 999px;
  background: #fff;
}

.radio-option.selected .radio-mark {
  border-color: #667eea;
  background: #667eea;
}

.checkbox-mark {
  width: 18px;
  height: 18px;
  border: 2px solid #9ca3af;
  background: #fff;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.checkbox-option.selected .checkbox-mark {
  border-color: #667eea;
  background: #667eea;
  color: #fff;
}

.image-container {
  text-align: center;
}

/* 1 ảnh */
.image-single {
  display: flex;
  justify-content: center;
}
.image-single img {
  max-width: 65%;
  border-radius: 8px;
  cursor: pointer;
}

/* 2 ảnh */
.image-double {
  display: flex;
  justify-content: center;
  gap: 16px;
}
.image-double .image-wrapper-large {
  flex: 1;
  max-width: 45%;
  border-radius: 8px;
  cursor: pointer;
  overflow: hidden;
}

/* 3 ảnh */
.image-triple {
  display: flex;
  justify-content: center;
  gap: 12px;
}
.image-triple .image-wrapper-large {
  flex: 1;
  max-width: 30%;
  border-radius: 8px;
  cursor: pointer;
  overflow: hidden;
}

/* 4 ảnh trở lên = gallery */
.image-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 12px;
}

.image-wrapper,
.image-wrapper-large {
  border: 1px solid #eee;
  transition: transform 0.2s, box-shadow 0.2s;
}
.image-wrapper:hover,
.image-wrapper-large:hover {
  transform: scale(1.03);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.static-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}


.upload-container {
  display: grid;
  gap: 10px;
}

.file-input {
  padding: 8px 0;
}

.upload-preview {
  display: flex;
  align-items: center;
  gap: 12px;
}

.upload-preview img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.remove-file {
  border: 0;
  background: #fee2e2;
  color: #991b1b;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
}

.navigation {
  display: flex;
  gap: 10px;
  justify-content: center;
  padding: 12px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.btn {
  padding: 5px 12px;
  border-radius: 10px;
  border: 0;
  cursor: pointer;
  font-weight: 600;
}

.btn-primary {
  background-color: #1677ff;
  color: #fff;
}

.btn-secondary {
  background: #f3f4f6;
  color: #111827;
  border: 1px solid #e5e7eb;
}

.btn-success {
  background: #10b981;
  color: #fff;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: grid;
  place-items: center;
  z-index: 50;
}

.modal-inner {
  background: #fff;
  border-radius: 12px;
  padding: 10px;
  max-width: 90vw;
  max-height: 90vh;
}

.modal-inner img {
  max-width: 80vw;
  max-height: 80vh;
  display: block;
}

.modal-close {
  position: absolute;
  margin-top: -8px;
  margin-left: 8px;
  background: transparent;
  color: #fff;
  border: 0;
  font-size: 24px;
  cursor: pointer;
}
.form-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  padding: 6px 16px;
  background: #fff;
  border-top: 1px solid #e5e7eb;
}

.form-footer .btn.btn-secondary {
  justify-self: start; /* nút Back về bên trái */
}

.form-footer .step-indicator {
  justify-self: center; /* step nằm giữa */
  font-weight: 600;
  color: #374151;
}

.form-footer .footer-right {
  justify-self: end; /* nút Next/Submit về bên phải */
  display: flex;
  gap: 8px;
}

</style>
