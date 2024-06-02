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
      "code" => "Kód",
      "price" => "Cena",
      "product_type" => "Typ produktu",
      "manufacturer" => "Výrobce",
    ];
    const OTHER_COLUMNS = [
      "description" => "Popis",
    ];

    const DEFAULT_ORDER_BY_COLUMN = "code";
    const DEFAULT_ORDER = "asc";
    const DEFAULT_LIMIT = 10;

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

    public static function editProducts($changes = []) {
      if (empty($changes)) return;

      $pdo = Database::connect();

      $sql = "UPDATE product SET";

      //add changes to query
      $ids = [];
      $changedColumnsCount = count($changes);
      $alreadyChangedCount = 0;
      foreach ($changes as $column => $columnChanges) {
        if (!self::isValidColumn($column)) {
          continue;
        }

        $sql .= " $column = CASE id";

        foreach ($columnChanges as $id => $newValue) {
          $ids[$id] = true; //we collect unique ids
          $sql .= " WHEN ? THEN ?";
        }
        
        $sql .= " ELSE $column END";
        
        if (++$alreadyChangedCount < $changedColumnsCount) {
          $sql .= ",";
        }
      }
      $sql .= " WHERE id IN (" . str_repeat("?,", count($ids) - 1) . "?);";

      $stmt = $pdo->prepare($sql);

      //bind values
      $bindedParamsCount = 0;
      foreach($changes as $column => $columnChanges) {
        foreach($columnChanges as $id => $newValue) {
          $stmt->bindValue(++$bindedParamsCount, $id, PDO::PARAM_INT);
          $stmt->bindValue(++$bindedParamsCount, $newValue, is_int($newValue) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
      }
      foreach($ids as $id => $_) {
        $stmt->bindValue(++$bindedParamsCount, $id, PDO::PARAM_INT);
      }

      try {
        $stmt->execute();
      } catch(PDOException $e) {
        return ["error" => "Nastala neočekávaná chyba při ukládání změn"];
      } 
    }

    //private helper functions

    private static function addFiltersToQuery(&$sql, $filters) {
      if (!empty($filters)) {
        $sql .= " WHERE ";
        $filtersCount = count($filters);
        $addedFiltersCount = 0;
        foreach ($filters as $column => $filter) {
          //$filter[0] is the operator, $filter[1] is the value
          if ($filter[0] === ComparisonOperator::Between) {
            $sql .= "product.$column " . ComparisonOperator::Between->value . " :${column}_min AND :${column}_max";
          } else {
            $sql .= "product.$column {$filter[0]->value} :$column"; 
          }

          if (++$addedFiltersCount < $filtersCount) {
            $sql .= " AND ";
          }
        }
      }
    }

    private static function bindFilterValues($stmt, $filters) {
      foreach ($filters as $column => $filter) {
        //$filter[0] is the operator, $filter[1] is the value
        if ($filter[0] === ComparisonOperator::Between) {
          $stmt->bindValue("${column}_min", $filter[1][0], is_int($filter[1][0]) ? PDO::PARAM_INT : PDO::PARAM_STR);
          $stmt->bindValue("${column}_max", $filter[1][1], is_int($filter[1][1]) ? PDO::PARAM_INT : PDO::PARAM_STR);
        } else {
          $stmt->bindValue($column, $filter[1], is_int($filter[1]) ? PDO::PARAM_INT : PDO::PARAM_STR);
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

    private static function isValidColumn($column) {
      return array_key_exists($column, self::ORDER_BY_COLUMNS) || array_key_exists($column, self::OTHER_COLUMNS);
    }
  }
?>