<template>
  <div>
    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Daily Inspection Section -->
      <div class="section-card inspection-section">
        <div class="section-header inspection-header">
          <h3 class="section-title">Daily Inspection</h3>
          <span class="level-badge">DI</span>
        </div>
        <div class="section-body">
          <Input
            v-model:value="searchInspection"
            placeholder="Search inspection..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </Input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredInspectionItems"
              :key="item.wi_id"
              class="inspection-item"
              :class="{ active: item.wi_id === selectedInspectionCode }"
              @click="selectInspectionCode(item.wi_id)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" @click.stop="openModal(item.wi_id)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Maintenance Level 1 -->
      <div class="section-card maintenance-section level-1-section">
        <div class="section-header level-1-header">
          <h3 class="section-title">Maintenance Level 1</h3>
          <span class="level-badge">ML01</span>
        </div>
        <div class="section-body">
          <Input
            v-model:value="searchLevel1"
            placeholder="Search level 1..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </Input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel1Items"
              :key="item.wi_id"
              class="inspection-item"
              :class="{ active: item.wi_id === selectedLevel1Code }"
              @click="selectMaintenanceCode(item.wi_id, 1)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" @click.stop="openModal(item.wi_id)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Maintenance Level 2 -->
      <div class="section-card maintenance-section level-2-section">
        <div class="section-header level-2-header">
          <h3 class="section-title">Maintenance Level 2</h3>
          <span class="level-badge">ML02</span>
        </div>
        <div class="section-body">
          <Input
            v-model:value="searchLevel2"
            placeholder="Search level 2..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </Input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel2Items"
              :key="item.wi_id"
              class="inspection-item"
              :class="{ active: item.wi_id === selectedLevel2Code }"
              @click="selectMaintenanceCode(item.wi_id, 2)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" @click.stop="openModal(item.wi_id)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Maintenance Level 3 -->
      <div class="section-card maintenance-section level-3-section">
        <div class="section-header level-3-header">
          <h3 class="section-title">Maintenance Level 3</h3>
          <span class="level-badge">ML03</span>
        </div>
        <div class="section-body">
          <Input
            v-model:value="searchLevel3"
            placeholder="Search level 3..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </Input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel3Items"
              :key="item.wi_id"
              class="inspection-item"
              :class="{ active: item.wi_id === selectedLevel3Code }"
              @click="selectMaintenanceCode(item.wi_id, 3)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" @click.stop="openModal(item.wi_id)" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Viewer Modal -->
    <Modal 
      v-model:open="isModalVisible" 
      title="Instruction Details" 
      @cancel="handleModalCancel" 
      width="800px"
    >
      <template #footer>
        <Button @click="handleModalCancel">Close</Button>
      </template>
      <FormViewer v-if="isModalVisible" :key="selectedWiId" :id="selectedWiId" />
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  SearchOutlined, 
  EyeOutlined, 
  DownloadOutlined
} from '@ant-design/icons-vue'
import axiosClient from '../utils/axiosClient'
import { Modal, Button, Input, Card, Space, Breadcrumb, Tooltip, Tag, Dropdown, Select } from 'ant-design-vue'
import FormViewer from './FormViewer.vue'


// Props definition
const props = defineProps({
  uuid: {
    type: String,
    required: true
  },
  id: {
    type: [String, Number],
    required: true
  }
})

// Reactive data
const selectedInspectionCode = ref('')
const selectedLevel1Code = ref('')
const selectedLevel2Code = ref('')
const selectedLevel3Code = ref('')
const searchInspection = ref('')
const searchLevel1 = ref('')
const searchLevel2 = ref('')
const searchLevel3 = ref('')
const isModalVisible = ref(false)
const selectedWiId = ref('')
const loading = ref(false)

// Data from API
const allWorkInstructions = ref([])
const inspectionItems = ref([])
const level1Items = ref([])
const level2Items = ref([])
const level3Items = ref([])

// Computed properties for filtering
const filteredInspectionItems = computed(() => {
  if (!searchInspection.value) return inspectionItems.value
  return inspectionItems.value.filter(item => 
    item.code.toLowerCase().includes(searchInspection.value.toLowerCase()) ||
    item.name.toLowerCase().includes(searchInspection.value.toLowerCase())
  )
})

const filteredLevel1Items = computed(() => {
  if (!searchLevel1.value) return level1Items.value
  return level1Items.value.filter(item =>
    item.code.toLowerCase().includes(searchLevel1.value.toLowerCase()) ||
    item.name.toLowerCase().includes(searchLevel1.value.toLowerCase())
  )
})

