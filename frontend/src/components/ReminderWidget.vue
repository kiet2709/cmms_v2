<template>
  <a-card class="reminder" :bordered="false">
    <template #title>
      <a-space>
        <a-check-circle-outlined style="color: #52c41a" />
        <span>Reminder</span>
      </a-space>
    </template>

    <ReminderGroup title="Job" :items="jobItems" />
    <ReminderGroup title="Assets - Equipment" :items="assetItems" />
    <ReminderGroup title="Maintenance - Repair" :items="maintenanceItems" />
    <ReminderGroup title="Purchase - Warehouse" :items="purchaseItems" />
  </a-card>
</template>

<script setup lang="ts">
import { CheckCircleOutlined as ACheckCircleOutlined } from '@ant-design/icons-vue'
import ReminderGroup from './ReminderGroup.vue'

// mock cứng dữ liệu
const jobItems = [
  { label: 'Expired', count: 49 }
]

const assetItems = [
  { label: 'Transfer of personal assets', count: 5 },
  { label: 'Unit asset turnover', count: 5 },
  { label: 'Proposal for liquidation', count: 1 },
  { label: 'Liquidation slip', count: 3 },
  { label: 'Assets - Equipment Inventory Order', count: 4 }
]

const maintenanceItems = [
  { label: 'Periodic maintenance plan (area)', count: 1 },
  { label: 'Periodic maintenance voucher (Assets - Equipment)', count: 566 },
  { label: 'Incident report (Assets - Equipment)', count: 2 },
  { label: 'Incident report (area)', count: 1 }
]

const purchaseItems = [
  { label: 'Recommended purchase', count: 1 },
  { label: 'Purchase order', count: 2 }
]
</script>

<style scoped>
.reminder {
  background: #fff;
}
</style>
