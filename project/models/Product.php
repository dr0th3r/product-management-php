<?php
  require_once "config/db.php";

  class Product {
    public $id;
    public $code;
    public $price;
    public $product_type;
    public $manufacturer;
    public $description;

    public static function getFiltered($filters = [], $orderBy = "code", $order = "ASC", $page = 1, $limit = 5) {
      $pdo = Database::connect();

      $sql = "
        SELECT
          product.*,
          product_type.name as product_type_name,
          manufacturer.name as manufacturer_name
        FROM 
          product
        JOIN 
          product_type ON product.product_type = product_type.id
        JOIN
          manufacturer ON product.manufacturer = manufacturer.id
      ";

      if (!empty($filters)) {
        $sql .= "WHERE ";
        $filter_count = count($filters);
        $i = 0;
        foreach ($filters as $key => $value) {
          //$value[0] is the operator, $value[1] is the value
          if ($value[0] === "BETWEEN") {
            $sql .= "product.$key BETWEEN :${key}_min AND :${key}_max";
          } else {
            $sql .= "product.$key $value[0] :$key"; 
          }
          if ($i++ < $filter_count - 1) {
            $sql .= " AND ";
          }
        }
      }

      $sql .= " ORDER BY $orderBy $order LIMIT :limit OFFSET :offset;";

      $stmt = $pdo->prepare($sql);

      foreach ($filters as $key => $value) {
        //$value[0] is the operator, $value[1] is the value
        if ($value[0] === "BETWEEN") {
          $stmt->bindValue("${key}_min", $value[1][0], $value[2]);
          $stmt->bindValue("${key}_max", $value[1][1], $value[2]);
        } else {
          $stmt->bindValue($key, $value[1], $value[2]);
        }
      }

      $stmt->bindValue("limit", $limit, PDO::PARAM_INT);
      $stmt->bindValue("offset", ($page - 1) * $limit, PDO::PARAM_INT);

      try {
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    public static function getFilteringOptions() {
      $pdo = Database::connect();

      $sql = "
        SELECT MIN(price) as price_min, MAX(price) as price_max FROM product;
        SELECT id, name FROM product_type;
        SELECT id, name FROM manufacturer;
      ";

      $stmt = $pdo->query($sql);

      try {
        $results = [];
        do {
          $data = [];
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
          }
          if (!empty($data)) {
            $results[] = $data;
          }
        } while ($stmt->nextRowset());

        return [
          "price-min" => $results[0][0]["price_min"],
          "price-max" => $results[0][0]["price_max"],
          "product-types" => $results[1],
          "manufacturers" => $results[2]
        ];
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }
  }