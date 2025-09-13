<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import DailyInspectionPage from './DailyInspectionPage.vue'
import { 
  EditOutlined, 
  DeleteOutlined, 
  FileSearchOutlined, 
  ExclamationCircleOutlined,
  PlusOutlined,
  ReloadOutlined,
  FilterOutlined,
  HomeOutlined,
  EyeOutlined
} from '@ant-design/icons-vue';
import { 
  Modal, 
  Button, 
  Input, 
  Card, 
  Space, 
  Breadcrumb, 
  Tooltip, 
  Tag, 
  Dropdown, 
  Select
} from 'ant-design-vue';
import MaintenanceTaskPage from './MaintenanceTaskPage.vue';

const router = useRouter();
const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
const filterVisible = ref(false);
const filters = ref({
  family: null,
  category: null,
  status: null
});

// call API DailyTaskController
async function fetchTodayEquipments() {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'MaintenanceController',
        m: 'getMachineWithMaintenancePlan',
      }
    });
    data.value = res.data?.data || [];
  } finally {
    loading.value = false;
  }
}

function handleEdit(equipment_id) {
  router.push({ name: 'UpdateEquipment', params: { uuid: equipment_id } });
}

function confirmDelete(item) {
  deleteTarget.value = item;
  showDeleteModal.value = true;
}

function performDelete() {
  if (deleteTarget.value) {
    console.log('Deleting:', deleteTarget.value.equipment_id);
    // TODO: call delete API nếu cần
  }
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

function cancelDelete() {
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

function handleRefresh() {
  fetchTodayEquipments();
}

function handleAddNew() {
  router.push({ name: 'CreateEquipment' });
}

// state modal view task
const showViewTaskModal = ref(false);
const selectedEquipment = ref(null);

function handleViewTasks(item) {
  selectedEquipment.value = item;
  showViewTaskModal.value = true;
}

function closeViewTaskModal() {
  showViewTaskModal.value = false;
  selectedEquipment.value = null;
}

// filter tại chỗ
const filteredData = computed(() => {
  let filtered = data.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      (item.machine_id && item.machine_id.toLowerCase().includes(q)) ||
      (item.family && item.family.toLowerCase().includes(q)) ||
      (item.model && item.model.toLowerCase().includes(q))
    );
  }
  if (filters.value.family) {
    filtered = filtered.filter(item => item.family === filters.value.family);
  }
  if (filters.value.category) {
    filtered = filtered.filter(item => item.category_name === filters.value.category);
  }
  if (filters.value.status) {
    filtered = filtered.filter(item => item.status === filters.value.status);
  }
  return filtered;
});

const uniqueFamilies = computed(() => [...new Set(data.value.map(i => i.family).filter(Boolean))]);
const uniqueCategories = computed(() => [...new Set(data.value.map(i => i.category_name).filter(Boolean))]);
const uniqueStatuses = computed(() => [...new Set(data.value.map(i => i.status).filter(Boolean))]);

function clearFilters() {
  filters.value = { family: null, category: null, status: null };
  filterVisible.value = false;
}

function getStatusColor(status) {
  const colors = {
    complete: 'green',
    incomplete: 'red',
    pending: 'orange',
    default: 'default'
  };
  return colors[status] || colors.default;
}

// modal delete
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

onMounted(() => {
  fetchTodayEquipments();
});

const breadcrumbItems = [
  { title: 'Dashboard', path: '/dashboard' },
  { title: 'Maintenance Equipments' }
];
</script>

