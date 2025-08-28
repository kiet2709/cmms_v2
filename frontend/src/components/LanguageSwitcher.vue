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
import { computed, watch } from 'vue'
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
      
      try {
        // Change language
        await changeLanguage(key)
        
        // Update URL with new language prefix
        const currentPath = router.currentRoute.value.path
        const pathWithoutLang = removeLanguagePrefix(currentPath)
        const newPath = addLanguagePrefix(pathWithoutLang, key)
        
        // Navigate to new path
        await router.push(newPath)
      } catch (error) {
        console.error('Language change failed:', error)
      }
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

.language-switcher .ant-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  padding: 4px 12px;
  height: 32px;
  transition: all 0.3s;
}

.language-switcher .ant-btn:hover {
  border-color: #3a8cff;
  color: #3a8cff;
}

.flag-icon {
  display: inline-block;
  width: 16px;
  height: 12px;
  background-size: cover;
  background-position: center;
  border-radius: 2px;
  flex-shrink: 0;
}

.flag-us { 
  background-image: url('https://flagcdn.com/16x12/us.png'); 
}
.flag-vn { 
  background-image: url('https://flagcdn.com/16x12/vn.png'); 
}
.flag-cn { 
  background-image: url('https://flagcdn.com/16x12/cn.png'); 
}
.flag-tw { 
  background-image: url('https://flagcdn.com/16x12/tw.png'); 
}
.flag-jp { 
  background-image: url('https://flagcdn.com/16x12/jp.png'); 
}
.flag-kr { 
  background-image: url('https://flagcdn.com/16x12/kr.png'); 
}

:deep(.ant-dropdown-menu-item) {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  transition: all 0.3s;
}

:deep(.ant-dropdown-menu-item.active) {
  background-color: #e6f7ff;
  color: #3a8cff;
  font-weight: 500;
}

:deep(.ant-dropdown-menu-item:hover) {
  background-color: #f5f5f5;
}

:deep(.ant-dropdown-menu-item.active:hover) {
  background-color: #e6f7ff;
}

/* Responsive design */
@media (max-width: 768px) {
  .language-switcher .ant-btn {
    padding: 4px 8px;
  }
  
  .language-switcher .ant-btn span:not(.flag-icon):not(.anticon) {
    display: none;
  }
}
</style>