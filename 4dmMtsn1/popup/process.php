<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../' . ADMIN_LOGIN_PATH);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $link = clean_input($_POST['link'] ?? '');
            $timer = (int) ($_POST['timer'] ?? 5000);
            $status = (int) ($_POST['status'] ?? 1);

            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../../uploads/popups/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($fileExt, $allowed)) {
                    throw new Exception("Hanya file gambar (JPG, PNG, WEBP, GIF) yang diperbolehkan.");
                }

                $fileName = uniqid('popup_') . '.' . $fileExt;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/popups/' . $fileName;

                    $stmt = $pdo->prepare("INSERT INTO app_popup (image_path, link, timer, status) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$imagePath, $link, $timer, $status]);

                    $_SESSION['success'] = "Pop-up berhasil ditambahkan.";
                } else {
                    throw new Exception("Gagal mengupload gambar.");
                }
            } else {
                throw new Exception("Wajib memilih gambar untuk pop-up baru.");
            }

        } elseif ($action === 'update') {
            $id = (int) $_POST['id'];
            $link = clean_input($_POST['link'] ?? '');
            $timer = (int) ($_POST['timer'] ?? 5000);
            $status = (int) ($_POST['status'] ?? 1);
            $imagePath = $_POST['existing_image'];

            // Handle New Image Upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../../uploads/popups/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($fileExt, $allowed)) {
                    throw new Exception("Format file tidak valid.");
                }

                $fileName = uniqid('popup_') . '.' . $fileExt;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Delete old image if exists
                    if (!empty($imagePath) && file_exists('../../' . $imagePath)) {
                        unlink('../../' . $imagePath);
                    }
                    $imagePath = 'uploads/popups/' . $fileName;
                }
            }

            $stmt = $pdo->prepare("UPDATE app_popup SET image_path = ?, link = ?, timer = ?, status = ? WHERE id = ?");
            $stmt->execute([$imagePath, $link, $timer, $status, $id]);
            $_SESSION['success'] = "Pop-up berhasil diperbarui.";

        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];

            // Get image path first
            $stmt = $pdo->prepare("SELECT image_path FROM app_popup WHERE id = ?");
            $stmt->execute([$id]);
            $popup = $stmt->fetch();

            if ($popup) {
                if (!empty($popup['image_path']) && file_exists('../../' . $popup['image_path'])) {
                    unlink('../../' . $popup['image_path']);
                }

                $stmt = $pdo->prepare("DELETE FROM app_popup WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = "Pop-up berhasil dihapus.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
