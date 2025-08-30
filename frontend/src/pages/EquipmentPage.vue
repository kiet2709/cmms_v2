<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import MasterPlan from './MasterPlan.vue';
import { 
  EditOutlined, 
  DeleteOutlined, 
  FileSearchOutlined, 
  ExclamationCircleOutlined,
  PlusOutlined,
  ReloadOutlined,
  FilterOutlined,
  DownloadOutlined,
  HomeOutlined
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
  Menu, 
  Statistic,
  Row,
  Col,
  Select,
  DatePicker
} from 'ant-design-vue';

const router = useRouter();
const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
const selectedRowKeys = ref([]);
const filterVisible = ref(false);
const filters = ref({
  family: null,
  category: null,
  dateRange: null
});

const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  totalPages: 0,
});

const showMasterPlanModal = ref(false);
const selectedUuid = ref(null);


// modal delete
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

// Statistics
const stats = computed(() => ({
  total: data.value.length,
  categories: [...new Set(data.value.map(item => item.category))].length,
  families: [...new Set(data.value.map(item => item.family))].length,
  avgHistoryCount: data.value.length > 0 ? Math.round(data.value.reduce((sum, item) => sum + (Number(item.history_count) || 0), 0) / data.value.length) : 0
}));

async function fetchEquipments(page = 1, limit = 10) {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'EquipmentController',
        m: 'getAllEquipments',
        page,
        limit,
      }
    });
    const apiData = res.data.data || [];
    const totalItems = res.data.total_in_all_page || 0;
    const totalPages = res.data.total_pages || 1;

    pagination.value.total = totalItems;
    pagination.value.current = page;
    pagination.value.pageSize = limit;
    pagination.value.totalPages = totalPages;

    data.value = apiData.map((item, index) => ({
      no: (page - 1) * limit + index + 1,
      task: null,
      ...item
    }));
  } finally {
    loading.value = false;
  }
}

function handlePageChange(newPage) {
  fetchEquipments(newPage, pagination.value.pageSize);
}

function handleEdit(uuid) {
  router.push({ name: 'UpdateEquipment', params: { uuid } });
}

function confirmDelete(item) {
  deleteTarget.value = item;
  showDeleteModal.value = true;
}

function performDelete() {
  if (!isMatched.value) return;
  // gọi API delete
  if (deleteTarget.value) {
    console.log('Deleting:', deleteTarget.value.uuid);
    // TODO: gọi API xóa
  }
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

function cancelDelete() {
  showDeleteModal.value = false;
  deleteTarget.value = null;
}

const confirmationInput = ref("")

// Chỉ khi user nhập đúng thì nút Yes mới enable
const isMatched = computed(() => {
  return confirmationInput.value.trim() === deleteTarget.value?.machine_id
})

function openMasterPlan(uuid) {
  selectedUuid.value = uuid;
  showMasterPlanModal.value = true;
}

function closeMasterPlan() {
  showMasterPlanModal.value = false;
  selectedUuid.value = null;
}

function handleRefresh() {
  fetchEquipments(pagination.value.current, pagination.value.pageSize);
}

function handleAddNew() {
  router.push({ name: 'CreateEquipment' });
}

function handleExport() {
  // TODO: Implement export functionality
  console.log('Exporting data...');
}

// filter tại chỗ (trên data page hiện tại)
const filteredData = computed(() => {
  let filtered = data.value;
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      (item.machine_id && item.machine_id.toLowerCase().includes(q)) ||
      (item.family && item.family.toLowerCase().includes(q)) ||
      (item.model && item.model.toLowerCase().includes(q)) ||
      (item.manufacturing_date && item.manufacturing_date.toLowerCase().includes(q))
    );
  }

  // Apply filters
  if (filters.value.family) {
    filtered = filtered.filter(item => item.family === filters.value.family);
  }
  
  if (filters.value.category) {
    filtered = filtered.filter(item => item.category === filters.value.category);
  }

  return filtered;
});

function formatNumber(num) {
  if (num === null || num === undefined) return '';
  return Number(num).toLocaleString();
}

const sortConfig = ref({ key: null, order: null }); 

function sortBy(key) {
  if (sortConfig.value.key === key) {
    sortConfig.value.order = sortConfig.value.order === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.key = key;
    sortConfig.value.order = 'asc';
  }
}

const sortedData = computed(() => {
  let arr = [...filteredData.value];
  if (!sortConfig.value.key) return arr;

  return arr.sort((a, b) => {
    let valA = a[sortConfig.value.key];
    let valB = b[sortConfig.value.key];

    if (sortConfig.value.key === 'history_count') {
      valA = Number(valA);
      valB = Number(valB);
    }

    if (sortConfig.value.key === 'manufacturing_date') {
      valA = new Date(valA);
      valB = new Date(valB);
    }

    if (valA < valB) return sortConfig.value.order === 'asc' ? -1 : 1;
    if (valA > valB) return sortConfig.value.order === 'asc' ? 1 : -1;
    return 0;
  });
});

