export function applyTheme(theme) {
  const resolved = theme === 'light' ? 'light' : 'dark';
  document.documentElement.classList.remove('theme-dark', 'theme-light');
  document.documentElement.classList.add(resolved === 'dark' ? 'theme-dark' : 'theme-light');
  localStorage.setItem('lumia-theme', resolved);
  return resolved;
}

export function initTheme() {
  const stored = localStorage.getItem('lumia-theme');
  const theme = stored === 'light' || stored === 'dark' ? stored : 'dark';
  applyTheme(theme);
  return theme;
}
