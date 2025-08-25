<template>
  <div>
    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Inspection Code Section -->
      <div class="section-card inspection-section">
        <div class="section-header inspection-header">
          <h3 class="section-title">Daily Inspection</h3>
          <span class="level-badge">DI</span>
        </div>
        <div class="section-body">
          <a-input
            v-model:value="searchInspection"
            placeholder="Search inspection..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </a-input>
          
          <div class="inspection-list">
            <div
              v-for="code in filteredInspectionCodes"
              :key="code.id"
              class="inspection-item"
              :class="{ active: code.id === selectedInspectionCode }"
              @click="selectInspectionCode(code.id)"
            >
              <span class="inspection-code">{{ code.id }}</span>
              <EyeOutlined class="view-icon" />
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
          <a-input
            v-model:value="searchLevel1"
            placeholder="Search level 1..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </a-input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel1Items"
              :key="item.id"
              class="inspection-item"
              :class="{ active: item.id === selectedLevel1Code }"
              @click="selectMaintenanceCode(item.id, 1)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" />
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
          <a-input
            v-model:value="searchLevel2"
            placeholder="Search level 2..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </a-input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel2Items"
              :key="item.id"
              class="inspection-item"
              :class="{ active: item.id === selectedLevel2Code }"
              @click="selectMaintenanceCode(item.id, 2)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" />
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
          <a-input
            v-model:value="searchLevel3"
            placeholder="Search level 3..."
            class="search-input"
            allowClear
          >
            <template #prefix>
              <SearchOutlined class="search-icon" />
            </template>
          </a-input>
          
          <div class="inspection-list">
            <div
              v-for="item in filteredLevel3Items"
              :key="item.id"
              class="inspection-item"
              :class="{ active: item.id === selectedLevel3Code }"
              @click="selectMaintenanceCode(item.id, 3)"
            >
              <span class="inspection-code">{{ item.code }}</span>
              <EyeOutlined class="view-icon" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { 
  SearchOutlined, 
  EyeOutlined, 
  DownloadOutlined
} from '@ant-design/icons-vue'

