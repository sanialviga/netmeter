# Standar Throughput & Prosedur Audit Fisik Jaringan Lokal (SOP)

Dokumen ini merupakan panduan teknis operasional bagi Network Administrator, IT Rumah Sakit, dan Teknisi Jaringan dalam melakukan evaluasi kelaikan link fisik jaringan lokal intranet menggunakan **NetMeter Pro**.

---

## 1. Ambang Batas Throughput & Dampak Operasional

| Kategori Bandwidth | Grade | Evaluasi Teknis | Dampak di Rumah Sakit & Klinik | Dampak di Kampus / Sekolah |
| :--- | :---: | :--- | :--- | :--- |
| **≥ 600 — 1000 Mbps** | 🟢 **A+** | **Full Gigabit (1000Base-T)**<br>Kabel Cat5e/6 terpasang 8 pin utuh, switch Gigabit berfungsi optimal. | Pengambilan citra PACS/DICOM instan (1-3 detik), transfer database SIMRS tanpa hambatan. | Ujian CBT lancar untuk ribuan siswa bersamaan, streaming video edukasi tanpa buffer. |
| **> 150 — 600 Mbps** | 🔵 **A** | **High-Speed Link**<br>Mendukung Gigabit atau Wi-Fi 5 GHz berkecepatan tinggi dengan kanal bersih. | Operasional RME harian aman, farmasi & kasir responsif, bridging SatuSehat lancar. | Akses SIAKAD dan perpustakaan digital responsif, sinkronisasi file tugas cepat. |
| **50 — 150 Mbps** | 🟡 **B** | **Plafon Fast Ethernet (100 Mbps)**<br>Kabel atau switch terkunci pada negosiasi 100Base-TX. | Terjadi antrean saat dokter membuka radiologi resolusi tinggi, loading SIMRS mulai terasa lambat saat jam sibuk. | Akses ujian massal mulai mengalami antrean koneksi, upload berkas besar lambat. |
| **< 50 Mbps** | 🔴 **C/D** | **Degraded / Kritis**<br>Terjadi crosstalk, degradasi kabel, kabel tertekuk, atau konektor bermasalah. | **Risiko Tinggi!** Antrean loket pendaftaran menumpuk, rekam medis gagal sync, risiko kehilangan data transaksi pasien. | Ujian CBT rentan *time out* / *auto-logout*, dosen dan mahasiswa mengeluhkan koneksi putus-nyambung. |

---

## 2. Checklist Fisik Audit Jaringan (IT Troubleshooting)

Jika hasil pengujian NetMeter Pro menunjukkan **Grade B (< 150 Mbps)** atau **Grade C/D (< 50 Mbps)**, lakukan langkah inspeksi fisik berikut:

### A. Pemeriksaan Kabel UTP (Unshielded Twisted Pair)
1. **Cek Marking Tipe Kabel**: Pastikan kabel yang terpasang minimal bertuliskan **Cat5e** atau disarankan **Cat6**. Hindari penggunaan kabel Cat5 lama yang hanya mendukung maksimal 100 Mbps.
2. **Panjang Tarikan Kabel**: Pastikan panjang tarikan kabel dari switch ke komputer klien tidak melebihi batas standar **90 meter** (total link horizontal maksimal 100 meter termasuk patch cord).
3. **Kondisi Fisik Kabel**: Periksa apakah ada bagian kabel yang terjepit pintu, tertekuk tajam (*sharp bend*), ditarik terlalu kencang, atau berjalan paralel terlalu dekat dengan kabel listrik bertegangan tinggi tanpa pelindung (*conduit/EMI interference*).

### B. Konektor RJ-45 & Pengujian Pin Tester
1. **Gunakan LAN Cable Tester**: Pasang kedua ujung kabel ke master & remote cable tester.
2. **Verifikasi 8 Pin Menyala Berurutan**:
   - Untuk mencapai kecepatan **Gigabit (1000 Mbps)**, seluruh **8 pin (4 pasang kabel)** wajib terhubung sempurna!
   - Jika lampu LED yang menyala hanya 4 pin (pin 1, 2, 3, 6), kecepatan jaringan **secara otomatis akan terkunci di maksimal 100 Mbps (Fast Ethernet)**.
3. **Cek Hasil Crimping**: Pastikan jaket luar kabel terkunci erat di dalam konektor RJ-45 dan pin tembaga menusuk isolasi kabel dengan kedalaman yang rata.

### C. Outlet Modular RJ-45 di Dinding (Wallplate / Faceplate)
1. **Periksa Punch-Down Block**: Buka wallplate di dinding tempat komputer klien terhubung.
2. **Pastikan Standar Terminasi Seragam**: Gunakan standar **T568B** (atau T568A) secara konsisten di seluruh titik.
3. **Periksa Korosi & Debu**: Bersihkan pin kuningan di dalam modular jack dari debu atau karat yang dapat menimbulkan *packet loss* atau hambatan sinyal listrik (*attenuation*).

### D. Switch Distribusi & Port Negotiation
1. **Spesifikasi Port Switch**: Pastikan switch yang digunakan bukan switch 10/100 Mbps lawas, melainkan sudah mendukung **10/100/1000 Mbps (Gigabit Ethernet)**.
2. **Status Lampu Indikator Link Port**:
   - Pada switch Gigabit umumnya:
     - Lampu **Hijau** = Terhubung pada kecepatan **1000 Mbps (Gigabit)**.
     - Lampu **Oranye/Kuning** = Terhubung pada kecepatan **100 Mbps**.
3. **Duplex Mode**: Pastikan port terkonfigurasi pada **Full-Duplex**, bukan Half-Duplex (yang memicu collision paket data).

### E. Jalur Nirkabel (Wi-Fi Access Point)
1. **Pita Frekuensi (Band Selection)**:
   - Pastikan komputer klien terhubung ke frekuensi **5 GHz (802.11ac / Wi-Fi 6)** untuk mendapatkan throughput di atas 200 Mbps.
   - Pita frekuensi **2.4 GHz** rentan terhadap interferensi saluran (*channel overlap*) dan memiliki batas throughput riil umumnya hanya 30 - 70 Mbps.
2. **Kekuatan Sinyal (RSSI)**: Pastikan nilai RSSI di komputer klien minimal **-65 dBm** atau lebih baik.

---

## 3. Format Pelaporan & Dokumentasi Berita Acara

Setelah asesmen selesai:
1. Klik **Export PDF** untuk mencetak laporan resmi Berita Acara A4 Landscape langsung ke pengambil kebijakan / manajemen.
2. Klik **Export Word** jika ingin mengedit atau menambahkan catatan khusus sebelum ditandatangani oleh Penanggung Jawab Ruangan dan Tim IT Auditor.
3. Klik **Export Excel** untuk merekapitulasi data seluruh titik jaringan dalam bentuk tabel dengan filter tanggal dan grade kelaikan.
