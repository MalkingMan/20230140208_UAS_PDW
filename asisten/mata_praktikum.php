<?php
// 1. Koneksi ke Database & Inisialisasi
require_once '../config.php';
$pageTitle = 'Kelola Mata Praktikum';
$activePage = 'praktikum'; // Untuk menyorot menu aktif di sidebar

// 2. Logika untuk memproses form (Create, Update, Delete)
$message = '';
$error = false;
$edit_data = null;

// Logika Tambah & Update Data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $nama_praktikum = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if (empty($nama_praktikum)) {
        $message = "Nama praktikum tidak boleh kosong!";
        $error = true;
    } else {
        if ($id) {
            // Proses Update
            $sql = "UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $id);
            $action = 'diperbarui';
        } else {
            // Proses Insert
            $sql = "INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $nama_praktikum, $deskripsi);
            $action = 'ditambahkan';
        }

        if ($stmt->execute()) {
            // Perbaikan: Gunakan double quotes dan pindahkan semicolon
            $message = "Data praktikum berhasil $action!";
        } else {
            $message = "Terjadi kesalahan saat menyimpan data.";
            $error = true;
        }
        $stmt->close();
    }
}

// Logika Delete Data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM mata_praktikum WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Data berhasil dihapus!";
    } else {
        $message = "Gagal menghapus data.";
        $error = true;
    }
    $stmt->close();
}

// Logika untuk Mode Edit (mengambil data yang akan diedit)
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM mata_praktikum WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}


// 3. Mengambil Semua Data Praktikum untuk Ditampilkan (Read)
$praktikum_list = [];
$sql = "SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum_list[] = $row;
    }
}

// 4. Panggil Header
require_once 'templates/header.php';
?>

<!-- Form Tambah/Edit dengan Gaya Baru -->
<div class="bg-white p-6 rounded-lg shadow-md mb-8 border border-[#38000A]">
    <h2 class="text-2xl font-bold text-[#38000A] mb-4"><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Mata Praktikum</h2>

    <!-- =================== PERUBAHAN DI SINI =================== -->
    <?php if ($message): ?>
    <div class="mb-4 px-4 py-3 rounded-md <?php echo $error ? 'bg-[#CD1C18] text-white' : 'bg-[#FFA896] text-[#38000A]'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    <!-- =================== AKHIR PERUBAHAN =================== -->
    
    <form action="mata_praktikum.php" method="post">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="nama_praktikum" class="block text-[#38000A] font-semibold mb-2">Nama Praktikum</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" class="w-full px-4 py-2 border border-[#38000A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CD1C18]" value="<?php echo htmlspecialchars($edit_data['nama_praktikum'] ?? ''); ?>" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-[#38000A] font-semibold mb-2">Deskripsi (Opsional)</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full px-4 py-2 border border-[#38000A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CD1C18]"><?php echo htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
        </div>
        <div>
            <button type="submit" name="submit" class="bg-[#CD1C18] hover:bg-[#9B1313] text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300">
                <?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="mata_praktikum.php" class="ml-4 text-[#CD1C18] hover:text-[#9B1313]">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel Daftar Praktikum dengan Gaya Baru -->
<div class="bg-white p-6 rounded-lg shadow-md border border-[#38000A]">
    <h2 class="text-2xl font-bold text-[#38000A] mb-4">Daftar Mata Praktikum</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-[#38000A] text-white">
                <tr>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">Nama Praktikum</th>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">Deskripsi</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[#38000A]">
                <?php if (empty($praktikum_list)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-4">Belum ada data mata praktikum.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($praktikum_list as $praktikum): ?>
                    <tr class="border-b border-[#38000A] hover:bg-[#FFA896]">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></td>
                        <td class="py-3 px-4">
                            <a href="mata_praktikum.php?action=edit&id=<?php echo $praktikum['id']; ?>" class="text-blue-500 hover:text-blue-700 font-semibold mr-4">Edit</a>
                            <a href="mata_praktikum.php?action=delete&id=<?php echo $praktikum['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
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