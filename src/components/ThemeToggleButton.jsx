import { Moon, Sun } from 'lucide-react';
import { useEffect, useState } from 'react';
import { applyTheme } from '../lib/theme';

export function ThemeToggleButton({ className = '' }) {
  const [theme, setTheme] = useState(() => {
    const stored = localStorage.getItem('lumia-theme');
    return stored === 'light' || stored === 'dark' ? stored : 'dark';
  });

  useEffect(() => {
    applyTheme(theme);
  }, [theme]);

  const toggle = () => {
    setTheme((current) => (current === 'dark' ? 'light' : 'dark'));
  };

  return (
    <button
      type="button"
      onClick={toggle}
      className={`theme-toggle-btn ${className}`.trim()}
      aria-label={theme === 'dark' ? 'Ativar tema claro' : 'Ativar tema escuro'}
      title={theme === 'dark' ? 'Tema claro' : 'Tema escuro'}
    >
      {theme === 'dark' ? <Sun size={18} /> : <Moon size={18} />}
    </button>
  );
}
