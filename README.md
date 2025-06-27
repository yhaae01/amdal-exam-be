# 📝 SITERAL

SITERAL (Sistem Informasi Seleksi Tenaga Teknis Operasional Amdalnet) merupakan sistem informasi ujian online berbasis web menggunakan Laravel 12 (backend), PostgreSQL (database), dan React + Vite (frontend).

---

## 🚀 Teknologi yang Digunakan

- **Backend**: Laravel 12
- **Autentikasi**: JWT Auth ([https://jwt-auth.readthedocs.io](https://jwt-auth.readthedocs.io))
- **Database**: PostgreSQL
- **Frontend**: React + Vite
- **Penyimpanan Gambar**: Local Storage (disk `public`)

---

## 🧭 Alur Aplikasi

### 1. Autentikasi

- `POST /api/v1/login` – Login dengan email dan password
- `POST /api/v1/logout` – Logout
- `GET /api/v1/me` – Ambil data user yang sedang login

> Token JWT harus dikirim di header:

`http
Authorization: Bearer <TOKEN>
`


### 2. Manajemen Ujian (Admin)

#### 📘 Exam

- `GET /api/v1/exams`
- `POST /api/v1/exams`
- `GET /api/v1/exams/{id}`
- `PUT /api/v1/exams/{id}`
- `DELETE /api/v1/exams/{id}`

#### ❓ Question

- `GET /api/v1/questions`
- `POST /api/v1/questions`
- `GET /api/v1/questions/{id}`
- `PUT /api/v1/questions/{id}`
- `DELETE /api/v1/questions/{id}`

#### 🔘 Option

- `GET /api/v1/options`
- `POST /api/v1/options`
- `GET /api/v1/options/{id}`
- `PUT /api/v1/options/{id}`
- `DELETE /api/v1/options/{id}`

#### 🗓️ Exam Batch (Sesi Ujian)

- `GET /api/v1/exam-batches` – Daftar semua batch ujian
- `POST /api/v1/exam-batches` – Buat batch ujian
- `GET /api/v1/exam-batches/{id}` – Detail batch
- `DELETE /api/v1/exam-batches/{id}` – Hapus batch
- `POST /api/v1/exam-batches/{id}/assign-users` – Assign user ke batch tertentu


### 3. Pelaksanaan Ujian (User)

#### 🟢 Mulai Ujian

- `POST /api/v1/exam-submissions/start`
  - Body: `{ "exam_id": "<uuid>", "exam_batch_id": "<uuid>" }`
  - Hanya bisa dijalankan jika waktu batch aktif dan user terdaftar di batch tersebut

#### ✏️ Jawab Soal

- `POST /api/v1/answers`
- `PUT /api/v1/answers/{id}`
- `GET /api/v1/answers/{id}`

#### ✅ Submit Ujian

- `POST /api/v1/exam-submissions/{submission}/submit`

#### 📄 Lihat Hasil

- `GET /api/v1/exam-submissions/{submission}`
- `GET /api/v1/my-submissions`

---

## 📂 Struktur Database Utama

- **users** – menyimpan user dan role
- **exams** – daftar ujian
- **questions** – daftar soal ujian
- **options** – pilihan jawaban soal
- **exam_batches** – daftar sesi/batch ujian
- **exam_batch_user** – pivot user yang terdaftar di sesi tertentu
- **exam_submissions** – data pengerjaan ujian oleh user
- **answers** – jawaban dari setiap soal dalam ujian

---

## 📌 Catatan

- Soal dan opsi bisa memiliki gambar (`image`) yang disimpan di `storage/app/public`
- Field `is_correct` disembunyikan dari API response untuk menjaga integritas ujian
- Soal bisa berupa pilihan ganda (`multiple_choice`) atau esai (`essay`)
- Pelaksanaan ujian dibatasi berdasarkan waktu sesi/batch yang ditentukan oleh admin

---

## 📦 Setup

```bash
# Clone & install
composer install
cp .env.example .env
php artisan key:generate

# Konfigurasi JWT
php artisan jwt:secret

# Migrate & seed
php artisan migrate:fresh --seed

# Jalankan server
php artisan serve
```

---

> Dibuat dengan ❤️ oleh tim pengembang.

Surya Intan Permana ([https://github.com/yhaae01](https://github.com/yhaae01))

Dandun Gigih Prakoso ([https://github.com/DandunGP](https://github.com/DandunGP))

Aditya Rizqi Ardhana ([https://github.com/Adityarizqi7](https://github.com/Adityarizqi7))

Jarot Setiawan ([https://github.com/Ja7Ca](https://github.com/Ja7Ca))
