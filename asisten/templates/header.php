<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" href="../mahasiswa/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 1.5rem;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar h3 {
            color: #1e3a8a;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .sidebar p {
            text-align: center;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .sidebar nav ul {
            list-style: none;
            padding: 0;
        }
        .sidebar nav a {
            display: block;
            padding: 0.75rem 1rem;
            color: #111827;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar nav a:hover, .sidebar nav a.active {
            background-color: #1e3a8a;
            color: #ffffff;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>Panel Asisten</h3>
    <p><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="modul.php" class="<?php echo ($activePage == 'modul') ? 'active' : ''; ?>">Manajemen Modul</a></li>
            <li><a href="laporan.php" class="<?php echo ($activePage == 'laporan') ? 'active' : ''; ?>">Laporan Masuk</a></li>
            <li><a href="mata_praktikum.php" class="<?php echo ($activePage == 'praktikum') ? 'active' : ''; ?>">Kelola Praktikum</a></li>
            <li><a href="akun.php" class="<?php echo ($activePage == 'akun') ? 'active' : ''; ?>">Kelola Akun</a></li>
        </ul>
    </nav>
</div>

<div class="main-content">
    <header class="header">
        <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        <a href="../logout.php" class="btn btn-danger">Logout</a>
    </header>
    <main>