<?php
session_start(); // Start the session at the top of the file
if (isset($_SESSION["register_error"])) {
    echo "<script>alert('" . $_SESSION["register_error"] . "');</script>";
    unset($_SESSION["register_error"]); // Clear the error after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Democratische-bioscoop - Register</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>REGISTER</h1>
        <p>Already have an account? <a href="index.php">LOGIN</a></p>
      </div>
      <form action="php/register.php" method="post" class="login-form">
        <div class="form-group">
          <label for="username">USERNAME</label>
          <input type="text" id="username" name="username" placeholder="username" required>
        </div>
        <div class="form-group">
          <label for="password">PASSWORD</label>
          <input type="password" id="password" name="password" placeholder="password" required>
        </div>
        <div class="form-group">
          <label for="confirm-password">CONFIRM PASSWORD</label>
          <input type="password" id="confirm-password" name="confirm-password" placeholder="confirm password" required>
        </div>
     
        <button type="submit" class="login-btn">REGISTER</button>
      </form>
      <?php
    if (isset($_SESSION["register_error"])) {
        echo "<p style='color: red;'>" . $_SESSION["register_error"] . "</p>";
        unset($_SESSION["register_error"]);
    }
    ?>
    </div>
  </div>
</body>
</html>
