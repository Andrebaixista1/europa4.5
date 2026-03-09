import { ChevronDown, Settings, UserCircle2 } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { Logo } from './Logo';
import { ThemeToggleButton } from './ThemeToggleButton';

const pageTitles = {
  '/dashboard': 'Painel',
  '/configuracoes': 'Configuracoes',
  '/consultas/cliente': 'Consulta Cliente',
  '/profile': 'Perfil',
};

export function AppShell() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const [openConsultas, setOpenConsultas] = useState(false);
  const [openUserMenu, setOpenUserMenu] = useState(false);

  const consultasRef = useRef(null);
  const userMenuRef = useRef(null);

  useEffect(() => {
    const onClickOutside = (event) => {
      if (consultasRef.current && !consultasRef.current.contains(event.target)) {
        setOpenConsultas(false);
      }
      if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
        setOpenUserMenu(false);
      }
    };

    document.addEventListener('click', onClickOutside);
    return () => document.removeEventListener('click', onClickOutside);
  }, []);

  const activeTitle = useMemo(() => pageTitles[location.pathname] || 'Europa 4.5', [location.pathname]);

  const handleLogout = async () => {
    await logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="app-shell">
      <header className="top-nav">
        <div className="container nav-inner">
          <div className="nav-left">
            <Link to="/dashboard" className="brand-link">
              <Logo className="brand-logo" />
              <span className="brand-text">Europa 4.5</span>
            </Link>

            <nav className="primary-nav" aria-label="Navegacao principal">
              <NavLink to="/dashboard" className={({ isActive }) => `nav-link${isActive ? ' is-active' : ''}`}>
                Painel
              </NavLink>

              <div ref={consultasRef} className="nav-dropdown">
                <button
                  type="button"
                  className={`nav-link nav-link-button${location.pathname.startsWith('/consultas') ? ' is-active' : ''}`}
                  onClick={() => setOpenConsultas((prev) => !prev)}
                >
                  <span>Consultas</span>
                  <ChevronDown size={15} />
                </button>

                {openConsultas && (
                  <div className="nav-dropdown-menu">
                    <NavLink to="/consultas/cliente" className="nav-dropdown-item">
                      Consulta Cliente
                    </NavLink>
                  </div>
                )}
              </div>
            </nav>
          </div>

          <div className="nav-right">
            <ThemeToggleButton className="nav-theme-btn" />

            <div ref={userMenuRef} className="user-menu">
              <button type="button" className="user-menu-trigger" onClick={() => setOpenUserMenu((prev) => !prev)}>
                <span>{user?.name || user?.login || 'Usuario'}</span>
                <ChevronDown size={15} />
              </button>

              {openUserMenu && (
                <div className="user-menu-dropdown">
                  <NavLink to="/configuracoes" className="user-menu-item">
                    <Settings size={15} />
                    <span>Configuracoes</span>
                  </NavLink>
                  <NavLink to="/profile" className="user-menu-item">
                    <UserCircle2 size={15} />
                    <span>Perfil</span>
                  </NavLink>
                  <button type="button" className="user-menu-item danger" onClick={handleLogout}>
                    <span>Sair</span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </header>

      <main className="main-area">
        <section className="page-heading">
          <div className="container">
            <h1>{activeTitle}</h1>
          </div>
        </section>

        <section className="page-content">
          <div className="container">
            <Outlet />
          </div>
        </section>
      </main>

      <footer className="app-footer">
        <div className="container">
          &copy; 2025-2026 Nova Europa 4. Todos os direitos reservados. Criado e Desenvolvido por Andre Felipe
        </div>
      </footer>
    </div>
  );
}
