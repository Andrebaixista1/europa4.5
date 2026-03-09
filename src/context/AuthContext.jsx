import { useCallback, useEffect, useMemo, useState } from 'react';
import { AuthContext } from './auth-context';
import { fetchCsrfToken, http } from '../lib/http';

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadMe = useCallback(async () => {
    try {
      const { data } = await http.get('/api/front/me');
      setUser(data?.user ?? null);
      return data?.user ?? null;
    } catch {
      setUser(null);
      return null;
    }
  }, []);

  useEffect(() => {
    let active = true;

    (async () => {
      await loadMe();
      if (active) setLoading(false);
    })();

    return () => {
      active = false;
    };
  }, [loadMe]);

  const login = useCallback(async ({ login, password, remember }) => {
    const csrfToken = await fetchCsrfToken();

    const { data } = await http.post(
      '/api/front/login',
      { login, password, remember: !!remember },
      {
        headers: {
          'X-CSRF-TOKEN': csrfToken,
        },
      },
    );

    setUser(data?.user ?? null);
    return data;
  }, []);

  const logout = useCallback(async () => {
    try {
      const csrfToken = await fetchCsrfToken();
      await http.post(
        '/api/front/logout',
        {},
        {
          headers: {
            'X-CSRF-TOKEN': csrfToken,
          },
        },
      );
    } finally {
      setUser(null);
    }
  }, []);

  const value = useMemo(
    () => ({
      user,
      loading,
      isAuthenticated: !!user,
      refreshUser: loadMe,
      login,
      logout,
    }),
    [user, loading, loadMe, login, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
