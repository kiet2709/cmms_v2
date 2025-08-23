// src/stores/dailyTaskStore.js
import { defineStore } from 'pinia'

export const useDailyTaskStore = defineStore('dailyTask', {
  state: () => ({
    currentUuid: null
  }),
  actions: {
    setUuid(uuid) {
      this.currentUuid = uuid
    },
    clearUuid() {
      this.currentUuid = null
    }
  }
})
