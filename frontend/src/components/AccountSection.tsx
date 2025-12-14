import { FormEvent, useState } from 'react';
import { authApi } from '../api';
import { useAuth } from '../context/AuthContext';

export const AccountSection = () => {
  const { user, refreshProfile } = useAuth();
  const [name, setName] = useState(user?.name ?? '');
  const [email, setEmail] = useState(user?.email ?? '');
  const [status, setStatus] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  if (!user) return null;

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setStatus(null);
    setSaving(true);
    try {
      await authApi.updateMe({ name, email });
      await refreshProfile();
      setStatus('Profile updated');
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? 'Update failed';
      setStatus(msg);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="card">
      <div className="card-header">
        <h2>Account</h2>
      </div>
      <form onSubmit={onSubmit} className="form-grid">
        <label>
          Name
          <input value={name} onChange={(e) => setName(e.target.value)} required />
        </label>
        <label>
          Email
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </label>
        {status && <p className="muted">{status}</p>}
        <button className="primary" type="submit" disabled={saving}>
          {saving ? 'Saving...' : 'Save'}
        </button>
      </form>
    </div>
  );
};