<template>
  <div class="app-container">
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Maintenance Machine
        </h1>
        <div class="breadcrumb">
          <span>Tasks</span>
          <span class="separator">›</span>
          <span class="current">Maintenance</span>
        </div>
      </div>
      <div class="header-right">
        <div class="action-buttons">
          <Space>
            <Button @click="handleRefresh" :loading="loading">
              <ReloadOutlined />
              Refresh
            </Button>
            <!-- <Button @click="handleExport" icon="download">
              <DownloadOutlined />
              Export
            </Button> -->
            <!-- <Button type="primary" @click="handleAddNew">
              <PlusOutlined />
              Add Equipment
            </Button> -->
          </Space>
        </div>
      </div>
    </div>
  </div>
  <div class="equipment-management">
    <!-- Main Content Card -->
    <Card class="main-content-card">
      <!-- Search and Filter Bar -->
      <!-- giữ nguyên toolbar + filter -->

      <!-- Results Info -->
      <div class="results-info">
        <span>Showing {{ filteredData.length }} equipments</span>
      </div>

      <!-- Table -->
      <div class="table-container" :class="{ 'loading': loading }">
        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Machine ID</th>
                <th>Model</th>
                <th>Cavity</th>
                <th>Category</th>
                <th>Status</th>
                <th>Done / Total</th>
                <th>Technician</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredData" :key="item.equipment_id">
                <td><strong>{{ item.machine_id }}</strong></td>
                <td>{{ item.model }}</td>
                <td>{{ item.cavity }}</td>
                <td>{{ item.category }}</td>
                <td>
                  <Tag :color="getStatusColor('incomplete')">{{ item.status ?? 'incomplete' }}</Tag>
                </td>
                <!-- <td>{{ item.count_done + '/' + item.count_pending }}</td> -->
                <td>{{ '' }}</td>
                <td>{{ item.inspectors ?? '' }}</td>
                <td>
                  <div class="action-buttons-cell">
                    <Tooltip title="View Tasks of Equipment">
                      <Button type="text" @click="handleViewTasks(item)">
                        <EyeOutlined />
                      </Button>
                    </Tooltip>
                    <!-- <Tooltip title="Edit Equipment">
                      <Button type="text" @click="handleEdit(item.equipment_id)" class="edit-btn">
                        <EditOutlined />
                      </Button>
                    </Tooltip>
                    <Tooltip title="Delete Equipment Task">
                      <Button type="text" danger @click="confirmDelete(item)" class="delete-btn">
                        <DeleteOutlined />
                      </Button>
                    </Tooltip> -->
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Card>

    <!-- View Tasks Modal -->
    <Modal
      v-model:open="showViewTaskModal"
      title="Daily Inspection"
      width="97%"
      :style="{ top: '3px'}"
      :footer="null"
      @cancel="closeViewTaskModal"
    >
      <MaintenanceTaskPage
        v-if="selectedEquipment"
        :uuid="selectedEquipment.ms_uuid"
        :machine-id="selectedEquipment.machine_id"
      />
    </Modal>

    <!-- Delete Confirmation -->
    <Modal v-model:open="showDeleteModal" title="Confirm Delete" @ok="performDelete" @cancel="cancelDelete" ok-text="Yes, Delete" cancel-text="Cancel" ok-type="danger">
      <div class="delete-content">
        <div class="warning-icon"><ExclamationCircleOutlined /></div>
        <div class="warning-text">
          <p>Are you sure you want to delete this equipment?</p>
          <div class="equipment-info">
            <strong>{{ deleteTarget?.machine_id }}</strong>
            <span class="equipment-details">{{ deleteTarget?.family }} - {{ deleteTarget?.model }}</span>
          </div>
          <p class="warning-note">This action cannot be undone.</p>
        </div>
      </div>
    </Modal>
  </div>
</template>


<style scoped>
.app-container {
  background: rgb(245,245,245);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}
.app-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.app-title {
  font-size: 28px;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}
.User-management {
  padding: 10px;
  background: #f5f5f5;
  min-height: 100vh;
}
.breadcrumb {
  margin-top: 5px;
  font-size: 14px;
  color: #6c757d;
  display: flex;
  align-items: center;
  gap: 8px;
}
.current {
  color: #667eea;
  font-weight: 500;
}
.equipment-management {
  padding: 24px;
  background: #f5f5f5;
  min-height: 100vh;
}


.page-header {
  background: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.title-section h1 {
  margin: 0 0 8px 0;
  font-size: 32px;
  font-weight: 600;
  color: #1a1a1a;
}

.subtitle {
  color: #666;
  font-size: 16px;
  margin: 0;
}

.stats-row {
  margin-bottom: 24px;
}

.stats-row .ant-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.main-content-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: none;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
}

.search-section {
  flex: 1;
  max-width: 400px;
}

.search-input {
  border-radius: 8px;
}

