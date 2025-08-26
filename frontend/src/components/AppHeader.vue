<template>
  <header class="modern-navbar">
    <!-- Logo Section -->
    <div class="navbar-brand" @click="goToMain">
      <div class="logo-container">
        <img :src="logo" alt="CMMS Logo" class="logo-image" />
        <div class="brand-text">
          <span class="brand-name">CMMS</span>
          <!-- <span class="brand-subtitle">cmms</span> -->
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="navbar-nav">
      <div class="nav-menu">
        <!-- Equipment Menu -->
        <div 
          class="nav-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/user') }"
          @mouseenter="showSubmenu = 'User'"
          @mouseleave="showSubmenu = null"
        >
          <div class="nav-link">
                  <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="10" r="3"></circle>
            <path d="M6 20c0-3 3-5 6-5s6 2 6 5"></path>
          </svg>


            <span>User</span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
          </div>
          <div class="submenu" :class="{ show: showSubmenu === 'User' }">
            <div class="submenu-content">
              <a @click="onMenuClick('/dashboard/user')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18v18H3V3z"></path>
                  <path d="M9 9h6v6H9V9z"></path>
                </svg>
                <div>
                  <div class="submenu-title">List User</div>
                  <div class="submenu-desc">View all User</div>
                </div>
              </a>
              <a @click="onMenuClick('/dashboard/user/permission')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <div>
                  <div class="submenu-title">Permissions</div>
                  <div class="submenu-desc">Permissions User</div>
                </div>
              </a>
            </div>
          </div>
        </div>
        <!-- Equipment Menu -->
        <div 
          class="nav-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/equipments') }"
          @mouseenter="showSubmenu = 'equipment'"
          @mouseleave="showSubmenu = null"
        >
          <div class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
              <line x1="8" y1="21" x2="16" y2="21"></line>
              <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <span>Equipment</span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
          </div>
          <div class="submenu" :class="{ show: showSubmenu === 'equipment' }">
            <div class="submenu-content">
              <a @click="onMenuClick('/dashboard/equipments')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18v18H3V3z"></path>
                  <path d="M9 9h6v6H9V9z"></path>
                </svg>
                <div>
                  <div class="submenu-title">List Equipment</div>
                  <div class="submenu-desc">View all equipment</div>
                </div>
              </a>
              <a @click="onMenuClick('/dashboard/equipments/add')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <div>
                  <div class="submenu-title">Add Equipment</div>
                  <div class="submenu-desc">Create new equipment</div>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Category Menu -->
        <div 
          class="nav-item single-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/categories') }"
          @click="onMenuClick('/dashboard/categories')"
        >
          <div class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
            <span>Category</span>
          </div>
        </div>

        <!-- Working Instructions Menu -->
        <div 
          class="nav-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/working-instructions') }"
          @mouseenter="showSubmenu = 'instructions'"
          @mouseleave="showSubmenu = null"
        >
          <div class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <span>Instructions</span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
          </div>
          <div class="submenu" :class="{ show: showSubmenu === 'instructions' }">
            <div class="submenu-content">
              <a @click="onMenuClick('/dashboard/working-instructions')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18v18H3V3z"></path>
                  <path d="M9 9h6v6H9V9z"></path>
                </svg>
                <div>
                  <div class="submenu-title">List Instructions</div>
                  <div class="submenu-desc">View all instructions</div>
                </div>
              </a>
              <a @click="onMenuClick('/dashboard/working-instructions/add')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <div>
                  <div class="submenu-title">Add Instruction</div>
                  <div class="submenu-desc">Create new instruction</div>
                </div>
              </a>
            </div>
          </div>
        </div>



        <!-- Tasks Menu -->
        <div 
          class="nav-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/tasks/daily') }"
          @mouseenter="showSubmenu = 'tasks'"
          @mouseleave="showSubmenu = null"
        >
          <div class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <span>Tasks</span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
          </div>
          <div class="submenu" :class="{ show: showSubmenu === 'tasks' }">
            <div class="submenu-content">
              <a @click="onMenuClick('/dashboard/tasks/daily')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18v18H3V3z"></path>
                  <path d="M9 9h6v6H9V9z"></path>
                </svg>
                <div>
                  <div class="submenu-title">List Daily Inspection</div>
                  <div class="submenu-desc">View all daily inspection</div>
                </div>
              </a>
              <!-- <a @click="onMenuClick('/dashboard/daily-tasks/add')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <div>
                  <div class="submenu-title">Add Instruction</div>
                  <div class="submenu-desc">Create new instruction</div>
                </div>
              </a> -->
            </div>
          </div>
        </div>

        <!-- Test Menu -->
        <div 
          class="nav-item" 
          :class="{ active: selectedKey.startsWith('/dashboard/test') }"
          @mouseenter="showSubmenu = 'test'"
          @mouseleave="showSubmenu = null"
        >
          <div class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 11H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h4m6-6h4a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-4m-6 0v4a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-4m-6 0h6"></path>
            </svg>
            <span>Test</span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
          </div>
          <div class="submenu" :class="{ show: showSubmenu === 'test' }">
            <div class="submenu-content">
              <a @click="onMenuClick('/dashboard/test')" class="submenu-item">
                <svg class="submenu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                <div>
                  <div class="submenu-title">Category Test</div>
                  <div class="submenu-desc">Test categories</div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- User Section -->
    <div class="navbar-user">
      <div class="user-profile">
        <div class="user-info">
          <div class="user-name">{{ user.name }}</div>
          <div class="user-role">{{ user.role }}</div>
        </div>
        <div 
          class="user-avatar" 
          @click="toggleUserMenu"
          :class="{ active: showUserMenu }"
        >
          <span class="avatar-text">{{ user.name.charAt(0).toUpperCase() }}</span>
          <div class="online-indicator"></div>
        </div>
      </div>

      <!-- User Dropdown Menu -->
      <div class="user-menu" :class="{ show: showUserMenu }">
        <div class="user-menu-header">
          <div class="user-menu-avatar">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div class="user-menu-info">
            <div class="user-menu-name">{{ user.name }}</div>
            <div class="user-menu-role">{{ user.role }}</div>
          </div>
        </div>
        <div class="user-menu-divider"></div>
        <a @click="handleMenuClick('profile')" class="user-menu-item">
          <svg class="menu-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>Profile</span>
        </a>
        <a @click="handleMenuClick('logout')" class="user-menu-item logout">
          <svg class="menu-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16,17 21,12 16,7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Logout</span>
        </a>
      </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" @click="toggleMobileMenu">
      <span class="hamburger-line" :class="{ active: showMobileMenu }"></span>
      <span class="hamburger-line" :class="{ active: showMobileMenu }"></span>
      <span class="hamburger-line" :class="{ active: showMobileMenu }"></span>
    </button>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" :class="{ show: showMobileMenu }" @click="closeMobileMenu"></div>
  </header>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import logo from "@/assets/logo.png";
