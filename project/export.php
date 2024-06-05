<?php 
  require_once "controllers/ProductController.php"; 

  $controller = new ProductController();

  $filename = "produkt-" . date("Y-m-d") . ".csv";

  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  $fp = fopen("php://output", "w");

  $data = $controller::getAllProducts();

  if (count($data) > 0) {
    fputcsv($fp, array_keys($data[0]));

    foreach ($data as $product) {
      fputcsv($fp, $product);
    }
  }

  fclose($fp);
  exit();
?>