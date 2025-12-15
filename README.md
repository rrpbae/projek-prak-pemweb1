# JeRaDar - Project Game Web (Limbo Style)

Halo gais, ini adalah project game web **JeRaDar** yang konsepnya ngambil inspirasi dari game LIMBO. Jadi temanya dark-dark siluet gitu. Project ini dibuat pake PHP native sama MySQL, terus gamenya sendiri full pake logika HTML5 Canvas + JavaScript.

## 🎮 Fitur-fiturnya

### Gameplay
- **Visual Dark**: Grafisnya item putih estetik, ada efek partikel sama parallax backgroundnya juga biar smooth.
- **Endless Run**: Gamenya lari terus gak abis-abis, rintangannya digenerate random (ada kotak, duri, gergaji mesin).
- **Mekanik RPG Dikit**: Ada **Health Bar** sama **Stamina**. Kalo lompat staminanya kurang, kalo nabrak rintangan darahnya abis.
- **Save System**: Bisa **SAVE GAME**! Progres level & skor kesimpen di database, jadi kalo login lagi bisa lanjut (Cloud Save nih boss).

### Fitur Web
- **Login/Register**: Sistem autentikasi user biar datanya aman.
- **Admin Panel**: Ada dashboard khusus admin buat ngeliat list user yang daftar & ngehapus akun player kalo perlu.

## 🛠️ Tech Stack (Pake Apa Aja?)

- **Backend**: PHP Native (murni tanpa framework wkwk)
- **Database**: MySQL
- **Frontend**: HTML5, CSS Dasar, JavaScript (Canvas API)
- **Server**: XAMPP (Apache)

## 🚀 Cara Jalanin (Setup Guide)

1.  **Download project ini**, terus taro/copy foldernya ke `htdocs` (biasanya di `C:\xampp\htdocs`).

2.  **Setting Database**:
    - Buka `localhost/phpmyadmin` di browser.
    - Bikin database baru, kasih nama `limbo_db`.
    - Terus, klik tab **SQL** dan jalanin/copas codingan di bawah ini buat bikin tabelnya:

    **1. Tabel `users`** (buat nampung akun)
    ```sql
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('player', 'admin') DEFAULT 'player'
    );
    ```

    **2. Tabel `game_saves`** (buat nyimpen progres)
    ```sql
    CREATE TABLE game_saves (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        level INT DEFAULT 1,
        score INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ```

3.  **Cek Koneksi**:
    - Buka file `konek.php`, pastiin settingannya udah sesuai sama XAMPP kalian (biasanya usernya `root`, passwordnya kosong, dbnya `limbo_db`).

4.  **Gas Mainin**:
    - Nyalain Apache & MySQL di XAMPP Control Panel.
    - Buka browser, akses: `http://localhost/PrakPemweb/projek-prak-pemweb1/`

## 🕹️ Cara Main
- **Spasi / Klik Layar**: Lompat (Jump)
- **ESC**: Pause Game (Istirahat bentar)
- **Tombol SAVE**: Jangan lupa klik icon SAVE di pojok kanan atas game kalo mau udahan, biar skor terakhir kesimpen di server.

## 👥 Info Akun
- **Default Role**: Kalo baru daftar, otomatis jadi 'player'.
- **Admin Access**: Kalo mau nyobain fitur admin dashboard, ubah kolom `role` di tabel `users` dari 'player' jadi 'admin' lewat phpMyAdmin (manual edit ya).

---
*Project ini dibuat untuk tugas Praktikum Pemrograman Web 1.*