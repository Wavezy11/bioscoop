<?php
// Start de sessie
session_start();

// Database verbinding
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    die("Je moet ingelogd zijn om te kunnen stemmen.");
}

// Haal de gebruikers-ID op uit de sessie
$userId = $_SESSION['user_id'];

// Verwerk de stem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $movieId = intval($_POST['movie_id']);
    $action = $_POST['action'];

    if ($action === "vote") {
        // Controleer of de gebruiker al op een film heeft gestemd
        $checkVote = $conn->prepare("SELECT * FROM votes WHERE user_id = ?");
        $checkVote->bind_param("i", $userId);
        $checkVote->execute();
        $result = $checkVote->get_result();
        $checkVote->close();

        if ($result->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Je hebt al gestemd op een film."]);
            exit;
        }

        // Registreer de stem
        $stmt = $conn->prepare("UPDATE films SET votes = votes + 1 WHERE id = ?");
        $stmt->bind_param("i", $movieId);
        $stmt->execute();
        $stmt->close();

        // Voeg de stem toe aan de votes-tabel
        $stmt = $conn->prepare("INSERT INTO votes (user_id, movie_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $movieId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "message" => "Stem succesvol geregistreerd!"]);
        exit;
    }

    if ($action === "undo") {
        // Verwijder de stem
        $stmt = $conn->prepare("DELETE FROM votes WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("ii", $userId, $movieId);
        $stmt->execute();
        $stmt->close();

        // Verlaag het aantal stemmen
        $stmt = $conn->prepare("UPDATE films SET votes = votes - 1 WHERE id = ?");
        $stmt->bind_param("i", $movieId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "message" => "Stem succesvol verwijderd!"]);
        exit;
    }
}

// Haal alle films op uit de database
$sql = "SELECT * FROM films ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Democratische Bioscoop - Voting</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <script>
        async function vote(movieId, action) {
            try {
                const response = await fetch("voting.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `movie_id=${movieId}&action=${action}`
                });

                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            } catch (error) {
                console.error("Fout bij stemmen:", error);
                alert("Er is een fout opgetreden tijdens het stemmen.");
            }
        }
    </script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-links">
                <a href="home.php">HOME</a>
                <a href="search.php">SEARCH</a>
                <a href="profile.php">PROFILE</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="profile-container">
            <h2>Stem op de film die jij wilt zien</h2>
            <div class="movies-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="movie-card">
                        <img src="<?= $row['image_url'] ?>" alt="<?= $row['title'] ?>" class="movie-poster">
                        <div class="movie-info">
                            <h3 class="movie-title"><?= $row['title'] ?></h3>
                            <p class="movie-genre"><?= $row['category'] ?? "Geen categorie" ?></p>
                            <p class="movie-votes">Votes: <?= $row['votes'] ?></p>
                            <?php
                            // Controleer of de gebruiker al op een film heeft gestemd
                            $voteCheck = $conn->prepare("SELECT movie_id FROM votes WHERE user_id = ?");
                            $voteCheck->bind_param("i", $userId);
                            $voteCheck->execute();
                            $voteResult = $voteCheck->get_result();
                            $voteCheck->close();

                            $alreadyVoted = false;
                            $votedMovieId = null;
                            if ($voteResult->num_rows > 0) {
                                $voteData = $voteResult->fetch_assoc();
                                $alreadyVoted = true;
                                $votedMovieId = $voteData['movie_id'];
                            }

                            if ($alreadyVoted && $votedMovieId == $row['id']) {
                                echo '<button class="vote-btn" onclick="vote(' . $row['id'] . ', \'undo\')">Undo</button>';
                            } elseif (!$alreadyVoted) {
                                echo '<button class="vote-btn" onclick="vote(' . $row['id'] . ', \'vote\')">Vote</button>';
                            } else {
                                echo '<button class="vote-btn" disabled>Stemmen Niet Mogelijk</button>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <style>
  .vote-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

.vote-btn:hover {
    background-color: #0056b3;
}

.vote-btn:active {
    background-color: #003f7f;
}

.vote-btn[disabled] {
    background-color: #ccc;
    color: #666;
    cursor: not-allowed;
}

.undo-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

.undo-btn:hover {
    background-color: #bd2130;
}

.undo-btn:active {
    background-color: #a71d2a;
}

    </style>
</body>
</html>

<?php $conn->close(); ?>
