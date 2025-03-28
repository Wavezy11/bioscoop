<?php
// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "films";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete operations
if (isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: dashboard.php?tab=users&message=User deleted successfully");
    exit();
}

if (isset($_POST['delete_film'])) {
    $id = $_POST['film_id'];
    $conn->query("DELETE FROM films WHERE id = $id");
    header("Location: dashboard.php?tab=films&message=Film deleted successfully");
    exit();
}

if (isset($_POST['delete_cast'])) {
    $id = $_POST['cast_id'];
    $conn->query("DELETE FROM cast_members WHERE id = $id");
    header("Location: dashboard.php?tab=cast&message=Cast member deleted successfully");
    exit();
}

// Handle add/edit operations
if (isset($_POST['add_user']) || isset($_POST['edit_user'])) {
    $username = $_POST['username'];
    $password = isset($_POST['password']) && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $avatar = $_POST['avatar'] ?? 'uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png';
    
    if (isset($_POST['add_user'])) {
        if (empty($password)) {
            header("Location: dashboard.php?tab=users&error=Password is required for new users");
            exit();
        }
        $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin, avatar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $username, $password, $is_admin, $avatar);
        $stmt->execute();
        header("Location: dashboard.php?tab=users&message=User added successfully");
    } else {
        $id = $_POST['user_id'];
        if (!empty($password)) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, is_admin = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("ssisi", $username, $password, $is_admin, $avatar, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, is_admin = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("sisi", $username, $is_admin, $avatar, $id);
        }
        $stmt->execute();
        header("Location: dashboard.php?tab=users&message=User updated successfully");
    }
    exit();
}

