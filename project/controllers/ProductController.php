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

      $selectedProductTypeId = $_GET["product_type"] ?? "";
      $selectedManufacturerId = $_GET["manufacturer"] ?? "";
      $selectedProductType = "";
      $selectedManufacturer = "";

      foreach ($filteringOptions["product_types"] as $productType) {
        if ($productType["id"] == $selectedProductTypeId) {
          $selectedProductType = $productType["name"];
          break;
        }
      }

      foreach ($filteringOptions["manufacturers"] as $manufacturer) {
        if ($manufacturer["id"] == $selectedManufacturerId) {
          $selectedManufacturer = $manufacturer["name"];
          break;
        }
      }

      include "views/ProductFilterMenu.php";
    }

    public function getPageNav() {
      $pageCount = ceil(Product::getProductCount(self::parseFilters()) / Product::DEFAULT_LIMIT);
      $currentPage = $_GET["page"] ?? 1;

      if ($currentPage > 1) {
        $firstPageQuery = http_build_query(["page" => 1] + $_GET);
      }
      if ($currentPage > 2) {
        $previousPageQuery = http_build_query(["page" => $currentPage - 1] + $_GET);
      }
      if ($currentPage < ($pageCount - 1)) {
        $nextPageQuery = http_build_query(["page" => $currentPage + 1] + $_GET);
      }
      if ($currentPage < $pageCount) {
        $lastPageQuery = http_build_query(["page" => $pageCount] + $_GET);
      }

      include "views/ProductPageNav.php";
    }

    public function getTableHead() {  
      $currentOrderByColumn = $_GET["order_by"] ?? Product::DEFAULT_ORDER_BY_COLUMN;
      $isCurrentOrderAsc = ($_GET["order"] ?? "asc") == "asc";

      $columns = Product::ORDER_BY_COLUMNS + Product::OTHER_COLUMNS;
      $queries = [];

      foreach (Product::ORDER_BY_COLUMNS as $key => $_) {
        $isCurrentOrderByColumn = $currentOrderByColumn == $key;

        $newQueryParams = [];

        $newQueryParams["order_by"] = $key;
        $newQueryParams["order"] = 
          ($isCurrentOrderByColumn && $isCurrentOrderAsc) ? "desc" : "asc";
        $newQueryParams["page"] =
          ($isCurrentOrderByColumn && !empty($_GET["page"])) ? $_GET["page"] : 1;

        $queries[] = http_build_query($newQueryParams + $_GET);
      }

      include "views/ProductTableHead.php";
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