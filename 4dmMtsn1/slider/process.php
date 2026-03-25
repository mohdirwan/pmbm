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
            $title = clean_input($_POST['title']);
            $description = clean_input($_POST['description']);
            $sort_order = (int) $_POST['sort_order'];
            $is_active = (int) $_POST['is_active'];

            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../../uploads/sliders/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($fileExt, $allowed)) {
                    throw new Exception("Hanya file gambar (JPG, PNG, WEBP) yang diperbolehkan.");
                }

                $fileName = uniqid('slider_') . '.' . $fileExt;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/sliders/' . $fileName;

                    $stmt = $pdo->prepare("INSERT INTO app_sliders (title, description, image_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $imagePath, $sort_order, $is_active]);

                    $_SESSION['success'] = "Slider berhasil ditambahkan.";
                } else {
                    throw new Exception("Gagal mengupload gambar.");
                }
            } else {
                throw new Exception("Wajib memilih gambar untuk slider baru.");
            }

        } elseif ($action === 'update') {
            $id = (int) $_POST['id'];
            $title = clean_input($_POST['title']);
            $description = clean_input($_POST['description']);
            $sort_order = (int) $_POST['sort_order'];
            $is_active = (int) $_POST['is_active'];
            $imagePath = $_POST['existing_image'];

            // Handle New Image Upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../../uploads/sliders/';
                $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($fileExt, $allowed)) {
                    throw new Exception("Format file tidak valid.");
                }

                $fileName = uniqid('slider_') . '.' . $fileExt;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Delete old image if exists
                    if (!empty($imagePath) && file_exists('../../' . $imagePath)) {
                        unlink('../../' . $imagePath);
                    }
                    $imagePath = 'uploads/sliders/' . $fileName;
                }
            }

            $stmt = $pdo->prepare("UPDATE app_sliders SET title = ?, description = ?, image_path = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $description, $imagePath, $sort_order, $is_active, $id]);
            $_SESSION['success'] = "Slider berhasil diperbarui.";

        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];

            // Get image path first
            $stmt = $pdo->prepare("SELECT image_path FROM app_sliders WHERE id = ?");
            $stmt->execute([$id]);
            $slider = $stmt->fetch();

            if ($slider) {
                if (!empty($slider['image_path']) && file_exists('../../' . $slider['image_path'])) {
                    unlink('../../' . $slider['image_path']);
                }

                $stmt = $pdo->prepare("DELETE FROM app_sliders WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = "Slider berhasil dihapus.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;


