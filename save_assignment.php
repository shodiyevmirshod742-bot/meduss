<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Подавляем вывод ошибок в браузер, но логируем их
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

$host = "localhost";
$username = "root";
$password = "root";
$database = "myp";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подключения к БД']);
    exit;
}

// Получаем данные
$patientId      = $_POST['patientId'] ?? null;
$assignment     = $_POST['assignment'] ?? '';
$name           = $_POST['name'] ?? '';
$dose           = $_POST['dose'] ?? '';
$unit           = $_POST['unit'] ?? '';
$selected_date  = $_POST['selected_date'] ?? '';
$selected_time  = $_POST['selected_time'] ?? '';

if (!$patientId || !$name || !$dose || !$unit || !$selected_date || !$selected_time) {
    echo json_encode(['success' => false, 'message' => 'Не все данные заполнены']);
    exit;
}

// Запрос
$stmt = $conn->prepare("
    INSERT INTO assignments 
    (patientId, assignment, name, dose, unit, selected_date, selected_time) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса: ' . $conn->error]);
    exit;
}

$stmt->bind_param("issssss", $patientId, $assignment, $name, $dose, $unit, $selected_date, $selected_time);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Назначение сохранено']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении: ' . $stmt->error]);
}

$stmt->close();
$conn->close();