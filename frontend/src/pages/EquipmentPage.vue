<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import { EditOutlined, DeleteOutlined, FileSearchOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { Modal, Button, Input } from 'ant-design-vue';

const router = useRouter();
const loading = ref(false);
const data = ref([]);
const searchQuery = ref('');
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

function openMasterPlan(uuid) {
  selectedUuid.value = uuid;
  showMasterPlanModal.value = true;
}

function closeMasterPlan() {
  showMasterPlanModal.value = false;
  selectedUuid.value = null;
}

// filter tại chỗ (trên data page hiện tại)
const filteredData = computed(() => {
  if (!searchQuery.value) return data.value;
  const q = searchQuery.value.toLowerCase();
  return data.value.filter(item => 
    (item.machine_id && item.machine_id.toLowerCase().includes(q)) ||
    (item.family && item.family.toLowerCase().includes(q)) ||
    (item.model && item.model.toLowerCase().includes(q)) ||
    (item.manufacturing_date && item.manufacturing_date.toLowerCase().includes(q))
  );
});

onMounted(() => {
  fetchEquipments(pagination.value.current, pagination.value.pageSize);
});
</script>

<template>
  <div class="equipment-table-container">
    <!-- Search -->
    <div class="search-bar">
      <Input 
        v-model:value="searchQuery" 
        placeholder="Search by Machine ID, Family, Model, or Manufacturing Date" 
        allow-clear
      />
    </div>

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
            <th>Master Plan</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredData" :key="item.uuid">
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
              <Button type="link" @click="openMasterPlan(item.uuid)">
                <FileSearchOutlined /> View
              </Button>
            </td>
            <td>
              <EditOutlined 
                @click="handleEdit(item.uuid)" 
                style="cursor:pointer; margin-right:10px;" 
              />
              <DeleteOutlined 
                @click="confirmDelete(item)" 
                style="cursor:pointer;" 
              />
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

    <!-- Master Plan Modal -->
    <Modal 
      v-model:open="showMasterPlanModal" 
      title="Master Plan" 
      @cancel="closeMasterPlan"
      footer={null}
    >
      <h3>Master Plan for {{ selectedUuid }}</h3>
      <p>TODO: show master plan details here</p>
    </Modal>

    <!-- Delete Confirm Modal -->
    <Modal
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
        <strong>{{ deleteTarget?.machine_id }}</strong>?
      </p>
    </Modal>
  </div>
</template>

<style scoped>
.search-bar {
  margin-bottom: 1rem;
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
</style>