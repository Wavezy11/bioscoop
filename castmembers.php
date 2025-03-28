<?php
// Verbind met de database
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

// Verkrijg de film_id uit de URL
if (isset($_GET['film_id'])) {
    $film_id = $_GET['film_id'];
} else {
    echo "Geen film_id opgegeven!";
    exit();
}

// Controleer of het formulier voor de castleden is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verkrijg de waarden uit het formulier
    $name = $_POST["name"];
    $role = $_POST["role"];
    $image_url = $_POST["image_url"]; // Voeg de afbeelding URL toe
    $character_name = $_POST["character_name"]; // Optioneel: Karakter naam

    // SQL-query om het castlid toe te voegen
    $sql = "INSERT INTO cast_members (film_id, name, image_url, character_name) VALUES (?, ?, ?, ?)";

    // Bereid de query voor en bind de parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $film_id, $name, $image_url, $character_name);

    // Voer de query uit en controleer of het succesvol was
   
    // Sluit de statement
    $stmt->close();
}

// Sluit de databaseverbinding
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Castmember Toevoegen</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styling voor de container waarin het formulier zich bevindt */
.add-cast-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #fafafa;
  padding: 20px;
}

/* Card styling voor het formulier */
.add-cast-card {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 500px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Hover-effect voor de card */
.add-cast-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

/* Koptekst van het formulier */
.add-cast-header {
  text-align: center;
  margin-bottom: 30px;
}

.add-cast-header h1 {
  font-size: 26px;
  color: #222;
  margin-bottom: 10px;
}

.add-cast-header p {
  font-size: 16px;
  color: #777;
}

/* Formulier styling */
.add-cast-form .form-group {
  margin-bottom: 25px;
}

.add-cast-form label {
  display: block;
  margin-bottom: 10px;
  font-size: 16px;
  font-weight: 600;
  color: #444;
}

.add-cast-form input,
.add-cast-form textarea {
  width: 100%;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  font-size: 16px;
  color: #333;
  transition: border-color 0.3s ease, background-color 0.3s ease;
}

/* Focus-effect voor de invoervelden */
.add-cast-form input:focus,
.add-cast-form textarea:focus {
  border-color: #e50914;
  background-color: #fff;
  outline: none;
}

/* Placeholder styling */
.add-cast-form input::placeholder,
.add-cast-form textarea::placeholder {
  color: #aaa;
}

/* Knop styling */
.add-cast-btn {
  width: 100%;
  padding: 15px;
  background-color: #e50914;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.add-cast-btn:hover {
  background-color: #f40612;
  transform: translateY(-2px);
}

/* Responsieve aanpassingen voor kleinere schermen */
@media (max-width: 600px) {
  .add-cast-card {
    padding: 30px;
  }

  .add-cast-header h1 {
    font-size: 22px;
  }

  .add-cast-form input,
  .add-cast-form textarea {
    padding: 12px;
    font-size: 14px;
  }

  .add-cast-btn {
    font-size: 16px;
    padding: 14px;
  }
}

        </style>
</head>
<body>
<div class="add-cast-container">
        <div class="add-cast-card">
            <div class="add-cast-header">
                <h1>Voeg een Castmember Toe</h1>
                <p>Voeg een nieuw castlid toe voor de film.</p>
            </div>
            <form action="" method="POST">
                <div>
                    <label for="name">Naam van het Castlid:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="role">Rol van het Castlid:</label>
                    <input type="text" id="role" name="role" required>
                </div>
                <div>
                    <label for="image_url">Afbeelding URL:</label>
                    <input type="text" id="image_url" name="image_url" required>
                </div>
                <div>
                    <label for="character_name">Karakter Naam:</label>
                    <input type="text" id="character_name" name="character_name">
                </div>
                <button type="submit">Voeg Castlid Toe</button>
            </form>
            <?php 
             if ($stmt->execute()) {
                echo "<p>Castlid succesvol toegevoegd!</p>";
            } else {
                echo "<p>Fout bij het toevoegen van het castlid: " . $conn->error . "</p>";
            }
            
            ?>
        </div>
    </div>
</body>
</html>