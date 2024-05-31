<?php
  require_once "models/Product.php";

  const PRICE_MAX_DEFAULT = 1 << 30;

  class ProductController {
    const SORT_COLUMNS = [
      "code" => "Code",
      "price" => "Price",
      "product-type" => "Product Type",
      "manufacturer" => "Manufacturer",
    ];
    const OTHER_COLUMNS = ["Description"];

    public function getFilterMenu() {
      $hiddenInputs = [];
      foreach (["sort_by", "sort_order"] as $key => $value) {
        if (!empty($_GET[$key])) {
          $hiddenInputs[] = $key;
        }
      }

      $filteringOptions = Product::getFilteringOptions();

      $selectedProductType = $_GET["product-type"] ?? "";
      $selectedManufacturer = $_GET["manufacturer"] ?? "";

      include "views/ProductFilterMenu.php";
    }

    public function getTableHead() {  
      echo "<thead><tr>";

      foreach (self::SORT_COLUMNS as $key => $value) {
        $isSortByKey = $_GET["sort_by"] == $key;

        $queryParams = [];

        $queryParams["sort_by"] = $key;
        $queryParams["sort_order"] = 
          ($isSortByKey && ($_GET["sort_order"] == "asc")) ? "desc" : "asc";
        $queryParams["page"] =
          ($isSortByKey && !empty($_GET["page"])) ? 1 : $_GET["page"];
        
        $queryString = http_build_query($queryParams);
        echo "<th><a href='?{$queryString}'>{$value}</a></th>";
      }

      foreach (self::OTHER_COLUMNS as $value) {
        echo "<th>{$value}</th>";
      }

      echo "</tr></thead>";
    }

    public function getTableBody() {
      $products = Product::getFiltered($this->parseFilters(), ...$this->parseSortingAndPagination());

      include "views/ProductTableBody.php";
    }

    private function parseFilters() {
      $priceMin = empty($_GET["price-min"]) ? 0 : intval($_GET["price-min"]);
      $priceMax = empty($_GET["price-max"]) ? PRICE_MAX_DEFAULT : intval($_GET["price-max"]);

      if ($priceMin > $priceMax) {
        $tmp = $priceMin;
        $priceMin = $priceMax;
        $priceMax = $tmp;
      }
      
      $filters = [];
      if (!empty($_GET["search"])) {
        $filters["code"] = ["LIKE", "%{$_GET['search']}%", PDO::PARAM_STR];
      }
      if ($priceMin != 0 || $priceMax != PRICE_MAX_DEFAULT) {
        $filters["price"] = ["BETWEEN", [$priceMin, $priceMax], PDO::PARAM_INT];
      }
      if (!empty($_GET["product-type"])) {
        $filters["product_type"] = ["=", $_GET["product-type"], PDO::PARAM_INT];
      }
      if (!empty($_GET["manufacturer"])) {
        $filters["manufacturer"] = ["=", $_GET["manufacturer"], PDO::PARAM_INT];
      }

      return $filters;
    }

    private function parseSortingAndPagination() {
      $sortingAndPagination = [];

      foreach (["sort_by", "sort_order", "page"] as $key) {
        if (!empty($_GET[$key])) {
          $sortingAndPagination[] = $_GET[$key];
        }
      }

      return $sortingAndPagination;
    }
  }

?>