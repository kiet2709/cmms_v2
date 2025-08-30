<template>
  <div class="category-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Manage account types
        </h1>
        <div class="breadcrumb">
          <span class="link-span" @click="$router.push('/dashboard/user')">User</span>
          <span class="separator">›</span>
          <span class="current">Account Type</span>
        </div>
      </div>     
    </div>
    <div class="category-content">
      <!-- Add Category Form -->
      <div class="add-category-card">
        <div class="card-header">
          <h3 class="card-title">
            Add New Account Type
          </h3>
        </div>
        
        <div class="accounttype-form">
          <div class="form-group">  
            <label for="nameType">Name</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                id="nameType" 
                v-model="accounttype" 
                placeholder="Enter account type..." 
                required
                class="form-input"
              >
            </div>
          </div>
          
          <div class="form-group">
            <label for="name">Description</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                id="name" 
                v-model="categoryName" 
                placeholder="Enter description..." 
                required
                class="form-input"
              >
            </div>
          </div>
          
          <button type="submit" class="submit-btn" :disabled="loading" @click="saveCategory">
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
      
      <!-- Category List -->
      <div class="category-list-card">
        <div class="card-header">
          <h3 class="card-title">
            Account Type List
            <span class="category-count">{{ roles.length }}</span>
          </h3>
        </div>
        
        <div class="table-container" v-if="roles.length > 0">
          <div class="table-wrapper">
            <table class="category-table">
              <thead>
                <tr>
                  <th>
                    Name
                  </th>
                  <th>
                    Description
                  </th>
                  <th>
                    <i class="table-icon"></i>
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in roles" :key="item.uuid" class="table-row">
                  <td>
                    <span class="code-badge">{{ item.name }}</span>
                  </td>
                  <td class="category-name">{{ item.description }}</td>
                  <td>
                    <div class="action-buttons">
                      <!-- Nút Edit -->
                      <EditOutlined class="edit-btn" title="Edit"
                        style="cursor:pointer; margin-right: 8px;"
                        @click="openEditModal(item)"
                      />

                      <!-- Modal Edit -->
                      <a-modal
                        v-model:open="showEditModal"
                        title="Edit Category"
                        @ok="submitEdit"
                        @cancel="cancelEdit"
                        ok-text="Save"
                        cancel-text="Cancel"
                      >
                        <a-form layout="vertical">
                          <a-form-item label="Category Code">
                            <a-input v-model:value="editForm.code" placeholder="Enter category code" />
                          </a-form-item>

                          <a-form-item label="Category Name">
                            <a-input v-model:value="editForm.name" placeholder="Enter category name" />
                          </a-form-item>
                        </a-form>
                      </a-modal>
                       <!-- Nút delete -->
                      <DeleteOutlined 
                        class="delete-btn" 
                        style="cursor:pointer;" 
                        @click="deleteCategory(item)" 
                      />

                      <!-- Modal xác nhận xoá -->
                      <a-modal
                        v-model:open="showDeleteModal"
                        title="Confirm Delete"
                        @ok="performDelete"
                        @cancel="cancelDelete"
                        ok-text="Yes, delete"
                        cancel-text="Cancel"
                      >
                        <p>
                          <ExclamationCircleOutlined style="color: red; margin-right: 8px;" />
                          Are you sure you want to delete 
                          <strong>{{ deleteTarget?.name }}</strong>?
                        </p>
                      </a-modal>
                      <!-- <button class="delete-btn" @click="deleteCategory(index)" title="Delete">
                        <i class="btn-icon">🗑️</i>
                      </button> -->
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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
        </div>
        
        
        <div v-else class="empty-state">
          <div class="empty-icon">📦</div>
          <h4>No categories yet</h4>
          <p>Add the first category to start managing products</p>
        </div>
      </div>
    </div>
     
  </div>
</template>

<script setup>
import { EditOutlined, DeleteOutlined, FileSearchOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { ref, onMounted } from 'vue';
import { 
  Button, 
} from 'ant-design-vue';

import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import roleApi from '@/stores/roleApi'

const router = useRouter();

const loading = ref(false);
const data = ref([]);
const categoryName = ref('');
const accounttype = ref('');
const showDeleteModal = ref(false);
const deleteTarget = ref(null)
const showEditModal = ref(false)
const roles = ref([])

const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  totalPages: 0,
});
onMounted(async () => {
  roles.value = await roleApi.getRoles()
})
// fetch categories
async function fetchCategories(page = 1, limit = 10) {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'CategoryController',
        m: 'getAllCategories',
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
      ...item
    }));
    console.log(data.value);
    
  } finally {
    loading.value = false;
  }
}

