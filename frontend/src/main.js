


// main.js - Setup the i18n plugin
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import i18nPlugin, { vTranslate } from './plugins/i18n'

// Ant Design Vue components (if using)
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css';
import { createPinia } from 'pinia'

const app = createApp(App)

// Install plugins
app.use(router)
app.use(Antd)
app.use(createPinia())
app.use(i18nPlugin)

// Register global directive
app.directive('translate', vTranslate)

app.mount('#app')

// ==========================================
// Usage Examples in Components
// ==========================================

/* 
1. Using in template with global methods:
<template>
  <div>
    <h1 v-translate>Dashboard</h1>
    <p>{{ $tSync('Welcome to the system') }}</p>
    <button @click="loadData">{{ $tSync('Load Data') }}</button>
  </div>
</template>
*/

/*
2. Using with composable:
<template>
  <div>
    <h1>{{ title }}</h1>
    <p>{{ welcome }}</p>
    <button @click="handleClick">{{ buttonText }}</button>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useI18n } from '@/plugins/i18n'

export default {
  setup() {
    const { t, tSync, currentLanguage } = useI18n()
    
    const title = ref('')
    const welcome = ref('')
    const buttonText = ref('')
    
    onMounted(async () => {
      title.value = await t('Equipment Management')
      welcome.value = await t('Welcome to CMMS System')
      buttonText.value = await t('Add New Equipment')
    })
    
    const handleClick = async () => {
      const message = await t('Equipment added successfully!')
      console.log(message)
    }
    
    return {
      title,
      welcome,
      buttonText,
      handleClick
    }
  }
}
</script>
*/

/*
3. Using TranslatedText component:
<template>
  <div>
    <TranslatedText text="Dashboard" />
    <TranslatedText 
      text="Hello {name}, you have {count} notifications" 
      :params="{ name: userName, count: notificationCount }" 
    />
  </div>
</template>

<script>
import TranslatedText from '@/components/TranslatedText.vue'

export default {
  components: {
    TranslatedText
  },
  data() {
    return {
      userName: 'John',
      notificationCount: 5
    }
  }
}
</script>
*/

/*
4. Language Switcher usage:
<template>
  <div class="header">
    <div class="header-content">
      <h1 v-translate>CMMS System</h1>
      <LanguageSwitcher />
    </div>
  </div>
</template>

<script>
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

export default {
  components: {
    LanguageSwitcher
  }
}
</script>
*/

/*
5. Using in your existing PermissionPage.vue:
<template>
  <div class="auth-management">
    <div class="header">
      <div class="header-content">
        <i class="fas fa-shield-alt header-icon"></i>
        <div>
          <h1 v-translate>Authorization Management</h1>
          <p v-translate>Manage user permissions and access control</p>
        </div>
        <LanguageSwitcher />
      </div>
    </div>
    <!-- Rest of your component -->
  </div>
</template>

<script>
import { useI18n } from '@/plugins/i18n'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

export default {
  components: {
    LanguageSwitcher
  },
  setup() {
    const { t, currentLanguage } = useI18n()
    
    // Your existing setup code...
    
    return {
      // Your existing returns...
      t,
      currentLanguage
    }
  }
}
</script>
*/

// ==========================================
// Router Navigation with Language Support
// ==========================================

/*
// In your components, use these helper methods for navigation:
import { useRouter } from 'vue-router'
import { currentLanguage } from '@/utils/translation'
import { addLanguagePrefix } from '@/router'

export default {
  setup() {
    const router = useRouter()
    
    const navigateWithLanguage = (path) => {
      const localizedPath = addLanguagePrefix(path, currentLanguage.value)
      router.push(localizedPath)
    }
    
    // Usage examples:
    const goToEquipments = () => navigateWithLanguage('/dashboard/equipments')
    const goToUsers = () => navigateWithLanguage('/dashboard/user')
    const goToPermissions = () => navigateWithLanguage('/dashboard/user/permission')
    
    return {
      navigateWithLanguage,
      goToEquipments,
      goToUsers,
      goToPermissions
    }
  }
}
*/

