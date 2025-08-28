import axiosClient from '@/utils/axiosClient';  // file axios config của bạn

const roleApi = {
  // Lấy toàn bộ roles từ DB
  async getRoles() {
    try {
      const res = await axiosClient.get('', {
        params: {
          c: 'RoleController',
          m: 'getRoles'
        }
      });
      return Array.isArray(res.data) ? res.data : []; // giả sử API trả về mảng [{uuid, name, description}, ...]
    } catch (error) {
      console.error("Lỗi khi lấy roles:", error);
      throw error;
    }
  }
}

export default roleApi;
