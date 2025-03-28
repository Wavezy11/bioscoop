<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get film_id from URL parameter
$film_id = isset($_GET['film_id']) ? intval($_GET['film_id']) : 0;

if ($film_id <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch cast members for this film
$cast_members = [];
$stmt = $conn->prepare("SELECT * FROM cast_members WHERE film_id = ? ORDER BY order_in_credits");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();

while ($cast = $result->fetch_assoc()) {
    $cast_members[] = $cast;
}

// Close connection
$conn->close();

// Return cast members as JSON
echo json_encode($cast_members);
?>