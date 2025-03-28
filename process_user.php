<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "films");
if ($mysqli->connect_error) {
    die("Verbinding mislukt: " . $mysqli->connect_error);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addUser();
        break;
    case 'make_admin':
        makeAdmin();
        break;
    case 'remove_admin':
        removeAdmin();
        break;
    case 'delete':
        deleteUser();
        break;
    default:
        $_SESSION['message'] = "Ongeldige actie.";
        header('Location: dashboard.php');
        exit();
}

function addUser() {
    global $mysqli;
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Controleer of alle vereiste velden zijn ingevuld
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = "Gebruikersnaam en wachtwoord zijn verplicht.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Controleer of de gebruikersnaam al bestaat
    $result = $mysqli->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Gebruikersnaam bestaat al.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Hash het wachtwoord
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Voeg de gebruiker toe
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $hashed_password, $is_admin);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Gebruiker succesvol toegevoegd.";
    } else {
        $_SESSION['message'] = "Fout bij het toevoegen van de gebruiker: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function makeAdmin() {
    global $mysqli;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        $_SESSION['message'] = "Geen gebruiker ID opgegeven.";
        header('Location: dashboard.php');
        exit();
    }
    
    if ($mysqli->query("UPDATE users SET is_admin = 1 WHERE id = $id")) {
        $_SESSION['message'] = "Gebruiker is nu een admin.";
    } else {
        $_SESSION['message'] = "Fout bij het updaten van de gebruiker: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function removeAdmin() {
    global $mysqli;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        $_SESSION['message'] = "Geen gebruiker ID opgegeven.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Voorkom dat de laatste admin wordt verwijderd
    $result = $mysqli->query("SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1");
    $row = $result->fetch_assoc();
    if ($row['admin_count'] <= 1) {
        $_SESSION['message'] = "Kan de laatste admin niet verwijderen.";
        header('Location: dashboard.php');
        exit();
    }
    
    if ($mysqli->query("UPDATE users SET is_admin = 0 WHERE id = $id")) {
        $_SESSION['message'] = "Admin rechten verwijderd.";
    } else {
        $_SESSION['message'] = "Fout bij het updaten van de gebruiker: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function deleteUser() {
    global $mysqli;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        $_SESSION['message'] = "Geen gebruiker ID opgegeven.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Controleer of het de huidige gebruiker is
    if ($id == $_SESSION['user_id']) {
        $_SESSION['message'] = "Je kunt je eigen account niet verwijderen.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Controleer of het de laatste admin is
    $result = $mysqli->query("SELECT is_admin FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
    
    if ($user['is_admin'] == 1) {
        $result = $mysqli->query("SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1");
        $row = $result->fetch_assoc();
        if ($row['admin_count'] <= 1) {
            $_SESSION['message'] = "Kan de laatste admin niet verwijderen.";
            header('Location: dashboard.php');
            exit();
        }
    }
    
    if ($mysqli->query("DELETE FROM users WHERE id = $id")) {
        $_SESSION['message'] = "Gebruiker succesvol verwijderd.";
    } else {
        $_SESSION['message'] = "Fout bij het verwijderen van de gebruiker: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

