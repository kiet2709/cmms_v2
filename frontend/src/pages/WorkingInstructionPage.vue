<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axiosClient from '@/utils/axiosClient';
import { EyeOutlined } from "@ant-design/icons-vue";
import FormViewer from './FormViewer.vue';


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

    // gán số thứ tự
    data.value = apiData.map((item, index) => ({
      no: (page - 1) * limit + index + 1,
      task: null, // dropdown task
      ...item
    }));
    console.log(data.value);
    
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



// modal state
const isModalOpen = ref(false)
const modalContent = ref("")
const currentId = ref(null)


const viewItem = (payload) => {
    const id =
    typeof payload === 'string'
      ? payload
      : payload?.uuid ?? payload?.id ?? payload?.value ?? null

  currentId.value = id
  if (!id) {
    console.warn('Không tìm thấy uuid/id trong item:', payload)
  }
  // console.log('item: ' + item);
  
  isModalOpen.value = true
}
const closeModal = () => {
  isModalOpen.value = false
}

</script>

<template>
  <div class="equipment-table-container">
    <button class="add-btn" @click="console.log('Open add equipment modal')">
      Add Working Instruction
    </button>

    <div v-if="loading">Loading...</div>

    <div v-else class="table-responsive">
      <table class="equipment-table">
        <thead>
          <tr>
            <th>Task Code</th>
            <th>Description</th>
            <th>Daily Inspection / Maintainance</th>
            <th>Timestamp</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data"  :key="item.uuid || item.id">
            <td>{{ item.code }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.type }}</td>
            <td>{{ item.updated_at }}</td>
            <td>
                <span class="view-btn" @click="viewItem(item)">
                  <EyeOutlined />
                </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Overlay -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <button class="modal-close" @click="closeModal">×</button>
        <FormViewer v-if="isModalOpen" :key="currentId" :id="currentId" />
      </div>
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


/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  width: 90%;
  height: 80%;
  border-radius: 12px;
  padding: 20px;
  position: relative;
  overflow: auto;
}

.modal-close {
  position: absolute;
  right: 15px;
  top: 10px;
  border: none;
  background: none;
  font-size: 28px;
  cursor: pointer;
}

.view-btn {
  cursor: pointer;
  font-size: 18px;
  color: #1890ff;
}
</style>
