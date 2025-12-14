import api, { setAuthToken } from './client';
import type { User, SessionItem, PaginatedSessions, Reservation } from './types';

type AuthResponse = {
  token: string;
  user: User;
};

export const authApi = {
  setToken: setAuthToken,
  async register(payload: { name: string; email: string; password: string }): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/register', payload);
    return data;
  },
  async login(payload: { email: string; password: string }): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/login', payload);
    return data;
  },
  async me(): Promise<User> {
    const { data } = await api.get<User>('/me');
    return data;
  },
  async updateMe(payload: Partial<Pick<User, 'name' | 'email'>>): Promise<User> {
    const { data } = await api.patch<User>('/me', payload);
    return data;
  },
};

export const sessionsApi = {
  async list(page = 1, limit = 10): Promise<PaginatedSessions> {
    const { data } = await api.get<PaginatedSessions>('/sessions', { params: { page, limit } });
    return data;
  },
  async book(sessionId: string): Promise<{ id: string; session: SessionItem; createdAt: string }> {
    const { data } = await api.post('/reservations', { sessionId });
    return data;
  },
};

export const reservationsApi = {
  async list(): Promise<{ data: Reservation[] }> {
    const { data } = await api.get<{ data: Reservation[] }>('/reservations');
    return data;
  },
  async cancel(id: string): Promise<void> {
    await api.delete(`/reservations/${id}`);
  },
};
