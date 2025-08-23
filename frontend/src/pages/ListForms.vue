<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import DataTable from '../components/DataTable.vue';

const route = useRoute();
const router = useRouter();



const loading = ref(false);
const columns = ref([]);
const data = ref([]);
const pagination = ref({
  current: Number(route.query.page) || 1,
  pageSize: Number(route.query.limit) || 10,
  total: 0,
  showTotal: (total) => `Total ${total} items`,
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '50', '100']
});

// 🔹 Helper flatten object (convert nested object thành key_key)
function flattenObject(obj, parentKey = '') {
  return Object.entries(obj).reduce((acc, [key, value]) => {
    const newKey = parentKey ? `${parentKey}_${key}` : key;
    if (value && typeof value === 'object' && !Array.isArray(value)) {
      Object.assign(acc, flattenObject(value, newKey));
    } else {
      acc[newKey] = value;
    }
    return acc;
  }, {});
}

async function fetchEquipments(page = 1, limit = 10) {
  try {
    loading.value = true;
    const res = await axiosClient.get(`forms?page=${page}&limit=${limit}`);

    const apiData = res.data.data.map(item => {
      // flatten toàn bộ record
      let flat = flattenObject(item);

      // Bỏ uuid, id, equipment_id, category_id
      const { uuid, 
            id, 
            created_by, 
            updated_by, 
            updated_at, 
            deleted_by, 
            deleted_at,  
            created_at, 
            daily_plan_item_id,
            ...rest } = flat;

    if (rest.frequency !== undefined) {
        rest.frequency = rest.frequency === 0 ? 'Daily' : 'Shift'
    }

      return {uuid, ...rest};
    });

    const totalItems = res.data.total_in_all_page;
    pagination.value.total = totalItems;
    pagination.value.current = page;
    pagination.value.pageSize = limit;

    router.replace({ path: route.path, query: { page, limit } });

    if (apiData.length > 0) {
      // build columns từ keys (trừ uuid)
      columns.value = [
        { title: 'No.', dataIndex: 'no', key: 'no', width: 80 },
        ...Object.keys(apiData[0])
          .filter((key) => key !== 'uuid') // 👈 bỏ uuid khỏi cột
          .map((key) => {
            const cleanKey = key.replace(/^equipment_/, '');
            return {
              title: cleanKey.replace(/_/g, ' ')
                            .replace(/^./, (s) => s.toUpperCase()),
              dataIndex: key,
              key: key,
              width: 150
            };
          })
      ];

      data.value = apiData.map((row, index) => ({
        no: (page - 1) * limit + index + 1,
        key: index,
        ...row
      }));
    }
  } finally {
    loading.value = false;
  }
}

function handleTableChange(pag) {
  fetchEquipments(pag.current, pag.pageSize);
}

function openAddEquipmentModal() {
  console.log('Open add category modal');
}

onMounted(() => {
  fetchEquipments(pagination.value.current, pagination.value.pageSize);
});

function customRow(record) {
  return {
    onClick: () => {
      router.push(`/dashboard/forms/${record.uuid}`);
    }
  };
}

</script>



<template>
  <DataTable
    :columns="columns"
    :data-source="data"
    :pagination="pagination"
    :loading="loading"
    search-key="model"
    search-placeholder="Search daily inspection plan..."
    :action-button="{ label: 'Add daily inspection plan', event: 'add-daily-inspection-plan' }"
    @change="handleTableChange"
    @add-equipment="openAddEquipmentModal"
    :customRow="customRow"
  />
</template>
