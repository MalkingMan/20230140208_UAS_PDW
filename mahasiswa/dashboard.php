<?php
// 1. Panggil file konfigurasi dan header
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php';

// 2. Ambil ID mahasiswa dari session
$id_mahasiswa = $_SESSION['user_id'];

// =================== BAGIAN STATISTIK (TIDAK ADA PERUBAHAN) ===================
$praktikum_diikuti = 0;
$tugas_selesai = 0;
$total_modul = 0;

$sql_prak = "SELECT COUNT(id) AS total FROM pendaftaran WHERE id_mahasiswa = ?";
$stmt_prak = $conn->prepare($sql_prak);
$stmt_prak->bind_param("i", $id_mahasiswa);
$stmt_prak->execute();
$result_prak = $stmt_prak->get_result();
if ($result_prak->num_rows > 0) {
    $praktikum_diikuti = $result_prak->fetch_assoc()['total'];
}
$stmt_prak->close();

$sql_laporan = "SELECT COUNT(id) AS total FROM laporan WHERE id_mahasiswa = ?";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->bind_param("i", $id_mahasiswa);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
if ($result_laporan->num_rows > 0) {
    $tugas_selesai = $result_laporan->fetch_assoc()['total'];
}
$stmt_laporan->close();

if ($praktikum_diikuti > 0) {
    $sql_modul = "SELECT COUNT(m.id) AS total FROM modul m JOIN pendaftaran d ON m.id_praktikum = d.id_praktikum WHERE d.id_mahasiswa = ?";
    $stmt_modul = $conn->prepare($sql_modul);
    $stmt_modul->bind_param("i", $id_mahasiswa);
    $stmt_modul->execute();
    $result_modul = $stmt_modul->get_result();
    if($result_modul->num_rows > 0){
        $total_modul = $result_modul->fetch_assoc()['total'];
    }
    $stmt_modul->close();
}
$tugas_menunggu = $total_modul - $tugas_selesai;

// =================== LOGIKA NOTIFIKASI BARU (TANPA UNION) ===================
$notifikasi_list = [];

// Ambil notifikasi pendaftaran
$sql_daftar = "SELECT 'daftar' AS tipe, p.nama_praktikum AS teks_utama, d.id_praktikum AS link_id, d.tanggal_daftar AS tanggal
               FROM pendaftaran d JOIN mata_praktikum p ON d.id_praktikum = p.id
               WHERE d.id_mahasiswa = ?";
$stmt_daftar = $conn->prepare($sql_daftar);
$stmt_daftar->bind_param("i", $id_mahasiswa);
$stmt_daftar->execute();
$result_daftar = $stmt_daftar->get_result();
while($row = $result_daftar->fetch_assoc()) {
    $notifikasi_list[] = $row;
}
$stmt_daftar->close();

// Ambil notifikasi nilai
$sql_nilai = "SELECT 'nilai' AS tipe, m.judul_modul AS teks_utama, l.id_modul AS link_id, l.tanggal_nilai AS tanggal
              FROM laporan l JOIN modul m ON l.id_modul = m.id
              WHERE l.id_mahasiswa = ? AND l.nilai IS NOT NULL";
$stmt_nilai = $conn->prepare($sql_nilai);
$stmt_nilai->bind_param("i", $id_mahasiswa);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while($row = $result_nilai->fetch_assoc()) {
    $notifikasi_list[] = $row;
}
$stmt_nilai->close();

// Urutkan semua notifikasi berdasarkan tanggal secara descending (terbaru dulu)
usort($notifikasi_list, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// Ambil hanya 3 notifikasi teratas
$notifikasi_list = array_slice($notifikasi_list, 0, 3);

$conn->close();
?>

<div style="background: linear-gradient(to right, #3b82f6, #60a5fa); color: white; padding: 2rem; border-radius: 0.5rem; margin-bottom: 2rem;">
    <h2 style="font-size: 1.875rem; font-weight: 700;">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p style="margin-top: 0.5rem; opacity: 0.9;">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; font-weight: 800; color: #1e3a8a;"><?php echo $praktikum_diikuti; ?></div>
        <div style="margin-top: 0.5rem; color: #6b7280;">Praktikum Diikuti</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; font-weight: 800; color: #10b981;"><?php echo $tugas_selesai; ?></div>
        <div style="margin-top: 0.5rem; color: #6b7280;">Tugas Selesai</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; font-weight: 800; color: #facc15;"><?php echo $tugas_menunggu; ?></div>
        <div style="margin-top: 0.5rem; color: #6b7280;">Tugas Menunggu</div>
    </div>
</div>

<div class="card">
    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Notifikasi Terbaru</h3>
    <ul style="list-style: none; padding: 0;">
        <?php if (empty($notifikasi_list)): ?>
            <li style="text-align: center; color: #6b7280; padding: 2rem 0;">Belum ada notifikasi untuk Anda.</li>
        <?php else: ?>
            <?php foreach ($notifikasi_list as $notif): ?>
                <li style="border-bottom: 1px solid #f3f4f6; padding: 1rem 0;">
                    <?php if ($notif['tipe'] == 'daftar'): ?>
                        <span>✅ Anda berhasil mendaftar pada mata praktikum <strong><a href="course_detail.php?id=<?php echo $notif['link_id']; ?>"><?php echo htmlspecialchars($notif['teks_utama']); ?></a></strong>.</span>
                    <?php elseif ($notif['tipe'] == 'nilai' && !empty($notif['tanggal'])): ?>
                        <span>🔔 Nilai untuk <strong><a href="submit_assignment.php?id_modul=<?php echo $notif['link_id']; ?>"><?php echo htmlspecialchars($notif['teks_utama']); ?></a></strong> telah diberikan.</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>