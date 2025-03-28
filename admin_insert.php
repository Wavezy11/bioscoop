<?php
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
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $is_admin = isset($_POST["is_admin"]) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $password, $is_admin);

    if ($stmt->execute()) {
        echo "Gebruiker succesvol geregistreerd.";
    } else {
        echo "Fout bij registreren: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie</title>
</head>
<body>
    <h1>Registreer een nieuwe gebruiker</h1>
    <form method="POST" action="">
        <label for="username">Gebruikersnaam:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Wachtwoord:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="is_admin">Admin rechten:</label>
        <input type="checkbox" id="is_admin" name="is_admin"><br><br>

        <button type="submit">Registreren</button>
    </form>
</body>
</html>
