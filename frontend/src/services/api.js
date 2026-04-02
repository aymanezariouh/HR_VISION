import axios from 'axios';

export const TOKEN_STORAGE_KEY = 'hrvision_token';
export const USER_STORAGE_KEY = 'hrvision_user';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost/HR-vision/public/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

export function getStoredToken() {
  return window.localStorage.getItem(TOKEN_STORAGE_KEY);
}

export function getStoredUser() {
  const user = window.localStorage.getItem(USER_STORAGE_KEY);

  return user ? JSON.parse(user) : null;
}

export function saveAuth(token, user) {
  window.localStorage.setItem(TOKEN_STORAGE_KEY, token);
  window.localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));
}

export function clearAuth() {
  window.localStorage.removeItem(TOKEN_STORAGE_KEY);
  window.localStorage.removeItem(USER_STORAGE_KEY);
}

api.interceptors.request.use((config) => {
  const token = getStoredToken();

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export default api;
