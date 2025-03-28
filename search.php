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

// Default placeholder image as data URI
$placeholder_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZWVlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5OTk5Ij5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Democratische-bioscoop - Search</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/search.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-links">
        <a href="home.php">HOME</a>
        <a href="search.php" class="active">SEARCH</a>
        <a href="php/profile.php">PROFILE</a>
      </div>
    </nav>
  </header>

  <main>
    <div class="search-container">
      <div class="search-bar">
        <input type="text" id="search-input" placeholder="Search for movies...">
        <button id="search-btn">Search</button>
      </div>

      <div class="search-results" id="search-results">
        <!-- Search results will be loaded here dynamically -->
      </div>
    </div>
  </main>

  <script>
    // Pass PHP data to JavaScript
    const databaseMovies = <?php echo $filmsJson; ?>;
    const placeholderImage = '<?php echo $placeholder_image; ?>';
  </script>
  <script src="js/search.js"></script>
</body>
</html>