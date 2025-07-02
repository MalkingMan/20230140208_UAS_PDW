<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Detail Praktikum';
$activePage = 'courses'; // Tetap aktifkan menu 'courses'

// Panggil header mahasiswa
require_once 'templates/header_mahasiswa.php';

// Pastikan ada ID praktikum di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>ID praktikum tidak valid.</div>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}

$id_praktikum = intval($_GET['id']);
$id_mahasiswa = $_SESSION['user_id'];
$message = '';
$error = false;

// 2. Logika untuk mendaftar praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    // Cek lagi apakah sudah terdaftar
    $sql_check = "SELECT id FROM pendaftaran WHERE id_mahasiswa = ? AND id_praktikum = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $sql_daftar = "INSERT INTO pendaftaran (id_mahasiswa, id_praktikum) VALUES (?, ?)";
        $stmt_daftar = $conn->prepare($sql_daftar);
        $stmt_daftar->bind_param("ii", $id_mahasiswa, $id_praktikum);
        if ($stmt_daftar->execute()) {
            $message = "Pendaftaran berhasil! Anda sekarang terdaftar di praktikum ini.";
        } else {
            $message = "Gagal mendaftar. Silakan coba lagi.";
            $error = true;
        }
        $stmt_daftar->close();
    }
    $stmt_check->close();
}

// 3. Mengambil data detail praktikum
$praktikum = null;
$sql = "SELECT nama_praktikum, deskripsi FROM mata_praktikum WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_praktikum);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $praktikum = $result->fetch_assoc();
} else {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>Praktikum tidak ditemukan.</div>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}
$stmt->close();

// 4. Cek status pendaftaran mahasiswa
$is_registered = false;
$sql_status = "SELECT id FROM pendaftaran WHERE id_mahasiswa = ? AND id_praktikum = ?";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("ii", $id_mahasiswa, $id_praktikum);
$stmt_status->execute();
$result_status = $stmt_status->get_result();
if ($result_status->num_rows > 0) {
    $is_registered = true;
}
$stmt_status->close();


// 5. Jika sudah terdaftar, ambil daftar modul
$modul_list = [];
if ($is_registered) {
    $sql_modul = "SELECT id, judul_modul, deskripsi_modul, file_materi FROM modul WHERE id_praktikum = ? ORDER BY created_at ASC";
    $stmt_modul = $conn->prepare($sql_modul);
    $stmt_modul->bind_param("i", $id_praktikum);
    $stmt_modul->execute();
    $result_modul = $stmt_modul->get_result();
    if ($result_modul->num_rows > 0) {
        while ($row = $result_modul->fetch_assoc()) {
            $modul_list[] = $row;
        }
    }
    $stmt_modul->close();
}

$conn->close();
?>

<?php if ($message): ?>
<div class="mb-6 px-4 py-3 rounded-md <?php echo $error ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="bg-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h1>
    <p class="mt-4 text-gray-600"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>

    <?php if (!$is_registered): ?>
    <form action="course_detail.php?id=<?php echo $id_praktikum; ?>" method="post" class="mt-6">
        <button type="submit" name="daftar" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-lg transition-colors duration-300">
            Daftar ke Praktikum Ini
        </button>
    </form>
    <?php else: ?>
    <div class="mt-6 inline-block bg-blue-100 text-blue-800 font-semibold py-2 px-4 rounded-full">
        <span class="mr-2">✓</span> Anda sudah terdaftar
    </div>
    <?php endif; ?>
</div>

<?php if ($is_registered): ?>
<div class="bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Modul & Tugas</h2>
    
    <?php if (empty($modul_list)): ?>
        <p class="text-gray-500">Belum ada modul yang ditambahkan untuk praktikum ini.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($modul_list as $modul): ?>
            <div class="border border-gray-200 p-4 rounded-lg flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($modul['judul_modul']); ?></h4>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($modul['deskripsi_modul']); ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                        Unduh Materi
                    </a>
                    <a href="submit_assignment.php?id_modul=<?php echo $modul['id']; ?>" class="bg-gray-700 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                        Kumpulkan Tugas
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
// Panggil footer mahasiswa
require_once 'templates/footer_mahasiswa.php';
?>