<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all films from database
$films = $conn->query("SELECT * FROM films ORDER BY id DESC");

// Convert films to JSON for JavaScript use
$filmsArray = [];
while ($film = $films->fetch_assoc()) {
    $filmsArray[] = $film;
}
$filmsJson = json_encode($filmsArray);

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Democratische-bioscoop - Home</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/home.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-links">
        <a href="home.php" class="active">HOME</a>
        <a href="search.php">SEARCH</a>
        <a href="php/profile.php">PROFILE</a>
      </div>
    </nav>
  </header>

  <main>
    <div class="genre-tabs">
      <button class="genre-tab active" data-genre="action">Action</button>
      <button class="genre-tab" data-genre="comedy">Comedy</button>
      <button class="genre-tab" data-genre="scify">Scify</button>
    </div>

    <div class="movies-container">
      <!-- Movies will be loaded here by JavaScript -->
    </div>
  </main>

  <script>
    // Pass PHP data to JavaScript
    const databaseMovies = <?php echo $filmsJson; ?>;
  </script>
  <script src="js/home.js"></script>
</body>
</html>