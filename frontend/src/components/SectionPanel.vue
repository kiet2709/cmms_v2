<template>
  <a-card class="panel" :title="title">
    <a-row :gutter="[16,16]">
      <a-col v-for="(it, idx) in items"
       :key="idx"
        :xs="24"
        :sm="12"
        :md="8"
        :lg="6"
       >
        <ActionTile
          :label="it.label"
          :icon="it.icon"
          :bg-color="bgColor"
          @click="() => $emit('select', it)"
        />
      </a-col>
    </a-row>
  </a-card>
</template>

<script setup>
import ActionTile from './ActionTile.vue'
defineProps({
  title: { type: String, required: true },
  items: { type: Array, required: true }, // [{label, icon}]
  bgColor: { type: String, default: '#e6fffb' }
})
defineEmits(['select'])
</script>

<style scoped>
.panel {
  margin-bottom: 16px;
  border-radius: 12px;
}
:deep(.ant-card-head-title){
  font-weight: 600;
  color: #389e0d; /* bạn có thể đổi theo section */
}
</style>
