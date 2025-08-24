<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$patientId = isset($_POST["patientId"]) ? $_POST["patientId"] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    // You can modify this based on your logic
    $patientId = isset($_POST["patientId"]) ? $_POST["patientId"] : null;
    $protocolName = $_POST["protocolName"];
    $complaintData = $_POST["complaintData"];
    $medicalHistory = $_POST["medicalHistory"];
    $lifeHistory = $_POST["lifeHistory"];
    $diagnosis = $_POST["diagnosis"];
    $conclusion = $_POST["conclusion"];
    $therapy = $_POST["therapy"];
    $recommendations = $_POST["recommendations"];
    $generalCondition = $_POST["generalCondition"];
    $consciousness = $_POST["consciousness"];
    $nutrition = $_POST["nutrition"];
    $additionalInfo = $_POST["additionalInfo"];
    $skinColor = $_POST["skinColor"];
    $skinMoisture = $_POST["skinMoisture"];
    $turgor = $_POST["turgor"];
    $edema = $_POST["edema"];
    $respiratoryRhythm = $_POST["respiratoryRhythm"];
    $respiratoryRate = $_POST["respiratoryRate"];
    $SPO2 = $_POST["SPO2"];
    $breathingNature = $_POST["breathingNature"];
    $wheezing = $_POST["wheezing"];
    $pleuralFrictionNoise = $_POST["pleuralFrictionNoise"];
    $respiratoryAdditionalInfo = $_POST["respiratoryAdditionalInfo"];
    $hemodynamics = $_POST["hemodynamics"];
    $systolicPressure = $_POST["systolicPressure"];
    $diastolicPressure = $_POST["diastolicPressure"];
    $heartRate = $_POST["heartRate"];
    $heartRhythm = $_POST["heartRhythm"];
    $heartTones = $_POST["heartTones"];
    $CardioAdditionalInfo = $_POST["CardioAdditionalInfo"];
    $tongueColor = $_POST["tongueColor"];
    $bellySize = $_POST["bellySize"];
    $abdomenPalpation = $_POST["abdomenPalpation"];
    $abdominalPainPalpation = $_POST["abdominalPainPalpation"];
    $stoolSinceOnset = $_POST["stoolSinceOnset"];
    $liver = $_POST["liver"];
    $protrusionFromRibArc = $_POST["protrusionFromRibArc"];
    $urination = $_POST["urination"];
    $natureOfUrination = $_POST["natureOfUrination"];
    $urineColor = $_POST["urineColor"];

    // Ensure $patientId is a valid integer or set it to NULL
    $patientId = is_numeric($patientId) ? $patientId : null;

    // Insert data into the 'protocols' table
    $host = "localhost";
    $username = "root";
    $password = "root";
    $database = "Myp";

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the patientId already exists in the protocols table
    $checkExistingSql = "SELECT protocolId FROM protocols WHERE patientId = ? ORDER BY protocolId DESC LIMIT 1";
    $checkExistingStmt = $conn->prepare($checkExistingSql);

    if ($checkExistingStmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $checkExistingStmt->bind_param("i", $patientId);
    $checkExistingStmt->execute();
    $checkExistingStmt->bind_result($maxProtocolId);
    $checkExistingStmt->fetch();
    $checkExistingStmt->close();

    // Increment the maximum protocolId or start from 1 if none exists
$newProtocolId = ($maxProtocolId !== null) ? $maxProtocolId + 1 : 1;

    $sql = "INSERT INTO protocols (patientId, protocolId, protocolName, complaintData, medicalHistory, lifeHistory, diagnosis, conclusion, therapy, recommendations, generalCondition, consciousness, nutrition, additionalInfo, skinColor, skinMoisture, turgor, edema, respiratoryRhythm, respiratoryRate, SPO2, breathingNature, wheezing, pleuralFrictionNoise, respiratoryAdditionalInfo, hemodynamics, systolicPressure, diastolicPressure, heartRate, heartRhythm, heartTones, CardioAdditionalInfo, tongueColor, bellySize, abdomenPalpation, abdominalPainPalpation, stoolSinceOnset, liver, protrusionFromRibArc, urination, natureOfUrination, urineColor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("iisssssssssssssssssiisssssiiisssssssssisss", $patientId, $newProtocolId, $protocolName, $complaintData, $medicalHistory, $lifeHistory, $diagnosis, $conclusion, $therapy, $recommendations, $generalCondition, $consciousness, $nutrition, $additionalInfo, $skinColor, $skinMoisture, $turgor, $edema, $respiratoryRhythm, $respiratoryRate, $SPO2, $breathingNature, $wheezing, $pleuralFrictionNoise, $respiratoryAdditionalInfo, $hemodynamics, $systolicPressure, $diastolicPressure, $heartRate, $heartRhythm, $heartTones, $CardioAdditionalInfo, $tongueColor, $bellySize, $abdomenPalpation, $abdominalPainPalpation, $stoolSinceOnset, $liver, $protrusionFromRibArc, $urination, $natureOfUrination, $urineColor);

 // Execute the statement for insertion
    if ($stmt->execute()) {
        echo "Protocol saved successfully";
    } else {
        // Handle the case when insertion fails
        echo "Error inserting protocol: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>