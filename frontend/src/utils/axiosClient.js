import axios from 'axios';

const axiosClient = axios.create({
  baseURL: 'http://localhost/cmms_v2/cip3/index.php', // thay bằng API thật
  headers: {
    'Content-Type': 'application/json',
  },
});

// Biến để lưu promise refresh token (tránh gọi nhiều lần 1 lúc)
let refreshTokenRequest = null;

// Interceptor request: tự gắn accessToken
// axiosClient.interceptors.request.use((config) => {
//   const token = localStorage.getItem('accessToken');
//   if (token) {
//     config.headers.Authorization = `Bearer ${token}`;
//   }
//   return config;
// });


axiosClient.interceptors.request.use((config) => {
  config.headers.Accept = 'application/json';
  const token = localStorage.getItem('accessToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});


// Interceptor response: bắt lỗi 401
// axiosClient.interceptors.response.use(
//   (response) => response,
//   async (error) => {
//     const originalRequest = error.config;

//     // Nếu là lỗi 401 và chưa thử refresh
//     if (error.response?.status === 401 && !originalRequest._retry) {
//       originalRequest._retry = true;

//       // Nếu chưa có request refresh nào đang chạy
//       if (!refreshTokenRequest) {
//         // const refreshToken = localStorage.getItem('refreshToken');
//         // if (!refreshToken) {
//         //   // Không có refreshToken => quay về login
//         //   import('@/router').then(({ default: router }) => {
//         //     router.push('/login');
//         //   });
//         //   return Promise.reject(error);
//         // }

//         // Gọi API refresh token
//         refreshTokenRequest = axiosClient.post(
//           'auth/refresh',
//           {}, // body trống nếu là Tymon JWTAuth mặc định
//           {
//             headers: {
//               Authorization: `Bearer ${localStorage.getItem('accessToken')}`
//             }
//           }
//         )
//           .then((res) => {
//             const newAccessToken = res.data.accessToken; // hoặc accessToken tùy API trả về
//             console.log('res: ' + res);
            
//             localStorage.setItem('accessToken', newAccessToken);
//             refreshTokenRequest = null;
//             return newAccessToken;
//           })
//           .catch((err) => {
//             refreshTokenRequest = null;
//             localStorage.removeItem('accessToken');
//             import('@/router').then(({ default: router }) => {
//               router.push('/login');
//             });
//             return Promise.reject(err);
//           });
//       }

//       // Đợi token mới
//       const newToken = await refreshTokenRequest;

//       // Gắn token mới vào request cũ và gọi lại
//       originalRequest.headers.Authorization = `Bearer ${newToken}`;
//       return axiosClient(originalRequest);
//     }

//     return Promise.reject(error);
//   }
// );

axiosClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    const status = error.response?.status;
    const message = error.response?.data?.message || '';

    // Bắt các trường hợp token hết hạn
    const isTokenExpired =
      (status === 401) ||
      (status === 400 && message.toLowerCase().includes('expired')) ||
      (status === 500 && message.toLowerCase().includes('token'));

    if (isTokenExpired && !originalRequest._retry) {
      originalRequest._retry = true;

      if (!refreshTokenRequest) {
        refreshTokenRequest = axiosClient.post(
          'auth/refresh',
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('accessToken')}`
            }
          }
        )
          .then((res) => {
            const newAccessToken = res.data.access_token || res.data.accessToken;
            console.log('res:  ' + res);
            
            localStorage.setItem('accessToken', newAccessToken);
            refreshTokenRequest = null;
            return newAccessToken;
          })
          .catch((err) => {
            refreshTokenRequest = null;
            localStorage.removeItem('accessToken');
            import('@/router').then(({ default: router }) => {
              router.push('/login');
            });
            return Promise.reject(err);
          });
      }

      const newToken = await refreshTokenRequest;
      originalRequest.headers.Authorization = `Bearer ${newToken}`;
      return axiosClient(originalRequest);
    }

    return Promise.reject(error);
  }
);

export default axiosClient;