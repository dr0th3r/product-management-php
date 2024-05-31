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
</head>
<body>
  <?php $controller->getFilterMenu(); ?>
  <table>
    <?php
      $controller->getTableHead();
      $controller->getTableBody();
    ?>
  </table>
</body>
</html>