import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 10, // Turunkan dulu ke 10 Virtual Users untuk tes awal
  iterations: 20, 
};

export default function () {
  const res = http.get('http://localhost:8000/penerimaan/pkm-pengawasan'); 
  
  // Cetak status code jika bukan 200
  if (res.status !== 200) {
    console.log(`Error Status: ${res.status}`);
  }

  check(res, {
    'status is 200': (r) => r.status === 200,
  });
}