import { useEffect, useState } from 'react';
import { sessionsApi } from '../api';
import type { SessionItem } from '../api/types';

type Status = { kind: 'idle' } | { kind: 'error'; message: string } | { kind: 'success'; message: string };

export const SessionsSection = ({ onBooked }: { onBooked: () => void }) => {
  const [sessions, setSessions] = useState<SessionItem[]>([]);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [limit] = useState(10);
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState<Status>({ kind: 'idle' });

  const fetchSessions = async (p = page) => {
    setLoading(true);
    setStatus({ kind: 'idle' });
    try {
      const res = await sessionsApi.list(p, limit);
      setSessions(res.data);
      setTotal(res.pagination.total);
      setPage(res.pagination.page);
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? 'Failed to load sessions';
      setStatus({ kind: 'error', message: msg });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSessions(page);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const book = async (sessionId: string) => {
    setStatus({ kind: 'idle' });
    try {
      await sessionsApi.book(sessionId);
      setStatus({ kind: 'success', message: 'Reservation created' });
      onBooked();
      fetchSessions(page);
    } catch (err: any) {
      const msg =
        err?.response?.data?.message ??
        err?.response?.data?.errors?.join(', ') ??
        'Booking failed';
      setStatus({ kind: 'error', message: msg });
    }
  };

  const maxPage = Math.max(1, Math.ceil(total / limit));

  return (
    <div className="card">
      <div className="card-header">
        <h2>Sessions</h2>
        <div className="pagination">
          <button onClick={() => fetchSessions(Math.max(1, page - 1))} disabled={page <= 1 || loading}>
            Prev
          </button>
          <span>
            Page {page} / {maxPage}
          </span>
          <button onClick={() => fetchSessions(Math.min(maxPage, page + 1))} disabled={page >= maxPage || loading}>
            Next
          </button>
        </div>
      </div>
      {status.kind === 'error' && <p className="error">{status.message}</p>}
      {status.kind === 'success' && <p className="success">{status.message}</p>}
      <div className="grid">
        {sessions.map((s) => (
          <div key={s.id} className="session-card">
            <div className="session-meta">
              <strong>{s.language}</strong>
              <span>{new Date(s.startAt).toLocaleString()}</span>
              <span>{s.location}</span>
            </div>
            <div className="session-footer">
              <span>
                Seats: {s.seatsRemaining ?? s.seats}/{s.seats}
              </span>
              <button onClick={() => book(s.id)} disabled={(s.seatsRemaining ?? s.seats) <= 0}>
                Book
              </button>
            </div>
          </div>
        ))}
        {!loading && sessions.length === 0 && <p className="muted">No sessions yet.</p>}
        {loading && <p className="muted">Loading sessions...</p>}
      </div>
    </div>
  );
};
