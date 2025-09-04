<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
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
import FormViewer from './FormViewer.vue';

const router = useRouter();
const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
const filterVisible = ref(false);
const filters = ref({
  type: null
});

const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  totalPages: 0,
});

// modal delete
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

// Modal state
const isModalOpen = ref(false);
const currentId = ref(null);

const fetchInstructions = async (page = 1, limit = 10) => {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'WorkingInstructionController',
        m: 'getAllWi',
        page,
        limit
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
};

const handlePageChange = (newPage) => {
  fetchInstructions(newPage, pagination.value.pageSize);
};

const handleAddNew = () => {
  console.log('Add Working Instruction');
  // router.push({ name: 'CreateWorkingInstruction' });
};

const code = ref('');

const viewItem = (payload) => {
  const id =
    typeof payload === 'string'
      ? payload
      : payload?.uuid ?? payload?.id ?? payload?.value ?? null;

  code.value = payload.code;    
  
  currentId.value = id;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

function confirmDelete(item) {
  deleteTarget.value = item;
  showDeleteModal.value = true;
}

function performDelete() {
  if (!isMatched.value) return;
  if (deleteTarget.value) {
    console.log('Deleting:', deleteTarget.value.id);
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
  return confirmationInput.value.trim() === deleteTarget.value?.code
})


// Filter options
const uniqueTypes = computed(() => [...new Set(data.value.map(item => item.type).filter(Boolean))]);

// Filtered data
const filteredData = computed(() => {
  let filtered = data.value;
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item =>
      (item.code && item.code.toLowerCase().includes(q)) ||
      (item.name && item.name.toLowerCase().includes(q)) ||
      (item.type && item.type.toLowerCase().includes(q)) ||
      (item.updated_at && item.updated_at.toLowerCase().includes(q))
    );
  }

  if (filters.value.type) {
    filtered = filtered.filter(item => item.type === filters.value.type);
  }

  return filtered;
});

const clearFilters = () => {
  filters.value = { type: null };
  filterVisible.value = false;
};

onMounted(() => {
  fetchInstructions(pagination.value.current, pagination.value.pageSize);
});

const breadcrumbItems = [
  { title: 'Dashboard', path: '/dashboard' },
  { title: 'Working Instructions' }
];
</script>

<template>
  <div class="app-container">
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Working Instructions
        </h1>
        <div class="breadcrumb">
          <span>Instructions</span>
          <span class="separator">›</span>
          <span class="current">List Instructions</span>
        </div>
      </div>
      <div class="header-right">
        <Space>
          <Button @click="fetchInstructions(pagination.current, pagination.pageSize)" :loading="loading">
            <ReloadOutlined />
            Refresh
          </Button>
          <Button type="primary" @click="handleAddNew">
            <PlusOutlined />
            Add Instruction
          </Button>
        </Space>
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
            placeholder="Search by code, name, type, or date..."
            size="large"
            class="search-input"
            allow-clear
          />
        </div>
        <div class="filter-section">
          <Space>
            <Dropdown v-model:open="filterVisible" placement="bottomRight" trigger="click">
              <Button>
                <FilterOutlined />
                Filters
                <span v-if="filters.type" class="filter-badge">●</span>
              </Button>
              <template #overlay>
                <div class="filter-dropdown">
                  <div class="filter-group">
                    <label>Type</label>
                    <Select
                      v-model:value="filters.type"
                      placeholder="Select type"
                      allow-clear
                      style="width: 200px"
                    >
                      <Select.Option v-for="type in uniqueTypes" :key="type" :value="type">
                        {{ type }}
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
        <span>Showing {{ filteredData.length }} of {{ pagination.total }} instructions</span>
      </div>

      <!-- Table -->
      <div class="table-container" :class="{ 'loading': loading }">
        <div v-if="loading" class="loading-overlay">
          <div class="loading-spinner">Loading...</div>
        </div>
        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Task Code</th>
                <th>Description</th>
                <th>Daily Inspection / Maintenance</th>
                <th>Frequency</th>
                <th>Creation Date</th>
                <th>View</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredData" :key="item.uuid || item.id">
                <td>{{ item.code }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.type }}</td>
                <td>{{ item.frequency == 'Unit' ? item.unit_value + ' ' + item.unit_type : item.frequency }}</td>
                <td>{{ item.updated_at }}</td>
                <td>
                  <Button type="link" @click="viewItem(item)">
                    <EyeOutlined />
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

        <div v-if="!loading && filteredData.length === 0" class="empty-state">
          <div class="empty-content">
            <EyeOutlined class="empty-icon" />
            <h3>No instructions found</h3>
            <p>Try adjusting your search criteria or add new instruction</p>
            <Button type="primary" @click="handleAddNew">
              <PlusOutlined />
              Add Instruction
            </Button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        <div class="pagination-info">
          <span>
            Showing {{ (pagination.current - 1) * pagination.pageSize + 1 }} to 
            {{ Math.min(pagination.current * pagination.pageSize, pagination.total) }} 
            of {{ pagination.total }} results
          </span>
        </div>
        <div class="pagination-controls">
          <Button :disabled="pagination.current === 1" @click="handlePageChange(pagination.current - 1)">
            Previous
          </Button>
          <span class="page-numbers">
            <span class="current-page">{{ pagination.current }}</span>
            <span class="page-separator">of</span>
            <span class="total-pages">{{ pagination.totalPages }}</span>
          </span>
          <Button :disabled="pagination.current === pagination.totalPages" @click="handlePageChange(pagination.current + 1)">
            Next
          </Button>
        </div>
      </div>
    </Card>

    <!-- Modal -->
    <Modal 
      v-model:open="isModalOpen" 
      :title="code" 
      @cancel="closeModal" 
      :style="{ top: '3px'}"
      width="800px">
      <template #footer>
        <Button @click="closeModal">Closeee</Button>
      </template>
      <FormViewer v-if="isModalOpen" :key="currentId" :id="currentId" />
    </Modal>

    <!-- Enhanced Delete Confirmation Modal -->

    <Modal
    v-model:open="showDeleteModal"
    title="Confirm Working Instruction Deletion"
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
        <p>Are you sure you want to delete this Working Instruction?</p>

        <div class="equipment-info no-copy">
            <strong>{{ deleteTarget?.type }}</strong>
            <span class="equipment-details">
              {{ deleteTarget?.code }} - {{ deleteTarget?.name }}
            </span>
        </div>

        <p class="warning-note">This action cannot be undone.</p>

        <div class="confirm-input no-copy">
          <label>
            Please type <strong>{{ deleteTarget?.code }}</strong> to confirm:
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


/* Dùng style tương tự EquipmentPage.vue */
.app-container {
  min-height: 100vh;
  background: rgb(245,245,245);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Header */
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

.title-icon {
  font-size: 32px;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
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

.header-right {
  display: flex;
  gap: 12px;
}

.equipment-management {
  padding: 24px;
  background: #f5f5f5;
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
  background: #f3f3f3;
  border-bottom: 2px solid #e9e8e8;
}
.modern-table th {
  padding: 16px 12px;
  font-weight: 600;
  color: #333;
  border-bottom: none;
}

.modern-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f5f5f5;
}

.view-btn {
  cursor: pointer;
  color: #1890ff;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.empty-icon {
  font-size: 64px;
  color: #d9d9d9;
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

.modern-table th {
  text-align: left !important;
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


</style>
