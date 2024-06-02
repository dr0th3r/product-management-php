<?php
  require_once "controllers/ProductController.php"; 

  header("Content-Type: application/json");

  $controller = new ProductController();

  function getPatchData() {
    $data = file_get_contents("php://input");
    return json_decode($data, true);
  }

  if ($_SERVER["REQUEST_METHOD"] == "PATCH") {
    $data = getPatchData();

    echo json_encode($controller->editProducts($data));
  }
?>