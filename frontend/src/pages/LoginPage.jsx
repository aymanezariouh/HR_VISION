import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import api, { getStoredToken, saveAuth } from '../services/api';

function LoginPage() {
  const navigate = useNavigate();
  const existingToken = getStoredToken();
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    device_name: 'react-frontend',
  });
  const [errorMessage, setErrorMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (existingToken) {
    return <Navigate to="/dashboard" replace />;
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setErrorMessage('');
    setIsSubmitting(true);

    try {
      const response = await api.post('/login', formData);
      const token = response.data.data.token;
      const user = response.data.data.user;

      saveAuth(token, user);
      navigate('/dashboard', { replace: true });
    } catch (error) {
      setErrorMessage(
        error.response?.data?.message || 'Unable to sign in with the provided credentials.',
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  function handleChange(event) {
    const { name, value } = event.target;

    setFormData((current) => ({
      ...current,
      [name]: value,
    }));
  }

  return (
    <main className="login-page">
      <section className="login-card">
        <div className="login-badge">HRVision</div>
        <h1>Welcome back</h1>
        <p className="login-text">
          Sign in with your HRVision account to access the dashboard.
        </p>

        <form className="login-form" onSubmit={handleSubmit}>
          {errorMessage ? <p className="alert-error">{errorMessage}</p> : null}

          <div className="form-field">
            <label className="form-label" htmlFor="email">
              Email
            </label>
            <input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              className="form-input"
              placeholder="test@example.com"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-field">
            <label className="form-label" htmlFor="password">
              Password
            </label>
            <input
              id="password"
              name="password"
              type="password"
              autoComplete="current-password"
              className="form-input"
              placeholder="Enter your password"
              value={formData.password}
              onChange={handleChange}
              required
            />
          </div>

          <button type="submit" className="primary-button" disabled={isSubmitting}>
            {isSubmitting ? 'Signing in...' : 'Sign In'}
          </button>
        </form>
      </section>
    </main>
  );
}

export default LoginPage;
