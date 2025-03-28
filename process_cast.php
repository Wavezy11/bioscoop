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
        addCast();
        break;
    case 'edit':
        editCast();
        break;
    case 'delete':
        deleteCast();
        break;
    default:
        $_SESSION['message'] = "Ongeldige actie.";
        header('Location: dashboard.php');
        exit();
}

function addCast() {
    global $mysqli;
    
    $name = $_POST['name'] ?? '';
    $character_name = $_POST['character_name'] ?? null;
    $film_id = $_POST['film_id'] ?? 0;
    $image_url = $_POST['image_url'] ?? '';
    
    // Controleer of alle vereiste velden zijn ingevuld
    if (empty($name) || empty($film_id)) {
        $_SESSION['message'] = "Naam en film zijn verplicht.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Controleer of de film bestaat
    $result = $mysqli->query("SELECT * FROM films WHERE id = $film_id");
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Film niet gevonden.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Verwerk de afbeelding als er geen URL is opgegeven
    if (empty($image_url) && isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image_url = processImage();
    }
    
    // Voeg het cast lid toe
    $stmt = $mysqli->prepare("INSERT INTO cast_members (name, character_name, film_id, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $character_name, $film_id, $image_url);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Cast lid succesvol toegevoegd.";
    } else {
        $_SESSION['message'] = "Fout bij het toevoegen van het cast lid: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function editCast() {
    global $mysqli;
    
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $character_name = $_POST['character_name'] ?? null;
    $film_id = $_POST['film_id'] ?? 0;
    $image_url = $_POST['image_url'] ?? '';
    
    // Controleer of alle vereiste velden zijn ingevuld
    if (empty($id) || empty($name) || empty($film_id)) {
        $_SESSION['message'] = "ID, naam en film zijn verplicht.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Controleer of het cast lid bestaat
    $result = $mysqli->query("SELECT * FROM cast_members WHERE id = $id");
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Cast lid niet gevonden.";
        header('Location: dashboard.php');
        exit();
    }
    
    $cast = $result->fetch_assoc();
    
    // Controleer of de film bestaat
    $result = $mysqli->query("SELECT * FROM films WHERE id = $film_id");
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Film niet gevonden.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Verwerk de afbeelding als er een nieuwe is geüpload
    $new_image_url = $cast['image_url'];
    if (!empty($image_url)) {
        $new_image_url = $image_url;
    } else if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $new_image_url = processImage();
    }
    
    // Update het cast lid
    $stmt = $mysqli->prepare("UPDATE cast_members SET name = ?, character_name = ?, film_id = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $name, $character_name, $film_id, $new_image_url, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Cast lid succesvol bijgewerkt.";
    } else {
        $_SESSION['message'] = "Fout bij het bijwerken van het cast lid: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function deleteCast() {
    global $mysqli;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        $_SESSION['message'] = "Geen cast lid ID opgegeven.";
        header('Location: dashboard.php');
        exit();
    }
    
    if ($mysqli->query("DELETE FROM cast_members WHERE id = $id")) {
        $_SESSION['message'] = "Cast lid succesvol verwijderd.";
    } else {
        $_SESSION['message'] = "Fout bij het verwijderen van het cast lid: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function processImage() {
    // Standaard afbeelding URL als er geen afbeelding is geüpload
    $image_url = '/images/placeholder.jpg';
    
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $upload_dir = 'images/cast/';
        
        // Maak de map aan als deze niet bestaat
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = '/' . $target_file;
        }
    }
    
    return $image_url;
}

