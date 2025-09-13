import { defineStore } from 'pinia'
import axiosClient from '@/utils/axiosClient'

export const useUserStore = defineStore('user', {
  state: () => ({
    rawUser: null // giữ nguyên dữ liệu từ API
  }),

  getters: {
    // Getter để các component dùng chung format { name, role }
    user(state) {
      if (!state.rawUser) return { name: '', role: '' }
      return {
        name: state.rawUser.user?.username || '',
        role: state.rawUser.role || ''
      }
    }
  },

  actions: {
    async fetchUser() {
      try {
        const token = localStorage.getItem('accessToken')
        const res = await axiosClient.get('', {
          params: {
            c: 'UserController',
            m: 'getProfile'
          },
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        // console.log(JSON.stringify(res, null, 2));
        
        this.rawUser = res.data
        // console.log(res.data);
        
      } catch (e) {
        console.error('Failed to fetch user', e)
      }
    },

    setUser(userData) {
      this.rawUser = userData
    }
  }
})
