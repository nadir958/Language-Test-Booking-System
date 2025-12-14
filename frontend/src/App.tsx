import { useAuth } from './context/AuthContext';
import { AuthPanel } from './components/AuthPanel';
import { AccountSection } from './components/AccountSection';
import { SessionsSection } from './components/SessionsSection';
import { ReservationsSection } from './components/ReservationsSection';
import './styles.css';
import { useState } from 'react';

function App() {
  const { user, token, loading, logout } = useAuth();
  const [reservationsRefresh, setReservationsRefresh] = useState(0);

  if (loading) {
    return (
      <div className="page">
        <p>Loading...</p>
      </div>
    );
  }

  return (
    <div className="page">
      <header className="topbar">
        <div>
          <h1>Language Test Booking</h1>
          <p className="muted">Book language test sessions, manage your account and reservations.</p>
        </div>
        {user && token && (
          <div className="user-chip">
            <span>{user.name}</span>
            <button onClick={logout}>Logout</button>
          </div>
        )}
      </header>

      {!token && (
        <section className="layout">
          <AuthPanel />
        </section>
      )}

      {token && (
        <section className="layout">
          <AccountSection />
          <SessionsSection onBooked={() => setReservationsRefresh((v) => v + 1)} />
          <ReservationsSection refreshSignal={reservationsRefresh} />
        </section>
      )}
    </div>
  );
}

export default App;
