<?php
session_start();

// Databaseverbinding met MySQLi
$mysqli = new mysqli("localhost", "root", "", "films");

if ($mysqli->connect_error) {
    die("Verbinding mislukt: " . $mysqli->connect_error);
}

// Controleer of de gebruiker al ingelogd is
if (isset($_SESSION['user_id'])) {
    // Gebruiker is ingelogd, dus redirect naar het dashboard of profiel
    if ($_SESSION['is_admin'] == 1) {
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: home.php');  // Vervang met een andere pagina voor gewone gebruikers
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Haal de gebruiker op inclusief 'is_admin' kolom
    $sql = "SELECT id, username, password, is_admin FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Sla gebruikersgegevens op in de sessie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];  // Sla de admin-status op in de sessie

        // Redirect naar de juiste pagina op basis van admin-status
        if ($user['is_admin'] == 1) {
            header('Location: dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = "Ongeldige gebruikersnaam of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Democratische-bioscoop - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>LOGIN</h1>
                <p>Don't have an account? <a href="register.php">REGISTER</a></p>
            </div>
            
            <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
            
            <form action="" method="post" class="login-form">
                <div class="form-group">
                    <label for="username">USERNAME</label>
                    <input type="text" id="username" name="username" placeholder="username" required>
                </div>
                <div class="form-group">
                    <label for="password">PASSWORD <span class="forgot"><a href="#">FORGOT?</a></span></label>
                    <input type="password" id="password" name="password" placeholder="password" required>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
