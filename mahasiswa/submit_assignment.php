<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Pengumpulan Tugas';
$activePage = ''; // Tidak ada menu aktif khusus

// Panggil header
require_once 'templates/header_mahasiswa.php';

// Pastikan ID Modul valid
if (!isset($_GET['id_modul']) || empty($_GET['id_modul'])) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>ID Modul tidak valid.</div>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}

$id_modul = intval($_GET['id_modul']);
$id_mahasiswa = $_SESSION['user_id'];
$message = '';
$error = false;

// 2. Logika Pengumpulan Laporan dengan Diagnostik Lengkap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_laporan'])) {
    
    // =================== BAGIAN DIAGNOSTIK DIMULAI DI SINI ===================
    
    // Langkah A: Bangun path folder tujuan dengan cara paling andal
    // dirname(__DIR__) akan menunjuk ke folder UAS_PDW dari /mahasiswa/
    $upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'laporan' . DIRECTORY_SEPARATOR;

    // Langkah B: Cek apakah direktori tujuan ada
    if (!is_dir($upload_dir)) {
        $message = "DIAGNOSIS GAGAL: Folder tujuan tidak ada. PHP mencari di: " . htmlspecialchars($upload_dir);
        $error = true;
    }
    // Langkah C: Cek apakah direktori tujuan bisa ditulisi
    elseif (!is_writable($upload_dir)) {
        $message = "DIAGNOSIS GAGAL: Folder tujuan tidak bisa ditulisi. Masalah izin (permissions). Cek folder: " . htmlspecialchars($upload_dir);
        $error = true;
    }
    // Langkah D: Cek error dari sisi PHP saat proses unggah awal
    elseif ($_FILES['file_laporan']['error'] !== UPLOAD_ERR_OK) {
        $message = "DIAGNOSIS GAGAL: Terjadi error internal saat proses unggah. Kode Error PHP: " . $_FILES['file_laporan']['error'];
        $error = true;
    }
    // Langkah E: Jika semua pemeriksaan lolos, baru coba pindahkan file
    else {
        $file_tmp = $_FILES['file_laporan']['tmp_name'];
        $file_name = $id_mahasiswa . '_' . time() . '_' . basename($_FILES['file_laporan']['name']);
        $destination = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            // Jika berhasil, baru simpan ke database
            $sql = "INSERT INTO laporan (id_modul, id_mahasiswa, file_laporan) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $id_modul, $id_mahasiswa, $file_name);
            if ($stmt->execute()) {
                $message = "Laporan berhasil dikumpulkan!";
            } else {
                $message = "Gagal menyimpan data ke database. Mungkin Anda sudah mengumpulkan laporan untuk modul ini.";
                $error = true;
                unlink($destination); // Hapus file jika gagal simpan DB
            }
            $stmt->close();
        } else {
            // Jika tetap gagal setelah semua cek lolos
            $message = "DIAGNOSIS GAGAL FINAL: Fungsi move_uploaded_file() gagal. Ini bisa disebabkan oleh antivirus atau konfigurasi keamanan server XAMPP.";
            $error = true;
        }
    }
    // =================== BAGIAN DIAGNOSTIK SELESAI ===================
}


// 3. Ambil data modul & status pengumpulan (Kode ini tidak diubah)
$modul = null;
$laporan = null;

// Ambil info modul
$sql_modul = "SELECT m.judul_modul, p.nama_praktikum FROM modul m JOIN mata_praktikum p ON m.id_praktikum = p.id WHERE m.id = ?";
$stmt_modul = $conn->prepare($sql_modul);
$stmt_modul->bind_param("i", $id_modul);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
if ($result_modul->num_rows > 0) {
    $modul = $result_modul->fetch_assoc();
} else {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg'>Modul tidak ditemukan.</div>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}
$stmt_modul->close();

// Cek apakah sudah pernah kumpul laporan
$sql_laporan = "SELECT file_laporan, tanggal_kumpul, nilai, feedback FROM laporan WHERE id_modul = ? AND id_mahasiswa = ?";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->bind_param("ii", $id_modul, $id_mahasiswa);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
if ($result_laporan->num_rows > 0) {
    $laporan = $result_laporan->fetch_assoc();
}
$stmt_laporan->close();

$conn->close();
?>

<div class="bg-white p-8 rounded-xl shadow-lg">
    <a href="javascript:history.back()" class="text-blue-600 hover:underline mb-6 inline-block">&larr; Kembali ke Detail Praktikum</a>
    <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($modul['judul_modul']); ?></h1>
    <p class="text-lg text-gray-500 mt-1">Praktikum: <?php echo htmlspecialchars($modul['nama_praktikum']); ?></p>

    <hr class="my-6">

    <?php if ($message): ?>
    <div class="mb-6 px-4 py-3 rounded-md <?php echo $error ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <?php if ($laporan): ?>
        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
            <h3 class="text-xl font-bold text-blue-800">Status Pengumpulan</h3>
            <p class="mt-2 text-gray-700">Anda telah mengumpulkan laporan pada:</p>
            <p class="font-semibold text-lg"><?php echo date('d F Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></p>
            <a href="../uploads/laporan/<?php echo htmlspecialchars($laporan['file_laporan']); ?>" target="_blank" class="text-blue-600 hover:underline mt-2 inline-block">Lihat File Terkumpul</a>

            <div class="mt-4 pt-4 border-t border-blue-200">
                <h4 class="font-bold">Nilai & Feedback</h4>
                <?php if (isset($laporan['nilai'])): ?>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $laporan['nilai']; ?></p>
                    <p class="mt-2 text-gray-600"><strong>Feedback dari Asisten:</strong> <?php echo nl2br(htmlspecialchars($laporan['feedback'])); ?></p>
                <?php else: ?>
                    <p class="text-gray-500 mt-2">Laporan Anda belum dinilai oleh asisten.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div>
            <h3 class="text-xl font-bold text-gray-800">Unggah File Laporan Anda</h3>
            <p class="text-gray-600 mt-1">Pastikan file dalam format yang diizinkan (PDF, DOC, DOCX).</p>
            <form action="submit_assignment.php?id_modul=<?php echo $id_modul; ?>" method="post" enctype="multipart/form-data" class="mt-4">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <input type="file" name="file_laporan" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100" required>
                </div>
                <button type="submit" class="mt-6 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                    Kumpulkan Laporan
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
// Panggil footer
require_once 'templates/footer_mahasiswa.php';
?>