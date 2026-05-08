<?php
// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti sesuai username MySQL kamu
define('DB_PASS', '');           // Ganti sesuai password MySQL kamu
define('DB_NAME', 'db_beasiswa');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die(json_encode(['error' => 'Koneksi database gagal: ' . mysqli_connect_error()]));
}

mysqli_set_charset($conn, 'utf8');

// Fungsi helper
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query Error: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    return $result;
}

function fetchAll($sql) {
    $result = query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function fetchOne($sql) {
    $result = query($sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>
