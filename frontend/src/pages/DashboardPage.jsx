import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { clearAuth, getStoredToken, getStoredUser } from '../services/api';

function DashboardPage() {
  const navigate = useNavigate();
  const [user, setUser] = useState(() => getStoredUser());

  useEffect(() => {
    const token = getStoredToken();

    if (!token) {
      navigate('/', { replace: true });
    }
  }, [navigate]);

  async function handleLogout() {
    try {
      await api.post('/logout');
    } catch (error) {
      // Keep logout simple even if the request fails.
    }

    clearAuth();
    navigate('/', { replace: true });
  }

  return (
    <main className="dashboard-page">
      <section className="dashboard-card">
        <div className="dashboard-top">
          <div>
            <p className="dashboard-label">Dashboard</p>
            <h1>Hello, {user?.name || 'User'}</h1>
            <p className="dashboard-text">
              This is a simple dashboard placeholder after login.
            </p>
          </div>

          <button type="button" className="secondary-button" onClick={handleLogout}>
            Logout
          </button>
        </div>

        <div className="dashboard-grid">
          <div className="info-box">
            <span className="info-title">Email</span>
            <strong>{user?.email || 'Not available'}</strong>
          </div>

          <div className="info-box">
            <span className="info-title">Role</span>
            <strong>{user?.role || 'Not available'}</strong>
          </div>
        </div>
      </section>
    </main>
  );
}

export default DashboardPage;
