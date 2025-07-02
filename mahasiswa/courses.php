<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';

// Panggil header mahasiswa
require_once 'templates/header_mahasiswa.php';

// 2. Mengambil data semua mata praktikum
$praktikum_list = [];
$sql = "SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum_list[] = $row;
    }
}
$conn->close();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Katalog Mata Praktikum</h1>
    <p class="mt-2 text-gray-600">Temukan dan daftar ke praktikum yang Anda minati.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if (empty($praktikum_list)): ?>
        <div class="col-span-full text-center py-10 bg-white rounded-lg shadow">
            <p class="text-gray-500">Saat ini belum ada mata praktikum yang tersedia.</p>
        </div>
    <?php else: ?>
        <?php foreach ($praktikum_list as $praktikum): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                <p class="text-gray-600 text-sm mb-4 min-h-[60px]">
                    <?php echo htmlspecialchars(substr($praktikum['deskripsi'], 0, 100)) . (strlen($praktikum['deskripsi']) > 100 ? '...' : ''); ?>
                </p>
                <div class="mt-4">
                    <a href="course_detail.php?id=<?php echo $praktikum['id']; ?>" class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-block transition-colors duration-300">
                        Lihat Detail & Daftar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// Panggil footer mahasiswa
require_once 'templates/footer_mahasiswa.php';
?>