<?php
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = "root";
$database = "Myp";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$patientId = $_GET['patientId'] ?? null;

if ($patientId) {
    $stmt = $conn->prepare("SELECT * FROM assignments WHERE patientId = ?");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($assignments);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();