import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 10,         // Turunkan ke 10 VU dulu untuk pengujian awal
  duration: '30s', // Jalankan tes berdurasi 30 detik
};

const BASE_URL = 'http://127.0.0.1:8000'; // Gunakan IP 127.0.0.1 (lebih cepat dibanding localhost)

// Daftar endpoint dipisah antara Halaman Biasa dan Halaman Ekspor Berat
const endpoints = [
  '/',
  '/penerimaan',
  '/penerimaan/ppm',
  '/search-wp',
  '/login',
  '/penerimaan/pkm-pengawasan',
  '/penerimaan/pkm-pemeriksaan',
  '/penerimaan/pkm-penagihan',
];

export default function () {
  // Ambil 1 endpoint secara acak tiap iterasi
  const path = endpoints[Math.floor(Math.random() * endpoints.length)];
  const res = http.get(`${BASE_URL}${path}`, {
    timeout: '10s', // Batas waktu tunggu max 10 detik
  });

  check(res, {
    'status is 200/302': (r) => r.status === 200 || r.status === 302,
  });

  sleep(1); // Beri jeda 1 detik tiap user
}