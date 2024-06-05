<?php
  require_once "../models/Product.php";

  require_once "../config/db.php";

  class ProductController {
    const EXPORT_PATH = "/products/export";

    public static function getPage() {
      $title = "Správa produktů";
      $description = $title;
      $styles = ["product/style.css"];
      $scripts = ["product/script.js"];
    
      //we need to export the url with the current query params so that filtering and ordering is preserved
      $exportUrl = self::EXPORT_PATH . "?" . http_build_query($_GET);
      $product = self::class;

      $filters = self::parseFilters();

      require "../views/product/page.php";
    }

    public static function editProducts() {
      $changes = json_decode(file_get_contents("php://input"), true);

      header('Content-Type: application/json');

      if (empty($changes)) {
        http_response_code(400);
        return ["error" => "Nebyly poskytnuty žádné změny"];
      }

      $error = Product::editProducts($changes);
      if (isset($error)) {
        http_response_code(500);
        return $error;
      }
    }

    public static function exportProducts() {
      $filename = "produkty-" . date("Y-m-d") . ".csv";

      header("Content-Type: text/csv");
      header("Content-Disposition: attachment; filename=\"$filename\"");
      $fp = fopen("php://output", "w");

      //null limit means no limit
      $data = Product::getFiltered(
        self::parseFilters(),
        ...self::parseOrdering(),
        limit: null
      );

      if (count($data) > 0) {
        fputcsv($fp, array_keys($data[0]));

        foreach ($data as $product) {
          fputcsv($fp, $product);
        }
      }

      fclose($fp);
      exit();
    }

    public static function getFilterMenu() {      
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

      require "../views/product/filterMenu.php";
    }

    public static function getNav($filters = []) {
      $pageCount = ceil(Product::getProductCount($filters) / Product::DEFAULT_LIMIT);
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

      require "../views/product/nav.php";
    }

    public static function getTableHead() {  
      [$currentOrderByColumn, $order] = self::parseOrdering();

      $columns = Product::ORDER_BY_COLUMNS + Product::OTHER_COLUMNS;
      $queries = [];
      $isAsc = [];

      foreach (Product::ORDER_BY_COLUMNS as $key => $_) {
        $isCurrentlyOrderedByThisColumn = $currentOrderByColumn === $key;

        $newQueryParams = [];

        $newQueryParams["order_by"] = $key;

        //if this column is the one by which products are currently ordered
        if ($isCurrentlyOrderedByThisColumn) {
          if ($order === Order::Asc) {
            $isAsc[] = true;
            $newQueryParams["order"] = Order::Desc;
          } else {
            $isAsc[] = false;
            $newQueryParams["order"] = Order::Asc;
          }

          $newQueryParams["page"] = $_GET["page"] ?? 1;
        } else {
          $isAsc[] = false;
          $newQueryParams["order"] = Order::Asc;
          $newQueryParams["page"] = 1;
        }

        //since the value is enum, we need to get the string value
        $newQueryParams["order"] = $newQueryParams["order"]->value;

        $queries[] = http_build_query($newQueryParams + $_GET);
      }

      require "../views/product/tableHead.php";
    }

    public static function getTableBody($filters) {
      $products = Product::getFiltered(
        $filters,
        ...self::parseOrdering(),
        page: $_GET["page"] ?? 1,
      );

      require "../views/product/tableBody.php";
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
        $filters["code"] = [ComparisonOperator::Like, "%{$_GET['search']}%"];
      }
      if ($priceMin != 0 || $priceMax != PHP_INT_MAX) {
        $filters["price"] = [ComparisonOperator::Between, [$priceMin, $priceMax]];
      }
      if (!empty($_GET["product_type"])) {
        $filters["product_type"] = [ComparisonOperator::Equals, $_GET["product_type"]];
      }
      if (!empty($_GET["manufacturer"])) {
        $filters["manufacturer"] = [ComparisonOperator::Equals, $_GET["manufacturer"]];
      }

      return $filters;
    }

    private static function parseOrdering() {
      $orderBy = $_GET["order_by"] ?? Product::DEFAULT_ORDER_BY_COLUMN;
      //if the column is not in the array of orderable columns, use the default one
      $orderBy = Product::ORDER_BY_COLUMNS[$orderBy] ? $orderBy : Product::DEFAULT_ORDER_BY_COLUMN;
      $order = empty($_GET["order"])
        ? Product::DEFAULT_ORDER
        : (Order::tryFrom($_GET["order"]) ?? Product::DEFAULT_ORDER);

      return [$orderBy, $order];
    }
  }

?>