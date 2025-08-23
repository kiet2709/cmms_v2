<script setup lang="ts">

import {
  AppstoreOutlined,
  FileTextOutlined,
  ToolOutlined,
  ShoppingCartOutlined,
  TagsOutlined,
  UserOutlined,
} from '@ant-design/icons-vue'

import SectionPanel from '@/components/SectionPanel.vue'
import ReminderWidget from '@/components/ReminderWidget.vue'
import CalendarWidget from '@/components/CalendarWidget.vue'

import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

import { onMounted, computed  } from 'vue'

const router = useRouter()

const userStore = useUserStore()

onMounted(async () => {
  await userStore.fetchUser()
})

// Hàm filter chung theo role
function filterItemsByRole(items: any[], role: string) {
  const blockedLabelsByRole: Record<string, string[]> = {
    user: ['Category', 
      'Users management', 
      'Form questions',
      'Daily inspection management',
      'Equipments management',
      'Equipment declaration'
    ],
    // admin: [], các role khác có thể thêm
  }

  const blockedLabels = blockedLabelsByRole[role] || []
  return items.filter(item => !blockedLabels.includes(item.label))
}

// dữ liệu tạm
const allAssetItems = [
  // { label: 'Assets - Equipment Declaration', icon: AppstoreOutlined },
  // { label: 'View Assets - Equipment information', icon: FileTextOutlined },
  // { label: 'Update status', icon: ToolOutlined },
  { label: 'Equipments management', icon: AppstoreOutlined, route: '/dashboard/equipments' },
  { label: 'Equipment declaration', icon: FileTextOutlined, route: '/dashboard/equipments/add' },
  { label: 'Category', icon: ToolOutlined, route: '/dashboard/categories' },
]

const allMaintenanceItems = [
  { label: 'Daily inspection management', icon: FileTextOutlined, route: '/dashboard/daily-inspection-plans'  },
  { label: 'Today', icon: ToolOutlined, route: '/dashboard/daily-inspection-plans/today'   },
  { label: 'History', icon: ToolOutlined, route: '/dashboard/daily-inspection-plans/history'   },
  { label: 'Form questions', icon: ToolOutlined, route: '/dashboard/forms'   },
]

// const purchaseItems = [
//   { label: 'Recommended purchase', icon: ShoppingCartOutlined },
//   { label: 'Invoice / receipt', icon: TagsOutlined },
// ]

const allManagementItems = [
  { label: 'Users management', icon: UserOutlined, route: '/dashboard/users' }, // thêm route ở đây
]


// Computed items đã filter
const assetItems = computed(() => filterItemsByRole(allAssetItems, userStore.user.role))
const maintenanceItems = computed(() => filterItemsByRole(allMaintenanceItems, userStore.user.role))
const managementItems = computed(() => filterItemsByRole(allManagementItems, userStore.user.role))

function onSelectItem(item: any) {
  if (item.route) {
    router.push(item.route)
  } else {
    console.log('Selected:', item)
  }
}



</script>

<template> 
  <div class="main-layout">
    <div class="main-content">

        <!-- Mua hàng - Kho -->
        <SectionPanel
          v-if="managementItems.length"
            title="Management"
            :items="managementItems"
            bg-color="#fff7e6"
            @select="onSelectItem"
        />

        <!-- Bảo trì - Sửa chữa -->
        <SectionPanel
            v-if="maintenanceItems.length"
            title="Inspection"
            :items="maintenanceItems"
            bg-color="#f6ffed"
            @select="onSelectItem"
        />


        <!-- Tài sản - Thiết bị -->
        <SectionPanel
            v-if="assetItems.length"
            title="Assets - Equipment"
            :items="assetItems"
            bg-color="#e6fffb"
            @select="onSelectItem"
        />



        
        <!-- Mua hàng - Kho -->
        <!-- <SectionPanel
            title="Purchase - Warehouse"
            :items="purchaseItems"
            bg-color="#fff7e6"
            @select="onSelectItem"
        /> -->

    </div>

    <div class="sidebar">
      <!-- <ReminderWidget /> -->
      <CalendarWidget />
    </div>
  </div>
    

</template>


<style scoped>
.main-layout {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
}

.main-content {
  flex: 1;
  min-height: 100vh; 
}

.sidebar {
  width: 300px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column; /* xếp theo cột */
  gap: 16px; /* khoảng cách giữa các widget */
}

/* Responsive cho mobile/tablet */
@media (max-width: 1024px) {
  .main-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    order: 2; /* Cho sidebar xuống dưới */
  }

  .main-content {
    order: 1;
    width: 100%;
    min-height: auto; 
  }
}
</style>