const filteredLevel2Items = computed(() => {
  if (!searchLevel2.value) return level2Items.value
  return level2Items.value.filter(item =>
    item.code.toLowerCase().includes(searchLevel2.value.toLowerCase()) ||
    item.name.toLowerCase().includes(searchLevel2.value.toLowerCase())
  )
})

const filteredLevel3Items = computed(() => {
  if (!searchLevel3.value) return level3Items.value
  return level3Items.value.filter(item =>
    item.code.toLowerCase().includes(searchLevel3.value.toLowerCase()) ||
    item.name.toLowerCase().includes(searchLevel3.value.toLowerCase())
  )
})

// Methods
const selectInspectionCode = (wiId) => {
  selectedInspectionCode.value = wiId
}

const selectMaintenanceCode = (wiId, level) => {
  if (level === 1) selectedLevel1Code.value = wiId
  else if (level === 2) selectedLevel2Code.value = wiId
  else if (level === 3) selectedLevel3Code.value = wiId
}

const openModal = (wiId) => {
  selectedWiId.value = wiId
  isModalVisible.value = true
}

const handleModalCancel = () => {
  isModalVisible.value = false
  selectedWiId.value = ''
}

const handleExport = () => {
  console.log('Exporting master plan for UUID:', props.uuid)
  // TODO: Implement export functionality
}

const splitDataByType = (data) => {
  inspectionItems.value = data.filter(item => item.type === 'Daily Inspection')
  level1Items.value = data.filter(item => item.type === 'Maintenance Level 1')
  level2Items.value = data.filter(item => item.type === 'Maintenance Level 2')
  level3Items.value = data.filter(item => item.type === 'Maintenance Level 3')
}

const loadMasterPlanData = async () => {
  try {
    loading.value = true
    
    const res = await axiosClient.get('', {
      params: {
        c: 'WorkingInstructionController',
        m: 'getWiByMachineId',
        equipment_id: props.id
      }
    })

    if (res.data && res.data.status === 'success') {
      allWorkInstructions.value = res.data.data
      splitDataByType(res.data.data)
    } else {
      console.error('Failed to load data:', res.data?.message)
    }
  } catch (error) {
    console.error('Error loading master plan data:', error)
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadMasterPlanData()
})
</script>

<style scoped>
.master-plan-container {
  padding: 24px;
  background: #f8fafc;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
}

.header-content h2.page-title {
  margin: 0 0 4px 0;
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
}

.page-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.export-btn {
  height: 40px;
  border-radius: 8px;
  font-weight: 600;
  background: #2563eb;
  border-color: #2563eb;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 320px 1fr 1fr 1fr;
  gap: 20px;
  height: calc(100vh - 140px);
}

/* Section Cards */
.section-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.section-header {
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
  font-weight: 600;
}

/* Industrial Color Scheme */
.inspection-header {
  background: linear-gradient(135deg, #475569 0%, #334155 100%);
}

.level-1-header {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.level-2-header {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.level-3-header {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
}

.section-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
}

.level-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}

.icon-btn {
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: color 0.2s;
  font-size: 16px;
}

.icon-btn:hover {
  color: white;
}

.section-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

/* Search Input */
.search-input {
  margin-bottom: 16px;
}

.search-input .ant-input {
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
}

.search-icon {
  color: #64748b;
}

/* Inspection List */
.inspection-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.inspection-item {
  display: flex;
  align-items: center;
  padding: 14px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
}

.inspection-item:hover {
  border-color: #3b82f6;
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.inspection-item.active {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.inspection-code {
  flex: 1;
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.view-icon {
  color: #64748b;
  opacity: 0;
  transition: opacity 0.2s;
  cursor: pointer;
}

.inspection-item:hover .view-icon {
  opacity: 1;
}

.view-icon:hover {
  color: #3b82f6;
}

/* Modal */
.modal-content {
  padding: 16px 0;
}

/* Responsive Design */
@media (max-width: 1400px) {
  .content-grid {
    grid-template-columns: 300px 1fr 1fr 1fr;
    gap: 16px;
  }
}

@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;
    height: auto;
  }
  
  .inspection-section {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .header-section {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .master-plan-container {
    padding: 16px;
  }
}

/* Custom scrollbar for industrial look */
.section-body::-webkit-scrollbar {
  width: 8px;
}

.section-body::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.section-body::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.section-body::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>