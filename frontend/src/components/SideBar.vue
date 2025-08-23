<template>
  <!-- MOBILE: không dùng a-layout/a-layout-sider, chỉ a-menu -->
  <div v-if="mobile" class="mobile-menu">
    <a-menu
      v-model:selectedKeys="selectedKeys"
      v-model:openKeys="openKeys"
      theme="dark"
      mode="inline"
      @click="onMenuClick"
    >

      <a-sub-menu key="sub1">
        <template #title>
          <span><user-outlined /><span>User</span></span>
        </template>
        <a-menu-item key="/dashboard/users">List users</a-menu-item>
        <a-menu-item key="/dashboard/users/add">Add user</a-menu-item>
      </a-sub-menu>


      <a-sub-menu key="sub2">
        <template #title>
          <span><bars-outlined /><span>Category</span></span>
        </template>
        <a-menu-item key="/dashboard/categories">List categories</a-menu-item>
        <a-menu-item key="/dashboard/categories/add">Add category</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="sub3">
        <template #title>
          <span><build-outlined /><span>Equipment</span></span>
        </template>
        <a-menu-item key="/dashboard/equipments">List equipments</a-menu-item>
        <a-menu-item key="/dashboard/equipments/add">Add equipment</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="sub4">
        <template #title>
          <span><calendar-outlined /><span>Plan</span></span>
        </template>

        <a-sub-menu key="sub5">
          <template #title>
            <span><schedule-outlined /><span>Daily inspection</span></span>
          </template>
          <a-menu-item key="/dashboard/daily-inspection-plans">List daily inspections</a-menu-item>
          <a-menu-item key="/dashboard/daily-inspection-plans/add">Add daily inspection</a-menu-item>
          <a-menu-item key="/dashboard/daily-inspection-plans/today">Daily inspection today</a-menu-item>
          <a-menu-item key="/dashboard/daily-inspection-plans/history">History daily inspection</a-menu-item>
        </a-sub-menu>

        <a-menu-item key="/dashboard/plan/level1">Level 1 maintenance</a-menu-item>
        <a-menu-item key="/dashboard/plan/level2">Level 2 maintenance</a-menu-item>
        <a-menu-item key="/dashboard/plan/level3">Level 3 maintenance</a-menu-item>

      </a-sub-menu>

        <a-sub-menu key="sub6">
        <template #title>
          <span><form-outlined /><span>Forms</span></span>
        </template>
        <a-menu-item key="/dashboard/forms">List forms</a-menu-item>
        <a-menu-item key="/dashboard/forms/add">Add form</a-menu-item>
      </a-sub-menu>



      <!-- <a-menu-item key="/dashboard/option1">
        <pie-chart-outlined /><span>Option 1</span>
      </a-menu-item>

      <a-menu-item key="/dashboard/option2">
        <desktop-outlined /><span>Option 2</span>
      </a-menu-item>



      <a-sub-menu key="sub2">
        <template #title>
          <span><team-outlined /><span>Team</span></span>
        </template>
        <a-menu-item key="/dashboard/team1">Team 1</a-menu-item>
        <a-menu-item key="/dashboard/team2">Team 2</a-menu-item>
      </a-sub-menu>

      <a-menu-item key="/dashboard/file">
        <file-outlined /><span>File</span>
      </a-menu-item> -->
    </a-menu>
  </div>

  <!-- DESKTOP: dùng a-layout-sider bình thường -->
  <a-layout-sider
    v-else
    v-model:collapsed="collapsed"
    collapsible
    :width="260"
    :collapsedWidth="80"
    style="background:#001529"
  >
    <a-menu
      v-model:selectedKeys="selectedKeys"
      v-model:openKeys="openKeys"
      theme="dark"
      mode="inline"
      @click="onMenuClick"
    >
      <a-sub-menu 
        v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
        key="sub1"
      >
        <template #title>
          <span><build-outlined /><span>Equipment</span></span>
        </template>
        <a-menu-item key="/dashboard/equipments">List equipments</a-menu-item>
        <a-menu-item key="/dashboard/equipments/add">Add equipment</a-menu-item>
      </a-sub-menu>

      <a-sub-menu 
        v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
        key="sub2"
      >
        <template #title>
          <span><bars-outlined /><span>Category</span></span>
        </template>
        <a-menu-item key="/dashboard/categories">List categories</a-menu-item>
        <a-menu-item key="/dashboard/categories/add">Add category</a-menu-item>
      </a-sub-menu>


      <a-sub-menu 
        v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
        key="sub3"
      >
        <template #title>
          <span><bars-outlined /><span>Working instruction</span></span>
        </template>
        <a-menu-item key="/dashboard/working-instructions">List working instructions</a-menu-item>
        <a-menu-item key="/dashboard/working-instructions/add">Add working instructions</a-menu-item>
      </a-sub-menu>


      <!-- <a-sub-menu 
        v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
        key="sub1"
      >
        <template #title>
          <span><user-outlined /><span>User</span></span>
        </template>
        <a-menu-item key="/dashboard/users">List users</a-menu-item>
        <a-menu-item key="/dashboard/users/add">Add user</a-menu-item>
      </a-sub-menu>





      <a-sub-menu  key="sub4">
        <template #title>
          <span><calendar-outlined /><span>Plan</span></span>
        </template>

        <a-sub-menu key="sub5">
          <template #title>
            <span><schedule-outlined /><span>Daily inspection</span></span>
          </template>
          <a-menu-item 
            v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
            key="/dashboard/daily-inspection-plans">List daily inspections</a-menu-item>
          <a-menu-item 
            v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
            key="/dashboard/daily-inspection-plans/add">Add daily inspection</a-menu-item>
        <a-menu-item key="/dashboard/daily-inspection-plans/today">Daily inspection today</a-menu-item>
          <a-menu-item key="/dashboard/daily-inspection-plans/history">History daily inspection</a-menu-item>
        </a-sub-menu>

        <a-menu-item key="/dashboard/plan/level1">Level 1 maintenance</a-menu-item>
        <a-menu-item key="/dashboard/plan/level2">Level 2 maintenance</a-menu-item>
        <a-menu-item key="/dashboard/plan/level3">Level 3 maintenance</a-menu-item>

      </a-sub-menu>

        <a-sub-menu 
          v-if="userStore.rawUser && userStore.rawUser.role !== 'user'" 
          key="sub6"
        >
        <template #title>
          <span><form-outlined /><span>Forms</span></span>
        </template>
        <a-menu-item key="/dashboard/forms">List forms</a-menu-item>
        <a-menu-item key="/dashboard/forms/add">Add form</a-menu-item>
      </a-sub-menu> -->


      <!-- <a-menu-item key="/dashboard/option1">
        <pie-chart-outlined /><span>Maintenance plan</span>
      </a-menu-item>

      <a-menu-item key="/dashboard/option2">
        <desktop-outlined /><span>Option 2</span>
      </a-menu-item>



      <a-sub-menu key="sub2">
        <template #title>
          <span><team-outlined /><span>Team</span></span>
        </template>
        <a-menu-item key="/dashboard/team1">Team 1</a-menu-item>
        <a-menu-item key="/dashboard/team2">Team 2</a-menu-item>
      </a-sub-menu>

      <a-menu-item key="/dashboard/file">
        <file-outlined /><span>File</span>
      </a-menu-item> -->
    </a-menu>
  </a-layout-sider>
