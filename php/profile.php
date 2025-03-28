<?php
session_start();

// Databaseverbinding
$pdo = new PDO('mysql:host=localhost;dbname=films;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Haal de gebruikersinformatie op
$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Gebruiker niet gevonden.");
}

// Verwerk avatar upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $target_dir = "uploads/";
    $file_name = time() . '_' . basename($_FILES["avatar"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Controleer of het een afbeelding is
    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if ($check === false) {
        die("Bestand is geen afbeelding.");
    }
    
    // Verplaats het bestand
    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$target_file, $user_id]);
        
        // Redirect om de pagina te verversen
        header("Location: profile.php");
        exit();
    } else {
        die("Er was een probleem met het uploaden van je bestand.");
    }
}

// Gebruik standaard avatar als er geen is ingesteld
$avatar_path = !empty($user['avatar']) ? $user['avatar'] : 'img/avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Accountinstellingen</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-links">
                <a href="../home.php">HOME</a>
                <a href="../search.php">SEARCH</a>
                <a href="profile.php" class="active">PROFIEL</a>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?= htmlspecialchars($avatar_path) ?>" alt="Profiel Avatar">
                </div>
                <h1 id="user-name">Welkom, <?= htmlspecialchars($user['username']) ?></h1>
                <p>Hier kun je jouw profiel en avatar aanpassen.</p>
            </div>

            <form action="profile.php" method="post" enctype="multipart/form-data" class="avatar-form">
                <div class="form-group">
                    <label for="avatar">Wijzig je avatar:</label>
                    <input type="file" name="avatar" id="avatar" required>
                </div>
                <button type="submit" class="upload-btn">Upload Avatar</button>
            </form>

            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value" id="watched-count">0</span>
                    <span class="stat-label">Watched</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="watchlist-count">0</span>
                    <span class="stat-label">Watchlist</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="votes-count">0</span>
                    <span class="stat-label">Votes</span>
                </div>
            </div>

            <button id="logout-btn" class="logout-btn" onclick="window.location.href='logout.php'">Uitloggen</button>
        </div>
    </main>
</body>
</html>
