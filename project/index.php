<?php 
  require_once "controllers/ProductController.php"; 

  $controller = new ProductController();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
  <?php 
    $controller->getFilterMenu(); 
    $controller->getPageNav();
  ?>
  <table>
    <?php
      $controller->getTableHead();
      $controller->getTableBody();
    ?>
  </table>

  <button id="edit-btn">Edit</button>

  <script src="public/script.js"></script>
</body>
</html>