import { AlertCircle, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { Logo } from '../components/Logo';
import { ThemeToggleButton } from '../components/ThemeToggleButton';

function getLoginError(error) {
  if (error?.response?.data?.message) {
    return String(error.response.data.message);
  }

  if (error?.response?.status === 422) {
    return 'Credenciais invalidas.';
  }

  return 'Nao foi possivel entrar agora.';
}

export function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const [form, setForm] = useState({ login: '', password: '', remember: true });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  const onChange = (event) => {
    const { name, value, type, checked } = event.target;
    setForm((current) => ({
      ...current,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const onSubmit = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      await login(form);
      const target = location.state?.from || '/dashboard';
      navigate(target, { replace: true });
    } catch (err) {
      setError(getLoginError(err));
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="guest-screen">
      <ThemeToggleButton className="guest-theme-btn" />

      <div className="guest-center">
        <div className="guest-brand">
          <Logo className="guest-brand-logo" />
          <span>Europa 4.5</span>
        </div>

        <form className="guest-card" onSubmit={onSubmit}>
          <h1 className="guest-title">Login</h1>

          <label className="field">
            <span>Login</span>
            <input
              type="text"
              name="login"
              value={form.login}
              onChange={onChange}
              autoComplete="username"
              required
              autoFocus
            />
          </label>

          <label className="field">
            <span>Senha</span>
            <input
              type="password"
              name="password"
              value={form.password}
              onChange={onChange}
              autoComplete="current-password"
              required
            />
          </label>

          <label className="checkbox-row">
            <input type="checkbox" name="remember" checked={form.remember} onChange={onChange} />
            <span>Lembrar-me</span>
          </label>

          {error && (
            <div className="inline-error" role="alert">
              <AlertCircle size={16} />
              <span>{error}</span>
            </div>
          )}

          <div className="guest-actions">
            <Link to="/forgot-password" className="link-muted">
              Esqueceu sua senha?
            </Link>

            <button type="submit" className="btn-primary" disabled={submitting}>
              {submitting ? <Loader2 size={16} className="spin" /> : null}
              <span>{submitting ? 'Entrando...' : 'Log in'}</span>
            </button>
          </div>
        </form>
      </div>

      <footer className="guest-footer">
        &copy; 2025-2026 Nova Europa 4. Todos os direitos reservados. Criado e Desenvolvido por Andre Felipe
      </footer>
    </div>
  );
}
