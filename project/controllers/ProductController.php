<?php
  require_once "models/Product.php";

  class ProductController {
    public function getFilterMenu() {      
      $hiddenInputs = [];
      foreach (["order_by", "order", "page"] as $queryParam => $_) {
        if (!empty($_GET[$queryParam])) {
          $hiddenInputs[$queryParam] = $_GET[$queryParam];
        }
      }

      $filteringOptions = Product::getFilteringOptions();

      $priceMin = floor(empty($_GET["price_min"]) ? $filteringOptions["price_min"] : $_GET["price_min"]);
      $priceMax = ceil(empty($_GET["price_max"]) ? $filteringOptions["price_max"] : $_GET["price_max"]);

      if ($priceMin > $priceMax) {
        $tmp = $priceMin;
        $priceMin = $priceMax;
        $priceMax = $tmp;
      }

      $selectedProductType = $_GET["product_type"] ?? "";
      $selectedManufacturer = $_GET["manufacturer"] ?? "";

      include "views/ProductFilterMenu.php";
    }

    public function getPageNav() {
      $pageCount = ceil(Product::getProductCount(self::parseFilters()) / Product::DEFAULT_LIMIT);
      $currentPage = $_GET["page"] ?? 1;

      if ($currentPage > 1) {
        $previousPageQuery = http_build_query(["page" => $currentPage - 1] + $_GET);
        echo "<a href='?{$previousPageQuery}'>Previous page</a>";
      }
      if ($currentPage < $pageCount) {
        $nextPageQuery = http_build_query(["page" => $currentPage + 1] + $_GET);
        echo "<a href='?{$nextPageQuery}'>Next page</a>";
      }
    }

    public function getTableHead() {  
      echo "<thead><tr>";

      $currentOrderByColumn = $_GET["order_by"] ?? Product::DEFAULT_ORDER_BY_COLUMN;
      $isCurrentOrderAsc = ($_GET["order"] ?? "asc") == "asc";

      foreach (Product::ORDER_BY_COLUMNS as $key => $value) {
        $isCurrentOrderByColumn = $currentOrderByColumn == $key;

        $newQueryParams = [];

        $newQueryParams["order_by"] = $key;
        $newQueryParams["order"] = 
          ($isCurrentOrderByColumn && $isCurrentOrderAsc) ? "desc" : "asc";
        $newQueryParams["page"] =
          ($isCurrentOrderByColumn && !empty($_GET["page"])) ? $_GET["page"] : 1;

        $queryString = http_build_query($newQueryParams + $_GET);

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

    public function editProducts($changes) {
      return Product::editProducts($changes);
    }

    private static function parseFilters() {
      $priceMin = empty($_GET["price_min"]) ? 0 : floor($_GET["price_min"]);
      $priceMax = empty($_GET["price_max"]) ? PHP_INT_MAX : ceil($_GET["price_max"]);

      if ($priceMin > $priceMax) {
        $tmp = $priceMin;
        $priceMin = $priceMax;
        $priceMax = $tmp;
      }
      
      $filters = [];
      if (!empty($_GET["search"])) {
        $filters["code"] = ["LIKE", "%{$_GET['search']}%"];
      }
      if ($priceMin != 0 || $priceMax != PHP_INT_MAX) {
        $filters["price"] = ["BETWEEN", [$priceMin, $priceMax]];
      }
      if (!empty($_GET["product_type"])) {
        $filters["product_type"] = ["=", $_GET["product_type"]];
      }
      if (!empty($_GET["manufacturer"])) {
        $filters["manufacturer"] = ["=", $_GET["manufacturer"]];
      }

      return $filters;
    }
  }

?>