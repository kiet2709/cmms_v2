<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import { 
  EyeOutlined,
  ReloadOutlined,
  PlusOutlined,
  FilterOutlined,
  HomeOutlined
} from '@ant-design/icons-vue';
import { 
  Modal, 
  Button, 
  Input, 
  Card, 
  Space, 
  Breadcrumb, 
  Dropdown, 
  Select
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

const viewItem = (payload) => {
  const id =
    typeof payload === 'string'
      ? payload
      : payload?.uuid ?? payload?.id ?? payload?.value ?? null;

  currentId.value = id;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

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
  <div class="equipment-management">
    <!-- Breadcrumb -->
    <Breadcrumb class="breadcrumb-nav">
      <Breadcrumb.Item>
        <router-link to="/">
          <HomeOutlined />
          <span>Home</span>
        </router-link>
      </Breadcrumb.Item>
      <Breadcrumb.Item v-for="item in breadcrumbItems" :key="item.title">
        <router-link v-if="item.path" :to="item.path">{{ item.title }}</router-link>
        <span v-else>{{ item.title }}</span>
      </Breadcrumb.Item>
    </Breadcrumb>

    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1>Working Instructions</h1>
          <p class="subtitle">Manage and monitor daily working instructions</p>
        </div>
        <div class="action-buttons">
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
    </div>

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
                <th>Timestamp</th>
                <th>View</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredData" :key="item.uuid || item.id">
                <td>{{ item.code }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.type }}</td>
                <td>{{ item.updated_at }}</td>
                <td>
                  <Button type="link" @click="viewItem(item)">
                    <EyeOutlined />
                    View
                  </Button>
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
    <Modal v-model:open="isModalOpen" title="Instruction Details" @cancel="closeModal" width="800px">
      <template #footer>
        <Button @click="closeModal">Close</Button>
      </template>
      <FormViewer v-if="isModalOpen" :key="currentId" :id="currentId" />
    </Modal>
  </div>
</template>

<style scoped>
/* Dùng style tương tự EquipmentPage.vue */
.equipment-management {
  padding: 24px;
  background: #f5f5f5;
  min-height: 100vh;
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
</style>
