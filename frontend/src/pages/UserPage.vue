<script setup>
import { ref, onMounted, computed } from 'vue';
import axiosClient from '@/utils/axiosClient';
import MasterPlan from './MasterPlan.vue';
import roleApi from '@/stores/roleApi'

import { 
  EditOutlined, 
  DeleteOutlined, 
  FileSearchOutlined, 
  ExclamationCircleOutlined,
  PlusOutlined,
  ReloadOutlined,
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
  Tooltip, 
  Tag, 
  Dropdown, 
  
  Select,
  message,
} from 'ant-design-vue';

const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
const filterVisible = ref(false);
const showUserModal = ref(false)
const filters = ref({
  role: null,
});
const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: "User", role: "Admin" }),
  },
});
const roles = ref([])
const formRef = ref(null)
const newUser = ref({
  employment_id: '',
  employment_name: '',
  username: '',
  position: '',
  role: {
uuid: '',
name: '',
  },
  password: ''
})

onMounted(async () => {
  roles.value = await roleApi.getRoles()
})

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


const isEditMode = ref(false);
function handleAddNew() {
  // mở modal thay vì redirect
  showUserModal.value = true
  isEditMode.value = false
}


// Statistics
const stats = computed(() => ({
  total: data.value.length,
  categories: [...new Set(data.value.map(item => item.category))].length,
  families: [...new Set(data.value.map(item => item.family))].length,
  avgHistoryCount: data.value.length > 0 ? Math.round(data.value.reduce((sum, item) => sum + (Number(item.history_count) || 0), 0) / data.value.length) : 0
}));

async function fetchUsers(page = 1, limit = 10) {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'UserController',
        m: 'getAllUsers',
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
      ...item,
        role: {
        uuid: item.role_uuid, 
        name: item.role_name
      }
    }));
  } finally {
    loading.value = false;
  }
}

function handlePageChange(newPage) {
  fetchUsers(newPage, pagination.value.pageSize);
}

function handleEdit(user) {
  isEditMode.value = true
  Object.assign(newUser.value, user) // copy dữ liệu user sang form
  showUserModal.value = true
}

function confirmDelete(item) {
  deleteTarget.value = item;
  showDeleteModal.value = true;
}



function cancelDelete() {
  showDeleteModal.value = false;
  deleteTarget.value = null;
}


function closeMasterPlan() {
  showMasterPlanModal.value = false;
  selectedUuid.value = null;
}

function handleRefresh() {
  fetchUsers(pagination.value.current, pagination.value.pageSize);
}




// filter tại chỗ (trên data page hiện tại)
const filteredData = computed(() => {
  let filtered = data.value;
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      (item.employment_id && item.employment_id.toLowerCase().includes(q)) ||
      (item.username && item.username.toLowerCase().includes(q)) ||
      (item.employment_name && item.employment_name.toLowerCase().includes(q)) ||
      (item.created_at && item.created_at.toLowerCase().includes(q))
    );
  }

  // Apply filters
  if (filters.value.role) {
    filtered = filtered.filter(item => item.role === filters.value.role);
  }
  

  return filtered;
});



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

    if (sortConfig.value.key === 'created_at') {
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

function clearFilters() {
  filters.value = {
    role: null,
  };
  filterVisible.value = false;
}

function getRoleColor(category) {
  const colors = {
    'admin': 'green',
    'manager': 'orange', 
    'user': 'blue',
  };
  return colors[category] || colors.default;
}

onMounted(() => {
  fetchUsers(pagination.value.current, pagination.value.pageSize);
});

const breadcrumbItems = [
  {
    title: 'Dashboard',
    path: '/dashboard'
  },
  {
    title: 'User Management'
  }
];

const formRules = computed(() => ({
  employment_id: [
    { required: true, message: 'Please enter employee ID!', trigger: 'blur' },
    { min: 3, max: 20, message: 'Employee ID must be 3-20 characters!', trigger: 'blur' }
  ],
  employment_name: [
    { required: true, message: 'Please enter employee name!', trigger: 'blur' },
    { min: 2, max: 50, message: 'Name must be 2-50 characters!', trigger: 'blur' }
  ],
  username: [
    { required: true, message: 'Please enter username!', trigger: 'blur' },
    { min: 3, max: 30, message: 'Username must be 3-30 characters!', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_]+$/, message: 'Username can only contain letters, numbers and underscores!', trigger: 'blur' }
  ],
  position: [
    { required: true, message: 'Please enter position!', trigger: 'blur' }
  ],
  role: [
    { required: true, message: 'Please select a role!', trigger: 'change' }
  ],
  password: isEditMode.value
    ? [
        // Edit: chỉ check min nếu có nhập
        { min: 4, message: 'Password must be at least 4 characters!', trigger: 'blur' }
      ]
    : [
        // Add: bắt buộc phải nhập
        { required: true, message: 'Please enter password!', trigger: 'blur' },
        { min: 4, message: 'Password must be at least 4 characters!', trigger: 'blur' }
      ]
}))


