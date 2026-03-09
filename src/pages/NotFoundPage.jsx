import { Link } from 'react-router-dom';

export function NotFoundPage() {
  return (
    <div className="card-basic not-found">
      <h2>Pagina nao encontrada</h2>
      <p>Verifique o endereco e tente novamente.</p>
      <Link to="/dashboard" className="btn-primary">Voltar ao painel</Link>
    </div>
  );
}
