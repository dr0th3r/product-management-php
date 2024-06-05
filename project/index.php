<?php 
  require_once "controllers/ProductController.php"; 

  $controller = new ProductController();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Správa</title>
  <meta name="description" content="Správa produktů">
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
  <div class="container">
    <aside>
      <div>
        <?php 
          $controller::getFilterMenu(); 
          $controller::getPageNav();
        ?>
      </div>
      <div class="edit-btns">
        <button id="edit-btn">Upravit</button>
        <button id="cancel-btn" class="hidden">Neuložit</button>
      </div>
      <a href="export.php">Export</a>
    </aside>
    <table>
      <?php
        $controller::getTableHead();
        $controller::getTableBody();
      ?>
    </table>
  </div>
  <script src="public/script.js"></script>
</body>
</html>