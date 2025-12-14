import {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { authApi } from '../api';
import type { User } from '../api/types';

type AuthContextValue = {
  user: User | null;
  token: string | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string) => Promise<void>;
  logout: () => void;
  refreshProfile: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);
const TOKEN_KEY = 'ets_token';

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [token, setToken] = useState<string | null>(() => localStorage.getItem(TOKEN_KEY));
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState<boolean>(!!token);

  useEffect(() => {
    authApi.setToken(token);
    if (token) {
      localStorage.setItem(TOKEN_KEY, token);
      authApi
        .me()
        .then(setUser)
        .catch(() => {
          setUser(null);
          setToken(null);
          localStorage.removeItem(TOKEN_KEY);
        })
        .finally(() => setLoading(false));
    } else {
      localStorage.removeItem(TOKEN_KEY);
      setUser(null);
      setLoading(false);
    }
  }, [token]);

  const login = async (email: string, password: string) => {
    const res = await authApi.login({ email, password });
    setToken(res.token);
    setUser(res.user);
  };

  const register = async (name: string, email: string, password: string) => {
    const res = await authApi.register({ name, email, password });
    setToken(res.token);
    setUser(res.user);
  };

  const logout = () => {
    setToken(null);
    setUser(null);
  };

  const refreshProfile = async () => {
    if (!token) return;
    const me = await authApi.me();
    setUser(me);
  };

  const value = useMemo(
    () => ({ user, token, loading, login, register, logout, refreshProfile }),
    [user, token, loading],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = (): AuthContextValue => {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
};
