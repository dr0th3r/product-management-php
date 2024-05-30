<?php
  $sql = "
    SELECT MIN(price) as min_price, MAX(price) as max_price FROM product;
    SELECT id, name FROM product_type;
    SELECT id, name FROM manufacturer;
  ";

  $conn->multi_query($sql); 
  $results = [];
  do {
    $result = $conn->store_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }
    $results[] = $data;
    $result->free();
  } while ($conn->more_results() && $conn->next_result());

  $min_price = $results[0][0]["min_price"];
  $max_price = $results[0][0]["max_price"];

  $product_types = $results[1];
  $manufacturers = $results[2];
?>