.filter-section {
  flex-shrink: 0;
}

.filter-badge {
  color: #1890ff;
  margin-left: 4px;
}

.filter-dropdown {
  background: white;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  min-width: 250px;
}

.filter-group {
  margin-bottom: 16px;
}

.filter-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.filter-actions {
  border-top: 1px solid #f0f0f0;
  padding-top: 12px;
  text-align: right;
}

.results-info {
  margin-bottom: 16px;
  color: #666;
  font-size: 14px;
}

.table-container {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #f0f0f0;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.loading-spinner {
  padding: 20px;
  font-size: 16px;
  color: #666;
}

.table-responsive {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  font-size: 16px;
}

.modern-table thead {
  background: #fafafa;
  border-bottom: 2px solid #f0f0f0;
}

.modern-table th {
  padding: 16px 12px;
  text-align: left;
  font-weight: 600;
  color: #333;
  border-bottom: none;
  white-space: nowrap;
}

.modern-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.2s;
}

.modern-table th.sortable:hover {
  background: #f0f0f0;
}

.th-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sort-indicator {
  margin-left: 8px;
  font-size: 12px;
  opacity: 0.6;
}

.sort-asc, .sort-desc {
  color: #1890ff;
  opacity: 1;
}

.modern-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f5f5f5;
  vertical-align: middle;
}

.table-row {
  transition: background-color 0.2s;
}

.table-row:hover {
  background: #fafafa;
}

.machine-id {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.history-count {
  font-weight: 500;
}

.view-plan-btn {
  color: #1890ff;
}

.action-buttons-cell {
  display: flex;
  gap: 8px;
}

.edit-btn:hover {
  color: #1890ff;
  background: #f6ffed;
}

.delete-btn:hover {
  color: #ff4d4f;
  background: #fff2f0;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.empty-content {
  max-width: 300px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 64px;
  color: #d9d9d9;
  margin-bottom: 24px;
}

.empty-content h3 {
  color: #666;
  margin-bottom: 8px;
}

.empty-content p {
  margin-bottom: 24px;
}

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;
}

.pagination-info {
  color: #666;
  font-size: 14px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.current-page {
  font-weight: 600;
  color: #1890ff;
}

.page-separator {
  color: #999;
}

.pagination-btn {
  border-radius: 6px;
}

/* Modal Styles */
.master-plan-modal .ant-modal-header {
  border-radius: 12px 12px 0 0;
}

.master-plan-content {
  padding: 20px 0;
}

.todo-note {
  color: #666;
  font-style: italic;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 6px;
  margin-top: 16px;
}

.delete-modal .ant-modal-header {
  border-radius: 12px 12px 0 0;
}

.delete-content {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.warning-icon {
  font-size: 24px;
  color: #ff4d4f;
  flex-shrink: 0;
  margin-top: 4px;
}

.warning-text {
  flex: 1;
}

.equipment-info {
  margin: 16px 0;
  padding: 12px;
  background: #fff2f0;
  border-radius: 6px;
  border-left: 3px solid #ff4d4f;
}

.equipment-info strong {
  display: block;
  font-size: 16px;
  margin-bottom: 4px;
}

.equipment-details {
  color: #666;
  font-size: 14px;
}

.warning-note {
  color: #999;
  font-size: 13px;
  margin: 16px 0 0 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .equipment-management {
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .pagination-container {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .stats-row {
    margin-bottom: 16px;
  }
  
  .stats-row .ant-col {
    margin-bottom: 16px;
  }
  
  .action-buttons-cell {
    flex-direction: column;
    align-items: center;
  }
}

@media (max-width: 480px) {
  .modern-table th,
  .modern-table td {
    padding: 8px 6px;
    font-size: 12px;
  }
  
  .page-header {
    padding: 20px;
  }
  
  .title-section h1 {
    font-size: 24px;
  }
}


/* CSS cho tất cả th có class sortable hoặc tất cả th nếu muốn */
/* th.sortable, th {
  text-align: center !important;       

.th-content {
  display: flex !important;
  justify-content: center !important;  
  align-items: center !important;     
  gap: 4px !important;                 
}

.sort-indicator {
  display: inline-flex !important;
} */
</style>