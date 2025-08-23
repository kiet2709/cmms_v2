import { jwtDecode } from 'jwt-decode'

export function getToken() {
  return localStorage.getItem('accessToken')
}

export function isTokenValid() {
  const token = getToken()
  if (!token) return false
  try {
    const decoded = jwtDecode(token)
    const now = Date.now() / 1000 // tính bằng giây
    return decoded.exp && decoded.exp > now
  } catch (e) {
    return false
  }
}
