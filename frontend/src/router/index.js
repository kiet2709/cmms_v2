import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'
import LoginPage from '@/pages/LoginPage.vue'
import MainPage from '@/pages/MainPage.vue'
import ProfilePage from '@/pages/ProfilePage.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { isTokenValid } from '@/utils/auth' // hàm kiểm tra token
import TestPage from '../pages/TestPage.vue'
import TestPageee from '../pages/TestPageee.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import EquipmentPage from '../pages/EquipmentPage.vue'
import CategoryPage from '../pages/CategoryPage.vue'
import DailyPlanPage from '../pages/DailyPlanPage.vue'
import DailyPlanDetailPage from '../pages/DailyPlanDetailPage.vue'
import FormByPlanDetailPage from '../pages/FormByPlanDetailPage.vue'
import ListForms from '../pages/ListForms.vue'
import FormItemsPage from '../pages/FormItemsPage.vue'
import DailyTaskTodayPage from '../pages/DailyTaskTodayPage.vue'
import HistoryDailyTaskPage from '../pages/HistoryDailyTaskPage.vue'
import HistoryDailyTaskItemPage from '../pages/HistoryDailyTaskItemPage.vue'
import DailyTaskTodayItemPage from '../pages/DailyTaskTodayItemPage.vue'
import DailyFormByTaskDetailPage from '../pages/DailyFormByTaskDetailPage.vue'
import FormByHistoryDailyTaskItemPage from '../pages/FormByHistoryDailyTaskItemPage.vue'
import TestMachinePage from '../pages/AddEquipmentPage.vue'
import FormFlexiblePage from '../pages/AddWorkingInstructionPage.vue'
import FormViewer from '../pages/FormViewer.vue'
import AddEquipmentPage from '../pages/AddEquipmentPage.vue'
import AddCategoryPage from '../pages/AddCategoryPage.vue'
import AddWorkingInstructionPage from '../pages/AddWorkingInstructionPage.vue'
import WorkingInstructionPage from '../pages/WorkingInstructionPage.vue'
import UpdateEquipmentPage from '../pages/UpdateEquipmentPage.vue'

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