// functions
function generatePassword() {
  const lower = 'abcd'
  const nums = '123456789'
  const allChars = lower + nums

  let pass = ''
  pass += lower[Math.floor(Math.random() * lower.length)]
  pass += nums[Math.floor(Math.random() * nums.length)]
  for (let i = 4; i < 10; i++) {
    pass += allChars[Math.floor(Math.random() * allChars.length)]
  }
  newUser.value.password = pass.split('').sort(() => Math.random() - 0.5).join('')
}

async function performAddUser() {
  try {
    await formRef.value.validate()
    loading.value = true
    // gọi API ở đây

    await axiosClient.post('', newUser.value, {
      params: {
        c: 'UserController',
        m: 'createUser',
      },
    });
    
    await new Promise(r => setTimeout(r, 1000))
    message.success('User created successfully!')
    resetForm()
    // window.location.reload()
    handleRefresh()
  } catch (err) {
    if (err.errorFields) {
      message.error('Please fix the form errors!')
    } else {
      message.error('Failed to create user!')
      console.error(err)
    }
  } finally {
    loading.value = false
  }
}
async function performEditUser() {
  try {
    await formRef.value.validate()
    loading.value = true
    // gọi API update
    console.log('rhyhryhb: ' + newUser.value.role.uuid);
    
    await axiosClient.post('', {     
      uuid: newUser.value.uuid,   // cần uuid để biết user nào
      employment_id: newUser.value.employment_id,
      employment_name: newUser.value.employment_name,
      username: newUser.value.username,
      position: newUser.value.position,
      role_id: newUser.value.role.uuid,
      password: newUser.value.password,
      // updated_by: user.name
    },{
     params: {
        c: 'UserController',
        m: 'updateUser',
      }}
  )
    console.log("Data gửi update:", newUser.value)
    message.success('User updated successfully!')
    resetForm();
    
    fetchUsers(pagination.value.current, pagination.value.pageSize) // refresh data
  } catch (err) {
    if (err.errorFields) {
      message.error('Please fix the form errors!')
    } else {
      message.error('Failed to update user!')
      console.error(err)
    }
  } finally {
    loading.value = false
  }
}

async function handleDelete() {
  try {
    loading.value = true
    await axiosClient.delete('', {
      params: {
        c: 'UserController',
        m: 'deleteUser',
        uuid: deleteTarget.value.uuid
      }
    })
    message.success('User deleted successfully!')
    handleRefresh()  // load lại danh sách
  } catch (err) {
    message.error('Failed to delete user!')
    console.error(err)
  } finally {
    loading.value = false
  }

  showDeleteModal.value = false;
  deleteTarget.value = null;
}

function resetForm() {
  formRef.value?.resetFields()   // reset trước
  newUser.value = {
    employment_id: '',
    employment_name: '',
    username: '',
    position: '',
    role: {
      uuid: '',
      name: '',
    },
    password: ''
  }
  showUserModal.value = false    // sau đó mới đóng modal
}

</script>

