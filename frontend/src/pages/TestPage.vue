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

async function fetchUsers(page = 1, limit = 10) {
  try {
    loading.value = true;
    const res = await axiosClient.get(`users?page=${page}&limit=${limit}`);

    const apiData = res.data.data;
    const totalItems = res.data.total_in_all_page;

    pagination.value.total = totalItems;
    pagination.value.current = page;
    pagination.value.pageSize = limit;

    router.replace({ path: route.path, query: { page, limit } });

    if (apiData.length > 0) {
      columns.value = [
        { title: 'No.', dataIndex: 'no', key: 'no', width: 80 },
        ...Object.keys(apiData[0])
          .filter((key) => key !== 'id')
          .map((key) => ({
            title: key === 'role'
              ? 'Account type'
              : key.replace(/([a-z])([A-Z])/g, '$1 $2')
                   .replace(/^./, (s) => s.toUpperCase()),
            dataIndex: key,
            key: key,
            width: 150
          }))
      ];

      data.value = apiData.map((user, index) => ({
        no: (page - 1) * limit + index + 1,
        key: user.id || index,
        ...user
      }));
    }
  } finally {
    loading.value = false;
  }
}

function handleTableChange(pag) {
  fetchUsers(pag.current, pag.pageSize);
}

onMounted(() => {
  fetchUsers(pagination.value.current, pagination.value.pageSize);
});
</script>

<template>
    <DataTable
        :columns="columns"
        :data-source="data"
        :pagination="pagination"
        :loading="loading"
        search-key="username"
        search-placeholder="Search user..."
        :action-button="{ label: 'Add User', event: 'add-user' }"
        @change="handleTableChange"
        @add-user="openAddUserModal"
    />
</template>
