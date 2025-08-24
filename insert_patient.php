<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Myp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$patientId = $_POST['patientId'];
$patientName = $_POST['patientName'];
$dateOfBirth = $_POST['dateOfBirth'];
$dateOfAdmission = $_POST['dateOfAdmission'];
$gender = $_POST['gender'];
$height = $_POST['height'];
$weight = $_POST['weight'];
$bloodType = $_POST['bloodType'];
$rhFactor = $_POST['rhFactor'];
$bmi = $_POST['bmi'];

// Prepare SQL statement
$sql = "INSERT INTO patients (patientId, patientName, dateOfBirth, dateOfAdmission, gender, height, weight, bloodType, rhFactor, bmi) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssiissi", $patientId, $patientName, $dateOfBirth, $dateOfAdmission, $gender, $height, $weight, $bloodType, $rhFactor, $bmi);

if ($stmt->execute()) {
    echo "Patient added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>