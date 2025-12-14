import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

const api = axios.create({
  baseURL: `${API_BASE.replace(/\/$/, '')}/api`,
});

let authToken: string | null = null;

export const setAuthToken = (token: string | null) => {
  authToken = token;
};

api.interceptors.request.use((config) => {
  if (authToken) {
    config.headers = config.headers ?? {};
    config.headers.Authorization = `Bearer ${authToken}`;
  }
  return config;
});

export default api;