import axiosClient from "@/utils/axiosClient";

const router = useRouter();
const route = useRoute();

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: "User", role: "Admin" }),
  },
});

const selectedKey = ref(route.path);
const showSubmenu = ref(null);
const showUserMenu = ref(false);
const showMobileMenu = ref(false);

function goToMain() {
  router.push("/dashboard/equipments");
  closeMobileMenu();
}

function onMenuClick(path) {
  if (path === route.path) {
    router.replace({ path: "/redirect" }).then(() => {
      router.replace(path);
    });
  } else {
    router.push(path);
  }
  showSubmenu.value = null;
  closeMobileMenu();
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
  showUserMenu.value = false;
  closeMobileMenu();
};

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
}

function toggleMobileMenu() {
  showMobileMenu.value = !showMobileMenu.value;
}

function closeMobileMenu() {
  showMobileMenu.value = false;
  showSubmenu.value = null;
  showUserMenu.value = false;
}

function handleClickOutside(event) {
  if (!event.target.closest('.navbar-user')) {
    showUserMenu.value = false;
  }
  if (!event.target.closest('.nav-item')) {
    showSubmenu.value = null;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(
  () => route.path,
  (path) => {
    selectedKey.value = path;
  },
  { immediate: true }
);
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.modern-navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  height: 80px;
  background: #3a8cff;
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 4px 32px rgba(0, 0, 0, 0.1);
}

/* Logo Section */
.navbar-brand {
  cursor: pointer;
  transition: transform 0.3s ease;
}

.navbar-brand:hover {
  transform: scale(1.05);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-image {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  object-fit: contain;
  background: rgba(255, 255, 255, 0.7);
  padding: 8px;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.8rem;
  font-weight: 800;
  color: white;
  letter-spacing: 1px;
  line-height: 1;
}

.brand-subtitle {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Navigation */
.navbar-nav {
  flex: 1;
  display: flex;
  justify-content: center;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-item {
  position: relative;
  cursor: pointer;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-item:hover .nav-link,
.nav-item.active .nav-link {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.2);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.nav-icon {
  width: 20px;
  height: 20px;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.3s ease;
}

.nav-item:hover .dropdown-icon {
  transform: rotate(180deg);
}

.single-item .nav-link {
  cursor: pointer;
}

/* Submenu */
.submenu {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-top: 0.5rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  opacity: 0;
  visibility: hidden;
  transform: translateX(-50%) translateY(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 280px;
}

.submenu.show {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}

.submenu-content {
  padding: 0.75rem;
}

.submenu-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  color: #374151;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
}

.submenu-item:hover {
  background: #3a8cff;
  color: white;
  transform: translateX(4px);
}

.submenu-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.submenu-title {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.2;
}

.submenu-desc {
  font-size: 0.8rem;
  opacity: 0.7;
  line-height: 1.2;
}

/* User Section */
.navbar-user {
  position: relative;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
}

.user-info {
  text-align: right;
  color: white;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.2;
}

.user-role {
  font-size: 0.8rem;
  opacity: 0.7;
  line-height: 1.2;
}

.user-avatar {
  position: relative;
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  transition: all 0.3s ease;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.user-avatar:hover,
.user-avatar.active {
  transform: scale(1.1);
  border-color: rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.avatar-text {
  font-size: 1.1rem;
}

.online-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #10b981;
  border: 2px solid white;
  border-radius: 50%;
}

/* User Menu */
.user-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 240px;
}

.user-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.user-menu-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  color: white;
}

.user-menu-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.user-menu-name {
  font-weight: 600;
  font-size: 1rem;
}

.user-menu-role {
  font-size: 0.85rem;
  opacity: 0.8;
}

.user-menu-divider {
  height: 1px;
  background: rgba(0, 0, 0, 0.1);
}

.user-menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  color: #374151;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
}

.user-menu-item:hover {
  background: #f8fafc;
  color: #667eea;
}

.user-menu-item.logout:hover {
  background: #fef2f2;
  color: #ef4444;
}

.menu-item-icon {
  width: 20px;
  height: 20px;
}

/* Mobile Menu */
.mobile-menu-btn {
  display: none;
  flex-direction: column;
  gap: 4px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
}

.hamburger-line {
  width: 24px;
  height: 3px;
  background: white;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.hamburger-line.active:nth-child(1) {
  transform: rotate(45deg) translate(6px, 6px);
}

.hamburger-line.active:nth-child(2) {
  opacity: 0;
}

.hamburger-line.active:nth-child(3) {
  transform: rotate(-45deg) translate(6px, -6px);
}

.mobile-overlay {
  display: none;
  position: fixed;
  top: 80px;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.mobile-overlay.show {
  opacity: 1;
  visibility: visible;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-navbar {
    padding: 0 1rem;
  }
  
  .navbar-nav {
    display: none;
  }
  
  .user-info {
    display: none;
  }
  
  .mobile-menu-btn {
    display: flex;
  }
  
  .mobile-overlay {
    display: block;
  }
  
  .brand-subtitle {
    display: none;
  }
  
  .brand-name {
    font-size: 1.5rem;
  }
  
  .logo-image {
    width: 40px;
    height: 40px;
  }
}

@media (max-width: 480px) {
  .modern-navbar {
    height: 70px;
    padding: 0 0.75rem;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }
  
  .avatar-text {
    font-size: 1rem;
  }
}
</style>