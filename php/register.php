<?php
session_start(); // Start the session at the top of the file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";
// Verbinding maken
$conn = new mysqli($servername, $username, $password, $dbname);
// Controleer verbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Verwerk registratie
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm-password"];
    if ($password !== $confirm_password) {
        $_SESSION["register_error"] = "Wachtwoorden komen niet overeen.";
        header("Location: register.php");
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Controleer of de gebruikersnaam al bestaat
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION["register_error"] = "Deze gebruikersnaam is al in gebruik. Kies een andere.";
        header("Location: register.php");
        exit();
    } else {
        // Voeg de gebruiker toe
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ssi", $username, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<script>localStorage.setItem('isLoggedIn', 'true'); window.location.href = '../index.php';</script>";
            exit();
        } else {
            echo "Fout bij registreren: " . $conn->error;
        }
    }
    
    $stmt->close();
}
$conn->close();
?>