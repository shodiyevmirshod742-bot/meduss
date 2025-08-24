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

// Check if the query parameter is set
if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];

    // Sanitize the input to prevent SQL injection
    $searchQuery = mysqli_real_escape_string($conn, $searchQuery);

    // Query to search for patients by ID or name
    $sql = "SELECT * FROM patients WHERE patientId LIKE '%$searchQuery%' OR patientName LIKE '%$searchQuery%'";
    $result = mysqli_query($conn, $sql);

    // Check if there are any results
    if (mysqli_num_rows($result) > 0) {
        // Loop through the results and display them
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='search-result p-4 border-b border-gray-200 hover:bg-gray-100 rounded-lg'>";
            echo "<p class='text-lg font-semibold'>" . $row['patientName'] . " (ID: " . $row['patientId'] . ")</p>";
            echo "<p class='text-sm text-gray-600'>" . " Date of Birth: " . $row['dateOfBirth'] . "</p>";
            echo "<p class='text-sm text-gray-600'>" . " Date of Admission: " . $row['dateOfAdmission'] . "</p>";
            echo "<div class='flex items-center justify-between mt-2'>";
            // "Details" button now redirects to the patient's detailed page
            echo "<a href='view_patient.php?id=" . $row['patientId'] . "' class='bg-green-600 text-white py-1 px-4 rounded-full text-sm hover:bg-green-700'>Details</a>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        // If no results are found
        echo "<p class='text-gray-500'>No results found.</p>";
    }
}
?>