<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';

// Panggil header mahasiswa
require_once 'templates/header_mahasiswa.php';

// 2. Mengambil data praktikum yang diikuti oleh mahasiswa yang login
$id_mahasiswa = $_SESSION['user_id'];
$praktikum_list = [];

$sql = "SELECT p.id, p.nama_praktikum, p.deskripsi 
        FROM mata_praktikum p
        JOIN pendaftaran d ON p.id = d.id_praktikum
        WHERE d.id_mahasiswa = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum_list[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Praktikum Saya</h1>
    <p class="mt-2 text-gray-600">Berikut adalah daftar semua praktikum yang sedang Anda ikuti.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if (empty($praktikum_list)): ?>
        <div class="col-span-full text-center py-10 bg-white rounded-lg shadow">
            <p class="text-gray-500">Anda belum terdaftar di praktikum manapun.</p>
            <a href="courses.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                Cari Praktikum Sekarang
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($praktikum_list as $praktikum): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
            <div class="p-6 flex flex-col h-full">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                <p class="text-gray-600 text-sm mb-4 flex-grow">
                    <?php echo htmlspecialchars(substr($praktikum['deskripsi'], 0, 100)) . (strlen($praktikum['deskripsi']) > 100 ? '...' : ''); ?>
                </p>
                <div class="mt-4">
                    <a href="course_detail.php?id=<?php echo $praktikum['id']; ?>" class="w-full text-center bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg inline-block transition-colors duration-300">
                        Masuk ke Praktikum
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