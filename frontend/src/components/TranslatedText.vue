<template>
  <span v-if="!loading" :class="{ 'translated-text': isTranslated }">
    {{ translatedText }}
  </span>
  <span v-else class="loading-text">
    <span class="loading-dots">{{ originalText }}</span>
  </span>
</template>

<script>
import { ref, watch, onMounted, computed } from 'vue'
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
    },
    tag: {
      type: String,
      default: 'span'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const loading = ref(false)
    const translatedText = ref(props.text)
    const originalText = ref(props.text)
    
    const isTranslated = computed(() => {
      return currentLanguage.value !== 'en' && translatedText.value !== originalText.value
    })
    
    const translateText = async () => {
      if (currentLanguage.value === 'en') {
        translatedText.value = interpolateParams(props.text, props.params)
        loading.value = false
        return
      }
      
      loading.value = true
      
      try {
        const result = await $t(props.text, props.params)
        translatedText.value = result
      } catch (error) {
        console.warn('Translation failed for:', props.text, error)
        translatedText.value = interpolateParams(props.text, props.params)
      } finally {
        loading.value = false
      }
    }
    
    const interpolateParams = (text, params) => {
      let result = text
      Object.keys(params).forEach(key => {
        result = result.replace(new RegExp(`{${key}}`, 'g'), params[key])
      })
      return result
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
      originalText,
      isTranslated
    }
  }
}
</script>

<style scoped>
.loading-text {
  opacity: 0.6;
  position: relative;
}

.loading-dots {
  position: relative;
}

.loading-dots::after {
  content: '';
  position: absolute;
  top: 50%;
  right: -20px;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  border: 2px solid #3a8cff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 1s linear infinite;
}

.translated-text {
  transition: color 0.3s ease;
}

.translated-text:hover {
  color: #3a8cff;
}

@keyframes spin {
  to {
    transform: translateY(-50%) rotate(360deg);
  }
}

/* Animation for text changes */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-2px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.translated-text {
  animation: fadeIn 0.3s ease;
}
</style>