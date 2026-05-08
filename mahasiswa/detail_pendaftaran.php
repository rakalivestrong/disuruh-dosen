<?php
// detail_pendaftaran.php untuk mahasiswa - redirect ke admin version
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

// Verifikasi kepemilikan
$pendaftaran = fetchOne("SELECT p.id FROM pendaftaran p WHERE p.id = $id AND p.user_id = $userId");
if (!$pendaftaran) {
    header('Location: dashboard.php');
    exit;
}

// Tampilkan menggunakan file admin (shared), dengan parameter user
header("Location: ../admin/detail_pendaftaran.php?id=$id");
exit;
?>
