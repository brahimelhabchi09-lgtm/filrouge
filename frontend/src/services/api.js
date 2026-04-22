import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

export default api;

// Use this when your backend uses Sanctum cookie-based SPA auth.
export async function initSanctumCsrf() {
  const csrfEndpoint = import.meta.env.VITE_SANCTUM_CSRF_ENDPOINT || '/sanctum/csrf-cookie';
  await axios.get(csrfEndpoint, { withCredentials: true });
}

