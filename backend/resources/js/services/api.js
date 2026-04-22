import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

// Use this when your backend uses Sanctum cookie-based SPA auth.
export async function initSanctumCsrf() {
  const csrfEndpoint = import.meta.env.VITE_SANCTUM_CSRF_ENDPOINT || '/sanctum/csrf-cookie';
  await axios.get(csrfEndpoint, { withCredentials: true });
}

