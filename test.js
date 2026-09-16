import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 100,         // 100 Pengguna bersamaan
  iterations: 1000, // Total 1000 request
};

export default function () {
  const res = http.get('http://localhost:8000/penerimaan/pkm-pengawasan'); 

  check(res, {
    'status is 200': (r) => r.status === 200,
  });
}