</template>

<script lang="ts" setup>
import {
  PieChartOutlined,
  DesktopOutlined,
  UserOutlined,
  TeamOutlined,
  FileOutlined,
  ScheduleOutlined,
  BarsOutlined,
  CalendarOutlined,
  BuildOutlined,
  FormOutlined,
} from '@ant-design/icons-vue'
import { ref, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()

const role = computed(() =>  userStore.rawUser?.role || null)

console.log(role.value);



defineProps<{ mobile?: boolean }>()

const collapsed = ref(false)
const selectedKeys = ref<string[]>([])
const openKeys = ref<string[]>([])

const route = useRoute()
const router = useRouter()

// Map từ path sang openKeys cha
const openKeyMap: Record<string, string> = {
  '/dashboard/equipments': 'sub1',
  '/dashboard/equipments/add': 'sub1',
  '/dashboard/categories': 'sub2',
  '/dashboard/categories/add': 'sub2',
  '/dashboard/working-instructions/' : 'sub3',
  '/dashboard/working-instructions/add' : 'sub3',
  '/dashboard/forms': 'sub6',
  '/dashboard/forms/add': 'sub6',
}

watch(
  () => route.path,
  (path) => {
    selectedKeys.value = [path]
    if (openKeyMap[path]) {
      openKeys.value = [openKeyMap[path]]
    } else {
      openKeys.value = []
    }
  },
  { immediate: true }
)

function onMenuClick({ key }: { key: string }) {
  if (role.value === 'user' && openKeyMap[key] !== 'sub5') {
    return
  }

  if (key === route.path) {
    // Ép reload dữ liệu nếu cùng route
    router.replace({ path: '/redirect' }).then(() => {
      router.replace(key)
    })
  } else {
    router.push(key)
  }
}

</script>


<style scoped>

.mobile-menu { width: 100%; background: #001529; }
</style>
