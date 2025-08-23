<template>
  <header class="app-header">
    <!-- Logo -->
    <div class="header-left" @click="goToMain">
      <img :src="logo" alt="Logo" class="logo" />
      <span class="brand">CMMS</span>
    </div>

    <!-- Menu giữa -->
    <nav class="header-center">
      <ul class="menu">
        <li class="menu-item" :class="{ active: selectedKey.startsWith('/dashboard/equipments') }">
          <span>Equipment ▾</span>
          <ul class="submenu">
            <li @click="onMenuClick('/dashboard/equipments')">List equipments</li>
            <li @click="onMenuClick('/dashboard/equipments/add')">Add equipment</li>
          </ul>
        </li>

        <li class="menu-item" :class="{ active: selectedKey.startsWith('/dashboard/categories') }">
          <span>Category ▾</span>
          <ul class="submenu">
            <li @click="onMenuClick('/dashboard/categories')">List categories</li>
            <li @click="onMenuClick('/dashboard/categories/add')">Add category</li>
          </ul>
        </li>

        <li class="menu-item" :class="{ active: selectedKey.startsWith('/dashboard/working-instructions') }">
          <span>Working Instructions ▾</span>
          <ul class="submenu">
            <li @click="onMenuClick('/dashboard/working-instructions')">List working instructions</li>
            <li @click="onMenuClick('/dashboard/working-instructions/add')">Add working instruction</li>
          </ul>
        </li>
      </ul>
    </nav>

    <!-- User info -->
    <div class="header-right">
      <div class="user-info">
        <div class="name">{{ user.name }}</div>
        <div class="role">{{ user.role }}</div>
      </div>
      <div class="avatar-dropdown">
        <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
        <ul class="dropdown-menu">
          <li @click="handleMenuClick('profile')">👤 Profile</li>
          <li @click="handleMenuClick('logout')">🔓 Logout</li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import logo from "@/assets/logo.png";
import axiosClient from "@/utils/axiosClient";

const router = useRouter();
const route = useRoute();

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: "", role: "" }),
  },
});

const selectedKey = ref(route.path);

function goToMain() {
  router.push("/dashboard/equipments");
}

function onMenuClick(path) {
  if (path === route.path) {
    router.replace({ path: "/redirect" }).then(() => {
      router.replace(path);
    });
  } else {
    router.push(path);
  }
}

const handleMenuClick = async (key) => {
  if (key === "profile") {
    router.push("/profile");
  } else if (key === "logout") {
    try {
      await axiosClient.post("/auth/logout");
    } catch (err) {
      console.error("Logout API error:", err);
    } finally {
      localStorage.removeItem("accessToken");
      router.push("/login");
    }
  }
};

watch(
  () => route.path,
  (path) => {
    selectedKey.value = path;
  },
  { immediate: true }
);
</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 16px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
}

/* Logo */
.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}
.logo {
  width: 60px;
  height: 60px;
  object-fit: contain;
}
.brand {
  /* font-family: "Arial Black", Impact, sans-serif; */
  font-size: 32px;
  font-weight: 900;
  letter-spacing: 2px;
  color: black;
}

/* Menu */
.header-center {
  flex: 1;
  display: flex;
  justify-content: center;
}
.menu {
  list-style: none;
  display: flex;
  gap: 32px;
  margin: 0;
  padding: 0;
}
.menu-item {
  position: relative;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  color: black;
}
.menu-item.active > span {
  color: #1890ff;
  border-bottom: 2px solid #1890ff;
}
.menu-item:hover > .submenu {
  display: block;
}
.submenu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  list-style: none;
  padding: 8px 0;
  margin: 0;
  min-width: 200px;
   display: none; 
  border-radius: 6px;
}
.submenu li {
 padding: 14px 20px; 
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}
.submenu li:hover {
  background: #f5f5f5;
  color: #1890ff;
}
/* Hiện submenu khi hover */
.menu-item:hover .submenu {
  display: block;
}
/* Active state */
.menu-item.active > span {
  color: #007bff;
}

/* User */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.user-info {
  text-align: right;
}
.name {
  font-weight: 600;
  font-size: 16px;
}
.role {
  font-size: 13px;
  color: #888;
}

/* Avatar dropdown */
.avatar-dropdown {
  position: relative;
}
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #fa8c16;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  cursor: pointer;
}
.avatar-dropdown:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
  position: absolute;
  right: 0;
  top: 110%;
  background: white;
  list-style: none;
  margin: 0;
  padding: 6px 0;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  border-radius: 6px;
}
.dropdown-menu li {
  padding: 8px 16px;
  cursor: pointer;
  font-size: 15px;
}
.dropdown-menu li:hover {
  background: #f5f5f5;
  color: #1890ff;
}
.ant-dropdown-menu-item {
  padding: 14px 20px !important; /* tăng padding để item cao hơn */
  font-size: 16px !important;    /* chữ to hơn */
  font-weight: 600;              /* chữ in đậm */
  line-height: 1.6;              /* thoáng hơn */
  min-height: 48px;              /* đảm bảo chiều cao tối thiểu */
}

/* Submenu container */
.ant-dropdown-menu {
  min-width: 220px;              /* submenu rộng hơn mặc định */
  border-radius: 8px;            /* bo góc mềm mại */
  padding: 8px 0;                /* thoáng giữa các item */
}

/* Hover đẹp hơn */
.ant-dropdown-menu-item:hover {
  background-color: #f0f0f0 !important; /* nền xám nhạt */
  color: #000 !important;               /* chữ đen */
}
</style>
