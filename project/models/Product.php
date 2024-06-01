<?php
  require_once "config/db.php";

  class Product {
    public $id;
    public $code;
    public $price;
    public $product_type;
    public $manufacturer;
    public $description;

    const ORDER_BY_COLUMNS = [
      "code" => "Code",
      "price" => "Price",
      "product_type" => "Product Type",
      "manufacturer" => "Manufacturer",
    ];
    const OTHER_COLUMNS = ["Description"];

    const DEFAULT_ORDER_BY_COLUMN = "code";
    const DEFAULT_ORDER = "asc";
    const DEFAULT_LIMIT = 5;

    public static function getFiltered(
      $filters = [], 
      $orderBy = self::DEFAULT_ORDER_BY_COLUMN, 
      $order = self::DEFAULT_ORDER, 
      $page = 1, 
      $limit = self::DEFAULT_LIMIT
    ) {
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

      $stmt = self::prepareQuery($pdo, $sql, $filters, $orderBy, $order, $page, $limit);

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
          "price_min" => $results[0][0]["price_min"],
          "price_max" => $results[0][0]["price_max"],
          "product_types" => $results[1],
          "manufacturers" => $results[2]
        ];
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    public static function getProductCount(
      $filters = [], 
    ) {
      $pdo = Database::connect();

      $sql = "SELECT COUNT(*) as count FROM product";

      $stmt = self::prepareQuery($pdo, $sql, $filters);

      try {
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)["count"];
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    //private helper functions

    private static function addFiltersToQuery(&$sql, $filters) {
      if (!empty($filters)) {
        $sql .= " WHERE ";
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
    }

    private static function bindFilterValues($stmt, $filters) {
      foreach ($filters as $key => $value) {
        //$value[0] is the operator, $value[1] is the value
        if ($value[0] === "BETWEEN") {
          $stmt->bindValue("${key}_min", $value[1][0], $value[2]);
          $stmt->bindValue("${key}_max", $value[1][1], $value[2]);
        } else {
          $stmt->bindValue($key, $value[1], $value[2]);
        }
      }
    }

    private static function addPaginationToQuery(&$sql) {
      $sql .= " LIMIT :limit OFFSET :offset";
    }

    private static function bindPaginationValues($stmt, $page, $limit) {
      $stmt->bindValue("limit", $limit, PDO::PARAM_INT);
      $stmt->bindValue("offset", ($page - 1) * $limit, PDO::PARAM_INT);
    }

    private static function addOrderingToQuery(&$sql, $orderBy, $order) {
      if (!array_key_exists($orderBy, self::ORDER_BY_COLUMNS)) 
        $orderBy = self::DEFAULT_ORDER_BY_COLUMN;
      if ($order !== "asc" && $order !== "desc") 
        $order = self::DEFAULT_ORDER;

      $sql .= " ORDER BY product.$orderBy $order";
    }

    private static function prepareQuery($pdo, &$sql, $filters = [], $orderBy = null, $order = null, $page = null, $limit = null) {
      $shouldAddPagination = isset($page) && isset($limit);
      $shouldAddOrdering = isset($orderBy) && isset($order);

      self::addFiltersToQuery($sql, $filters);
      if ($shouldAddOrdering)
        self::addOrderingToQuery($sql, $orderBy, $order);
      if ($shouldAddPagination)
        self::addPaginationToQuery($sql);

      $sql .= ";";

      $stmt = $pdo->prepare($sql);

      self::bindFilterValues($stmt, $filters);
      //there is no ordering because we don't bind values for ordering
      if ($shouldAddPagination)
        self::bindPaginationValues($stmt, $page, $limit);

      return $stmt;
    }
  }
?>