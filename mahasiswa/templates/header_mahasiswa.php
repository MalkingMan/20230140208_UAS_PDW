<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a8a;
        }
        .navbar-nav {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .nav-link {
            color: #111827;
            font-weight: 500;
        }
        .nav-link.active {
            color: #1e3a8a;
            border-bottom: 2px solid #1e3a8a;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">SIMPRAK</a>
        <ul class="navbar-nav">
            <li><a href="dashboard.php" class="nav-link <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="my_courses.php" class="nav-link <?php echo ($activePage == 'my_courses') ? 'active' : ''; ?>">Praktikum Saya</a></li>
            <li><a href="courses.php" class="nav-link <?php echo ($activePage == 'courses') ? 'active' : ''; ?>">Cari Praktikum</a></li>
        </ul>
        <div>
            <span style="margin-right: 1rem; color: #6b7280;">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <header style="margin-bottom: 2rem;">
        <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    </header>
    <main>