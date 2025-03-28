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

// Get movie ID from URL parameter
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, we'll rely on JavaScript's localStorage
$movie = null;
$cast_members = [];

// If we have an ID, fetch the movie and cast from database
if ($movie_id > 0) {
    // Fetch movie details
    $stmt = $conn->prepare("SELECT * FROM films WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $movie = $result->fetch_assoc();
        
        // Fetch cast members for this movie
        $cast_query = $conn->prepare("SELECT * FROM cast_members WHERE film_id = ? ORDER BY order_in_credits");
        $cast_query->bind_param("i", $movie_id);
        $cast_query->execute();
        $cast_result = $cast_query->get_result();
        
        while ($cast = $cast_result->fetch_assoc()) {
            $cast_members[] = $cast;
        }
    }
}

// Convert movie and cast to JSON for JavaScript use
$movieJson = json_encode($movie);
$castJson = json_encode($cast_members);

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
  <title>Democratische-bioscoop - Movie Details</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/movie-details.css">
  <style>
    /* Style for the vote button */
.vote-btn {
    display: inline-block; /* Makes the button an inline element with block properties */
    padding: 10px 20px; /* Adds padding around the text */
    background-color: #4CAF50; /* Green background color */
    color: white; /* White text color */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Makes the text bold */
    border: none; /* Removes default border */
    border-radius: 5px; /* Rounds the corners */
    text-decoration: none; /* Removes underline from link */
    transition: background-color 0.3s, transform 0.2s; /* Smooth transition for hover effects */
    cursor: pointer; /* Changes cursor to pointer on hover */
}
/* Hover effect */
.vote-btn:hover {
    background-color: #45a049; /* Darker green on hover */
    transform: scale(1.05); /* Slightly enlarges the button */
}
/* Active effect */
.vote-btn:active {
    transform: scale(0.95); /* Slightly shrinks the button when clicked */
}
    </style>
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-links">
        <a href="home.php">HOME</a>
        <a href="search.php">SEARCH</a>
        <a href="php/profile.php">PROFILE</a>
      </div>
    </nav>
  </header>

  <main>
    <div class="movie-details">
      <div class="movie-backdrop">
        <div class="movie-info">
          <h1 id="movie-title">Movie Title</h1>
          <div class="movie-meta">
            <span id="movie-type">Movie</span> | <span id="movie-genres">Genre</span>
          </div>
          <div class="movie-rating">
            <span id="movie-rating">4.0</span>
          </div>
          <p id="movie-description">Movie description will be loaded here.</p>
          <button class="watch-btn"><span>▶</span> WATCH NOW</button>
        </div>
      </div>

      <div class="movie-cast">
        <h2>Cast</h2>
        <div id="cast-list">
          <?php if (!empty($cast_members)): ?>
            <div class="cast-list">
              <?php foreach ($cast_members as $actor): ?>
                <div class="cast-item">
                  <img src="<?= htmlspecialchars($actor['image_url']) ?>" alt="<?= htmlspecialchars($actor['name']) ?>" 
                       class="cast-photo" onerror="this.onerror=null; this.src='<?= $placeholder_image ?>'">
                  <p class="cast-name"><?= htmlspecialchars($actor['name']) ?></p>
                  <p class="character-name"><?= htmlspecialchars($actor['character_name'] ?? '') ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <!-- If no cast members from PHP, JavaScript will handle this -->
        </div>
      </div>

      <div class="navigation-buttons">
        <a href="home.php" class="back-btn">BACK</a>
        <a href="voting.php" class="vote-btn">VOTE</a>
      </div>
    </div>
  </main>

  <script>
    // Pass PHP data to JavaScript
    const databaseMovie = <?php echo $movie ? $movieJson : 'null'; ?>;
    const databaseCast = <?php echo !empty($cast_members) ? $castJson : '[]'; ?>;
    const placeholderImage = '<?php echo $placeholder_image; ?>';
  </script>
  <script src="js/movie-details.js"></script>
</body>
</html>