<?php 

// Databaseverbinding
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de verbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Controleer of het formulier is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verkrijg de waarden uit het formulier
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $url_trailer = $_POST["url_trailer"];
    $image_url = $_POST["image_url"];
    $votes = 0;  // Stel votes standaard in op 0
    $timestamp = time() * 1000;  // Huidige timestamp in milliseconden
    $date = time() * 1000;  // Huidige datum in milliseconden

    // SQL-query om de film toe te voegen
    $sql = "INSERT INTO films (title, description, category, url_trailer, image_url, votes, timestamp, date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Bereid de query voor en bind de parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiii", $title, $description, $category, $url_trailer, $image_url, $votes, $timestamp, $date);

    // Voer de query uit en controleer of het succesvol was
    if ($stmt->execute()) {
        echo "Film succesvol toegevoegd!";
        header("Location: ../castmembers.php");  // Redirect naar dashboard

    } else {
        echo "Fout bij het toevoegen van de film: " . $conn->error;
    }

    // Sluit de statement en de verbinding
    $stmt->close();
}

// Sluit de databaseverbinding
$conn->close();
?>