<template>
 <!-- <div v-if="userStore.user && userStore.rawUser">
    <p>Xin chào {{ userStore.user.name }}</p>
    <p>Role: {{ userStore.rawUser.role }}</p>
    <p>User: {{ userStore.rawUser.user }}</p>
  </div>
  <div v-else>
    <p>Đang tải...</p>
  </div> -->


<div v-if="user.name">
  <a-card>
    <!-- Header -->
    <div style="display: flex; align-items: center; gap: 16px;">
      <a-avatar size={80} style="background-color: #87d068">T</a-avatar>
      <div>
          <h3>{{ user.name }}</h3>
        <!-- <a-space>
          <a-button type="primary">Change avatar</a-button>
          <a-button danger>Delete avatar</a-button>
        </a-space> -->
      </div>
    </div>

    <a-divider />

    <!-- Form -->
    <a-form
      :model="user"
      layout="vertical"
    >
      <a-row :gutter="16">
        <a-col :span="6">
          <a-form-item label="Employment ID">
            <a-input v-model:value="user.code" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="username">
            <a-input v-model:value="user.name" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="Email">
            <a-input v-model:value="user.email" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="Last login">
            <a-input v-model:value="user.phone" disabled />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row :gutter="16">
        <a-col :span="6">
          <a-form-item label="Position">
            <a-input v-model:value="user.position" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="Full name">
            <a-input v-model:value="user.fullname" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="Account type">
            <a-input v-model:value="user.role" disabled />
          </a-form-item>
        </a-col>

        <a-col :span="6">
          <a-form-item label="Last logout">
            <a-input v-model:value="user.title" disabled />
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
  </a-card>
</div>

<div v-else class="loading-container">
  <a-spin size="large" />
</div>





</template>

<script setup>


import { useUserStore } from '@/stores/user' // ví dụ tên store
import { onMounted } from 'vue'
import { computed } from 'vue'


const userStore = useUserStore()

onMounted(async () => {
  await userStore.fetchUser()
  console.log(userStore.rawUser.user) 
})

import { reactive } from 'vue'

const user = computed(() => ({
  code: userStore.rawUser.user.employment_id,
  name: userStore.user.name,
  email: '',
  phone: '',
  position: userStore.rawUser.user.position,
  fullname: userStore.rawUser.user.employment_name,
  role: userStore.rawUser.user.role.name,
  title: ''
}))

// const user = reactive({
//   code: 'CMMS.NVKT',
//   name: userStore.user.name,
//   email: '',
//   phone: '',
//   unit: 'Văn phòng công ty',
//   department: 'Phòng Kỹ thuật',
//   position: 'Nhân viên',
//   title: 'Nhân viên kỹ thuật'
// })



</script>

<style scoped>

h3 {
  margin: 0;
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


</style>
