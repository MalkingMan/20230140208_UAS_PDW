<?php
// 1. Koneksi & Inisialisasi
require_once '../config.php';
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';

// Path untuk upload
define('UPLOAD_DIR', '../uploads/materi/');

// 2. Logika Pemrosesan Form
$message = '';
$error = false;
$edit_data = null;

// Logika Tambah & Update Modul
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id_praktikum = $_POST['id_praktikum'];
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi_modul = trim($_POST['deskripsi_modul']);
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $file_materi_lama = isset($_POST['file_materi_lama']) ? $_POST['file_materi_lama'] : '';

    if (empty($id_praktikum) || empty($judul_modul)) {
        $message = "Mata praktikum dan judul modul wajib diisi!";
        $error = true;
    } else {
        $file_materi = $file_materi_lama;
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['file_materi']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['file_materi']['name']);
            $file_materi = $file_name;
            
            if (!move_uploaded_file($file_tmp, UPLOAD_DIR . $file_name)) {
                $message = "Gagal mengunggah file materi.";
                $error = true;
            } else {
                if ($id && !empty($file_materi_lama) && file_exists(UPLOAD_DIR . $file_materi_lama)) {
                    unlink(UPLOAD_DIR . $file_materi_lama);
                }
            }
        }

        if (!$error) {
            if ($id) {
                $sql = "UPDATE modul SET id_praktikum = ?, judul_modul = ?, deskripsi_modul = ?, file_materi = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssi", $id_praktikum, $judul_modul, $deskripsi_modul, $file_materi, $id);
                $action = 'diperbarui';
            } else {
                if (empty($file_materi)) {
                    $message = "File materi wajib diunggah untuk modul baru!";
                    $error = true;
                } else {
                    $sql = "INSERT INTO modul (id_praktikum, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isss", $id_praktikum, $judul_modul, $deskripsi_modul, $file_materi);
                    $action = 'ditambahkan';
                }
            }

            if (!$error && isset($stmt) && $stmt->execute()) {
                $message = "Modul berhasil $action!";
            } elseif (!$error) {
                $message = "Terjadi kesalahan saat menyimpan data ke database.";
                $error = true;
            }
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}

// Logika Hapus Modul
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql_select = "SELECT file_materi FROM modul WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $stmt_select->bind_result($file_materi);
    $stmt_select->fetch();
    $stmt_select->close();

    $sql_delete = "DELETE FROM modul WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    if ($stmt_delete->execute()) {
        if (!empty($file_materi) && file_exists(UPLOAD_DIR . $file_materi)) {
            unlink(UPLOAD_DIR . $file_materi);
        }
        $message = "Modul berhasil dihapus!";
    } else {
        $message = "Gagal menghapus modul.";
        $error = true;
    }
    $stmt_delete->close();
}

// Logika untuk Mode Edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM modul WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}


// 3. Mengambil Data untuk Tampilan
$modul_list = [];
$sql = "SELECT m.id, m.judul_modul, m.file_materi, p.nama_praktikum 
        FROM modul m 
        JOIN mata_praktikum p ON m.id_praktikum = p.id 
        ORDER BY p.nama_praktikum, m.created_at ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $modul_list[] = $row;
    }
}

$praktikum_options = [];
$sql_p = "SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result_p = $conn->query($sql_p);
if ($result_p->num_rows > 0) {
    while($row = $result_p->fetch_assoc()){
        $praktikum_options[] = $row;
    }
}


// 4. Panggil Header
require_once 'templates/header.php';
?>

<!-- Bagian HTML dengan Gaya Baru -->
<div class="bg-white p-6 rounded-lg shadow-md mb-8 border border-[#38000A]">
    <h2 class="text-2xl font-bold text-[#38000A] mb-4"><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Modul</h2>

    <!-- =================== PERUBAHAN DI SINI =================== -->
    <?php if ($message): ?>
    <div class="mb-4 px-4 py-3 rounded-md <?php echo $error ? 'bg-[#CD1C18] text-white' : 'bg-[#FFA896] text-[#38000A]'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    <!-- =================== AKHIR PERUBAHAN =================== -->
    
    <form action="modul.php" method="post" enctype="multipart/form-data">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <input type="hidden" name="file_materi_lama" value="<?php echo $edit_data['file_materi']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="id_praktikum" class="block text-[#38000A] font-semibold mb-2">Mata Praktikum</label>
            <select id="id_praktikum" name="id_praktikum" class="w-full px-4 py-2 border border-[#38000A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CD1C18]" required>
                <option value="">-- Pilih Mata Praktikum --</option>
                <?php foreach($praktikum_options as $prak): ?>
                <option value="<?php echo $prak['id']; ?>" <?php echo (isset($edit_data) && $edit_data['id_praktikum'] == $prak['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($prak['nama_praktikum']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="judul_modul" class="block text-gray-700 font-semibold mb-2">Judul Modul</label>
            <input type="text" id="judul_modul" name="judul_modul" class="w-full px-4 py-2 border border-[#38000A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CD1C18]" value="<?php echo htmlspecialchars($edit_data['judul_modul'] ?? ''); ?>" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi_modul" class="block text-gray-700 font-semibold mb-2">Deskripsi (Opsional)</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" rows="3" class="w-full px-4 py-2 border border-[#38000A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CD1C18]"><?php echo htmlspecialchars($edit_data['deskripsi_modul'] ?? ''); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-700 font-semibold mb-2">File Materi (PDF/DOCX)</label>
            <input type="file" id="file_materi" name="file_materi" class="w-full text-[#38000A]" accept=".pdf,.doc,.docx">
             <?php if ($edit_data && !empty($edit_data['file_materi'])): ?>
                <p class="text-sm text-gray-500 mt-2">File saat ini: <?php echo htmlspecialchars($edit_data['file_materi']); ?>. Kosongkan jika tidak ingin mengubah file.</p>
            <?php endif; ?>
        </div>
        <div>
            <button type="submit" name="submit" class="bg-[#CD1C18] hover:bg-[#9B1313] text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300">
                <?php echo $edit_data ? 'Update Modul' : 'Simpan Modul'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="modul.php" class="ml-4 text-[#CD1C18] hover:text-[#9B1313]">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md border border-[#38000A]">
    <h2 class="text-2xl font-bold text-[#38000A] mb-4">Daftar Modul</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-[#38000A] text-white">
                <tr>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Mata Praktikum</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Judul Modul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">File</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[#38000A]">
                <?php if (empty($modul_list)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">Belum ada modul yang ditambahkan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($modul_list as $modul): ?>
                    <tr class="border-b border-[#38000A] hover:bg-[#FFA896]">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($modul['nama_praktikum']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($modul['judul_modul']); ?></td>
                        <td class="py-3 px-4">
                            <a href="<?php echo UPLOAD_DIR . htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="text-[#CD1C18] hover:underline">
                                Lihat File
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <a href="modul.php?action=edit&id=<?php echo $modul['id']; ?>" class="text-[#CD1C18] hover:text-[#9B1313] font-semibold mr-4">Edit</a>
                            <a href="modul.php?action=delete&id=<?php echo $modul['id']; ?>" class="text-[#CD1C18] hover:text-[#9B1313] font-semibold" onclick="return confirm('Yakin ingin menghapus modul ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// 5. Panggil Footer
require_once 'templates/footer.php';
?>