// ==========================================
// Automatic Page Title Translation
// ==========================================

/*
// Add this to your router/index.js afterEach hook:
router.afterEach(async (to) => {
  const { $t } = await import('@/utils/translation')
  
  // Extract page name from route
  const routeName = to.name?.replace(/_[a-z]{2}$/, '') // Remove language suffix
  
      const titleMap = {
    'EquipmentPage': 'Equipment Management',
    'UserPage': 'User Management', 
    'PermissionPage': 'Permission Management',
    'CategoryPage': 'Category Management',
    'WorkingInstructionPage': 'Working Instructions',
    'TodayEquipment': 'Daily Tasks',
    'DailyInspection': 'Daily Inspection',
    'AddEquipment': 'Add Equipment',
    'UpdateEquipment': 'Update Equipment',
    'AddWorkingInstruction': 'Add Working Instruction',
    'Profile': 'User Profile',
    'Login': 'Login'
  }
  
  if (titleMap[routeName]) {
    const translatedTitle = await $t(titleMap[routeName])
    document.title = `${translatedTitle} - CMMS`
  }
})
*/

// ==========================================
// Advanced Usage Patterns
// ==========================================

/*
// 1. Batch translation for dropdown options:
export const useEquipmentTypes = () => {
  const { t } = useI18n()
  const equipmentTypes = ref([])
  
  const loadEquipmentTypes = async () => {
    const types = [
      'Mechanical Equipment',
      'Electrical Equipment', 
      'HVAC System',
      'Safety Equipment',
      'Measurement Tools'
    ]
    
    equipmentTypes.value = await Promise.all(
      types.map(async (type) => ({
        value: type,
        label: await t(type)
      }))
    )
  }
  
  return { equipmentTypes, loadEquipmentTypes }
}
*/

/*
// 2. Form validation messages translation:
export const useValidationMessages = () => {
  const { t } = useI18n()
  
  const getValidationRules = async () => {
    return {
      name: [
        { required: true, message: await t('Please input the name!') },
        { min: 3, message: await t('Name must be at least 3 characters') }
      ],
      email: [
        { required: true, message: await t('Please input the email!') },
        { type: 'email', message: await t('Please enter a valid email!') }
      ]
    }
  }
  
  return { getValidationRules }
}
*/

/*
// 3. Dynamic content translation (for API data):
export const useContentTranslation = () => {
  const { t } = useI18n()
  
  const translateApiResponse = async (data, fieldsToTranslate = []) => {
    if (Array.isArray(data)) {
      return await Promise.all(
        data.map(item => translateApiResponse(item, fieldsToTranslate))
      )
    }
    
    if (typeof data === 'object' && data !== null) {
      const translated = { ...data }
      
      for (const field of fieldsToTranslate) {
        if (translated[field]) {
          translated[field] = await t(translated[field])
        }
      }
      
      return translated
    }
    
    return data
  }
  
  return { translateApiResponse }
}
*/

// ==========================================
// Ant Design Integration
// ==========================================

/*
// Ant Design locale setup for each language:
import { ConfigProvider } from 'ant-design-vue'
import enUS from 'ant-design-vue/es/locale/en_US'
import viVN from 'ant-design-vue/es/locale/vi_VN' 
import zhCN from 'ant-design-vue/es/locale/zh_CN'
import zhTW from 'ant-design-vue/es/locale/zh_TW'
import jaJP from 'ant-design-vue/es/locale/ja_JP'
import koKR from 'ant-design-vue/es/locale/ko_KR'

export const getAntdLocale = (languageCode) => {
  const localeMap = {
    en: enUS,
    vi: viVN,
    cn: zhCN, 
    tw: zhTW,
    ja: jaJP,
    ko: koKR
  }
  return localeMap[languageCode] || enUS
}

// Usage in App.vue:
<template>
  <ConfigProvider :locale="currentAntdLocale">
    <router-view />
  </ConfigProvider>
</template>

<script>
import { computed } from 'vue'
import { ConfigProvider } from 'ant-design-vue'
import { currentLanguage } from '@/utils/translation'
import { getAntdLocale } from '@/utils/antd-locales'

export default {
  components: {
    ConfigProvider
  },
  setup() {
    const currentAntdLocale = computed(() => getAntdLocale(currentLanguage.value))
    
    return {
      currentAntdLocale
    }
  }
}
</script>
*/

