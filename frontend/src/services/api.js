import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pos_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
)

export const apiGet = (url, params) => api.get(url, { params })
export const apiPost = (url, data) => api.post(url, data)
export const apiPut = (url, data) => api.put(url, data)
export const apiPatch = (url, data) => api.patch(url, data)
export const apiDelete = (url) => api.delete(url)

export default api
