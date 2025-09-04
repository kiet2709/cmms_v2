<!-- DailyPage.vue -->
<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import { 
  EditOutlined, DeleteOutlined, FileSearchOutlined, 
  ExclamationCircleOutlined, ReloadOutlined, 
  FilterOutlined, HomeOutlined, EyeOutlined,
  ThunderboltOutlined,PlayCircleOutlined , RocketOutlined, ToolOutlined
} from '@ant-design/icons-vue';
import { Modal, Button, Input, Card, Space, Breadcrumb, Tooltip, Tag, Dropdown, Select } from 'ant-design-vue';
import FormViewer from './FormViewer.vue';
import WITaskViewer from './WITaskViewer.vue';

const route = useRoute();
const uuid = route.params.uuid;

// Props definition
const props = defineProps({
  uuid: {
    type: String,
    required: true
  },
  machineId: {
    type: String,
    required: true
  }
})



const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
const filterVisible = ref(false);
const filters = ref({ status: null });

async function fetchDailyTasks() {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: { c: 'DailyTaskController', m: 'getDailyTasksByEquipment', equipment_id: props.uuid }
    });
    data.value = res.data?.data || [];
    console.log(props.uuid);
    
  } finally {
    loading.value = false;
  }
}

const filteredData = computed(() => {
  let filtered = data.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      (item.code && item.code.toLowerCase().includes(q)) ||
      (item.content && item.content.toLowerCase().includes(q)) ||
      (item.inspector_name && item.inspector_name.toLowerCase().includes(q))
    );
  }
  if (filters.value.status) filtered = filtered.filter(i => i.status === filters.value.status);
  return filtered;
});

const uniqueStatuses = computed(() => [...new Set(data.value.map(i => i.status).filter(Boolean))]);

function clearFilters() {
  filters.value = { status: null };
  filterVisible.value = false;
}

function getStatusColor(status) {
  const colors = { complete: 'green', incomplete: 'red', pending: 'orange', default: 'default', start: 'blue', done: 'green'};
  return colors[status] || colors.default;
}

function handleRefresh() { fetchDailyTasks(); }

function confirmDelete(task) {
  deleteTarget.value = task;
  showDeleteModal.value = true;
}

function performDelete() {
  if (deleteTarget.value) {
    console.log('Deleting task:', deleteTarget.value.code);
  }
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

function cancelDelete() {
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

onMounted(() => { fetchDailyTasks(); });

const breadcrumbItems = [
  { title: 'Dashboard', path: '/dashboard' },
  { title: 'Today Equipments', path: '/today-equipments' },
  { title: 'Daily Tasks' }
];

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

// 👁️ Modal view form
const isModalOpen = ref(false);
const currentId = ref(null);
function openFormViewer(task) {
  currentId.value = task.wi_id;
  isModalOpen.value = true;
}
function closeModal() {
  isModalOpen.value = false;
  currentId.value = null;
}

const emit = defineEmits(['tasks-updated'])

function loadNewDataWhenSubmittedAndCloseModal()
{
  handleRefresh();
  closeModal();
  emit('tasks-updated');
}

</script>

<template>
  <div class="equipment-management">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1>Daily Tasks</h1>
          <p class="subtitle">Tasks of equipment ID: {{ props.machineId }}</p>
        </div>
        <div class="action-buttons">
          <Space><Button @click="handleRefresh" :loading="loading"><ReloadOutlined /> Refresh</Button></Space>
        </div>
      </div>
    </div>

    <!-- Table -->
    <Card class="main-content-card">
      <div class="results-info"><span>Showing {{ filteredData.length }} tasks</span></div>

      <div class="table-container">
        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Code</th><th>Content</th><th>Date Start</th>
                <th>Inspected Date</th><th>Status</th><th>Result</th><th>Inspector</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="task in filteredData" :key="task.code + task.date_start">
                <td><strong>{{ task.code }}</strong></td>
                <td>{{ task.content }}</td>
                <td>{{ task.date_start }}</td>
                <td>{{ task.inspected_date }}</td>
                <td><Tag :color="getStatusColor(task.status ?? 'start')">{{ task.status ?? 'start' }}</Tag></td>
                <td>{{ task.result }}</td>
                <td>{{ task.inspector_name }}</td>
                <td>
                  <div class="action-buttons-cell">
                    <Tooltip title="Execute Tasks"><Button type="text" @click="openFormViewer(task)"><ToolOutlined  /></Button></Tooltip>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="!loading && filteredData.length === 0" class="empty-state">
          <FileSearchOutlined class="empty-icon" />
          <h3>No task found</h3><p>Try adjusting your filters</p>
        </div>
      </div>
    </Card>

    <!-- Delete Confirmation -->
    <Modal v-model:open="showDeleteModal" title="Confirm Delete" 
           @ok="performDelete" @cancel="cancelDelete" 
           ok-text="Yes, Delete" cancel-text="Cancel" ok-type="danger">
      <div class="delete-content">
        <div class="warning-icon"><ExclamationCircleOutlined /></div>
        <div class="warning-text">
          <p>Are you sure you want to delete this task?</p>
          <div class="equipment-info">
            <strong>{{ deleteTarget?.code }}</strong>
            <span class="equipment-details">{{ deleteTarget?.content }}</span>
          </div>
          <p class="warning-note">This action cannot be undone.</p>
        </div>
      </div>
    </Modal>

    <!-- 👁️ Form Viewer Modal -->
    <Modal :style="{ top: '3px'}" v-model:open="isModalOpen" title="Instruction Details" @cancel="closeModal" width="800px">
      <template #footer>
        <Button @click="closeModal">Close</Button>
      </template>
      <WITaskViewer v-if="isModalOpen" :key="currentId" :id="currentId" @submitted="loadNewDataWhenSubmittedAndCloseModal" />
    </Modal>
  </div>
</template>




<style scoped>
.equipment-management {
  padding: 24px;
  background: #f5f5f5;
  min-height: 100%;
}

.breadcrumb-nav {
  margin-bottom: 24px;
  font-size: 14px;
}

.breadcrumb-nav a {
  color: #666;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.breadcrumb-nav a:hover {
  color: #1890ff;
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
th.sortable, th {
  text-align: center !important;       /* căn giữa text */
}

.th-content {
  display: flex !important;
  justify-content: center !important;  /* căn giữa theo chiều ngang */
  align-items: center !important;      /* căn giữa theo chiều dọc */
  gap: 4px !important;                 /* khoảng cách giữa text và icon */
}

.sort-indicator {
  display: inline-flex !important;
}
</style>