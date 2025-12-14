import { FormEvent, useState } from 'react';
import { useAuth } from '../context/AuthContext';

type Mode = 'login' | 'register';

export const AuthPanel = () => {
  const { login, register } = useAuth();
  const [mode, setMode] = useState<Mode>('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      if (mode === 'login') {
        await login(email, password);
      } else {
        await register(name, email, password);
      }
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? err?.response?.data?.error ?? 'Request failed';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <div className="card-header">
        <div className="tabs">
          <button
            className={mode === 'login' ? 'active' : ''}
            onClick={() => setMode('login')}
            type="button"
          >
            Login
          </button>
          <button
            className={mode === 'register' ? 'active' : ''}
            onClick={() => setMode('register')}
            type="button"
          >
            Register
          </button>
        </div>
      </div>
      <form onSubmit={onSubmit} className="form-grid">
        {mode === 'register' && (
          <label>
            Name
            <input value={name} onChange={(e) => setName(e.target.value)} required />
          </label>
        )}
        <label>
          Email
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </label>
        <label>
          Password
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </label>
        {error && <p className="error">{error}</p>}
        <button className="primary" type="submit" disabled={loading}>
          {loading ? 'Working...' : mode === 'login' ? 'Login' : 'Create account'}
        </button>
      </form>
    </div>
  );
};
