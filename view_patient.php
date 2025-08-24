<?php
// Include database connection
$servername = "localhost";
$username = "root";  // Replace with your DB username
$password = "root";  // Replace with your DB password
$dbname = "Myp";     // Replace with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the id parameter is set
if (isset($_GET['id'])) {
    $patientId = $_GET['id'];

    // Sanitize the input to prevent SQL injection
    $patientId = mysqli_real_escape_string($conn, $patientId);

    // Query to get the patient's details by ID
    $sql = "SELECT * FROM patients WHERE patientId = '$patientId'";
    $result = mysqli_query($conn, $sql);

    // Fetch the patient's details
    $patient = mysqli_fetch_assoc($result);
} else {
    echo "No patient found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="max-w-4xl mx-auto my-8 p-6 bg-white shadow-lg rounded-2xl">

        <!-- Header Section -->
        <h2 class="text-3xl font-semibold text-center mb-8">Patient Details</h2>

        <!-- Patient Info in Two Rows -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- First Row -->
            <div class="space-y-4">
                <div>
                    <p class="text-lg font-medium">Patient Name:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['patientName']; ?></p>
                </div>
                <div>
                    <p class="text-lg font-medium">Patient ID:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['patientId']; ?></p>
                </div>
                <div>
                    <p class="text-lg font-medium">Date of Birth:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['dateOfBirth']; ?></p>
                </div>
                <div>
                    <p class="text-lg font-medium">Date of Admission:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['dateOfAdmission']; ?></p>
                </div>
            </div>

            <!-- Second Row -->
            <div class="space-y-4">
                <div>
                    <p class="text-lg font-medium">Gender:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['gender']; ?></p>
                </div>
                <div>
                    <p class="text-lg font-medium">Height:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['height']; ?> cm</p>
                </div>
                <div>
                    <p class="text-lg font-medium">Weight:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['weight']; ?> kg</p>
                </div>
                <div>
                    <p class="text-lg font-medium">Blood Type:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['bloodType']; ?></p>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Third Row (continued) -->
            <div class="space-y-4">
                <div>
                    <p class="text-lg font-medium">Rh Factor:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['rhFactor']; ?></p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-lg font-medium">BMI:</p>
                    <p class="text-xl text-gray-700"><?php echo $patient['bmi']; ?></p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-center">
            <a href="reg.html" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Back to Search
            </a>
        </div>

    <div class="w-full bg-white shadow-lg py-4 flex justify-center space-x-4 fixed bottom-0 left-0">
    <a href="exce.php?patientId=<?php echo $patient['patientId']; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Лист назначение</a>
    <a href="protocol.php?patientId=<?php echo $patient['patientId']; ?>" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Протоколы</a>
    <a href="history.html" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">History</a>
    <a href="excel.php" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">Reports</a>
    <a href="delete.html" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</a>
    <a href="reg.html" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Back</a>
</div>
</body>

</html>