

<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title">
          Authorization Management
        </h1>
        <div class="breadcrumb">
          <span class="link-span" @click="$router.push('/dashboard/user')">User</span>
          <span class="separator">›</span>
          <span class="current">Authorization</span>
        </div>
      </div>     
    </div>
    <div class="main-container">
      <!-- Sidebar - Account Types -->
      <div class="sidebar">
        <div class="sidebar-header">
          <i class="fas fa-users"></i>
          <h2>Account Types</h2>
        </div>
        <div class="account-types">
          <div 
            v-for="accountType in accountTypes" 
            :key="accountType.id"
            class="account-type"
            :class="{ active: selectedAccountType?.id === accountType.id }"
            @click="selectAccountType(accountType)"
          >
            <i :class="accountType.icon"></i>
            <div class="account-info">
              <span class="account-name">{{ accountType.name }}</span>
              <span class="account-desc">{{ accountType.description }}</span>
            </div>
            <div class="account-badge">{{ accountType.userCount }}</div>
          </div>
        </div>
      </div>

      <!-- Main Content - Permissions -->
      <div class="content-area">
        <div v-if="!selectedAccountType" class="empty-state">
          <i class="fas fa-hand-point-left"></i>
          <h3>Select an Account Type</h3>
          <p>Choose an account type from the sidebar to manage permissions</p>
        </div>

        <div v-else class="permissions-container">
          <div class="content-header">
            <div class="account-selected">
              <i :class="selectedAccountType.icon"></i>
              <div>
                <h2>{{ selectedAccountType.name }} Permissions</h2>
                <p>Configure access permissions for {{ selectedAccountType.name.toLowerCase() }} accounts</p>
              </div>
            </div>
            <button class="save-btn" @click="savePermissions">
              <i class="fas fa-save"></i>
              Save Changes
            </button>
          </div>

          <div class="permissions-grid">
            <div 
              v-for="resource in resources" 
              :key="resource.id"
              class="permission-card"
            >
              <div class="card-header">
                <i :class="resource.icon"></i>
                <div>
                  <h3>{{ resource.name }}</h3>
                  <p>{{ resource.description }}</p>
                </div>
              </div>

              <div class="permissions-list">
                <div 
                  v-for="permission in permissions" 
                  :key="permission.id"
                  class="permission-item"
                >
                  <div class="permission-info">
                    <i :class="permission.icon"></i>
                    <span>{{ permission.name }}</span>
                  </div>
                  <label class="toggle-switch">
                    <input 
                      type="checkbox" 
                      :checked="isPermissionGranted(resource.id, permission.id)"
                      @change="togglePermission(resource.id, permission.id)"
                    >
                    <span class="slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Success Toast -->
      <div v-if="showToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span>Permissions saved successfully!</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'

export default {
  name: 'AuthorizationManagement',
  setup() {
    // Reactive data
    const selectedAccountType = ref(null)
    const showToast = ref(false)
    
    // Account Types
    const accountTypes = reactive([
      {
        id: 1,
        name: 'User',
        description: 'Standard user access',
        icon: 'fas fa-user',
        userCount: 245
      },
      {
        id: 2,
        name: 'Manager',
        description: 'Management level access',
        icon: 'fas fa-user-tie',
        userCount: 28
      },
      {
        id: 3,
        name: 'Admin',
        description: 'Full system access',
        icon: 'fas fa-user-shield',
        userCount: 5
      }
    ])

    // Resources that need authorization
    const resources = reactive([
      {
        id: 1,
        name: 'Master Data',
        description: 'Core system data management',
        icon: 'fas fa-database'
      },
      {
        id: 2,
        name: 'Master Plan',
        description: 'Strategic planning and roadmaps',
        icon: 'fas fa-project-diagram'
      },
      {
        id: 3,
        name: 'User Management',
        description: 'User accounts and profiles',
        icon: 'fas fa-users-cog'
      },
      {
        id: 4,
        name: 'Account Types',
        description: 'Role and permission management',
        icon: 'fas fa-key'
      }
    ])

    // Available permissions
    const permissions = reactive([
      {
        id: 1,
        name: 'View',
        icon: 'fas fa-eye'
      },
      {
        id: 2,
        name: 'Add',
        icon: 'fas fa-plus'
      },
      {
        id: 3,
        name: 'Edit',
        icon: 'fas fa-edit'
      },
      {
        id: 4,
        name: 'Delete',
        icon: 'fas fa-trash'
      }
    ])

    // Permission matrix (accountTypeId -> resourceId -> permissionId -> boolean)
    const permissionMatrix = reactive({
      1: { // User
        1: { 1: true, 2: false, 3: false, 4: false }, // Master Data
        2: { 1: true, 2: false, 3: false, 4: false }, // Master Plan
        3: { 1: true, 2: false, 3: true, 4: false },  // User Management
        4: { 1: false, 2: false, 3: false, 4: false } // Account Types
      },
      2: { // Manager
        1: { 1: true, 2: true, 3: true, 4: false }, // Master Data
        2: { 1: true, 2: true, 3: true, 4: true },  // Master Plan
        3: { 1: true, 2: true, 3: true, 4: true },  // User Management
        4: { 1: true, 2: false, 3: false, 4: false } // Account Types
      },
      3: { // Admin
        1: { 1: true, 2: true, 3: true, 4: true }, // Master Data
        2: { 1: true, 2: true, 3: true, 4: true }, // Master Plan
        3: { 1: true, 2: true, 3: true, 4: true }, // User Management
        4: { 1: true, 2: true, 3: true, 4: true }  // Account Types
      }
    })

    // Methods
    const selectAccountType = (accountType) => {
      selectedAccountType.value = accountType
    }

    const isPermissionGranted = (resourceId, permissionId) => {
      if (!selectedAccountType.value) return false
      return permissionMatrix[selectedAccountType.value.id]?.[resourceId]?.[permissionId] || false
    }

    const togglePermission = (resourceId, permissionId) => {
      if (!selectedAccountType.value) return
      
      const accountId = selectedAccountType.value.id
      if (!permissionMatrix[accountId]) {
        permissionMatrix[accountId] = {}
      }
      if (!permissionMatrix[accountId][resourceId]) {
        permissionMatrix[accountId][resourceId] = {}
      }
      
      permissionMatrix[accountId][resourceId][permissionId] = !permissionMatrix[accountId][resourceId][permissionId]
    }

    const savePermissions = () => {
      // Simulate API call
      showToast.value = true
      setTimeout(() => {
        showToast.value = false
      }, 3000)
    }

    return {
      selectedAccountType,
      showToast,
      accountTypes,
      resources,
      permissions,
      permissionMatrix,
      selectAccountType,
      isPermissionGranted,
      togglePermission,
      savePermissions
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
.app-container {
  min-height: 100vh;
  background: rgb(245,245,245);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}
/* .auth-management {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
} */
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
/* Header
.header {
  background: white;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  font-size: 3rem;
  color: rgb(58, 140, 255);
}

.header h1 {
  color: black;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.header p {
  color: #666;
  font-size: 1.1rem;
} */

/* Main Container */
.main-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 2rem;
  min-height: calc(100vh - 140px);
}

/* Sidebar */
.sidebar {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2rem;
  color: rgb(1, 1, 1);
}

.sidebar-header i {
  font-size: 1.5rem;
}

.sidebar-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
}

.account-type {
  padding: 1.25rem;
  margin-bottom: 1rem;
  border-radius: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  overflow: hidden;
}

.account-type::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(58, 140, 255, 0.1), transparent);
  transition: left 0.5s;
}