// ==========================================
// Performance Optimizations
// ==========================================

/*
// 1. Lazy loading translations:
export const useLazyTranslation = () => {
  const loadedModules = new Set()
  
  const loadTranslationModule = async (moduleName) => {
    if (loadedModules.has(moduleName)) return
    
    const moduleTexts = {
      equipment: [
        'Equipment', 'Add Equipment', 'Edit Equipment', 'Delete Equipment',
        'Serial Number', 'Model', 'Manufacturer', 'Status', 'Location'
      ],
      user: [
        'User', 'Add User', 'Edit User', 'Delete User', 
        'Username', 'Email', 'Role', 'Active', 'Last Login'
      ],
      permission: [
        'Permission', 'Access Control', 'Role Management',
        'View', 'Add', 'Edit', 'Delete', 'Grant', 'Revoke'
      ]
    }
    
    if (moduleTexts[moduleName]) {
      await preloadTranslations(moduleTexts[moduleName], currentLanguage.value)
      loadedModules.add(moduleName)
    }
  }
  
  return { loadTranslationModule }
}
*/

/*
// 2. Translation caching with localStorage:
export class TranslationCache {
  constructor() {
    this.cacheKey = 'translation_cache'
    this.loadCache()
  }
  
  loadCache() {
    try {
      const cached = localStorage.getItem(this.cacheKey)
      if (cached) {
        const data = JSON.parse(cached)
        Object.keys(data).forEach(lang => {
          if (!translations[lang]) translations[lang] = {}
          Object.assign(translations[lang], data[lang])
        })
      }
    } catch (error) {
      console.warn('Failed to load translation cache:', error)
    }
  }
  
  saveCache() {
    try {
      localStorage.setItem(this.cacheKey, JSON.stringify(translations))
    } catch (error) {
      console.warn('Failed to save translation cache:', error)
    }
  }
  
  clearCache() {
    localStorage.removeItem(this.cacheKey)
    Object.keys(translations).forEach(lang => {
      delete translations[lang]
    })
  }
}

// Initialize cache
const translationCache = new TranslationCache()

// Save cache periodically
setInterval(() => {
  translationCache.saveCache()
}, 60000) // Save every minute
*/

// ==========================================
// URL Structure Examples
// ==========================================

/*
Your URLs will now look like this:

Default (English):
- /login
- /dashboard/equipments  
- /dashboard/user/permission

Vietnamese:
- /vi/login
- /vi/dashboard/equipments
- /vi/dashboard/user/permission  

Chinese (Simplified):
- /cn/login
- /cn/dashboard/equipments
- /cn/dashboard/user/permission

Chinese (Traditional):
- /tw/login
- /tw/dashboard/equipments
- /tw/dashboard/user/permission

Japanese:
- /ja/login
- /ja/dashboard/equipments
- /ja/dashboard/user/permission

Korean:
- /ko/login
- /ko/dashboard/equipments
- /ko/dashboard/user/permission
*/

// ==========================================
// Complete Component Example
// ==========================================