export default {
  name: 'MasterPlan',
  components: {
    SearchOutlined,
    EyeOutlined,
    DownloadOutlined
  },
  props: {
    uuid: {
      type: String,
      required: true
    }
  },
  setup(props) {
    // Reactive data
    const selectedInspectionCode = ref('DI-VT-000001')
    const selectedLevel1Code = ref('ML1-VIS-001')
    const selectedLevel2Code = ref('ML2-NDT-001')
    const selectedLevel3Code = ref('ML3-OVH-001')
    const searchInspection = ref('')
    const searchLevel1 = ref('')
    const searchLevel2 = ref('')
    const searchLevel3 = ref('')

    // Mock data for Inspection Codes
    const inspectionCodes = ref([
      { id: 'DI-VT-000001', name: 'Visual Inspection - Structural Components', type: 'Visual' },
      { id: 'DI-VT-000002', name: 'Visual Inspection - Surface Defects', type: 'Visual' },
      { id: 'DI-UT-000001', name: 'Ultrasonic Testing - Thickness Measurement', type: 'Ultrasonic' },
      { id: 'DI-MT-000001', name: 'Magnetic Testing - Crack Detection', type: 'Magnetic' },
      { id: 'DI-PT-000001', name: 'Penetrant Testing - Surface Flaws', type: 'Penetrant' },
      { id: 'DI-RT-000001', name: 'Radiographic Testing - Internal Defects', type: 'Radiographic' }
    ])

    // Mock data for Maintenance Level 1
    const level1Items = ref([
      { id: 'ML1-001', code: 'ML1-VIS-001', name: 'Visual Inspection', category: 'Visual' },
      { id: 'ML1-002', code: 'ML1-CLN-001', name: 'Surface Cleaning', category: 'Cleaning' },
      { id: 'ML1-003', code: 'ML1-LUB-001', name: 'Basic Lubrication', category: 'Lubrication' },
      { id: 'ML1-004', code: 'ML1-CHK-001', name: 'System Check', category: 'Operational' }
    ])

    // Mock data for Maintenance Level 2
    const level2Items = ref([
      { id: 'ML2-001', code: 'ML2-NDT-001', name: 'Non-Destructive Testing', category: 'Testing' },
      { id: 'ML2-002', code: 'ML2-CAL-001', name: 'Component Calibration', category: 'Calibration' },
      { id: 'ML2-003', code: 'ML2-REP-001', name: 'Minor Repairs', category: 'Repair' },
      { id: 'ML2-004', code: 'ML2-PER-001', name: 'Performance Testing', category: 'Performance' }
    ])

    // Mock data for Maintenance Level 3
    const level3Items = ref([
      { id: 'ML3-001', code: 'ML3-OVH-001', name: 'Complete Overhaul', category: 'Overhaul' },
      { id: 'ML3-002', code: 'ML3-ADV-001', name: 'Advanced Testing', category: 'Advanced Testing' },
      { id: 'ML3-003', code: 'ML3-MOD-001', name: 'System Modification', category: 'Modification' },
      { id: 'ML3-004', code: 'ML3-CRT-001', name: 'Certification & Documentation', category: 'Certification' }
    ])

    // Computed properties for filtering
    const filteredInspectionCodes = computed(() => {
      if (!searchInspection.value) return inspectionCodes.value
      return inspectionCodes.value.filter(code => 
        code.id.toLowerCase().includes(searchInspection.value.toLowerCase()) ||
        code.name.toLowerCase().includes(searchInspection.value.toLowerCase()) ||
        code.type.toLowerCase().includes(searchInspection.value.toLowerCase())
      )
    })

    const filteredLevel1Items = computed(() => {
      if (!searchLevel1.value) return level1Items.value
      return level1Items.value.filter(item =>
        item.code.toLowerCase().includes(searchLevel1.value.toLowerCase()) ||
        item.name.toLowerCase().includes(searchLevel1.value.toLowerCase()) ||
        item.category.toLowerCase().includes(searchLevel1.value.toLowerCase())
      )
    })

    const filteredLevel2Items = computed(() => {
      if (!searchLevel2.value) return level2Items.value
      return level2Items.value.filter(item =>
        item.code.toLowerCase().includes(searchLevel2.value.toLowerCase()) ||
        item.name.toLowerCase().includes(searchLevel2.value.toLowerCase()) ||
        item.category.toLowerCase().includes(searchLevel2.value.toLowerCase())
      )
    })

    const filteredLevel3Items = computed(() => {
      if (!searchLevel3.value) return level3Items.value
      return level3Items.value.filter(item =>
        item.code.toLowerCase().includes(searchLevel3.value.toLowerCase()) ||
        item.name.toLowerCase().includes(searchLevel3.value.toLowerCase()) ||
        item.category.toLowerCase().includes(searchLevel3.value.toLowerCase())
      )
    })

    // Methods
    const selectInspectionCode = (code) => {
      selectedInspectionCode.value = code
      console.log('Selected inspection code:', code)
    }

    const selectMaintenanceCode = (code, level) => {
      if (level === 1) selectedLevel1Code.value = code
      else if (level === 2) selectedLevel2Code.value = code
      else if (level === 3) selectedLevel3Code.value = code
      console.log(`Selected Level ${level} code:`, code)
    }

    const handleExport = () => {
      console.log('Exporting master plan for UUID:', props.uuid)
      // TODO: Implement export functionality
    }

    const loadMasterPlanData = async () => {
      console.log('Loading master plan data for UUID:', props.uuid)
      // TODO: Replace with actual API call
    }

    // Lifecycle
    onMounted(() => {
      loadMasterPlanData()
    })

    return {
      props,
      selectedInspectionCode,
      selectedLevel1Code,
      selectedLevel2Code,
      selectedLevel3Code,
      searchInspection,
      searchLevel1,
      searchLevel2,
      searchLevel3,
      filteredInspectionCodes,
      filteredLevel1Items,
      filteredLevel2Items,
      filteredLevel3Items,
      selectInspectionCode,
      selectMaintenanceCode,
      handleExport
    }
  }
}
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
}

.inspection-item:hover .view-icon {
  opacity: 1;
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