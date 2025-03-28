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
        addFilm();
        break;
    case 'edit':
        editFilm();
        break;
    case 'delete':
        deleteFilm();
        break;
    default:
        $_SESSION['message'] = "Ongeldige actie.";
        header('Location: dashboard.php');
        exit();
}

function addFilm() {
    global $mysqli;
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $url_trailer = $_POST['url_trailer'] ?? '';
    
    // Controleer of alle vereiste velden zijn ingevuld
    if (empty($title) || empty($description) || empty($category) || empty($url_trailer)) {
        $_SESSION['message'] = "Alle velden zijn verplicht.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Verwerk de afbeelding
    $image_url = processImage();
    
    // Huidige timestamp
    $timestamp = time() * 1000; // Milliseconden
    
    // Voeg toe aan de lokale database
    $stmt = $mysqli->prepare("INSERT INTO films (title, description, category, url_trailer, image_url, votes, timestamp, date) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
    $stmt->bind_param("sssssii", $title, $description, $category, $url_trailer, $image_url, $timestamp, $timestamp);
    
    if ($stmt->execute()) {
        $film_id = $mysqli->insert_id;
        
        // Voeg toe aan de externe API
        $apiData = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'url_trailer' => $url_trailer,
            'apikey' => 'P76BWGQysAgp5rxw'
        ];
        
        $apiResponse = sendToApi($apiData);
        
        if ($apiResponse['success']) {
            $_SESSION['message'] = "Film succesvol toegevoegd aan database en API.";
        } else {
            $_SESSION['message'] = "Film toegevoegd aan database, maar er was een probleem met de API: " . $apiResponse['message'];
        }
    } else {
        $_SESSION['message'] = "Fout bij het toevoegen van de film: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function editFilm() {
    global $mysqli;
    
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $url_trailer = $_POST['url_trailer'] ?? '';
    
    // Controleer of alle vereiste velden zijn ingevuld
    if (empty($id) || empty($title) || empty($description) || empty($category) || empty($url_trailer)) {
        $_SESSION['message'] = "Alle velden zijn verplicht.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Haal de huidige film op
    $result = $mysqli->query("SELECT * FROM films WHERE id = $id");
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Film niet gevonden.";
        header('Location: dashboard.php');
        exit();
    }
    
    $film = $result->fetch_assoc();
    
    // Verwerk de afbeelding als er een nieuwe is geüpload
    $image_url = $film['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image_url = processImage();
    }
    
    // Update de lokale database
    $stmt = $mysqli->prepare("UPDATE films SET title = ?, description = ?, category = ?, url_trailer = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $description, $category, $url_trailer, $image_url, $id);
    
    if ($stmt->execute()) {
        // Update de externe API
        $apiData = [
            'id' => $film['_id'] ?? '', // Gebruik het externe API ID als het beschikbaar is
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'url_trailer' => $url_trailer,
            'apikey' => 'P76BWGQysAgp5rxw'
        ];
        
        $apiResponse = updateInApi($apiData);
        
        if ($apiResponse['success']) {
            $_SESSION['message'] = "Film succesvol bijgewerkt in database en API.";
        } else {
            $_SESSION['message'] = "Film bijgewerkt in database, maar er was een probleem met de API: " . $apiResponse['message'];
        }
    } else {
        $_SESSION['message'] = "Fout bij het bijwerken van de film: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function deleteFilm() {
    global $mysqli;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        $_SESSION['message'] = "Geen film ID opgegeven.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Haal de huidige film op
    $result = $mysqli->query("SELECT * FROM films WHERE id = $id");
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Film niet gevonden.";
        header('Location: dashboard.php');
        exit();
    }
    
    $film = $result->fetch_assoc();
    
    // Verwijder uit de lokale database
    if ($mysqli->query("DELETE FROM films WHERE id = $id")) {
        // Verwijder uit de externe API
        $apiResponse = deleteFromApi($film['_id'] ?? '');
        
        if ($apiResponse['success']) {
            $_SESSION['message'] = "Film succesvol verwijderd uit database en API.";
        } else {
            $_SESSION['message'] = "Film verwijderd uit database, maar er was een probleem met de API: " . $apiResponse['message'];
        }
    } else {
        $_SESSION['message'] = "Fout bij het verwijderen van de film: " . $mysqli->error;
    }
    
    header('Location: dashboard.php');
    exit();
}

function processImage() {
    // Standaard afbeelding URL als er geen afbeelding is geüpload
    $image_url = '/images/placeholder.jpg';
    
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $upload_dir = 'images/';
        
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

function sendToApi($data) {
    $remoteServer = 'https://project-bioscoop-restservice.azurewebsites.net';
    $apiKey = 'P76BWGQysAgp5rxw';
    
    try {
        $ch = curl_init("$remoteServer/add/$apiKey");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'message' => $result['message'] ?? 'Onbekende fout',
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function updateInApi($data) {
    $remoteServer = 'https://project-bioscoop-restservice.azurewebsites.net';
    $apiKey = 'P76BWGQysAgp5rxw';
    
    try {
        $ch = curl_init("$remoteServer/update/$apiKey");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'message' => $result['message'] ?? 'Onbekende fout',
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function deleteFromApi($id) {
    if (empty($id)) {
        return ['success' => false, 'message' => 'Geen API ID beschikbaar'];
    }
    
    $remoteServer = 'https://project-bioscoop-restservice.azurewebsites.net';
    $apiKey = 'P76BWGQysAgp5rxw';
    
    try {
        $ch = curl_init("$remoteServer/delete/$apiKey/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'message' => $result['message'] ?? 'Onbekende fout',
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

