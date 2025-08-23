<template>
  <div class="group">
    <div class="group-title" role="button" :aria-expanded="open" @click="toggle">
      <a-space>
        <component :is="open ? DownOutlined : RightOutlined" class="caret" />
        <span>{{ title }}</span>
      </a-space>

        <a-badge
            v-if="totalCount > 0"
            :count="totalCount"
            :overflow-count="9999" 
            class="custom-badge"
        />
    </div>

    <transition name="fade">
      <ul v-show="open" class="mini">
        <li v-for="(item, idx) in safeItems" :key="idx">
          <span>{{ item.label }}</span>
            <a-badge
                v-if="item.count > 0"
                :count="item.count"
                :overflow-count="9999"  
                class="custom-badge"
            />
        </li>
      </ul>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { DownOutlined, RightOutlined } from '@ant-design/icons-vue'

const props = defineProps<{
  title: string
  items: { label: string; count: number }[]
  defaultOpen?: boolean
}>()

const open = ref(props.defaultOpen ?? true)
const safeItems = computed(() => props.items ?? [])

function toggle() { open.value = !open.value }

const totalCount = computed(() =>
  safeItems.value.reduce((sum, it) => sum + (Number(it.count) || 0), 0)
)
</script>

<style scoped>
.group { margin-bottom: 16px; }
.group-title {
  font-weight: 600;
  margin-bottom: 6px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
}
.caret { font-size: 12px; }
.mini { list-style: none; margin: 0; padding-left: 0; }
.mini li {
  display: flex; justify-content: space-between; align-items: center;
  font-size: 13px; padding: 6px 0;
}
.fade-enter-active, .fade-leave-active { transition: opacity .18s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.custom-badge :deep(.ant-badge-count) {
  border-radius: 50% !important;
  min-width: 22px;
  height: 22px;
  line-height: 22px;
  padding: 0;
  font-size: 12px;
}

.mini {
  list-style: none;
  margin: 0;
  padding-left: 26px; /* thụt vào */
}

</style>
