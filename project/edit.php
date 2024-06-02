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

    if (empty($data)) {
      http_response_code(400);
      echo json_encode(["error" => "No data provided"]);
      exit;
    }    

    echo json_encode([
      "msg" => $controller->editProducts($data),
      "data" => $data,
    ]);
  }
?>