// Get unique values for filter options
const uniqueFamilies = computed(() => [...new Set(data.value.map(item => item.family).filter(Boolean))]);
const uniqueCategories = computed(() => [...new Set(data.value.map(item => item.category).filter(Boolean))]);
const categories = ref([]);

function clearFilters() {
  filters.value = {
    family: null,
    category: null,
    dateRange: null
  };
  filterVisible.value = false;
}

const fetchCategoryData = async () => {
    try {
    const res = await axiosClient.get("", {
      params: { c: "CategoryController", m: "getAllCategories", limit: 100000 },
    });
    if (res.data.status === "success") {
      categories.value = res.data.data;
      console.log('category: ' + uniqueCategories.value[0].name);
      
    }
  } catch (e) {
    console.error("Error fetching Category:", e);
  }
}

function getCategoryColor(category) {
  const colors = {
    'Production': 'green',
    'Maintenance': 'orange', 
    'Testing': 'blue',
    'Quality': 'purple',
    'default': 'default'
  };
  return colors[category] || colors.default;
}

onMounted(() => {
  fetchEquipments(pagination.value.current, pagination.value.pageSize);
});

const breadcrumbItems = [
  {
    title: 'Dashboard',
    path: '/dashboard'
  },
  {
    title: 'Equipment Management'
  }
];
</script>

