<?php
include 'config.php';

header('Content-Type: application/json'); // Set header for JSON response

// Get the tanggal parameter from the GET request
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$tanggal_pulang = isset($_GET['tanggal_pulang']) ? $_GET['tanggal_pulang'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Validate and sanitize input
if (empty($tanggal)) {
    echo json_encode(["error" => "Tanggal is required"]);
    exit();
}

// Prepare the SQL query with a parameterized date
if ($status != 4) {
    $query = "
        SELECT DISTINCT t.id, t.type, t.platmobil, t.warna
FROM typemobil t
LEFT JOIN jadwalmobil j ON t.id = j.mobil_id
LEFT JOIN pemohon p ON t.id = p.type
WHERE 
    (NOT EXISTS (
        SELECT 1
        FROM jadwalmobil j2
        WHERE j2.mobil_id = t.id
        AND (
            (? BETWEEN j2.tanggal AND j2.tanggal_pulang)
            OR
            (? BETWEEN j2.tanggal AND j2.tanggal_pulang)
            OR
            (j2.tanggal BETWEEN ? AND ? OR j2.tanggal_pulang BETWEEN ? AND ?)
            OR
            (j2.tanggal <= ? AND j2.tanggal_pulang >= ?)
        )
    )
    OR p.status = 6) -- Tambahkan kondisi untuk status pemohon
    ";
} else {
    $query = "
        SELECT t.id, t.type, t.platmobil, t.warna
        FROM typemobil t";
}

// Prepare statement and bind parameters to prevent SQL injection
$stmt = $connKendaraan->prepare($query);

// Pastikan hanya 7 parameter yang dibutuhkan
if ($status != 4) {
    $stmt->bind_param(
        'ssssssss',
        $tanggal,
        $tanggal_pulang,
        $tanggal,
        $tanggal_pulang,
        $tanggal,
        $tanggal_pulang,
        $tanggal,
        $tanggal_pulang
    );
}



// Execute query
$stmt->execute();

// Get result
$result = $stmt->get_result();

// Fetch results into an array
$results = array();
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}

// Output results in JSON format
echo json_encode($results);

// Close the statement and connection
$stmt->close();
$connKendaraan->close();
