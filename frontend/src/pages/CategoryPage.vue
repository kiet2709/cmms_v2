<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';

const router = useRouter();

const loading = ref(false);
const data = ref([]);
const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  totalPages: 0,
});

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

// Khi nhấn nút, chuyển route
function goToAddCategory() {
  router.push('/dashboard/categories/add');
}

onMounted(() => {
  fetchCategories(pagination.value.current, pagination.value.pageSize);
});
</script>

<template>
  <div class="equipment-table-container">
    <button class="add-btn" @click="goToAddCategory">
      Add category
    </button>

    <div v-if="loading">Loading...</div>

    <div v-else class="table-responsive">
      <table class="equipment-table">
        <thead>
          <tr>
            <th>No.</th>
            <th>Code</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data" :key="item.uuid">
            <td>{{ item.no }}</td>
            <td>{{ item.code }}</td>
            <td>{{ item.name }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <button 
        :disabled="pagination.current === 1" 
        @click="handlePageChange(pagination.current - 1)">
        Previous
      </button>
      <span>Page {{ pagination.current }} of {{ pagination.totalPages }}</span>
      <button 
        :disabled="pagination.current === pagination.totalPages" 
        @click="handlePageChange(pagination.current + 1)">
        Next
      </button>
    </div>
  </div>
</template>

<style scoped>
.equipment-table-container {
  padding: 1rem;
  background-color: white;
}

.add-btn {
  margin-bottom: 1rem;
  padding: 0.5rem 1rem;
  color: #fff;
  background-color: #2932e1;
  border: 1px solid #2932e1;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-btn:hover {
  background-color: #0090d1;
  border-color: #0090d1;
}

.table-responsive {
  overflow-x: auto;
}

.equipment-table {
  width: 100%;
  border-collapse: collapse;
}

.equipment-table th, 
.equipment-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.equipment-table th {
  background-color: #f4f4f4;
}

.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