<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Equipment Management
        </h1>
        <div class="breadcrumb">
          <span>Equipment</span>
          <span class="separator">›</span>
          <span class="current">List Equipment</span>
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
    <div class="equipment-management">
      <!-- Main Content Card -->
      <Card class="main-content-card">
        <!-- Search and Filter Bar -->
        <div class="toolbar">
          <div class="search-section">
            <Input.Search
              v-model:value="searchQuery"
              placeholder="Search equipment by ID, family, model, or date..."
              size="large"
              class="search-input"
              allow-clear
            />
          </div>
          <div class="filter-section">
            <Space>
              <Dropdown 
                v-model:open="filterVisible" 
                placement="bottomRight"
                trigger="click"
              >
                <Button>
                  <FilterOutlined />
                  Filters
                  <span v-if="filters.family || filters.category" class="filter-badge">●</span>
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
                      >
                        <Select.Option v-for="family in uniqueFamilies" :key="family" :value="family">
                          {{ family }}
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
                      >
                        <Select.Option v-for="category in uniqueCategories" :key="category" :value="category">
                          {{ category }}
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
          <span>Showing {{ sortedData.length }} of {{ pagination.total }} equipment</span>
        </div>

        <Card class="table-container" :class="{ 'loading': loading }">
          <div v-if="loading" class="loading-overlay">
            <div class="loading-spinner">Loading...</div>
          </div>
          
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="sortable" @click="sortBy('machine_id')">
                    <div class="th-content">
                      Machine ID
                      <span class="sort-indicator">
                        <span v-if="sortConfig.key !== 'machine_id'">⇅</span>
                        <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                        <span v-else class="sort-desc">↓</span>
                      </span>
                    </div>
                  </th>
                  <th class="sortable" @click="sortBy('family')">
                    <div class="th-content">
                      Family
                      <span class="sort-indicator">
                        <span v-if="sortConfig.key !== 'machine_id'">⇅</span>
                        <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                        <span v-else class="sort-desc">↓</span>
                      </span>
                    </div>
                  </th>
                  <!-- <th>Family</th> -->
                  <th>Model</th>
                  <th>Cavity</th>
                  <th>Manufacturer</th>
                  <th class="sortable" @click="sortBy('manufacturing_date')">
                    <div class="th-content">
                      Manufacturing Date
                      <span class="sort-indicator">
                        <span v-if="sortConfig.key !== 'manufacturing_date'">⇅</span>
                        <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                        <span v-else class="sort-desc">↓</span>
                      </span>
                    </div>
                  </th>
                  <th class="sortable" @click="sortBy('history_count')">
                    <div class="th-content">
                      History Count
                      <span class="sort-indicator">
                        <span v-if="sortConfig.key !== 'history_count'">⇅</span>
                        <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                        <span v-else class="sort-desc">↓</span>
                      </span>
                    </div>
                  </th>
                  <th>Unit</th>
                  <th>Category</th>
                  <th>Master Plan</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in sortedData" :key="item.uuid" class="table-row">
                  <td class="machine-id">
                    <strong>{{ item.machine_id }}</strong>
                  </td>
                  <td>{{ item.family }}</td>
                  <td>{{ item.model }}</td>
                  <td>{{ item.cavity }}</td>
                  <td>{{ $tSync(item.manufacturer) }}</td>
                  <td>{{ item.manufacturing_date }}</td>
                  <td class="history-count">{{ formatNumber(item.history_count) }}</td>
                  <td>{{ item.unit }}</td>
                  <td>
                    <Tag :color="getCategoryColor(item.category)">
                      {{ item.category }}
                    </Tag>
                  </td>
                  <td>
                    <Button type="link" @click="openMasterPlan(item.uuid)" class="view-plan-btn">
                      <FileSearchOutlined />
                      View
                    </Button>
                  </td>
                  <td>
                    <div class="action-buttons-cell">
                      <Tooltip title="Edit Equipment">
                        <Button type="text" @click="handleEdit(item.uuid)" class="edit-btn">
                          <EditOutlined />
                        </Button>
                      </Tooltip>
                      <Tooltip title="Delete Equipment">
                        <Button type="text" danger @click="confirmDelete(item)" class="delete-btn">
                          <DeleteOutlined />
                        </Button>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Enhanced Pagination -->
          <div class="pagination-container">
            <div class="pagination-info">
              <span>
                Showing {{ (pagination.current - 1) * pagination.pageSize + 1 }} to 
                {{ Math.min(pagination.current * pagination.pageSize, pagination.total) }} 
                of {{ pagination.total }} results
              </span>
            </div>
            <div class="pagination-controls">
              <Button 
                :disabled="pagination.current === 1" 
                @click="handlePageChange(pagination.current - 1)"
                class="pagination-btn"
              >
                Previous
              </Button>
              
              <div class="page-numbers">
                <span class="current-page">{{ pagination.current }}</span>
                <span class="page-separator">of</span>
                <span class="total-pages">{{ pagination.totalPages }}</span>
              </div>
              
              <Button 
                :disabled="pagination.current === pagination.totalPages" 
                @click="handlePageChange(pagination.current + 1)"
                class="pagination-btn"
              >
                Next
              </Button>
            </div>
          </div>
        </Card>


      </Card>

      <!-- Equipment Table -->


      <!-- Master Plan Modal -->
      <Modal 
        v-model:open="showMasterPlanModal" 
        title="Equipment Master Plan" 
        @cancel="closeMasterPlan"
        width="1400px"
        :style="{ top: '3px' }"
        class="master-plan-modal"
      >
        <template #footer>
          <Button @click="closeMasterPlan">Close</Button>
        </template>
        <div class="master-plan-content">
          <MasterPlan :key="selectedUuid" :id="selectedUuid" />
        </div>
      </Modal>

      <!-- Enhanced Delete Confirmation Modal -->
      <Modal
        v-model:open="showDeleteModal"
        title="Confirm Equipment Deletion"
        @ok="performDelete"
        @cancel="cancelDelete"
        ok-text="Yes, Delete"
        cancel-text="Cancel"
        ok-type="danger"
        class="delete-modal"
        :ok-button-props="{ disabled: !isMatched }"
      >
        <div class="delete-content">
          <div class="warning-icon">
            <ExclamationCircleOutlined />
          </div>
          
          <Button 
            :disabled="pagination.current === pagination.totalPages" 
            @click="handlePageChange(pagination.current + 1)"
            class="pagination-btn"
          >
            Next
          </Button>
        </div>
      </Modal>


      <!-- Master Plan Modal -->
      <Modal 
        v-model:open="showMasterPlanModal" 
        title="Equipment Master Plan" 
        @cancel="closeMasterPlan"
        width="1400px"
        :style="{ top: '3px' }"
        class="master-plan-modal"
      >
        <template #footer>
          <Button @click="closeMasterPlan">Close</Button>
        </template>
        <div class="master-plan-content">
          <MasterPlan :key="selectedUuid" :id="selectedUuid" />
        </div>
      </Modal>

      <!-- Enhanced Delete Confirmation Modal -->
      <Modal
        v-model:open="showDeleteModal"
        title="Confirm Equipment Deletion"
        @ok="performDelete"
        @cancel="cancelDelete"
        ok-text="Yes, Delete"
        cancel-text="Cancel"
        ok-type="danger"
        class="delete-modal"
        :ok-button-props="{ disabled: !isMatched }"
      >
        <div class="delete-content">
          <div class="warning-icon">
            <ExclamationCircleOutlined />
          </div>
          <div class="warning-text">
            <p>Are you sure you want to delete this equipment?</p>

            <div class="equipment-info no-copy">
              <strong>{{ deleteTarget?.machine_id }}</strong>
              <span class="equipment-details">
                {{ deleteTarget?.family }} - {{ deleteTarget?.model }}
              </span>
            </div>

            <p class="warning-note">This action cannot be undone.</p>

            <div class="confirm-input no-copy">
              <label>
                Please type <strong>{{ deleteTarget?.machine_id }}</strong> to confirm:
              </label>
              <input
                v-model="confirmationInput"
                type="text"
                class="form-control"
                placeholder="Enter machine ID"
              />
            </div>
          </div>
        </div>
      </Modal>
    </div>
  </div>


  
</template>

<style scoped>

.delete-content {
  display: flex;
  gap: 12px;
}

.warning-icon {
  font-size: 28px;
  color: #faad14;
}

.warning-text {
  flex: 1;
}

.no-copy {
  user-select: none; /* chặn copy */
}

.confirm-input {
  margin-top: 12px;
}
.confirm-input input {
  margin-top: 12px;
  width: 100%;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
}




.equipment-management {
  padding: 24px;
  background: #f5f5f5;
}
.app-container {
  min-height: 100vh;
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
  padding: 20px;
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
  margin-bottom: 6px;
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
  border-radius: 6px ;
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