<?php 
  require_once "../controllers/ProductController.php"; 

  $controller = new ProductController();

  $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

  if ($url === "/") {
    $controller::getPage();
  }
  else if ($url === "/products/export") {
    $controller::exportProducts();
  }
  else if ($url === "/products/edit") {
    $controller::editProducts();
  }
  else {
    http_response_code(404);
    echo "Stránka nebyla nalezena: $url";
  }
?>