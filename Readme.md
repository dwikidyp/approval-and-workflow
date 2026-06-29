# Approval & Workflow Dokumen Kampus

Sistem Approval & Workflow Dokumen Kampus merupakan aplikasi berbasis web yang dirancang untuk membantu proses pengajuan, review, revisi, dan persetujuan dokumen akademik secara digital.

Aplikasi ini memungkinkan mahasiswa mengunggah dokumen, dosen melakukan review, dan admin akademik memberikan persetujuan akhir melalui alur workflow yang terstruktur dan terdokumentasi.

---

## 📌 Latar Belakang

Proses administrasi dokumen akademik di lingkungan kampus sering kali masih dilakukan secara manual, sehingga menyebabkan:

- Proses approval yang lambat
- Sulitnya memantau status dokumen
- Risiko kehilangan dokumen
- Tidak adanya audit trail aktivitas
- Kurangnya transparansi proses persetujuan

Melalui sistem ini, seluruh proses pengajuan dan persetujuan dokumen dapat dilakukan secara online dan terintegrasi.

---

## 🎯 Tujuan

- Digitalisasi proses administrasi dokumen kampus
- Mempercepat proses review dan approval
- Menyediakan monitoring status dokumen secara real-time
- Menyediakan riwayat approval yang terdokumentasi
- Mengurangi penggunaan dokumen fisik
- Mendukung transformasi digital kampus

---

## ✨ Fitur Utama

### Authentication & Authorization

- Login pengguna
- Role Based Access Control (RBAC)
- Hak akses berdasarkan role

Role yang tersedia:

- Mahasiswa
- Dosen
- Admin Akademik

---

### Manajemen Dokumen

- Upload dokumen PDF
- Kategori/Jenis Dokumen
- Download dokumen
- Preview dokumen
- Revisi dokumen

---

### Workflow Approval

#### Mahasiswa

- Upload dokumen
- Melihat status dokumen
- Revisi dokumen

#### Dosen

- Review dokumen
- Approve dokumen
- Reject dokumen
- Request revision

#### Admin Akademik

- Final approval dokumen
- Monitoring seluruh dokumen
- Manajemen jenis dokumen

---

### Status Workflow

```text
Pending
↓
Approved by Dosen
↓
Waiting Admin
↓
Approved
```

Alternatif:

```text
Pending
↓
Revision
```

atau

```text
Pending
↓
Rejected
```

---

### Dashboard Monitoring

Menampilkan:

- Total Dokumen
- Pending Documents
- Approved Documents
- Rejected Documents
- Revision Documents
- Recent Activities

---

### Activity Log

Sistem mencatat aktivitas:

- Upload dokumen
- Revisi dokumen
- Approval dosen
- Approval admin
- Perubahan status dokumen

---

### Notification

Notifikasi otomatis ketika:

- Dokumen perlu revisi
- Dokumen ditolak
- Dokumen disetujui dosen
- Menunggu final approval
- Dokumen disetujui admin

---

## 🏗️ Teknologi

| Teknologi | Keterangan |
|------------|------------|
| Laravel 12 | Backend Framework |
| Filament v3 | Admin Panel |
| Livewire | Reactive Component |
| Blade | Template Engine |
| MariaDB | Database |
| Docker | Containerization |
| Spatie Permission | Role & Permission |
| Spatie Activity Log | Activity Logging |
| Filament Notification | Sistem Notifikasi |

---

## 📊 Entity Relationship Diagram

### Users

```text
id
name
email
password
```

### Document Types

```text
id
name
description
```

### Documents

```text
id
user_id
document_type_id
title
description
file
status
submitted_at
```

### Approvals

```text
id
document_id
approved_by
status
notes
approved_at
```

---

## 🔄 Workflow Sistem

### Upload Dokumen

```text
Mahasiswa
↓
Upload Dokumen
↓
Pending
```

### Review Dosen

```text
Dosen
↓
Review
↓
Approve / Revision / Reject
```

### Final Approval

```text
Admin Akademik
↓
Final Approval
↓
Approved
```

---

## 🚀 Instalasi

### Clone Repository

```bash
git clone https://github.com/dwikidyp/approval-and-workflow.git

cd approval-and-workflow
```

---

### Copy Environment

```bash
cp .env.example .env
```

---

### Jalankan Docker

Karena project menggunakan boilerplate docker:

```bash
dcu
```

atau

```bash
docker compose up -d
```

---

### Generate Key

```bash
dca key:generate
```

---

### Migrasi Database

```bash
dca migrate
```

---

### Seeder

```bash
dca db:seed
```

---

### Storage Link

```bash
dca storage:link
```

---

### Generate Permission

Jika menggunakan Filament Shield:

```bash
dca shield:generate --all
```

---

## 👥 Role Sistem

### Mahasiswa

- Upload dokumen
- Revisi dokumen
- Monitoring status
- Download dokumen

### Dosen

- Review dokumen
- Approval dokumen
- Reject dokumen
- Request revision

### Admin Akademik

- Final approval
- Kelola jenis dokumen
- Monitoring aktivitas
- Monitoring dokumen

---

## 📸 Screenshot

Tambahkan screenshot aplikasi pada folder:

```text
docs/screenshots/
```

Contoh:

- Dashboard
- Document Management
- Approval Workflow
- Activity Log
- Notification System

---

## 📚 Referensi

- Laravel Documentation
- Filament Documentation
- Livewire Documentation
- Spatie Laravel Permission
- Spatie Activity Log

---

## 👨‍💻 Pengembang

**Dwiki Dzaki Yudi Putra**

Project Mata Kuliah Pemrograman Web

Universitas Esa Unggul

---

## 📄 License

Project ini dibuat untuk kebutuhan akademik dan pembelajaran.