export const CompleteExampleComponent = `
<template>
  <div class="equipment-page">
    <!-- Header with language switcher -->
    <div class="page-header">
      <h1 v-translate>Equipment Management</h1>
      <div class="header-actions">
        <a-button type="primary" @click="showAddModal">
          <PlusOutlined />
          {{ addButtonText }}
        </a-button>
        <LanguageSwitcher />
      </div>
    </div>
    
    <!-- Search form -->
    <div class="search-form">
      <a-form layout="inline">
        <a-form-item :label="searchLabelText">
          <a-input 
            v-model:value="searchText" 
            :placeholder="searchPlaceholderText"
          />
        </a-form-item>
        <a-form-item>
          <a-button @click="handleSearch">{{ searchButtonText }}</a-button>
        </a-form-item>
      </a-form>
    </div>
    
    <!-- Equipment table -->
    <a-table 
      :columns="tableColumns" 
      :data-source="equipmentList"
      :loading="loading"
    >
      <template #action="{ record }">
        <a-button @click="editEquipment(record)">{{ editText }}</a-button>
        <a-popconfirm 
          :title="deleteConfirmText" 
          @confirm="deleteEquipment(record)"
        >
          <a-button danger>{{ deleteText }}</a-button>
        </a-popconfirm>
      </template>
    </a-table>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { PlusOutlined } from '@ant-design/icons-vue'
import { useI18n, useLazyTranslation } from '@/plugins/i18n'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

export default {
  name: 'EquipmentPage',
  components: {
    PlusOutlined,
    LanguageSwitcher
  },
  setup() {
    const { t, tSync, currentLanguage } = useI18n()
    const { loadTranslationModule } = useLazyTranslation()
    
    // Reactive data
    const loading = ref(false)
    const searchText = ref('')
    const equipmentList = ref([])
    
    // Translated texts
    const addButtonText = ref('')
    const searchLabelText = ref('')
    const searchPlaceholderText = ref('')
    const searchButtonText = ref('')
    const editText = ref('')
    const deleteText = ref('')
    const deleteConfirmText = ref('')
    
    // Table columns with translation
    const tableColumns = computed(() => [
      {
        title: tSync('Equipment Name'),
        dataIndex: 'name',
        key: 'name'
      },
      {
        title: tSync('Serial Number'),
        dataIndex: 'serial',
        key: 'serial'
      },
      {
        title: tSync('Status'),
        dataIndex: 'status',
        key: 'status',
        customRender: ({ text }) => tSync(text)
      },
      {
        title: tSync('Actions'),
        key: 'action',
        slots: { customRender: 'action' }
      }
    ])
    
    // Initialize translations
    const initializeTranslations = async () => {
      await loadTranslationModule('equipment')
      
      addButtonText.value = await t('Add Equipment')
      searchLabelText.value = await t('Search')
      searchPlaceholderText.value = await t('Enter equipment name or serial number')
      searchButtonText.value = await t('Search')
      editText.value = await t('Edit')
      deleteText.value = await t('Delete')
      deleteConfirmText.value = await t('Are you sure you want to delete this equipment?')
    }
    
    // Methods
    const showAddModal = () => {
      // Navigate to add equipment page
      navigateWithLanguage('/dashboard/equipments/add')
    }
    
    const handleSearch = () => {
      // Implement search logic
      console.log('Searching for:', searchText.value)
    }
    
    const editEquipment = (record) => {
      navigateWithLanguage(\`/dashboard/equipments/update/\${record.uuid}\`)
    }
    
    const deleteEquipment = async (record) => {
      try {
        // API call to delete equipment
        const successMessage = await t('Equipment deleted successfully')
        // Show success message
      } catch (error) {
        const errorMessage = await t('Failed to delete equipment')
        // Show error message
      }
    }
    
    onMounted(() => {
      initializeTranslations()
      // Load equipment data
    })
    
    // Watch for language changes and re-initialize translations
    watch(currentLanguage, initializeTranslations)
    
    return {
      loading,
      searchText,
      equipmentList,
      tableColumns,
      addButtonText,
      searchLabelText,
      searchPlaceholderText,
      searchButtonText,
      editText,
      deleteText,
      deleteConfirmText,
      showAddModal,
      handleSearch,
      editEquipment,
      deleteEquipment
    }
  }
}
</script>
`