function handlePageChange(newPage) {
  fetchCategories(newPage, pagination.value.pageSize);
}
const editForm = ref({
  id: null,
  name: '',
  description: ''
})

function openEditModal(item) {
  // copy dữ liệu từ item vào form
  editForm.value = { ...item }
  showEditModal.value = true
}

function submitEdit() {
  console.log("Updating:", editForm.value)
  // gọi API update category tại đây
  showEditModal.value = false
}

function cancelEdit() {
  showEditModal.value = false
}
// ✅ mở modal khi nhấn delete
function deleteCategory(item) {
  deleteTarget.value = item
  showDeleteModal.value = true
}
function cancelDelete() {
  showDeleteModal.value = false
  deleteTarget.value = null
}

// Khi nhấn nút, chuyển route
function goToAddCategory() {
  router.push('/dashboard/categories/add');
}

onMounted(() => {
  fetchCategories(pagination.value.current, pagination.value.pageSize);
});


async function saveCategory() {
  if (!categoryName.value.trim()) {
    alert('Please enter a category name.');
    return;
  }

  loading.value = true;
  try {
    const res = await axiosClient.post('', {
      c: 'CategoryController',
      m: 'createCategory',
      name: categoryName.value.trim(),
      code: accounttype.value.trim()
    });
    alert(res.data.message || 'Category saved successfully!');
    categoryName.value = '';
  } catch (err) {
    console.error(err);
    alert('Error saving category.');
  } finally {
    loading.value = false;
  }
}
function editCategory(index) {
  const item = data.value[index];
  const newName = prompt("Enter new category name:", item.name);
  if (newName && newName.trim()) {
    data.value[index].name = newName.trim();
  }
}
// ✅ gọi API xoá khi nhấn OK trên modal
// async function performDelete() {
//   if (!deleteTarget.value) return
//   try {
//     await axiosClient.post('', {
//       c: 'CategoryController',
//       m: 'deleteCategory',
//       uuid: deleteTarget.value.uuid,
//     })
//     data.value = data.value.filter(c => c.uuid !== deleteTarget.value.uuid)
//   } catch (err) {
//     console.error(err)
//     alert('Error deleting category')
//   } finally {
//     showDeleteModal.value = false
//     deleteTarget.value = null
//   }
// }



</script>

<style scoped>
* {
  box-sizing: border-box;
}

.category-container {
  min-height: 100vh;
  background: #f8f9fa;
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
.link-span {
  cursor: pointer;
}
.page-title {
  font-size: 2.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #6c757d;
  margin: 0;
}

.category-content {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.add-category-card,
.category-list-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
  border: 1px solid #ccc;
  overflow: hidden;
}

.card-header {
  background: #f8f9fa;
  padding: 1.5rem 2rem;
  color: #343a40;
  border-bottom: 1px solid #e9ecef;
}

.card-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.category-count {
  background: #e9ecef;
  color: #495057;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.9rem;
  margin-left: auto;
}

.accounttype-form {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  z-index: 1;
  font-size: 1.1rem;
}

.form-input {
  width: 100%;
  padding: 1rem 1rem 1rem 1rem;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 1rem;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #495057;
  box-shadow: 0 0 0 2px rgba(73, 80, 87, 0.1);
}

.submit-btn {
  width: 100%;
  background: rgb(22, 119, 255);
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 6px;
  font-size: 1.1rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.submit-btn:hover {
  background: #5894cf;
}

.table-container {
  padding: 2rem;
}

.table-wrapper {
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid #dee2e6;
}

.category-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.category-table th {
  background: #f8f9fa;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 1px solid #dee2e6;
}

.table-icon {
  margin-right: 0.5rem;
}

.category-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f3f4;
}

.table-row:hover {
  background: #f8f9fa;
}

.code-badge {
  background: #eff0f0;
  color: rgb(17, 17, 17);
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: 500;
  font-size: 0.9rem;
  display: inline-block;
}

.category-name {
  font-weight: 500;
  color: #374151;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn {
  padding: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-btn {
  background: #e9ecef;
  color: #495057;
}

.edit-btn:hover {
  background: #dee2e6;
}

.delete-btn {
  background: #f8d7da;
  color: #721c24;
}

.delete-btn:hover {
  background: #f5c6cb;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 0.5rem 0;
  color: #374151;
  font-size: 1.2rem;
}

.empty-state p {
  margin: 0;
  opacity: 0.8;
}

.icon,
.btn-icon {
  display: inline-block;
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


/* Responsive Design */
@media (max-width: 768px) {
  .category-content {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .category-container {
    padding: 1rem;
  }
  
  .accounttype-form,
  .table-container {
    padding: 1.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}

/* Animation cho các phần tử mới được thêm */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-row {
  animation: slideIn 0.3s ease;
}
</style>