if (isset($_POST['add_film']) || isset($_POST['edit_film'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $url_trailer = $_POST['url_trailer'];
    $image_url = $_POST['image_url'];
    $timestamp = time() * 1000; // Current timestamp in milliseconds
    $date = isset($_POST['date']) ? $_POST['date'] : date('Y');
    
    if (isset($_POST['add_film'])) {
        // Add film to local database
        $stmt = $conn->prepare("INSERT INTO films (title, description, category, url_trailer, image_url, timestamp, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $title, $description, $category, $url_trailer, $image_url, $timestamp, $date);
        $stmt->execute();
        $film_id = $conn->insert_id;
        
        // Add film to API
        $apiData = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'url_trailer' => $url_trailer,
            'apikey' => 'P76BWGQysAgp5rxw',
            '_id' => $film_id // Link to our database ID
        ];
        
        $ch = curl_init('https://project-bioscoop-restservice.azurewebsites.net/add/P76BWGQysAgp5rxw');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        
        header("Location: dashboard.php?tab=films&message=Film added successfully to database and API");
    } else {
        $id = $_POST['film_id'];
        $stmt = $conn->prepare("UPDATE films SET title = ?, description = ?, category = ?, url_trailer = ?, image_url = ?, date = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $title, $description, $category, $url_trailer, $image_url, $date, $id);
        $stmt->execute();
        header("Location: dashboard.php?tab=films&message=Film updated successfully");
    }
    exit();
}

if (isset($_POST['add_cast']) || isset($_POST['edit_cast'])) {
    $name = $_POST['name'];
    $image_url = $_POST['image_url'];
    $film_id = $_POST['film_id'];
    $character_name = $_POST['character_name'];
    $order_in_credits = $_POST['order_in_credits'] ?? null;
    
    if (isset($_POST['add_cast'])) {
        $stmt = $conn->prepare("INSERT INTO cast_members (name, image_url, film_id, character_name, order_in_credits) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $name, $image_url, $film_id, $character_name, $order_in_credits);
        $stmt->execute();
        header("Location: dashboard.php?tab=cast&message=Cast member added successfully");
    } else {
        $id = $_POST['cast_id'];
        $stmt = $conn->prepare("UPDATE cast_members SET name = ?, image_url = ?, film_id = ?, character_name = ?, order_in_credits = ? WHERE id = ?");
        $stmt->bind_param("ssisii", $name, $image_url, $film_id, $character_name, $order_in_credits, $id);
        $stmt->execute();
        header("Location: dashboard.php?tab=cast&message=Cast member updated successfully");
    }
    exit();
}

// Get data for display
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
$films = $conn->query("SELECT * FROM films ORDER BY id DESC");
$cast_members = $conn->query("SELECT c.*, f.title as film_title FROM cast_members c JOIN films f ON c.film_id = f.id ORDER BY c.id DESC");
$films_for_select = $conn->query("SELECT id, title FROM films ORDER BY title");

// Determine active tab
$active_tab = $_GET['tab'] ?? 'users';

// Default placeholder image as data URI
$placeholder_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZWVlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5OTk5Ij5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bioscoop Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
    /* Algemene stijlen */
body {
    padding-top: 20px;
    background-color: #121212; /* Donkere achtergrond voor consistentie */
    color: black; /* Witte tekst om contrast te behouden */
    min-height: 100vh;
}

/* Dashboard header */
.dashboard-header {
    background-color: #343a40; /* Donkere achtergrond */
    color: white;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

/* Navigatie tabs */
.nav-tabs .nav-link {
    font-weight: 500;
    color: #ffffff; /* Witte kleur voor de tab links */
}

.nav-tabs .nav-link.active {
    background-color: #f8f9fa; /* Lichte achtergrond voor actieve link */
    border-bottom-color: #f8f9fa;
    color: #343a40; /* Donkere kleur voor actieve link tekst */
}

/* Tab-inhoud */
.tab-content {
    background-color: white;
    border: 1px solid #dee2e6;
    border-top: none;
    padding: 20px;
    border-radius: 0 0 5px 5px;
}

/* Actieknoppen */
.action-buttons {
    white-space: nowrap;
}

/* Tabel responsief */
.table-responsive {
    margin-bottom: 20px;
}

/* Meldingen */
.alert {
    margin-bottom: 20px;
}

/* Modale header */
.modal-header {
    background-color: #343a40; /* Donkere achtergrond voor modal */
    color: white;
}

/* Voeg-knop */
.btn-add {
    margin-bottom: 20px;
}

/* Miniatuur afbeeldingen */
.img-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

/* Profielpagina */
.profile-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

/* Profiel header */
.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    justify-content: center;
    flex-direction: column;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 15px;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#user-name {
    font-size: 24px;
    font-weight: bold;
    color:rgb(0, 0, 0);
    margin-bottom: 10px;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    background-color: #1a1a1a;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #ffffff;
}

.stat-label {
    font-size: 14px;
    color: #aaa;
}

/* Avatar formulier */
.avatar-form {
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
}

.avatar-form label {
    font-size: 18px;
    margin-bottom: 10px;
    color: #fff;
}

.avatar-form input[type="file"] {
    margin-bottom: 20px;
    border: 1px solid #ccc;
    padding: 10px;
    background-color: #1a1a1a;
    color: #fff;
    border-radius: 4px;
}

/* Upload en logout knoppen */
.upload-btn, .logout-btn {
    background-color: #e50914;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

.upload-btn:hover, .logout-btn:hover {
    background-color: #b4060f;
}

.logout-btn {
    background-color: #333;
}

@media (max-width: 768px) {
    .profile-header {
        text-align: center;
    }

    .profile-avatar {
        margin-bottom: 20px;
    }

    .profile-stats {
        flex-direction: column;
        gap: 20px;
    }

    .avatar-form {
        align-items: center;
    }

    .upload-btn {
        width: auto;
        margin-top: 15px;
    }
}

@media (max-width: 480px) {
    .profile-avatar {
        width: 80px;
        height: 80px;
    }

    #user-name {
        font-size: 20px;
    }

    .stat-value {
        font-size: 20px;
    }

    .upload-btn, .logout-btn {
        font-size: 14px;
        padding: 8px 16px;
    }
}

/* Movie details */
.movie-details {
    max-width: 1000px;
    margin: 0 auto;
}

.movie-backdrop {
    position: relative;
    height: 500px;
    background-size: cover;
    background-position: center;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-end;
}

.movie-backdrop::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0.3) 100%);
    border-radius: 12px;
}

.movie-info {
    position: relative;
    padding: 30px;
    width: 100%;
}

.movie-info h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.movie-meta {
    font-size: 16px;
    color: #aaa;
    margin-bottom: 15px;
}

.movie-rating {
    display: inline-block;
    background

    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="fas fa-film me-2"></i>Bioscoop Admin Dashboard</h1>
                </div>
                <div class="col-auto">
                    <a href="php/logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-2"></i>Uitloggen
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab == 'users' ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="<?= $active_tab == 'users' ? 'true' : 'false' ?>">
                    <i class="fas fa-users me-2"></i>Gebruikers
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab == 'films' ? 'active' : '' ?>" id="films-tab" data-bs-toggle="tab" data-bs-target="#films" type="button" role="tab" aria-controls="films" aria-selected="<?= $active_tab == 'films' ? 'true' : 'false' ?>">
                    <i class="fas fa-film me-2"></i>Films
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab == 'cast' ? 'active' : '' ?>" id="cast-tab" data-bs-toggle="tab" data-bs-target="#cast" type="button" role="tab" aria-controls="cast" aria-selected="<?= $active_tab == 'cast' ? 'true' : 'false' ?>">
                    <i class="fas fa-user-tie me-2"></i>Cast Leden
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Users Tab -->
            <div class="tab-pane fade <?= $active_tab == 'users' ? 'show active' : '' ?>" id="users" role="tabpanel" aria-labelledby="users-tab">
                <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Nieuwe Gebruiker
                </button>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Gebruikersnaam</th>
                                <th>Avatar</th>
                                <th>Admin</th>
                                <th>Aangemaakt op</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="rounded-circle img-thumbnail" 
                                             onerror="this.onerror=null; this.src='<?= $placeholder_image ?>'">
                                    </td>
                                    <td><?= $user['is_admin'] ? '<span class="badge bg-success">Ja</span>' : '<span class="badge bg-secondary">Nee</span>' ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-warning edit-user-btn" 
                                                data-id="<?= $user['id'] ?>" 
                                                data-username="<?= htmlspecialchars($user['username']) ?>" 
                                                data-is-admin="<?= $user['is_admin'] ?>" 
                                                data-avatar="<?= htmlspecialchars($user['avatar']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline delete-form">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Films Tab -->
            <div class="tab-pane fade <?= $active_tab == 'films' ? 'show active' : '' ?>" id="films" role="tabpanel" aria-labelledby="films-tab">
                <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#addFilmModal">
                    <i class="fas fa-plus me-2"></i>Nieuwe Film
                </button>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Poster</th>
                                <th>Titel</th>
                                <th>Categorie</th>
                                <th>Beschrijving</th>
                                <th>Trailer</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($film = $films->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $film['id'] ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($film['image_url']) ?>" alt="Poster" class="img-thumbnail"
                                             onerror="this.onerror=null; this.src='<?= $placeholder_image ?>'">
                                    </td>
                                    <td><?= htmlspecialchars($film['title']) ?></td>
                                    <td><?= htmlspecialchars($film['category']) ?></td>
                                    <td><?= mb_substr(htmlspecialchars($film['description']), 0, 50) . (strlen($film['description']) > 50 ? '...' : '') ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($film['url_trailer']) ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fab fa-youtube"></i>
                                        </a>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-warning edit-film-btn" 
                                                data-id="<?= $film['id'] ?>" 
                                                data-title="<?= htmlspecialchars($film['title']) ?>" 
                                                data-description="<?= htmlspecialchars($film['description']) ?>" 
                                                data-category="<?= htmlspecialchars($film['category']) ?>" 
                                                data-url-trailer="<?= htmlspecialchars($film['url_trailer']) ?>" 
                                                data-image-url="<?= htmlspecialchars($film['image_url']) ?>"
                                                data-date="<?= $film['date'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline delete-form">
                                            <input type="hidden" name="film_id" value="<?= $film['id'] ?>">
                                            <button type="submit" name="delete_film" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je deze film wilt verwijderen?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cast Tab -->
            <div class="tab-pane fade <?= $active_tab == 'cast' ? 'show active' : '' ?>" id="cast" role="tabpanel" aria-labelledby="cast-tab">
                <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#addCastModal">
                    <i class="fas fa-plus me-2"></i>Nieuw Cast Lid
                </button>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Naam</th>
                                <th>Film</th>
                                <th>Karakter</th>
                                <th>Volgorde</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cast = $cast_members->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $cast['id'] ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($cast['image_url']) ?>" alt="Cast" class="img-thumbnail"
                                             onerror="this.onerror=null; this.src='<?= $placeholder_image ?>'">
                                    </td>
                                    <td><?= htmlspecialchars($cast['name']) ?></td>
                                    <td><?= htmlspecialchars($cast['film_title']) ?></td>
                                    <td><?= htmlspecialchars($cast['character_name'] ?? '-') ?></td>
                                    <td><?= $cast['order_in_credits'] ?? '-' ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-warning edit-cast-btn" 
                                                data-id="<?= $cast['id'] ?>" 
                                                data-name="<?= htmlspecialchars($cast['name']) ?>" 
                                                data-image-url="<?= htmlspecialchars($cast['image_url']) ?>" 
                                                data-film-id="<?= $cast['film_id'] ?>" 
                                                data-character-name="<?= htmlspecialchars($cast['character_name'] ?? '') ?>" 
                                                data-order="<?= $cast['order_in_credits'] ?? '' ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline delete-form">
                                            <input type="hidden" name="cast_id" value="<?= $cast['id'] ?>">
                                            <button type="submit" name="delete_cast" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit cast lid wilt verwijderen?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Nieuwe Gebruiker Toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addUserForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Gebruikersnaam</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Wachtwoord</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Avatar URL</label>
                            <input type="text" class="form-control" id="avatar" name="avatar" value="uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
                            <label class="form-check-label" for="is_admin">Is Admin</label>
                        </div>
                        <button type="submit" name="add_user" class="btn btn-primary">Toevoegen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Gebruiker Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Gebruikersnaam</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Wachtwoord (leeg laten om niet te wijzigen)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_avatar" class="form-label">Avatar URL</label>
                            <input type="text" class="form-control" id="edit_avatar" name="avatar">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_admin" name="is_admin">
                            <label class="form-check-label" for="edit_is_admin">Is Admin</label>
                        </div>
                        <button type="submit" name="edit_user" class="btn btn-primary">Opslaan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Film Modal -->
    <div class="modal fade" id="addFilmModal" tabindex="-1" aria-labelledby="addFilmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFilmModalLabel">Nieuwe Film Toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addFilmForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titel</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Beschrijving</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Categorie</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="url_trailer" class="form-label">Trailer URL</label>
                            <input type="url" class="form-control" id="url_trailer" name="url_trailer" required>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Poster URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Jaar</label>
                            <input type="number" class="form-control" id="date" name="date" value="<?= date('Y') ?>" required>
                        </div>
                        <button type="submit" name="add_film" class="btn btn-primary">Toevoegen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Film Modal -->
    <div class="modal fade" id="editFilmModal" tabindex="-1" aria-labelledby="editFilmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFilmModalLabel">Film Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editFilmForm">
                        <input type="hidden" id="edit_film_id" name="film_id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Titel</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Beschrijving</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Categorie</label>
                            <input type="text" class="form-control" id="edit_category" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_url_trailer" class="form-label">Trailer URL</label>
                            <input type="url" class="form-control" id="edit_url_trailer" name="url_trailer" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Poster URL</label>
                            <input type="text" class="form-control" id="edit_image_url" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_date" class="form-label">Jaar</label>
                            <input type="number" class="form-control" id="edit_date" name="date" required>
                        </div>
                        <button type="submit" name="edit_film" class="btn btn-primary">Opslaan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Cast Modal -->
    <div class="modal fade" id="addCastModal" tabindex="-1" aria-labelledby="addCastModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCastModalLabel">Nieuw Cast Lid Toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addCastForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Naam</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="image_url_cast" class="form-label">Foto URL</label>
                            <input type="text" class="form-control" id="image_url_cast" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="film_id" class="form-label">Film</label>
                            <select class="form-select" id="film_id" name="film_id" required>
                                <option value="">Selecteer een film</option>
                                <?php 
                                $films_for_select->data_seek(0);
                                while ($film = $films_for_select->fetch_assoc()): 
                                ?>
                                    <option value="<?= $film['id'] ?>"><?= htmlspecialchars($film['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="character_name" class="form-label">Karakter Naam</label>
                            <input type="text" class="form-control" id="character_name" name="character_name">
                        </div>
                        <div class="mb-3">
                            <label for="order_in_credits" class="form-label">Volgorde in Credits</label>
                            <input type="number" class="form-control" id="order_in_credits" name="order_in_credits">
                        </div>
                        <button type="submit" name="add_cast" class="btn btn-primary">Toevoegen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Cast Modal -->
    <div class="modal fade" id="editCastModal" tabindex="-1" aria-labelledby="editCastModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCastModalLabel">Cast Lid Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editCastForm">
                        <input type="hidden" id="edit_cast_id" name="cast_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Naam</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url_cast" class="form-label">Foto URL</label>
                            <input type="text" class="form-control" id="edit_image_url_cast" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_film_id" class="form-label">Film</label>
                            <select class="form-select" id="edit_film_id" name="film_id" required>
                                <option value="">Selecteer een film</option>
                                <?php 
                                $films_for_select->data_seek(0);
                                while ($film = $films_for_select->fetch_assoc()): 
                                ?>
                                    <option value="<?= $film['id'] ?>"><?= htmlspecialchars($film['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_character_name" class="form-label">Karakter Naam</label>
                            <input type="text" class="form-control" id="edit_character_name" name="character_name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_order_in_credits" class="form-label">Volgorde in Credits</label>
                            <input type="number" class="form-control" id="edit_order_in_credits" name="order_in_credits">
                        </div>
                        <button type="submit" name="edit_cast" class="btn btn-primary">Opslaan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set active tab based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.querySelector(`#${tab}-tab`);
                if (tabElement) {
                    const tabInstance = new bootstrap.Tab(tabElement);
                    tabInstance.show();
                }
            }

            // Edit User Button Click
            document.querySelectorAll('.edit-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const username = this.getAttribute('data-username');
                    const isAdmin = this.getAttribute('data-is-admin') === '1';
                    const avatar = this.getAttribute('data-avatar');

                    document.getElementById('edit_user_id').value = id;
                    document.getElementById('edit_username').value = username;
                    document.getElementById('edit_password').value = '';
                    document.getElementById('edit_avatar').value = avatar;
                    document.getElementById('edit_is_admin').checked = isAdmin;

                    const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                    editUserModal.show();
                });
            });

            // Edit Film Button Click
            document.querySelectorAll('.edit-film-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const category = this.getAttribute('data-category');
                    const urlTrailer = this.getAttribute('data-url-trailer');
                    const imageUrl = this.getAttribute('data-image-url');
                    const date = this.getAttribute('data-date');

                    document.getElementById('edit_film_id').value = id;
                    document.getElementById('edit_title').value = title;
                    document.getElementById('edit_description').value = description;
                    document.getElementById('edit_category').value = category;
                    document.getElementById('edit_url_trailer').value = urlTrailer;
                    document.getElementById('edit_image_url').value = imageUrl;
                    document.getElementById('edit_date').value = date;

                    const editFilmModal = new bootstrap.Modal(document.getElementById('editFilmModal'));
                    editFilmModal.show();
                });
            });

            // Edit Cast Button Click
            document.querySelectorAll('.edit-cast-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const imageUrl = this.getAttribute('data-image-url');
                    const filmId = this.getAttribute('data-film-id');
                    const characterName = this.getAttribute('data-character-name');
                    const order = this.getAttribute('data-order');

                    document.getElementById('edit_cast_id').value = id;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_image_url_cast').value = imageUrl;
                    document.getElementById('edit_film_id').value = filmId;
                    document.getElementById('edit_character_name').value = characterName;
                    document.getElementById('edit_order_in_credits').value = order;

                    const editCastModal = new bootstrap.Modal(document.getElementById('editCastModal'));
                    editCastModal.show();
                });
            });
        });
    </script>
</body>
</html>