<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          User Management
        </h1>
        <div class="breadcrumb">
          <span>User</span>
          <span class="separator">›</span>
          <span class="current">List User</span>
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
            <Button type="primary" @click="handleAddNew">
              <PlusOutlined />
              Add User
            </Button>
          </Space>
        </div>
      </div>
    </div>
    
    <div class="User-management">    

    <!-- Add User Modal -->
    <a-modal
      v-model:open="showUserModal"
      :title="isEditMode ? 'Edit User' : 'Add New User'"
      wrapClassName="custom-modal enhanced-modal"
      @ok="isEditMode ? performEditUser() : performAddUser()"
      @cancel="resetForm"
      :destroyOnClose="true"
      :ok-text="isEditMode ? 'Update User' : 'Create User'"
      cancel-text="Cancel"
      :confirmLoading="loading"
      width="700px"
    >
      <div class="modal-content">
        <a-form 
          ref="formRef"
          :model="newUser" 
          :rules="formRules"
          layout="vertical"
          class="enhanced-form"
        >
          <div class="form-row">
            <a-form-item 
              label="Employee ID" 
              name="employment_id"
              class="form-item-enhanced"
            >
              <a-input 
                v-model:value="newUser.employment_id" 
                placeholder="Enter employee ID"
                size="large"
              >
                
              </a-input>
            </a-form-item>

            <a-form-item 
              label="Employee Name" 
              name="employment_name"
              class="form-item-enhanced"
            >
              <a-input 
                v-model:value="newUser.employment_name" 
                placeholder="Enter full name"
                size="large"
              >
                
              </a-input>
            </a-form-item>
          </div>

          <div class="form-row">
            <a-form-item 
              label="Username" 
              name="username"
              class="form-item-enhanced"
            >
              <a-input 
                v-model:value="newUser.username" 
                placeholder="Enter username"
                size="large"
              >
                
              </a-input>
            </a-form-item>

            <a-form-item 
              label="Position" 
              name="position"
              class="form-item-enhanced"
            >
              <a-input 
                v-model:value="newUser.position" 
                placeholder="Enter position"
                size="large"
              >
                
              </a-input>
            </a-form-item>
          </div>

          <a-form-item 
            label="Role" 
            name="role"
            class="form-item-enhanced"
          >
             <a-select v-model:value="newUser.role.uuid" placeholder="Select role">
                <a-select-option
                  v-for="role in roles"
                  :key="role.uuid"
                  :value="role.uuid"
                >
                  <div class="role-option">
                    <span class="role-text">{{ role.name }}</span>
                  </div>
                </a-select-option>
              </a-select>
          </a-form-item>

          <div class="password-section">
            <div class="password-header">
              <label class="password-label">Password</label>
              <a-button 
                type="link" 
                @click="generatePassword"
                class="generate-btn"
                size="small"
              >
              Generate Password
              </a-button>
            </div>
            
            <a-form-item name="password" class="form-item-enhanced">
              <a-input-password
                v-model:value="newUser.password"
                placeholder="Enter or generate password"
                size="large"
                :visibilityToggle="true"
                class="password-input"
              />
            </a-form-item>
          </div>
        </a-form>
      </div>
    </a-modal>


    <!-- Main Content Card -->
    <Card class="main-content-card">
      <!-- Search and Filter Bar -->
      <div class="toolbar">
        <div class="search-section">
          <Input.Search
            v-model:value="searchQuery"
            placeholder="Search User by ID, name,..."
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
                <span v-if="filters.role" class="filter-badge">●</span>
              </Button>
              <template #overlay>
                <div class="filter-dropdown">
                  <div class="filter-group">
                    <label>Account Type</label>
                    <Select
                      v-model:value="filters.role"
                      placeholder="Select role"
                      allow-clear
                      style="width: 200px"
                    >
                      <Select.Option v-for="role in uniqueFamilies" :key="role" :value="role">
                        {{ role }}
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
        <span>Showing {{ sortedData.length }} of {{ pagination.total }} User</span>
      </div>

      <!-- User Table -->
      <div class="table-container" :class="{ 'loading': loading }">
        <div v-if="loading" class="loading-overlay">
          <div class="loading-spinner">Loading...</div>
        </div>
        
        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th class="sortable" @click="sortBy('machine_id')">
                  <div class="th-content">
                    Employee ID
                    <span class="sort-indicator">
                      <span v-if="sortConfig.key !== 'machine_id'">⇅</span>
                      <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                      <span v-else class="sort-desc">↓</span>
                    </span>
                  </div>
                </th>
                <th>Employee Name</th>
                <th>User Name</th>
                <th>Position</th>
                <th class="sortable" @click="sortBy('created_at')">
                  <div class="th-content">
                    Creation Date
                    <span class="sort-indicator">
                      <span v-if="sortConfig.key !== 'created_at'">⇅</span>
                      <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                      <span v-else class="sort-desc">↓</span>
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="sortBy('last_login')">
                  <div class="th-content">
                    Last Login
                    <span class="sort-indicator">
                      <span v-if="sortConfig.key !== 'last_login'">⇅</span>
                      <span v-else-if="sortConfig.order === 'asc'" class="sort-asc">↑</span>
                      <span v-else class="sort-desc">↓</span>
                    </span>
                  </div>
                </th>
                
                <th>Account Type</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in sortedData" :key="item.uuid" class="table-row">
                <td class="machine-id">
                  <strong>{{ item.employment_id }}</strong>
                </td>
                <td>{{ item.employment_name }}</td>
                <td>{{ item.username }}</td>
                <td>{{ item.position }}</td>
                <td>{{ item.created_at }}</td>
                <td>{{ item.last_login }}</td>
                <td>
                  <Tag :color="getRoleColor(item.role)">
                    {{ item.role.name }}
                  </Tag>
                </td>
                <td>
                  <div class="action-buttons-cell">
                    <Tooltip title="Edit User">
                      <Button type="text" @click="handleEdit(item)" class="edit-btn">
                        <EditOutlined />
                      </Button>
                    </Tooltip>
                    <Tooltip title="Delete User">
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

        <!-- Empty State -->
        <div v-if="!loading && sortedData.length === 0" class="empty-state">
          <div class="empty-content">
            <FileSearchOutlined class="empty-icon" />
            <h3>No User found</h3>
            <p>Try adjusting your search criteria or add new User</p>
            <Button type="primary" @click="handleAddNew">
              <PlusOutlined />
              Add User
            </Button>
          </div>
        </div>
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

    <!-- Master Plan Modal -->
    <Modal 
      v-model:open="showMasterPlanModal" 
      title="User Master Plan" 
      @cancel="closeMasterPlan"
      width="1400px"
      class="master-plan-modal"
    >
      <template #footer>
        <Button @click="closeMasterPlan">Close</Button>
      </template>
      <div class="master-plan-content">
        <MasterPlan />
      </div>
    </Modal>

    <!-- Enhanced Delete Confirmation Modal -->
    <Modal
      v-model:open="showDeleteModal"
      title="Confirm User Deletion"
      @ok="handleDelete"
      @cancel="cancelDelete"
      ok-text="Yes, Delete"
      cancel-text="Cancel"
      ok-type="danger"
      class="delete-modal"
    >
      <div class="delete-content">
        <div class="warning-icon">
          <ExclamationCircleOutlined />
        </div>
        <div class="warning-text">
          <p>Are you sure you want to delete this User?</p>
          <div class="User-info">
            <strong>{{ deleteTarget?.employment_name }}</strong>
            <span class="User-details">
              {{ deleteTarget?.employment_id }} - {{ deleteTarget?.username }}
            </span>
          </div>
          <p class="warning-note">This action cannot be undone.</p>
        </div>
      </div>
    </Modal>
  </div>
  </div>
  
  
