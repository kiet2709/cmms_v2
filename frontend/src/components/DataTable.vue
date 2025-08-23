<template>
  <div v-if="!loading" class="table-container">
    <div class="table-toolbar">
      <slot name="toolbar">
        <a-button
          v-if="actionButton && actionButton.label"
          type="primary"
          @click="emit(actionButton.event || 'action')"
        >
          {{ actionButton.label }}
        </a-button>
      </slot>

      <a-input-search
        v-model:value="searchText"
        :placeholder="searchPlaceholder"
        enter-button
        @change="onSearch"
        @search="onSearch"
        style="max-width: 300px"
      />
    </div>

    <div class="table-scroll-wrapper">
      <a-table
        :columns="columns"
        :data-source="filteredData"
        :pagination="pagination"
        @change="(pag) => emit('change', pag)"
        row-key="id"
        bordered
        :scroll="{ x: 'max-content' }"
        :customRow="props.customRow"
        :rowClassName="() => 'clickable-row'"
      />
    </div>
  </div>

  <div v-else class="loading-container">
    <a-spin size="large" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  columns: { type: Array, required: true },
  dataSource: { type: Array, required: true },
  pagination: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  searchKey: { type: String, default: '' }, // field ưu tiên để search
  searchPlaceholder: { type: String, default: 'Search...' },
  actionButton: { // cấu hình nút hành động
    type: Object,
    default: () => ({
      label: '', // tên nút (ví dụ "Add User")
      event: ''  // tên event emit ra (ví dụ "add-user")
    })
  },
  customRow: Function,
});

const emit = defineEmits(['change', 'search', 'action']);

const searchText = ref('');

const filteredData = computed(() => {
  if (!searchText.value) return props.dataSource;
  const keyword = searchText.value.toLowerCase();

  if (props.searchKey && props.dataSource.length > 0) {
    return props.dataSource.filter((item) =>
      String(item[props.searchKey] || '')
        .toLowerCase()
        .includes(keyword)
    );
  }

  return props.dataSource.filter((item) =>
    Object.values(item).some((val) =>
      String(val).toLowerCase().includes(keyword)
    )
  );
});

function onSearch() {
  emit('search', searchText.value);
}
</script>




<style scoped>
.table-container {
  padding: 16px;
  background: #fff;
  border-radius: 8px;
}

/* Thanh control cố định khi scroll ngang */
.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  position: sticky;
  top: 0;
  z-index: 2;
  background: #fff;
  padding: 8px 0;
}

/* Bọc table để scroll ngang */
.table-scroll-wrapper {
  overflow-x: auto;
}

/* Cho phép chữ trong bảng xuống dòng */
:deep(.ant-table-cell) {
    max-width: 200px; /* Giới hạn chiều rộng */
  white-space: normal !important;
  word-break: break-word !important;
  overflow-wrap: break-word !important;
}

.loading-container {
  display: flex;
  justify-content: center; /* canh ngang */
  align-items: center;     /* canh dọc */
  height: 100vh;           /* full màn hình */
  width: 100%;
}

.loading-container .ant-spin {
  transform: scale(2); /* phóng to 3 lần */
}


/* hover vào row -> đổi màu cho tất cả cell */
:deep(.clickable-row:hover > td) {
  background-color: #fff7e6 !important;
  /* color: #fff !important; */
  color: #2932e1;
}

/* để con trỏ thành bàn tay */
:deep(.clickable-row) {
  cursor: pointer;
}

</style>
