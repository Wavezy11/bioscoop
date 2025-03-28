<?php
// Start session
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

// Verbinding maken
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verwerk login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashedPassword, $is_admin);
    
    if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["is_admin"] = $is_admin;
        
        // Stuur door op basis van is_admin waarde
        if ($is_admin == 1) {
            header("Location: dashboard.php");
        } else {
            header("Location: ../home.php");
        }
        exit();
    } else {
        echo "Ongeldige gebruikersnaam of wachtwoord.";
    }
    $stmt->close();
}

$conn->close();
?>
