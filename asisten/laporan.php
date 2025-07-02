<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';

// Panggil header
require_once 'templates/header.php';

// 2. Mengambil data semua laporan yang masuk
$laporan_list = [];
$sql = "SELECT
            l.id,
            u.nama AS nama_mahasiswa,
            p.nama_praktikum,
            m.judul_modul,
            l.tanggal_kumpul,
            l.nilai
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum p ON m.id_praktikum = p.id
        ORDER BY l.tanggal_kumpul DESC";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $laporan_list[] = $row;
    }
}
$conn->close();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Laporan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Tgl Kumpul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Mahasiswa</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Praktikum</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Modul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Status</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($laporan_list)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Belum ada laporan yang dikumpulkan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporan_list as $laporan): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        
                        <td class="py-3 px-4 whitespace-nowrap"><?php echo date('d M Y H:i', strtotime($laporan['tanggal_kumpul'])); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['judul_modul']); ?></td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <?php if (isset($laporan['nilai'])): ?>
                                <span class="bg-green-200 text-green-800 font-semibold py-1 px-3 rounded-full text-xs">Sudah Dinilai</span>
                            <?php else: ?>
                                <span class="bg-yellow-200 text-yellow-800 font-semibold py-1 px-3 rounded-full text-xs">Belum Dinilai</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <a href="beri_nilai.php?id_laporan=<?php echo $laporan['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Lihat & Nilai
                            </a>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Panggil footer
require_once 'templates/footer.php';
?>