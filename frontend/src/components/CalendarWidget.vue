<template>
  <div class="calendar-widget">
    <a-calendar v-model:value="value" :fullscreen="false" @panelChange="onPanelChange" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
const value = ref();
const onPanelChange = (value, mode) => {
  console.log(value, mode);
};
</script>

<style scoped>
.calendar-widget {
  width: 100%; /* chiếm hết cha */
  border-radius: 4px;
}
</style>