.account-type:hover::before {
  left: 100%;
}

.account-type:hover {
  background: rgba(58, 140, 255, 0.08);
  border-color: rgba(58, 140, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(58, 140, 255, 0.15);
}

.account-type.active {
  background: rgb(58, 140, 255);
  color: white;
  border-color: rgb(58, 140, 255);
  box-shadow: 0 8px 25px rgba(58, 140, 255, 0.3);
}

.account-type i {
  font-size: 1.5rem;
  width: 2rem;
  text-align: center;
}

.account-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.account-name {
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.account-desc {
  font-size: 0.9rem;
  opacity: 0.8;
}

.account-badge {
  background: rgba(58, 140, 255, 0.2);
  color: rgb(58, 140, 255);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.account-type.active .account-badge {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Content Area */
.content-area {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
  color: #999;
  text-align: center;
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 1rem;
  color: rgb(58, 140, 255);
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

/* Content Header */
.content-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f2f5;
}

.account-selected {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.account-selected i {
  font-size: 2.5rem;
  color: rgb(58, 140, 255);
}

.account-selected h2 {
  color: rgb(0, 0, 0);
  font-size: 1.8rem;
  margin-bottom: 0.25rem;
}

.account-selected p {
  color: #666;
}

.save-btn {
  background: rgb(58, 140, 255);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(58, 140, 255, 0.3);
}

.save-btn:hover {
  background: rgb(48, 120, 235);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(58, 140, 255, 0.4);
}

/* Permissions Grid */
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.permission-card {
  border: 2px solid #f0f2f5;
  border-radius: 15px;
  overflow: hidden;
  transition: all 0.3s ease;
  background: #fafbfc;
}

.permission-card:hover {
  border-color: rgba(58, 140, 255, 0.3);
  box-shadow: 0 8px 25px rgba(58, 140, 255, 0.15);
  transform: translateY(-3px);
}

.card-header {
  background: white;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 2px solid #f0f2f5;
}

.card-header i {
  font-size: 1.8rem;
  color: rgb(58, 140, 255);
}

.card-header h3 {
  color: rgb(58, 140, 255);
  font-size: 1.3rem;
  margin-bottom: 0.25rem;
}

.card-header p {
  color: #666;
  font-size: 0.9rem;
}

.permissions-list {
  padding: 1.5rem;
}

.permission-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #eee;
  transition: all 0.2s ease;
}

.permission-item:last-child {
  border-bottom: none;
}

.permission-item:hover {
  background: rgba(58, 140, 255, 0.05);
  margin: 0 -1rem;
  padding: 1rem;
  border-radius: 8px;
}

.permission-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 500;
  color: #333;
}

.permission-info i {
  font-size: 1.1rem;
  color: rgb(58, 140, 255);
  width: 1.5rem;
  text-align: center;
}

/* Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .slider {
  background-color: rgb(58, 140, 255);
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.slider:hover {
  box-shadow: 0 0 0 3px rgba(58, 140, 255, 0.2);
}

/* Toast */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: rgb(58, 140, 255);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 8px 25px rgba(58, 140, 255, 0.3);
  animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s;
  z-index: 1000;
}

.toast i {
  font-size: 1.2rem;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
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
@media (max-width: 1024px) {
  .main-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .permissions-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .main-container {
    padding: 1rem;
  }
  
  .header {
    padding: 1.5rem;
  }
  
  .header h1 {
    font-size: 2rem;
  }
  
  .content-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .permission-card {
    min-width: unset;
  }
}
</style>