const backendBase = import.meta.env.VITE_BACKEND_URL?.replace(/\/$/, '') || '';

export function BackendFrame({ path, title }) {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  const separator = normalizedPath.includes('?') ? '&' : '?';
  const src = `${backendBase}${normalizedPath}${separator}embedded=1`;

  return (
    <div className="backend-frame-wrap" aria-label={title || 'Conteudo'}>
      <iframe title={title || 'Conteudo'} src={src} className="backend-frame" loading="eager" />
    </div>
  );
}
