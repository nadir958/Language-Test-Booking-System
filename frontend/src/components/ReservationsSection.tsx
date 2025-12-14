import { useEffect, useState } from 'react';
import { reservationsApi } from '../api';
import type { Reservation } from '../api/types';

export const ReservationsSection = ({ refreshSignal }: { refreshSignal: number }) => {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState<string | null>(null);

  const load = async () => {
    setLoading(true);
    setStatus(null);
    try {
      const res = await reservationsApi.list();
      setReservations(res.data);
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? 'Failed to load reservations';
      setStatus(msg);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [refreshSignal]);

  const cancel = async (id: string) => {
    setStatus(null);
    try {
      await reservationsApi.cancel(id);
      await load();
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? 'Cancel failed';
      setStatus(msg);
    }
  };

  return (
    <div className="card">
      <div className="card-header">
        <h2>My reservations</h2>
      </div>
      {status && <p className="error">{status}</p>}
      {loading && <p className="muted">Loading...</p>}
      <div className="grid">
        {reservations.map((r) => (
          <div key={r.id} className="session-card">
            <div className="session-meta">
              <strong>{r.session.language}</strong>
              <span>{new Date(r.session.startAt).toLocaleString()}</span>
              <span>{r.session.location}</span>
              <small className="muted">Booked at {new Date(r.createdAt).toLocaleString()}</small>
            </div>
            <div className="session-footer">
              <button onClick={() => cancel(r.id)}>Cancel</button>
            </div>
          </div>
        ))}
        {!loading && reservations.length === 0 && <p className="muted">No reservations yet.</p>}
      </div>
    </div>
  );
};
