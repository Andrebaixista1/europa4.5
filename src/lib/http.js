import axios from 'axios';

export const http = axios.create({
  baseURL: '/',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function fetchCsrfToken() {
  const { data } = await http.get('/api/front/csrf-token');
  return data?.csrf_token || '';
}
