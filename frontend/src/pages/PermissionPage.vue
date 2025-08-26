<script setup>
  
</script>

<template>
    <div class="p-8 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Quản lý Phân quyền Người dùng</h2>
      
      <!-- User Management Section -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Danh sách Người dùng</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Người dùng</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.username }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <select @change="updateUserRole(user, $event.target.value)" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                  <option v-for="role in roles" :key="role.id" :value="role.id" :selected="user.role_id === role.id">
                    {{ role.name }}
                  </option>
                </select>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-green-200 text-green-700 rounded-full">Đã lưu</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Role Management Section -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Danh sách Vai trò & Quyền</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Vai trò</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quyền</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in roles" :key="role.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ role.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-for="permission in role.permissions" :key="permission" class="inline-block bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full">{{ permission }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="openEditRoleModal(role)" class="text-indigo-600 hover:text-indigo-900">
                  Chỉnh sửa quyền
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal for Editing Role Permissions -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-lg">
        <h3 class="text-xl font-bold mb-4">Chỉnh sửa Quyền cho Vai trò: {{ editingRole.name }}</h3>
        <div class="grid grid-cols-2 gap-4">
          <div v-for="permission in allPermissions" :key="permission.id" class="flex items-center">
            <input type="checkbox" :id="'perm-' + permission.id" :value="permission.name" v-model="selectedPermissions" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
            <label :for="'perm-' + permission.id" class="ml-2 text-gray-700">{{ permission.name }}</label>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="saveRolePermissions" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Lưu
          </button>
          <button @click="closeModal" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Hủy
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
/* Scoped styles using Tailwind CSS for a modern look */
/* Các style sử dụng Tailwind CSS để giao diện đẹp hơn */

.modal-overlay {
  background-color: rgba(0, 0, 0, 0.5);
}

table {
  border-collapse: separate;
  border-spacing: 0;
}
th, td {
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
}
th:first-child, td:first-child {
  border-left: none;
}
th:last-child, td:last-child {
  border-right: none;
}
tr:last-child td {
  border-bottom: none;
}

select {
  border: 1px solid #e5e7eb;
}

button {
  transition: all 0.2s ease-in-out;
}
</style>

