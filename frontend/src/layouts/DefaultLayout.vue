<template>
  <div>
    <AppHeader :user="userStore.user" />
    <div class="main-content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import AppHeader from '@/components/AppHeader.vue'
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()

onMounted(() => {
  userStore.fetchUser()
})


</script>

<style scoped>
.main-content {
  padding-top: 81px; /* chừa khoảng cho header cố định */
  background-color: #B8C2CC;
}
</style>
