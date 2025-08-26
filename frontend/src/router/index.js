import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'
import LoginPage from '@/pages/LoginPage.vue'
import MainPage from '@/pages/MainPage.vue'
import ProfilePage from '@/pages/ProfilePage.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { isTokenValid } from '@/utils/auth' // hàm kiểm tra token

import TestPageee from '../pages/TestPageee.vue'
import UserPage from '../pages/UserPage.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import EquipmentPage from '../pages/EquipmentPage.vue'
import CategoryPage from '../pages/CategoryPage.vue'
import FormViewer from '../pages/FormViewer.vue'
import AddEquipmentPage from '../pages/AddEquipmentPage.vue'
import AddCategoryPage from '../pages/AddCategoryPage.vue'
import AddWorkingInstructionPage from '../pages/AddWorkingInstructionPage.vue'
import WorkingInstructionPage from '../pages/WorkingInstructionPage.vue'
import UpdateEquipmentPage from '../pages/UpdateEquipmentPage.vue'
import TodayEquipment from '../pages/TodayEquipment.vue'
import DailyInspectionPage from '../pages/DailyInspectionPage.vue'

const routes = [
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/profile',
        name: 'Profile',
        component: ProfilePage,
      },
      {
        path: '/dashboard',
        component: DashboardLayout,
        children: [
          {
            path: 'equipments',
            name: 'EquipmentPage',
            component: EquipmentPage,
          },
          {
            path: 'equipments/add',
            name: 'AddEquipment',
            component: AddEquipmentPage,
          },
          {
            path: 'equipments/update/:uuid',
            name: 'UpdateEquipment',
            component: UpdateEquipmentPage,
          },
          {
            path: 'categories',
            name: 'CategoryPage',
            component: CategoryPage,
          },
          // {
          //   path: 'categories/add',
          //   name: 'AddCategory',
          //   component: AddCategoryPage,
          // },
          {
            path: 'test',
            name: 'Testpagee',
            component: TestPageee,
          },
          {
            path: 'user',
            name: 'UserPage',
            component: UserPage,
          },
          {
            path: 'user/permission',
            name: 'PermissionPage',
            component: PermissionPage,
          },
          {
            path: 'working-instructions',
            name: 'WorkingInstructionPage',
            component: WorkingInstructionPage,
          },
          {
            path: 'working-instructions/add',
            name: 'AddWorkingInstruction',
            component: AddWorkingInstructionPage,
          },
          {
            path: 'tasks/daily',
            name: 'TodayEquipment',
            component: TodayEquipment,
          },
          {
            path: 'tasks/daily/:uuid',
            name: 'DailyInspection',
            component: DailyInspectionPage,
          },
          {
            path: 'form2',
            name: 'ViewForm',
            component: FormViewer,
          },

        ]
      },
    ]
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

// Route guard
router.beforeEach((to, from, next) => {
  const tokenValid = isTokenValid()

  if ((to.meta.requiresAuth || to.path === '/') && !tokenValid) {
    // Nếu cần auth mà token không hợp lệ → quay lại login
    next('/login')
  } else if ((to.path === '/login' || to.path === '/') && tokenValid) {
    // Nếu đã login rồi mà vẫn vào login → chuyển sang main
    next('/dashboard/equipments')
  } else {
    next()
  }
})


export default router
