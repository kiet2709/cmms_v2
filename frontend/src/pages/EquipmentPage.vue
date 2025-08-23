<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axiosClient from '@/utils/axiosClient';

const loading = ref(false);
const data = ref([]);
const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  totalPages: 0,
});

async function fetchEquipments(page = 1, limit = 10) {
  loading.value = true;
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'EquipmentController',
        m: 'getAllEquipments',
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

    // gán số thứ tự
    data.value = apiData.map((item, index) => ({
      no: (page - 1) * limit + index + 1,
      task: null, // dropdown task
      ...item
    }));
  } finally {
    loading.value = false;
  }
}

function handlePageChange(newPage) {
  fetchEquipments(newPage, pagination.value.pageSize);
}

function handleTaskChange(equipment, value) {
  equipment.task = value;
  console.log('Selected task:', value, 'for equipment', equipment.uuid);
}

onMounted(() => {
  fetchEquipments(pagination.value.current, pagination.value.pageSize);
});
</script>

<template>
  <div class="equipment-table-container">
    <button class="add-btn" @click="console.log('Open add equipment modal')">
      Add Equipment
    </button>

    <div v-if="loading">Loading...</div>

    <div v-else class="table-responsive">
      <table class="equipment-table">
        <thead>
          <tr>
            <th>No.</th>
            <th>Machine Id</th>
            <th>Family</th>
            <th>Model</th>
            <th>Cavity</th>
            <th>Manufacturer</th>
            <th>Manufacturing Date</th>
            <th>History Count</th>
            <th>Unit</th>
            <th>Category</th>
            <th>Task</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data" 
            :key="item.uuid"     
            @click="$router.push({ name: 'UpdateEquipment', params: { uuid: item.uuid } })"
            style="cursor: pointer;"
          >
            <td>{{ item.no }}</td>
            <td>{{ item.machine_id }}</td>
            <td>{{ item.family }}</td>
            <td>{{ item.model }}</td>
            <td>{{ item.cavity }}</td>
            <td>{{ item.manufacturer }}</td>
            <td>{{ item.manufacturing_date }}</td>
            <td>{{ item.history_count }}</td>
            <td>{{ item.unit }}</td>
            <td>{{ item.category }}</td>
            <td>
              <select v-model="item.task" @change="handleTaskChange(item, item.task)">
                <option value="" disabled>Select task</option>
                <option>Daily Inspection</option>
                <option>Maintenance Level 1</option>
                <option>Maintenance Level 2</option>
                <option>Maintenance Level 3</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
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

.equipment-table td select {
  width: 100%;
  padding: 4px;
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
