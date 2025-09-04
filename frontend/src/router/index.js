// router/index.js - Enhanced multilingual version
import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'
import LoginPage from '@/pages/LoginPage.vue'
import ProfilePage from '@/pages/ProfilePage.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { isTokenValid } from '@/utils/auth'

import TestPageee from '../pages/TestPageee.vue'
import UserPage from '../pages/UserPage.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import EquipmentPage from '../pages/EquipmentPage.vue'
import CategoryPage from '../pages/CategoryPage.vue'
import FormViewer from '../pages/FormViewer.vue'
import AddEquipmentPage from '../pages/AddEquipmentPage.vue'
import AddWorkingInstructionPage from '../pages/AddWorkingInstructionPage.vue'
import WorkingInstructionPage from '../pages/WorkingInstructionPage.vue'
import UpdateEquipmentPage from '../pages/UpdateEquipmentPage.vue'
import TodayEquipment from '../pages/TodayEquipment.vue'
import DailyInspectionPage from '../pages/DailyInspectionPage.vue'
import PermissionPage from '../pages/PermissionPage.vue'
import AccountTypePage from '../pages/AccountTypePage.vue'
import MaintenanceMachinePage from '../pages/MaintenanceMachinePage.vue'

// Supported languages
export const SUPPORTED_LANGUAGES = {
  en: 'English',
  vi: 'Tiếng Việt', 
  cn: '中文(简体)',
  tw: '中文(繁體)',
  // ja: '日本語',
  // ko: '한국어'
}

export const DEFAULT_LANGUAGE = 'en'

// Helper function to get language from path
export const getLanguageFromPath = (path) => {
  const segments = path.split('/')
  const lang = segments[1]
  return SUPPORTED_LANGUAGES[lang] ? lang : DEFAULT_LANGUAGE
}

// Helper function to remove language prefix from path
export const removeLanguagePrefix = (path) => {
  const segments = path.split('/')
  const lang = segments[1]
  if (SUPPORTED_LANGUAGES[lang]) {
    return '/' + segments.slice(2).join('/')
  }
  return path
}

// Helper function to add language prefix to path
export const addLanguagePrefix = (path, lang) => {
  if (lang === DEFAULT_LANGUAGE) return path
  if (path === '/') return `/${lang}`
  return `/${lang}${path}`
}

// Create routes with language prefixes
const createLocalizedRoutes = () => {
  const baseRoutes = [
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
              path: 'equipments/categories',
              name: 'CategoryPage',
              component: CategoryPage,
            },
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
              path: 'user/type',
              name: 'AccountTypePage',
              component: AccountTypePage,
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
              path: 'tasks/maintenance',
              name: 'MaintenanceMachine',
              component: MaintenanceMachinePage,
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
    }
  ]

  const routes = []

  // Add default language routes (without prefix)
  routes.push(...baseRoutes)

  // Add localized routes for each supported language
  Object.keys(SUPPORTED_LANGUAGES).forEach(lang => {
    if (lang !== DEFAULT_LANGUAGE) {
      baseRoutes.forEach(route => {
        const localizedRoute = {
          ...route,
          path: `/${lang}${route.path === '/' ? '' : route.path}`,
          name: route.name ? `${route.name}_${lang}` : undefined,
          meta: { ...route.meta, language: lang }
        }

        // Handle nested children routes
        if (route.children) {
          localizedRoute.children = route.children.map(child => ({
            ...child,
            name: child.name ? `${child.name}_${lang}` : undefined,
            meta: { ...child.meta, language: lang }
          }))

          // Handle nested dashboard children
          localizedRoute.children = localizedRoute.children.map(child => {
            if (child.children) {
              child.children = child.children.map(grandchild => ({
                ...grandchild,
                name: grandchild.name ? `${grandchild.name}_${lang}` : undefined,
                meta: { ...grandchild.meta, language: lang }
              }))
            }
            return child
          })
        }

        routes.push(localizedRoute)
      })
    }
  })

  // Add catch-all route
  routes.push({
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  })

  return routes
}

const router = createRouter({
  history: createWebHashHistory(),
  routes: createLocalizedRoutes(),
  scrollBehavior(to, from, savedPosition) {
    // Nếu dùng nút back/forward thì giữ nguyên vị trí
    if (savedPosition) {
      return savedPosition
    } else {
      // Mặc định: luôn cuộn lên đầu trang
      return { top: 0 }
    }
  }
})

// Enhanced route guard with language support
router.beforeEach((to, from, next) => {
  const tokenValid = isTokenValid()
  const currentLang = getLanguageFromPath(to.path)
  const pathWithoutLang = removeLanguagePrefix(to.path)

  // Store current language in localStorage and global state
  if (currentLang !== localStorage.getItem('selectedLanguage')) {
    localStorage.setItem('selectedLanguage', currentLang)
    // Trigger language change event
    window.dispatchEvent(new CustomEvent('languageChanged', { detail: currentLang }))
  }

  if ((to.meta.requiresAuth || pathWithoutLang === '/') && !tokenValid) {
    // Redirect to login with language prefix
    const loginPath = addLanguagePrefix('/login', currentLang)
    next(loginPath)
  } else if ((pathWithoutLang === '/login' || pathWithoutLang === '/') && tokenValid) {
    // Redirect to dashboard with language prefix
    const dashboardPath = addLanguagePrefix('/dashboard/equipments', currentLang)
    next(dashboardPath)
  } else {
    next()
  }
})

export default router