<?php
  require_once "models/Product.php";

  class ProductController {
    public function getFilterMenu() {      
      $hiddenInputs = [];
      foreach (["order_by", "order"] as $key => $value) {
        if (!empty($_GET[$key])) {
          $hiddenInputs[] = $key;
        }
      }

      $filteringOptions = Product::getFilteringOptions();

      $selectedProductType = $_GET["product_type"] ?? "";
      $selectedManufacturer = $_GET["manufacturer"] ?? "";

      echo Product::getProductCount(self::parseFilters());

      include "views/ProductFilterMenu.php";
    }

    public function getTableHead() {  
      echo "<thead><tr>";

      $currentOrderByColumn = $_GET["order_by"] ?? Product::DEFAULT_ORDER_BY_COLUMN;
      $isCurrentOrderAsc = ($_GET["order"] ?? "asc") == "asc";

      foreach (Product::ORDER_BY_COLUMNS as $key => $value) {
        $isCurrentOrderByColumn = $currentOrderByColumn == $key;

        $queryParams = [];

        $queryParams["order_by"] = $key;
        $queryParams["order"] = 
          ($isCurrentOrderByColumn && $isCurrentOrderAsc) ? "desc" : "asc";
        $queryParams["page"] =
          ($isCurrentOrderByColumn && !empty($_GET["page"])) ? $_GET["page"] : 1;
        
        $queryString = http_build_query($queryParams);

        echo "<th><a href='?{$queryString}'>{$value}</a></th>";
      }

      foreach (Product::OTHER_COLUMNS as $value) {
        echo "<th>{$value}</th>";
      }

      echo "</tr></thead>";
    }

    public function getTableBody() {
      $products = Product::getFiltered(
        self::parseFilters(),
        orderBy: $_GET["order_by"] ?? Product::DEFAULT_ORDER_BY_COLUMN,
        order: $_GET["order"] ?? Product::DEFAULT_ORDER,
        page: $_GET["page"] ?? 1,
      );

      include "views/ProductTableBody.php";
    }

    private static function parseFilters() {
      $priceMin = empty($_GET["price_min"]) ? 0 : intval($_GET["price_min"]);
      $priceMax = empty($_GET["price_max"]) ? PHP_INT_MAX : intval($_GET["price_max"]);

      if ($priceMin > $priceMax) {
        $tmp = $priceMin;
        $priceMin = $priceMax;
        $priceMax = $tmp;
      }
      
      $filters = [];
      if (!empty($_GET["search"])) {
        $filters["code"] = ["LIKE", "%{$_GET['search']}%", PDO::PARAM_STR];
      }
      if ($priceMin != 0 || $priceMax != PHP_INT_MAX) {
        $filters["price"] = ["BETWEEN", [$priceMin, $priceMax], PDO::PARAM_INT];
      }
      if (!empty($_GET["product_type"])) {
        $filters["product_type"] = ["=", $_GET["product_type"], PDO::PARAM_INT];
      }
      if (!empty($_GET["manufacturer"])) {
        $filters["manufacturer"] = ["=", $_GET["manufacturer"], PDO::PARAM_INT];
      }

      return $filters;
    }
  }

?>