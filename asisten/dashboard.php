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

<!-- Kartu Statistik (Sudah Dinamis) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-[#e85d04]/10 p-3 rounded-full">
            <svg class="w-6 h-6 text-[#e85d04]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-[#262626]"><?php echo $total_modul; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-[#262626]"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-amber-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-[#262626]"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<!-- Tabel Aktivitas Terbaru (Sudah Dinamis) -->
<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-[#262626] mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if (empty($aktivitas_terbaru)): ?>
            <p class="text-center text-gray-500 py-4">Belum ada aktivitas laporan.</p>
        <?php else: ?>
            <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-[#d8c3a5] flex-shrink-0 flex items-center justify-center mr-4">
                        <span class="font-bold text-[#691F0C]">
                            <?php 
                                $words = explode(' ', $aktivitas['nama_mahasiswa']);
                                $initials = '';
                                if(isset($words[0])) $initials .= strtoupper(substr($words[0], 0, 1));
                                if(isset($words[1])) $initials .= strtoupper(substr($words[1], 0, 1));
                                echo $initials ?: 'U';
                            ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-800">
                            <strong><?php echo htmlspecialchars($aktivitas['nama_mahasiswa']); ?></strong> 
                            mengumpulkan laporan untuk 
                            <strong><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></strong>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?php 
                                $date = new DateTime($aktivitas['tanggal_kumpul']);
                                echo $date->format('d M Y, H:i');
                            ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Panggil Footer
require_once 'templates/footer.php';
?>