</template>

<style scoped>
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


.page-header {
  background: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 19px;
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
  font-size: 17px;
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
  margin-bottom: 15px;
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
  font-size: 15px;
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
  font-size: 17px;
}

.modern-table thead {
  background: #f3f3f3;
  border-bottom: 2px solid #e9e8e8;
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
  border-bottom: 1px solid #e3e1e1;
  vertical-align: middle;
}

.table-row {
  transition: background-color 0.2s;
}

.table-row:hover {
  background: #fafafa;
}

/* .machine-id {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
} */

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

.User-info {
  margin: 16px 0;
  padding: 12px;
  background: #fff2f0;
  border-radius: 6px;
  border-left: 3px solid #ff4d4f;
}

.User-info strong {
  display: block;
  font-size: 16px;
  margin-bottom: 4px;
}

.User-details {
  color: #666;
  font-size: 14px;
}

.warning-note {
  color: #999;
  font-size: 13px;
  margin: 16px 0 0 0;
}
.enhanced-modal :deep(.ant-modal-content) {
  border-radius: 12px;
  overflow: hidden;
}

.enhanced-modal :deep(.ant-modal-header) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-bottom: none;
  padding: 20px 24px;
}

.enhanced-modal :deep(.ant-modal-title) {
  color: white;
  font-size: 20px;
  font-weight: 600;
}

