# ⚡ NetMeter Pro — Intranet Throughput & Real Bandwidth Assessment Tool

[![Version](https://img.shields.io/badge/version-2.5.0-blue.svg)](https://github.com/sanialviga)
[![License](https://img.shields.io/badge/license-LGPL--3.0-green.svg)](LICENSE)
[![Platform](https://img.shields.io/badge/platform-Web%20%7C%20Node.js%20%7C%20PHP-orange.svg)]()
[![UI Design](https://img.shields.io/badge/UI-NetStock%20ArchitectUI-0284c7.svg)]()
[![Responsive](https://img.shields.io/badge/responsive-Desktop%20%7C%20Tablet%20%7C%20Mobile-emerald.svg)]()

> **Sistem Asesmen Real Bandwidth & Throughput Jaringan Lokal (Intranet) Enterprise-Grade** yang dirancang khusus untuk audit kelaikan koneksi LAN di **Rumah Sakit (RS)**, **Klinik**, **Kampus / Sekolah**, dan **Perkantoran Modern**.

---

## 📌 Latar Belakang & Masalah

Pada infrastruktur jaringan lokal fasilitas kesehatan (RS/Klinik) maupun institusi pendidikan, pengujian kecepatan internet publik (seperti Ookla Speedtest atau Fast.com) **tidak mampu merefleksikan kesehatan jaringan intranet yang sebenarnya**.

Ketika sistem **SIMRS**, transfer citra radiologi **PACS/DICOM**, penginputan **RME (Rekam Medis Elektronik)**, sinkronisasi **BPJS / SatuSehat Kemenkes**, atau pelaksanaan ujian kampus berbasis komputer (**CBT Exam**) mengalami *loading lama / lagging*, kendala utamanya hampir selalu berada pada **kelaikan fisik jalur kabel LAN, terminasi konektor, outlet dinding, atau switch distribusi lokal**, bukan pada kapasitas internet ISP.

**NetMeter Pro** hadir untuk mengukur performa riil *throughput intranet* secara langsung antara komputer klien (loket pendaftaran, poli rawat jalan, kasir, radiologi, lab) dengan laptop server audit yang ditempatkan di ruang data center / server LAN.

---

## 🎯 Standar Penilaian Throughput Real (Audit Standard)

NetMeter Pro menerapkan standar kelaikan jaringan lokal berbasis kapasitas transfer aktual:

| Throughput (Download/Upload) | Grade | Kelaikan | Keterangan & Rekomendasi Teknis |
| :--- | :---: | :---: | :--- |
| **≥ 600 — 1000+ Mbps** | 🟢 **A+** | **Sangat Baik** | Full Gigabit link (1000Base-T). Seluruh 4-pair (8 pin) kabel Cat5e/Cat6 terterminasi sempurna. Sangat aman untuk transfer file besar radiologi PACS/DICOM dan SIMRS. |
| **> 150 — 600 Mbps** | 🔵 **A** | **Baik** | Mendukung Gigabit Ethernet atau Wi-Fi 5 GHz berkecepatan tinggi. Aman untuk seluruh operasional harian. |
| **50 — 150 Mbps** | 🟡 **B** | **Cukup** | Terindikasi limitasi Fast Ethernet (100 Mbps). Rentan antrean transaksi data saat jam sibuk antrean pasien atau sinkronisasi klaim massal. |
| **< 50 Mbps** | 🔴 **C/D** | **Kurang Baik** | **Kondisi Kritis!** Terjadi *packet degradation*, pin tester mati, atau interferensi parah. Membutuhkan intervensi dan perbaikan fisik segera oleh tim IT. |

---

## 🚀 Fitur Utama & Keunggulan

### 1. Desain NetStock Enterprise ArchitectUI (Human & Professional)
- Palet warna solid **Ocean Blue** (`#2563eb`), kartu bersudut presisi (`border-radius: 12px`), bayangan lembut (*soft elevation*), dan kontras tipografi tinggi yang nyaman dipandang.
- **Tampilan Bebas Scroll (*Above-The-Fold Ready*)**: Seluruh metrik pengujian, informasi perangkat klien, serta kartu analisis risiko berada di area layar atas tanpa perlu scroll, sangat ideal saat layar monitor diatur *split-screen* berdampingan dengan jendela Windows **Sistem > Tentang (About PC)** untuk dokumentasi foto/screenshot audit.
- **Dukungan Dark Mode & Light Mode**: Dilengkapi tombol beralih tema yang responsif dan menyimpan preferensi tema pengguna.

### 2. Validasi Formulir Pra-Uji (Mandatory Controls)
- **Lokasi / Titik Pengujian (Wajib Diisi)**: Mencegah pengujian anonim tanpa kejelasan ruang/titik pengujian (misal: `Loket Pendaftaran 1`, `Poli Radiologi`, `Kasir Rawat Inap`).
- **Profil Jaringan Terintegrasi**: Default profil disesuaikan untuk Rumah Sakit / Klinik (`SIMRS, PACS, RME`), Kampus/Sekolah (`CBT, SIAKAD, E-Learning`), dan Perkantoran.
- **Media Koneksi (Wajib Dipilih)**: Wajib menentukan media fisik yang digunakan (`Kabel LAN UTP Cat5e/Cat6`, `Wi-Fi 5 GHz`, `Wi-Fi 2.4 GHz`, `Fiber Optic`).

### 3. Deteksi Identitas Hardware Klien Secara Lengkap & Real-Time
- **IP Address Lokal Klien**: Dideteksi otomatis dari header koneksi jaringan.
- **MAC Address Fisik Klien (ARP Lookup)**: Server laptop membaca tabel ARP lokal secara server-side (`arp -a <client_ip>`) sehingga MAC address asli adapter klien tercatat akurat.
- **Hostname Komputer Desktop**: Resolusi nama host klien otomatis melalui reverse DNS / NetBIOS.
- **Spesifikasi Hardware Klien**: Deteksi jumlah Core CPU, RAM, perkiraan kapasitas penyimpanan klien (`navigator.storage.estimate()`), tipe Browser, Sistem Operasi, dan resolusi layar monitor.

### 4. Heartbeat Real-Time & Pemantauan Server
- **Indikator Live Heartbeat**: Mengukur latensi round-trip (ping ms) ke laptop server secara terus-menerus dengan animasi denyut nadi hijau (*online*) atau merah (*offline*).
- **Server Storage Indicator**: Memantau kapasitas penyimpanan berkas riwayat di laptop server.

### 5. Penyimpanan Riwayat Terpusat di Laptop Server
- Ketika laptop auditor dihubungkan ke jaringan LAN ruang server dan staf/klien mengakses IP laptop dari komputer di berbagai ruangan:
  - **Seluruh log riwayat tersimpan terpusat di hard drive laptop auditor (`data/history.json`)**, bukan di browser komputer klien!
- **Manajemen Riwayat Lengkap**:
  - Hapus 1 riwayat pengujian tertentu.
  - Hapus seluruh riwayat pengujian pada hari ini.
  - Reset total riwayat dengan modal konfirmasi aman.
  - Pencarian & filter berdasarkan tanggal pengujian.

### 6. Fitur Multi-Export Berita Acara Resmi
- 📄 **Export Dokumen Word (`.doc`)**:
  - Diformat khusus dalam orientasi **A4 Landscape** berstandar MSOffice.
  - Memuat Kop Dokumen Resmi Berita Acara Audit Throughput & Kelaikan Jaringan Lokal.
  - Dilengkapi 4 Kartu Ringkasan KPI (Grade A+, A, B, C/D), tabel audit terstruktur dengan border rapi, dan **blok tanda tangan ganda** (*Penanggung Jawab Lokasi* & *Auditor Jaringan*) yang terlindung dari pemotongan halaman (`page-break-inside: avoid`).
- 📕 **Export Dokumen PDF (`.pdf`)**:
  - Menghasilkan berkas PDF A4 Landscape secara langsung via browser menggunakan `jsPDF` dan `jspdf-autotable`.
  - Margin dan ukuran sel terkalibrasi presisi dengan proteksi auto page-break agar tanda tangan tidak bertubrukan dengan footer halaman.
- 📊 **Export Dokumen Excel (`.xlsx`)**:
  - Menghasilkan berkas spreadsheet berformat rapi via `SheetJS` (`xlsx.full.min.js`) lengkap dengan styling header, auto-filter kolom, dan auto-fit lebar kolom.

### 7. Panduan Checklist Fisik IT & Mitigasi Risiko
Menyediakan checklist perbaikan jika kecepatan jaringan di bawah standar:
1. **Pemeriksaan Kabel UTP**: Cek kategori kabel (Cat5e atau Cat6) dan panjang tarikan kabel ke switch hub.
2. **Konektor RJ-45 & Terminasi**: Uji menggunakan LAN Cable Tester; pastikan ke-8 lampu LED menyala berurutan (pin 1 s/d 8). Terminasi 4-pin hanya mentok di 100 Mbps!
3. **Outlet Modular Dinding**: Periksa *punch-down block* RJ-45 di wallplate tembok dari potensi korosi, kabel longgar, atau pemasangan yang tidak sesuai standar T568A/T568B.
4. **Port Switch Distribusi**: Pastikan port switch yang terhubung sudah berkecepatan 10/100/1000 Mbps (Gigabit) dan tidak terkunci di negosiasi 100 Mbps *Half-Duplex*.
5. **Akses Nirkabel (Wi-Fi)**: Evaluasi frekuensi Access Point; utamakan pita 5 GHz untuk area padat klien dan hindari kanal 2.4 GHz yang penuh interferensi.

---

## 💻 Panduan Instalasi & Penggunaan

### Kebutuhan Sistem
- **Laptop / PC Server**: Windows 10/11, Linux, atau macOS.
- **Web Server**: Apache / XAMPP (PHP 7.4 - 8.2+) **ATAU** Node.js (v16.x - v22.x+).
- **Klien**: Browser modern apa pun (Chrome, Edge, Firefox, Safari).

---

### Cara 1: Menjalankan via Apache / XAMPP (Rekomendasi di Ruang Server)
1. Salin folder proyek ini ke direktori htdocs XAMPP:
   ```text
   C:\xampp\htdocs\speedtest\
   ```
2. Buka **XAMPP Control Panel** dan jalankan modul **Apache**.
3. Pastikan laptop terhubung ke switch LAN atau access point lokal.
4. Periksa alamat IP laptop Anda via Command Prompt (`ipconfig`), misalnya: `192.168.1.100`.
5. Akses dari browser:
   - **Di Laptop Server**: [http://localhost/speedtest/](http://localhost/speedtest/)
   - **Di Komputer Klien (Loket/Poli/Lab)**: `http://192.168.1.100/speedtest/`

---

### Cara 2: Menjalankan via Node.js Standalone
1. Buka folder proyek di terminal, lalu instal dependensi (opsional):
   ```bash
   npm install
   ```
2. Jalankan server:
   ```bash
   npm start
   # ATAU klik dua kali file start-server.bat
   ```
3. Akses dari browser:
   - **Di Laptop Server**: [http://localhost:3000/](http://localhost:3000/)
   - **Di Komputer Klien**: `http://192.168.1.100:3000/`

---

## 📁 Struktur Direktori

```text
speedtest/
├── assets/                    # Pustaka frontend offline (SheetJS, jsPDF, autotable)
│   ├── jspdf.plugin.autotable.min.js
│   ├── jspdf.umd.min.js
│   └── xlsx.full.min.js
├── backend/                   # Skrip backend PHP untuk engine throughput & telemetri
│   ├── empty.php              # Endpoint upload payload
│   ├── garbage.php            # Endpoint download stream throughput
│   ├── getIP.php              # Resolusi IP, MAC ARP, Hostname, dan Client Hardware
│   └── history.php            # REST API CRUD riwayat pengujian lokal
├── data/                      # Direktori penyimpanan database lokal
│   └── history.json           # Berkas JSON penyimpanan riwayat pengujian terpusat
├── docs/                      # Dokumentasi teknis audit jaringan
│   └── STANDAR_THROUGHPUT.md  # SOP & panduan mitigasi bottleneck jaringan
├── index.html                 # Halaman utama aplikasi (NetStock Enterprise UI)
├── server.js                  # Engine HTTP & REST server standalone berbasis Node.js
├── speedtest.js               # Library core runner speedtest
├── speedtest_worker.js        # Web Worker multithreaded engine
├── start-server.bat           # Launcher one-click untuk Windows
├── package.json               # Metadata proyek & script runner
└── README.md                  # Dokumentasi resmi aplikasi
```

---

## 🛡️ Keamanan & Privasi
- Seluruh pengujian dan penyimpanan riwayat dilakukan **100% secara lokal** di dalam jaringan intranet Anda.
- **Tidak ada data yang dikirimkan ke cloud pihak ketiga atau internet eksternal**.
- Aman digunakan pada infrastruktur data sensitif rumah sakit (kepatuhan data rekam medis pasien).

---

## 👨‍💻 Pengembang & Auditor

**NetMeter Pro** dirancang dan dikembangkan oleh:

- **Nama**: Adi Sani Alviga
- **GitHub**: [@sanialviga](https://github.com/sanialviga)
- **Organisasi / Portofolio**: PT Netkrom Solusindo / NetStock Enterprise

Built with ❤️ using HTML5, CSS3, Modern JavaScript, SheetJS, jsPDF & PHP / Node.js Engine.  
&copy; 2026 NetMeter. All rights reserved.
