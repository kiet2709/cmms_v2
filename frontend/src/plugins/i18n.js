// plugins/i18n.js - Vue plugin for internationalization
import { currentLanguage, $t, $tSync, changeLanguage, initializeLanguage } from '@/utils/translation'

export default {
  install(app) {
    // Global properties
    app.config.globalProperties.$t = $t
    app.config.globalProperties.$tSync = $tSync
    app.config.globalProperties.$currentLang = currentLanguage
    app.config.globalProperties.$changeLanguage = changeLanguage

    // Initialize language on app start
    initializeLanguage()
  }
}

// LanguageSwitcher.vue - Language switching component
export const LanguageSwitcherTemplate = `
<template>
  <div class="language-switcher">
    <a-dropdown>
      <template #overlay>
        <a-menu @click="handleLanguageChange">
          <a-menu-item 
            v-for="(label, code) in supportedLanguages" 
            :key="code"
            :class="{ active: currentLanguage === code }"
          >
            <span class="flag-icon" :class="getFlagClass(code)"></span>
            {{ label }}
          </a-menu-item>
        </a-menu>
      </template>
      <a-button>
        <span class="flag-icon" :class="getFlagClass(currentLanguage)"></span>
        {{ supportedLanguages[currentLanguage] }}
        <DownOutlined />
      </a-button>
    </a-dropdown>
  </div>
</template>

<script>
import { computed } from 'vue'
import { DownOutlined } from '@ant-design/icons-vue'
import { useRouter } from 'vue-router'
import { currentLanguage, changeLanguage } from '@/utils/translation'
import { SUPPORTED_LANGUAGES, addLanguagePrefix, removeLanguagePrefix } from '@/router'

export default {
  name: 'LanguageSwitcher',
  components: {
    DownOutlined
  },
  setup() {
    const router = useRouter()
    
    const supportedLanguages = computed(() => SUPPORTED_LANGUAGES)
    
    const getFlagClass = (code) => {
      const flagMap = {
        en: 'flag-us',
        vi: 'flag-vn', 
        cn: 'flag-cn',
        tw: 'flag-tw',
        ja: 'flag-jp',
        ko: 'flag-kr'
      }
      return flagMap[code] || 'flag-us'
    }
    
    const handleLanguageChange = async ({ key }) => {
      if (key === currentLanguage.value) return
      
      // Change language
      await changeLanguage(key)
      
      // Update URL with new language prefix
      const currentPath = router.currentRoute.value.path
      const pathWithoutLang = removeLanguagePrefix(currentPath)
      const newPath = addLanguagePrefix(pathWithoutLang, key)
      
      router.push(newPath)
    }
    
    return {
      currentLanguage,
      supportedLanguages,
      getFlagClass,
      handleLanguageChange
    }
  }
}
</script>

<style scoped>
.language-switcher {
  display: inline-block;
}

.flag-icon {
  display: inline-block;
  width: 16px;
  height: 12px;
  margin-right: 8px;
  background-size: cover;
  border-radius: 2px;
}

.flag-us { background-image: url('https://flagcdn.com/16x12/us.png'); }
.flag-vn { background-image: url('https://flagcdn.com/16x12/vn.png'); }
.flag-cn { background-image: url('https://flagcdn.com/16x12/cn.png'); }
.flag-tw { background-image: url('https://flagcdn.com/16x12/tw.png'); }
.flag-jp { background-image: url('https://flagcdn.com/16x12/jp.png'); }
.flag-kr { background-image: url('https://flagcdn.com/16x12/kr.png'); }

.ant-menu-item.active {
  background-color: #e6f7ff;
  color: #1890ff;
}
</style>`

// TranslatedText.vue - Component for automatic text translation
export const TranslatedTextTemplate = `
<template>
  <span v-if="!loading">{{ translatedText }}</span>
  <span v-else class="loading-text">{{ originalText }}</span>
</template>

<script>
import { ref, watch, onMounted } from 'vue'
import { currentLanguage, $t } from '@/utils/translation'

export default {
  name: 'TranslatedText',
  props: {
    text: {
      type: String,
      required: true
    },
    params: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const loading = ref(false)
    const translatedText = ref(props.text)
    const originalText = ref(props.text)
    
    const translateText = async () => {
      if (currentLanguage.value === 'en') {
        translatedText.value = props.text
        return
      }
      
      loading.value = true
      try {
        translatedText.value = await $t(props.text, props.params)
      } catch (error) {
        console.warn('Translation failed:', error)
        translatedText.value = props.text
      } finally {
        loading.value = false
      }
    }
    
    // Watch for language changes
    watch(currentLanguage, translateText)
    
    // Watch for text changes
    watch(() => props.text, (newText) => {
      originalText.value = newText
      translateText()
    })
    
    // Watch for param changes
    watch(() => props.params, translateText, { deep: true })
    
    onMounted(translateText)
    
    return {
      loading,
      translatedText,
      originalText
    }
  }
}
</script>

<style scoped>
.loading-text {
  opacity: 0.6;
}
</style>`

// Directive for automatic translation
export const vTranslate = {
  created(el, binding) {
    const originalText = el.textContent || binding.value
    el._originalText = originalText
    
    const updateTranslation = async () => {
      if (currentLanguage.value === 'en') {
        el.textContent = originalText
        return
      }
      
      try {
        el.textContent = await $t(originalText)
      } catch (error) {
        el.textContent = originalText
      }
    }
    
    el._updateTranslation = updateTranslation
    updateTranslation()
    
    // Listen for language changes
    window.addEventListener('languageChanged', updateTranslation)
  },
  
  updated(el, binding) {
    if (binding.value !== binding.oldValue) {
      el._originalText = binding.value
      el._updateTranslation()
    }
  },
  
  unmounted(el) {
    window.removeEventListener('languageChanged', el._updateTranslation)
  }
}

// Composable for component-level translation management
export const useI18n = () => {
  const translateAsync = async (text, params = {}) => {
    return await $t(text, params)
  }
  
  const translateSync = (text, params = {}) => {
    return $tSync(text, params)
  }
  
  const formatMessage = async (template, params = {}) => {
    const translated = await $t(template, params)
    return translated
  }
  
  return {
    currentLanguage,
    t: translateAsync,
    tSync: translateSync,
    formatMessage,
    changeLanguage
  }
}