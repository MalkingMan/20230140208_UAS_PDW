<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan'; // Tetap di menu laporan

// Path download laporan
define('DOWNLOAD_DIR', '../uploads/laporan/');

// Panggil header
require_once 'templates/header.php';

// Pastikan ada ID Laporan di URL
if (!isset($_GET['id_laporan']) || empty($_GET['id_laporan'])) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>ID Laporan tidak valid.</div>";
    require_once 'templates/footer.php';
    exit();
}

$id_laporan = intval($_GET['id_laporan']);
$message = '';
$error = false;

// 2. Logika untuk menyimpan nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nilai'])) {
    $nilai = $_POST['nilai'];
    $feedback = trim($_POST['feedback']);

    // Validasi sederhana
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $message = "Nilai harus berupa angka antara 0 dan 100.";
        $error = true;
    } else {
        $sql = "UPDATE laporan SET nilai = ?, feedback = ?, tanggal_nilai = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $nilai, $feedback, $id_laporan);
        
        if ($stmt->execute()) {
            $message = "Nilai berhasil disimpan!";
        } else {
            $message = "Gagal menyimpan nilai.";
            $error = true;
        }
        $stmt->close();
    }
}


// 3. Mengambil data detail laporan untuk ditampilkan
$laporan_detail = null;
$sql_detail = "SELECT 
                    l.id, l.file_laporan, l.tanggal_kumpul, l.nilai, l.feedback,
                    u.nama AS nama_mahasiswa,
                    m.judul_modul,
                    p.nama_praktikum
               FROM laporan l
               JOIN users u ON l.id_mahasiswa = u.id
               JOIN modul m ON l.id_modul = m.id
               JOIN mata_praktikum p ON m.id_praktikum = p.id
               WHERE l.id = ?";

$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_laporan);
$stmt_detail->execute();
$result = $stmt_detail->get_result();

if ($result->num_rows > 0) {
    $laporan_detail = $result->fetch_assoc();
} else {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>Detail laporan tidak ditemukan.</div>";
    require_once 'templates/footer.php';
    exit();
}
$stmt_detail->close();
$conn->close();
?>

<!-- =================== PERUBAHAN DI SINI =================== -->
<?php if ($message): ?>
<div class="mb-6 px-4 py-3 rounded-md <?php echo $error ? 'bg-[#CD1C18] text-white' : 'bg-[#FFA896] text-[#38000A]'; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>
<!-- =================== AKHIR PERUBAHAN =================== -->

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <a href="laporan.php" class="text-blue-600 hover:underline mb-6 inline-block">&larr; Kembali ke Daftar Laporan</a>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($laporan_detail['judul_modul']); ?></h2>
        <p class="text-lg text-gray-500 mt-1"><?php echo htmlspecialchars($laporan_detail['nama_praktikum']); ?></p>
        
        <hr class="my-4">

        <div class="space-y-2">
            <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($laporan_detail['nama_mahasiswa']); ?></p>
            <p><strong>Dikumpulkan pada:</strong> <?php echo date('d F Y, H:i', strtotime($laporan_detail['tanggal_kumpul'])); ?></p>
        </div>
        
        <div class="mt-6">
            <a href="<?php echo DOWNLOAD_DIR . htmlspecialchars($laporan_detail['file_laporan']); ?>" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                Unduh File Laporan
            </a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Form Penilaian</h3>
        <form action="beri_nilai.php?id_laporan=<?php echo $id_laporan; ?>" method="post">
            <div class="mb-4">
                <label for="nilai" class="block text-gray-700 font-semibold mb-2">Nilai (0-100)</label>
                <input type="number" id="nilai" name="nilai" min="0" max="100" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($laporan_detail['nilai'] ?? ''); ?>" required>
            </div>
            <div class="mb-6">
                <label for="feedback" class="block text-gray-700 font-semibold mb-2">Feedback (Opsional)</label>
                <textarea id="feedback" name="feedback" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($laporan_detail['feedback'] ?? ''); ?></textarea>
            </div>
            <div>
                <button type="submit" name="submit_nilai" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Panggil footer
require_once 'templates/footer.php';
?>