.modal-content {
  padding: 20px 0;
}

.enhanced-form {
  font-size: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-item-enhanced {
  margin-bottom: 20px;
}

.form-item-enhanced :deep(.ant-form-item-label > label) {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
}

.form-item-enhanced :deep(.ant-input),
.form-item-enhanced :deep(.ant-select-selector) {
  border-radius: 8px;
  font-size: 15px;
  transition: all 0.3s ease;
}

.form-item-enhanced :deep(.ant-input:focus),
.form-item-enhanced :deep(.ant-select-focused .ant-select-selector) {
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}



.role-select :deep(.ant-select-item) {
  padding: 12px;
}

.role-option {
  display: flex;
  align-items: center;
  gap: 12px;
}

.role-icon {
  font-size: 18px;
}

.role-text {
  font-weight: 600;
  flex: 1;
}

.role-desc {
  font-size: 12px;
  color: #8892b0;
}

.password-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-top: 8px;
}

.password-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.password-label {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
}

.generate-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 6px;
  padding: 4px 12px;
  font-size: 13px;
  font-weight: 500;
}

.generate-btn:hover {
  background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
  color: white;
}

.password-input {
  margin-bottom: 12px;
}

/* .password-strength {
  display: flex;
  align-items: center;
  gap: 12px;
} */

.strength-bar {
  flex: 1;
  height: 4px;
  background: #e1e5e9;
  border-radius: 2px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  transition: all 0.3s ease;
  border-radius: 2px;
}

.strength-fill.weak {
  background: #ff4757;
}

.strength-fill.medium {
  background: #ffa502;
}

.strength-fill.strong {
  background: #2ed573;
}

.strength-text {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.strength-text.weak {
  color: #ff4757;
}

.strength-text.medium {
  color: #ffa502;
}

.strength-text.strong {
  color: #2ed573;
}

.enhanced-modal :deep(.ant-modal-footer) {
  border-top: 1px solid #e1e5e9;
  padding: 16px 24px;
}

.enhanced-modal :deep(.ant-btn-primary) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  height: 40px;
  font-size: 15px;
  font-weight: 600;
}

.enhanced-modal :deep(.ant-btn-default) {
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  height: 40px;
  font-size: 15px;
  font-weight: 600;
}

.form-item-enhanced :deep(.ant-form-item-has-error .ant-input),
.form-item-enhanced :deep(.ant-form-item-has-error .ant-select-selector) {
  border-color: #ff4757 !important;
  box-shadow: 0 0 0 2px rgba(255, 71, 87, 0.2) !important;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .enhanced-modal :deep(.ant-modal) {
    width: 90% !important;
    max-width: none !important;
  }
}


/* Responsive Design */
@media (max-width: 768px) {
  .User-management {
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

.modern-table th {
  text-align: left;
  padding-left: 12px; /* thêm khoảng cách nhìn thoáng hơn */
}
.sort-indicator {
  display: inline-flex !important;
}
</style>