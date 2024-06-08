<?php
  require_once "../config/db.php";

  class Product {
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
    const DEFAULT_ORDER = Order::Asc;
    const DEFAULT_LIMIT = 10;

    public static function getFiltered(
      $filters = [], 
      $orderBy = self::DEFAULT_ORDER_BY_COLUMN, 
      $order = self::DEFAULT_ORDER, 
      $page = 1, 
      $limit = self::DEFAULT_LIMIT
    ) {
      try {
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

        //we prepare the query by adding filters, ordering and pagination
        $stmt = self::prepareQuery($pdo, $sql, $filters, $orderBy, $order, $page, $limit);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    public static function getFilteringOptions() {
      try {
        $pdo = Database::connect();

        $sql = "
          SELECT MIN(price) as price_min, MAX(price) as price_max FROM product;
          SELECT id, name FROM product_type;
          SELECT id, name FROM manufacturer;
        ";

        $stmt = $pdo->query($sql);

        /*
        since min and max prices are fetch in an associative array in the first row, we can do this
        as options will be an associative array of price_min, price_max, product_types and manufacturers
        */
        $options = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->nextRowset();
        //we want to fetch them as id-name pairs
        $options["product_types"] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $stmt->nextRowset();
        $options["manufacturers"] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return $options;
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    public static function getProductCount(
      $filters = [], 
    ) {
      try {
        $pdo = Database::connect();

        $sql = "SELECT COUNT(*) as count FROM product";

        $stmt = self::prepareQuery($pdo, $sql, $filters);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)["count"];
      } catch(PDOException $e) {
        echo "Database error: " . $e->getMessage();
      }
    }

    public static function editProducts($changes = []) {
      if (empty($changes)) return;

      try {
        $pdo = Database::connect();

        $sql = "UPDATE product SET";

        //add changes to query
        $ids = [];
        $changedColumnsCount = count($changes);
        $alreadyChangedCount = 0;
        foreach ($changes as $column => $columnChanges) {
          /*
          for each changed column we check if the column is valid (unlike filters or ordering 
          we don't check this in the controller) and if it is, we add the changes using the
          CASE statement with the product's id as the condition
          e.g. CASE id WHEN 1 THEN 100 WHEN 2 THEN 200 ELSE price END
          */
          if (!self::isValidColumn($column)) {
            continue;
          }

          $sql .= " $column = CASE id";

          foreach ($columnChanges as $id => $newValue) {
            $ids[$id] = true; //we collect unique ids
            $sql .= " WHEN ? THEN ?";
          }
          
          $sql .= " ELSE $column END";
          
          //if this is not the last changed column, we have to add a comma
          if (++$alreadyChangedCount < $changedColumnsCount) {
            $sql .= ",";
          }
        }

        /*
        for each unique id we add a question mark (that will be used later for binding) 
        so that we only work with the products that have been changed
        */
        $sql .= " WHERE id IN (" . str_repeat("?,", count($ids) - 1) . "?);";

        $stmt = $pdo->prepare($sql);

        //bind values
        $bindedParamsCount = 0;
        foreach($changes as $column => $columnChanges) {
          foreach($columnChanges as $id => $newValue) {
            //for each changed value we bind the product's id and the new value
            $stmt->bindValue(++$bindedParamsCount, $id, PDO::PARAM_INT);
            //the value can be either integer or string so we have to check the type
            $stmt->bindValue(++$bindedParamsCount, $newValue, is_int($newValue) ? PDO::PARAM_INT : PDO::PARAM_STR);
          }
        }
        //then we bind ids of changed products to the WHERE clause
        foreach($ids as $id => $_) {
          $stmt->bindValue(++$bindedParamsCount, $id, PDO::PARAM_INT);
        }

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
          /*
            in this case I don't implement another checking if $column is valid as only valid columns are extraced
            from the request. If we wanted another layer of security by checking if the column is in list of valid
            columns for filtering (which in this case is the same as keys of sort_by_columns).
          */
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
        /*
          In this case I don't implement another checking if $column is valid as only valid columns are extraced
          from the request. If we wanted another layer of security by checking if the column is in list of valid
          columns for filtering (which in this case is the same as keys of ORDER_BY_COLUMNS).
        */
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
      /*
        As well as with the filtering I don't implement validation of orderBy and order because they are
        extracted and validated in the controller. If we wanted another layer of security by checking, we could
        do it by checking $orderBy againts keys of ORDER_BY_COLUMNS and $order against values of Order enum.
      */

      $sql .= " ORDER BY product.$orderBy $order->value";
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