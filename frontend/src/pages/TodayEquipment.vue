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

// Call API DailyTaskController
async function fetchTodayEquipments() {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'DailyTaskController',
        m: 'get_today_equipments',
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
    // TODO: call delete API if needed
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

// State for modal view task
const showViewTaskModal = ref(false);
const selectedEquipment = ref(null);

function handleViewTasks(item) {
  selectedEquipment.value = item;
  showViewTaskModal.value = true;
  fetchDailyTasks(item.equipment_id);
}

function closeViewTaskModal() {
  showViewTaskModal.value = false;
  selectedEquipment.value = null;
}

const numberOfElement = ref(0);

async function fetchDailyTasks(equipmentId) {
  try {
    const res = await axiosClient.get('', {
      params: { c: 'DailyTaskController', m: 'getDailyTasksByEquipment', equipment_id: equipmentId }
    });
    
    numberOfElement.value = res.data?.data.length;
    console.log(numberOfElement.value);
  } finally {
    // No loading state change needed
  }
}

// Filter computed property
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

// Check if any filter is applied
const isFilterApplied = computed(() => {
  return filters.value.family || filters.value.category || filters.value.status;
});

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

// Modal delete
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

onMounted(() => {
  fetchTodayEquipments();
});

const breadcrumbItems = [
  { title: 'Dashboard', path: '/dashboard' },
  { title: 'Today Equipments' }
];
</script>

<template>
  <div class="app-container">
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Today's Equipments
        </h1>
        <div class="breadcrumb">
          <span>Tasks</span>
          <span class="separator">›</span>
          <span class="current">List Daily Inspection</span>
        </div>
      </div>
      <div class="header-right">
        <div class="action-buttons">
          <Space>
            <Button @click="handleRefresh" :loading="loading">
              <ReloadOutlined />
              Refresh
            </Button>
          </Space>
        </div>
      </div>
    </div>
  </div>
  <div class="equipment-management">
    <!-- Main Content Card -->
    <Card class="main-content-card">
      <!-- Search and Filter Bar -->
      <div class="toolbar">
        <div class="search-section">
          <Input.Search
            v-model:value="searchQuery"
            placeholder="Search by machine, family, model..."
            size="large"
            class="search-input"
            allow-clear
          />
        </div>
        <div class="filter-section">
          <Space>
            <Dropdown v-model:open="filterVisible" placement="bottomRight" trigger="click">
              <Button class="filter-button">
                <FilterOutlined />
                Filters
                <span v-if="isFilterApplied" class="filter-badge">●</span>
              </Button>
              <template #overlay>
                <div class="filter-dropdown">
                  <div class="filter-group">
                    <label>Family</label>
                    <Select
                      v-model:value="filters.family"
                      placeholder="Select family"
                      allow-clear
                      style="width: 200px"
                      @change="filterVisible = true"
                    >
                      <Select.Option v-for="fam in uniqueFamilies" :key="fam" :value="fam">
                        {{ fam }}
                      </Select.Option>
                    </Select>
                  </div>
                  <div class="filter-group">
                    <label>Category</label>
                    <Select
                      v-model:value="filters.category"
                      placeholder="Select category"
                      allow-clear
                      style="width: 200px"
                      @change="filterVisible = true"
                    >
                      <Select.Option v-for="cat in uniqueCategories" :key="cat" :value="cat">
                        {{ cat }}
                      </Select.Option>
                    </Select>
                  </div>
                  <div class="filter-group">
                    <label>Status</label>
                    <Select
                      v-model:value="filters.status"
                      placeholder="Select status"
                      allow-clear
                      style="width: 200px"
                      @change="filterVisible = true"
                    >
                      <Select.Option v-for="st in uniqueStatuses" :key="st" :value="st">
                        {{ st }}
                      </Select.Option>
                    </Select>
                  </div>
                  <div class="filter-actions">
                    <Button size="small" @click="clearFilters">Clear All</Button>
                  </div>
                </div>
              </template>
            </Dropdown>
          </Space>
        </div>
      </div>

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
                <th>Family</th>
                <th>Model</th>
                <th>Cavity</th>
                <th>Category</th>
                <th>Status</th>
                <th>Done / Total</th>
                <th>Inspectors</th>
                <th>Inspected Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredData" :key="item.equipment_id">
                <td><strong>{{ item.machine_id }}</strong></td>
                <td>{{ item.family }}</td>
                <td>{{ item.model }}</td>
                <td>{{ item.cavity }}</td>
                <td>{{ item.category_name }}</td>
                <td>
                  <Tag :color="getStatusColor(item.status)">{{ item.status }}</Tag>
                </td>
                <td>{{ item.count_done + '/' + item.count_pending }}</td>
                <td>{{ item.inspectors }}</td>
                <td>{{ item.inspected_date }}</td>
                <td>
                  <div class="action-buttons-cell">
                    <Tooltip title="View Tasks of Equipment">
                      <Button type="text" @click="handleViewTasks(item)">
                        <EyeOutlined />
                      </Button>
                    </Tooltip>
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
      :style="{ 
        top: '3px',
        maxHeight: 'calc(100vh - 20px)',
        paddingBottom: '0'
      }"
      :body-style="{
        height: 'auto',
        maxHeight: 'calc(100vh - 120px)',
        overflowY: numberOfElement <= 4 ? 'hidden' : 'auto',
      }"
      :footer="null"
      @cancel="closeViewTaskModal"
    >
      <DailyInspectionPage
        v-if="selectedEquipment"
        :uuid="selectedEquipment.equipment_id"
        :machine-id="selectedEquipment.machine_id"
        @tasks-updated="handleRefresh"
      />
    </Modal>

    <!-- Delete Confirmation -->
    <Modal
      v-model:open="showDeleteModal"
      title="Confirm Delete"
      @ok="performDelete"
      @cancel="cancelDelete"
      ok-text="Yes, Delete"
      cancel-text="Cancel"
      ok-type="danger"
    >
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
  padding: 20px 30px;
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

.breadcrumb {
  font-size: 14px;
  color: #6c757d;
  display: flex;
  align-items: center;
  gap: 8px;
}

.separator {
  color: #dee2e6;
}

.current {
  color: #667eea;
  font-weight: 500;
}

.equipment-management {
  padding: 24px;
  background: #f5f5f5;
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

.filter-button {
  display: flex;
  align-items: center;
  gap: 4px;
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

.modern-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f5f5f5;
  vertical-align: middle;
}

.table-row:hover {
  background: #fafafa;
}

.action-buttons-cell {
  display: flex;
  gap: 8px;
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

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-button {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .modern-table th,
  .modern-table td {
    padding: 8px 6px;
    font-size: 12px;
  }
}
</style>