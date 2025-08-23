<template>
  <a-layout-header class="app-header">
    <div class="header-left"
        @click="goToMain"
        style="
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 20px;
            cursor: pointer;
        "
    >
        <img
            :src="logo"
            alt="Logo"
            style="width: 60px; height: 60px; object-fit: contain;"
        />
        <span style="
            font-family: 'Arial Black', Impact, sans-serif;
            font-size: 50px;
            font-weight: 900;
            letter-spacing: 4px;
            display: inline-block;
            margin-left: 10px;
            ">
            CMMS
        </span>
    </div>

    <div class="header-right">
      <a-space size="small" align="center">
        <div class="user-info">
          <div class="name">{{ user.name }}</div>
          <div class="role">{{ user.role }}</div>
        </div>

        <a-dropdown placement="bottomRight">
          <a-avatar :size="40" :style="{ backgroundColor: '#fa8c16', cursor: 'pointer' }">
            {{ user.name.charAt(0).toUpperCase() }}
          </a-avatar>
          <template #overlay>
            <a-menu  @click="({ key }) => handleMenuClick(key)">
              <a-menu-item key="profile" class="menu-item-green" >
                <template #icon>
                  <span style="font-size: 16px;">👤</span>
                </template>
                Profile
              </a-menu-item>
              <a-menu-item key="logout" class="menu-item-green">
                <template #icon>
                  <span style="font-size: 16px;">🔓</span>
                </template>
                Logout
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </a-space>
    </div>
  </a-layout-header>
</template>

<script setup>

import logo from '@/assets/logo.png'
import { useRouter } from 'vue-router'
import axiosClient from '@/utils/axiosClient';


const router = useRouter()

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: '', role: '' })
  }
})



function goToMain() {
  router.push('/dashboard/equipments')
}

const handleMenuClick = async (key) => {
  if (key === 'profile') {
    router.push('/profile')
  } 
  else if (key === 'logout') {
    try {
      await axiosClient.post('/auth/logout')
    } catch (err) {
      console.error('Logout API error:', err)
    } finally {
      localStorage.removeItem('accessToken')
      router.push('/login')
    }
  }
}

</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 16px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;



  /* Cố định header */
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;

}

.logo {
  height: 40px;
}

.user-info {
  text-align: right;
  margin-right: 8px;
}

.name {
  font-weight: 500;
  font-size: 18px;
}

.role {
  font-size: 14px;
  color: #888;
}


@media (max-width: 768px) {
  .user-info .name,
  .user-info .role {
    display: none;
  }

  .user-info {
    margin-right: 0; /* Bỏ khoảng cách nếu cần */
  }
}

</style>
