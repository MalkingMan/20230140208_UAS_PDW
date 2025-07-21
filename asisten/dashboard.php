<?php
// 1. Panggil config.php SECARA LANGSUNG di paling atas.
// Ini adalah perbaikan utama untuk memastikan $conn selalu tersedia.
require_once '../config.php';

// 2. Panggil header untuk tampilan.
// Variabel $pageTitle dan $activePage harus didefinisikan sebelum header.
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php';

// 3. Sekarang $conn sudah pasti tersedia, kita bisa menjalankan query
// Inisialisasi variabel
$total_modul = 0;
$total_laporan = 0;
$laporan_belum_dinilai = 0;

// Menghitung total modul
$result_modul = $conn->query("SELECT COUNT(id) AS total FROM modul");
if ($result_modul) $total_modul = $result_modul->fetch_assoc()['total'] ?? 0;

// Menghitung total laporan masuk
$result_laporan = $conn->query("SELECT COUNT(id) AS total FROM laporan");
if ($result_laporan) $total_laporan = $result_laporan->fetch_assoc()['total'] ?? 0;

// Menghitung laporan yang belum dinilai
$result_belum_dinilai = $conn->query("SELECT COUNT(id) AS total FROM laporan WHERE nilai IS NULL");
if ($result_belum_dinilai) $laporan_belum_dinilai = $result_belum_dinilai->fetch_assoc()['total'] ?? 0;


// 4. Query untuk mengambil aktivitas laporan terbaru (misal: 5 terakhir)
$aktivitas_terbaru = [];
$sql_aktivitas = "SELECT 
                    u.nama AS nama_mahasiswa, 
                    m.judul_modul,
                    l.tanggal_kumpul
                  FROM laporan l
                  JOIN users u ON l.id_mahasiswa = u.id
                  JOIN modul m ON l.id_modul = m.id
                  ORDER BY l.tanggal_kumpul DESC
                  LIMIT 5";

$result_aktivitas = $conn->query($sql_aktivitas);
if ($result_aktivitas && $result_aktivitas->num_rows > 0) {
    while ($row = $result_aktivitas->fetch_assoc()) {
        $aktivitas_terbaru[] = $row;
    }
}

$conn->close();
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
    <div class="card">
        <p style="color: #6b7280; font-size: 0.875rem;">Total Modul Diajarkan</p>
        <p style="font-size: 2rem; font-weight: 700;"><?php echo $total_modul; ?></p>
    </div>
    <div class="card">
        <p style="color: #6b7280; font-size: 0.875rem;">Total Laporan Masuk</p>
        <p style="font-size: 2rem; font-weight: 700;"><?php echo $total_laporan; ?></p>
    </div>
    <div class="card">
        <p style="color: #6b7280; font-size: 0.875rem;">Laporan Belum Dinilai</p>
        <p style="font-size: 2rem; font-weight: 700;"><?php echo $laporan_belum_dinilai; ?></p>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Aktivitas Laporan Terbaru</h2>
    <?php if (empty($aktivitas_terbaru)): ?>
        <p style="text-align: center; color: #6b7280;">Belum ada aktivitas laporan.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Modul</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($aktivitas['nama_mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></td>
                        <td><?php echo (new DateTime($aktivitas['tanggal_kumpul']))->format('d M Y, H:i'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Panggil Footer
require_once 'templates/footer.php';
?>