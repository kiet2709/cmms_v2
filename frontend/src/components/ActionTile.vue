<template>
  <a-card
    :hoverable="true"
    class="tile"
    :style="{ backgroundColor: bgColor }"
    @click="$emit('click')"
  >
    <component :is="icon" class="tile-icon" />
    <div class="tile-label">{{ label }}</div>
  </a-card>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  icon: { type: [Object, Function], required: true }, // AntD icon component
  bgColor: { type: String, default: '#f6ffed' }       // màu nền mềm
})
defineEmits(['click'])
</script>

<style scoped>
    .tile {
    width: 100%; /* Đảm bảo lấp đầy chiều rộng của a-col */
    height: 100%; /* Đảm bảo lấp đầy chiều cao của a-col */
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    }

    .tile-icon {
    font-size: 28px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    }

    .tile-label {
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    }
    


</style>
