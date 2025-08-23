<template>
  <div class="layout">
    <!-- Header mobile có nút hamburger -->
    <div class="mobile-header" v-if="isMobile">
      <menu-outlined class="hamburger" @click="showSidebar = !showSidebar" />
      <span class="header-title">Dashboard</span>
    </div>

    <!-- Desktop: sider cố định bên trái -->
    <aside v-if="!isMobile" class="sidebar-desktop">
      <SideBar />
    </aside>

    <!-- Mobile: menu xổ dọc full-width (không dùng a-layout-sider) -->
    <div v-if="isMobile && showSidebar" class="sidebar-mobile">
      <SideBar :mobile="true" />
    </div>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import SideBar from '@/components/SideBar.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { MenuOutlined } from '@ant-design/icons-vue'

const isMobile = ref(false)
const showSidebar = ref(false)

const mq = window.matchMedia('(max-width: 768px)')
const apply = (e) => {
  isMobile.value = e.matches
  if (!e.matches) showSidebar.value = false // quay về desktop thì đóng mobile menu
}

onMounted(() => {
  apply(mq)
  mq.addEventListener('change', apply)
})
onBeforeUnmount(() => mq.removeEventListener('change', apply))
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

/* Desktop */
.sidebar-desktop {
  width: 260px;          /* chiều rộng sider */
  flex-shrink: 0;
  background: #001529;   /* trùng theme dark */
}
.main-content {
  flex: 1;
  padding: 16px;
  background: #f5f5f5;
  overflow-x: auto;
  
}

/* Mobile header */
.mobile-header {
  display: flex;
  align-items: center;
  background: #001529;
  color: #fff;
  padding: 8px 12px;
}
.hamburger {
  font-size: 22px;
  cursor: pointer;
  margin-right: 12px;
}

/* Mobile xếp dọc */
@media (max-width: 768px) {
  .layout { flex-direction: column; }
  .sidebar-mobile {
    width: 100%;
    background: #001529;
  }
  /* tạo khoảng cách nhẹ giữa menu và nội dung */
  /* .sidebar-mobile + .main-content { margin-top: 8px; } */

  .main-content {
    padding-left: 15px;
    padding-right: 15px;
  }
}
</style>
