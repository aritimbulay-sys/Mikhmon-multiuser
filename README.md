# Mikhmon Multi-User

Modifikasi Mikhmon v3 (MikroTik Hotspot Monitor) dengan:

- Halaman login dengan role **Admin** dan **User**.
- User hanya melihat router yang ditautkan kepadanya (boleh lebih dari satu).
- Admin mengelola user dan router (Tambah, Edit, Hapus) dari halaman **Users**.
- Ganti password oleh masing-masing user.
- Unggah logo dan editor template hanya sesuai hak akses.
- Tampilan mode **gelap, terang, dan otomatis**, dengan logo HotspotNOC dan latar jaringan.
- Status **Online/Offline** router di setiap kartu.

## Instalasi (Docker)

```bash
docker compose up -d
```

Lalu buka `http://<ip-server>:8080`.

Pastikan `include/config.php` dan `include/users.json` tersedia di server. Kedua file ini **tidak disertakan** di repo karena berisi data rahasia (password router dan user). Buat `include/config.php` dari instalasi Mikhmon v3 yang sudah ada, dan `include/users.json` akan dibuat otomatis saat admin pertama kali login.

## Catatan keamanan

- Password user disimpan dalam bentuk hash (`password_hash`).
- `users.json` dan `config.php` diblokir dari akses web (lihat `nginx.conf`).
- Ganti password admin default setelah instalasi.

## Lisensi

Mikhmon dibuat oleh Laksamadi Guko (laksa19) dan dilisensikan di bawah GNU GPL v2.
