<?php
  require_once "../models/Product.php";

  require_once "../config/db.php";

  class ProductController {

    //function that returns the page with product in a table
    public static function getPage() {
      //we define the title and description of the page as well as css and js files to be linked
      $title = "Správa produktů";
      $description = $title;
      $styles = ["product/style.css"];
      $scripts = ["product/script.js"];
    
      //we need to export the url with the current query params so that filtering and ordering is preserved
      $exportUrl = "/products/export?" . http_build_query($_GET);
      
      $controller = self::class;

      //we need to parse filters that will be used when getting parts of the page in the view
      $filters = self::parseFilters();

      require "../views/layout/filterableTable.php";
    }

    //function that decodes the changes from the request and calls the editProducts function from the model
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

    //function that exports products to a csv file
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

    //function that gets the filtering menu
    public static function getFilterMenu() {      
      //we need hidden inputs for these query params so that they are preserved when submitting the form
      $hiddenInputs = [];
      foreach (["order_by", "order", "page"] as $queryParam => $_) {
        if (!empty($_GET[$queryParam])) {
          $hiddenInputs[$queryParam] = $_GET[$queryParam];
        }
      }

      //filtering options that contain minimal and maximal price, product types and manufacturers
      $filteringOptions = Product::getFilteringOptions();

      //we get the min and max price from the query params or we use the default one
      $currentPriceMin = floor(empty($_GET["price_min"]) ? $filteringOptions["price_min"] : $_GET["price_min"]);
      $currentPriceMax = ceil(empty($_GET["price_max"]) ? $filteringOptions["price_max"] : $_GET["price_max"]);
      //if the min price is greater than the max price, we swap them
      if ($currentPriceMin > $currentPriceMax) {
        $tmp = $currentPriceMin;
        $currentPriceMin = $currentPriceMax;
        $currentPriceMax = $tmp;
      }

      //we check if the product type/manufacturer exists and if so, we store its id and name
      $selectedProductTypeId = self::parseIfArrayKey("product_type", $filteringOptions["product_types"]);
      $selectedProductTypeName = $filteringOptions["product_types"][$selectedProductTypeId] ?? "";
      $selectedManufacturerId = self::parseIfArrayKey("manufacturer", $filteringOptions["manufacturers"]);
      $selectedManufacturerName = $filteringOptions["manufacturers"][$selectedManufacturerId] ?? "";

      require "../views/product/filterMenu.php";
    }

    //function that gets the navigation between pages
    public static function getNav($filters = []) {
      $pageCount = ceil(Product::getProductCount($filters) / Product::DEFAULT_LIMIT);
      $currentPage =  min($_GET["page"] ?? 1, $pageCount);

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
      //we get the column by which data is currently ordered and the order (asc/desc)
      [$currentOrderByColumn, $order] = self::parseOrdering();

      /*NOTE: 
      we could optimise this by not building common querie and then do order and pagination
      queries separately
      */
      $columns = Product::ORDER_BY_COLUMNS + Product::OTHER_COLUMNS;
      $queries = [];
      $isAsc = [];

      /*
      for each column by which we can order data, we create a query for sorting
      by that column and store information about if the order is going to be ascending or descending
      */
      foreach (Product::ORDER_BY_COLUMNS as $key => $_) {
        $isCurrentlyOrderedByThisColumn = $currentOrderByColumn === $key;
        $newQueryParams = [];
        $newQueryParams["order_by"] = $key;

        //if this column is the one by which products are currently ordered
        if ($isCurrentlyOrderedByThisColumn && $order === Order::Asc) {
            $isAsc[] = true;
            $newQueryParams["order"] = Order::Desc;
        } else {
          $isAsc[] = false;
          $newQueryParams["order"] = Order::Asc;
        }
        //since the value is enum, we need to get the string value
        $newQueryParams["order"] = $newQueryParams["order"]->value;

        if ($isCurrentlyOrderedByThisColumn && isset($_GET["page"])) {
          $newQueryParams["page"] = $_GET["page"];
        }

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

    //NOTE: we could also take filter options as a parameter and apply filters if they are valid filter options
    //helper function to parse filters
    private static function parseFilters() {
      //we try to get price min/max and if there is none provide, we use 0/PHP_INT_MAX
      $priceMin = empty($_GET["price_min"]) ? 0 : floor($_GET["price_min"]);
      $priceMax = empty($_GET["price_max"]) ? PHP_INT_MAX : ceil($_GET["price_max"]);

      //if the price min is greater than the price max, we swap them
      if ($priceMin > $priceMax) {
        $tmp = $priceMin;
        $priceMin = $priceMax;
        $priceMax = $tmp;
      }
      
      /*
      we add filters to associative array where the key is the column name and the value is 
      a tuple containing comparison operator and the compared value(s)
      */
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

    //helper function to parse ordering
    private static function parseOrdering() {
      $orderBy = self::parseIfArrayKey("order_by", Product::ORDER_BY_COLUMNS, Product::DEFAULT_ORDER_BY_COLUMN);

      $order = empty($_GET["order"])
        ? Product::DEFAULT_ORDER
        : (Order::tryFrom($_GET["order"]) ?? Product::DEFAULT_ORDER);

      return [$orderBy, $order];
    }

    //NOTE: if we had more controllers, we should put this into separate file with helper functions
    //parses the query param and check if it's one of array keys, otherwise return default value
    private static function parseIfArrayKey(string $paramName, array $array, $default=null) {
      $key = $_GET[$paramName] ?? $default;
      return array_key_exists($key, $array)
        ? $key
        : $default;
    }
  }

?>