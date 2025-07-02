<?php
// 1. Panggil file konfigurasi dan header
require_once '../config.php';
$pageTitle = 'Kelola Akun Pengguna';
$activePage = 'akun';
require_once 'templates/header.php';

// 2. Inisialisasi variabel
$message = '';
$error = false;
$edit_data = null;

// 3. Logika untuk Create dan Update (Tidak ada perubahan di sini)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if (empty($nama) || empty($email) || empty($role)) {
        $message = "Nama, email, dan peran wajib diisi!";
        $error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
        $error = true;
    } else {
        if ($id) {
            $sql = "UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nama, $email, $role, $id);
            $action = 'diperbarui';
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql_pass = "UPDATE users SET password = ? WHERE id = ?";
                $stmt_pass = $conn->prepare($sql_pass);
                $stmt_pass->bind_param("si", $hashed_password, $id);
                $stmt_pass->execute();
                $stmt_pass->close();
            }
        } else {
            if (empty($password)) {
                $message = "Password wajib diisi untuk pengguna baru!";
                $error = true;
            } else {
                $sql_check = "SELECT id FROM users WHERE email = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("s", $email);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $message = "Email sudah terdaftar!";
                    $error = true;
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $sql = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                    $action = 'ditambahkan';
                }
                $stmt_check->close();
            }
        }

        if (!$error && isset($stmt) && $stmt->execute()) {
            $message = "Akun pengguna berhasil $action!";
        } elseif (!$error) {
            $message = "Gagal menyimpan data pengguna.";
            $error = true;
        }
        if (isset($stmt)) $stmt->close();
    }
}

// 4. Logika untuk Delete (Tidak ada perubahan di sini)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if ($_GET['id'] == $_SESSION['user_id']) {
        $message = "Anda tidak dapat menghapus akun Anda sendiri!";
        $error = true;
    } else {
        $id = $_GET['id'];
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Akun berhasil dihapus!";
        } else {
            $message = "Gagal menghapus akun.";
            $error = true;
        }
        $stmt->close();
    }
}

// 5. Logika untuk mode Edit (Tidak ada perubahan di sini)
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT id, nama, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// =================== PERUBAHAN LOGIKA PENGAMBILAN DATA ===================
// 6. Mengambil data pengguna dan memisahkannya berdasarkan peran

// Mengambil data Asisten
$asisten_list = [];
$sql_asisten = "SELECT id, nama, email, role FROM users WHERE role = 'asisten' ORDER BY nama ASC";
$result_asisten = $conn->query($sql_asisten);
if ($result_asisten->num_rows > 0) {
    while ($row = $result_asisten->fetch_assoc()) {
        $asisten_list[] = $row;
    }
}

// Mengambil data Mahasiswa
$mahasiswa_list = [];
$sql_mahasiswa = "SELECT id, nama, email, role FROM users WHERE role = 'mahasiswa' ORDER BY nama ASC";
$result_mahasiswa = $conn->query($sql_mahasiswa);
if ($result_mahasiswa->num_rows > 0) {
    while ($row = $result_mahasiswa->fetch_assoc()) {
        $mahasiswa_list[] = $row;
    }
}

$conn->close();
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold text-[#262626] mb-4"><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Akun Pengguna</h2>

    <?php if ($message): ?>
    <div class="mb-4 px-4 py-3 rounded-md <?php echo $error ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <form action="akun.php" method="post" class="space-y-4">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#e85d04] focus:ring-[#e85d04]">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#e85d04] focus:ring-[#e85d04]">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" <?php echo $edit_data ? '' : 'required'; ?> class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#e85d04] focus:ring-[#e85d04]">
            <?php if ($edit_data): ?>
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password.</p>
            <?php endif; ?>
        </div>
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Peran</label>
            <select id="role" name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#e85d04] focus:ring-[#e85d04]">
                <option value="mahasiswa" <?php echo (isset($edit_data) && $edit_data['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                <option value="asisten" <?php echo (isset($edit_data) && $edit_data['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
            </select>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" name="submit" class="inline-flex justify-center rounded-md border border-transparent bg-[#691F0C] py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-[#4a1607]">
                <?php echo $edit_data ? 'Update Akun' : 'Simpan Akun'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="akun.php" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>


<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold text-[#262626] mb-4">Daftar Akun Asisten</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#262626]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($asisten_list)): ?>
                    <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data asisten.</td></tr>
                <?php else: ?>
                    <?php foreach ($asisten_list as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="akun.php?action=edit&id=<?php echo $user['id']; ?>" class="text-[#691F0C] hover:text-[#4a1607] mr-4">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="akun.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold text-[#262626] mb-4">Daftar Akun Mahasiswa</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#262626]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($mahasiswa_list)): ?>
                    <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data mahasiswa.</td></tr>
                <?php else: ?>
                    <?php foreach ($mahasiswa_list as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="akun.php?action=edit&id=<?php echo $user['id']; ?>" class="text-[#691F0C] hover:text-[#4a1607] mr-4">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="akun.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
