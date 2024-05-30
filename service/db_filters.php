<?php
  $db_filters = "";

  $param_types = "";
  $prepared_params = [];

  function set_filter(string $column, string $comparison_type, string $param_type, $param_value) {
    global $db_filters, $param_types, $prepared_params;

    static $is_where_set = false;
    if (!$is_where_set) {
      $db_filters .= "WHERE ";
      $is_where_set = true;
    } else {
      $db_filters .= "AND ";
    }

    $param_types .= $param_type;
    $prepared_params[] = $param_value;
    $db_filters .= "{$column} {$comparison_type} ? ";
  }

  if (!empty($_GET["search"]))
    set_filter("product.code", "LIKE", "s", "%{$_GET['search']}%");
  // we don't need to check if it's min because we handle that in /components/filter_menu.php
  if ($_GET["price-min"] != $min_price)
    set_filter("product.price", ">=", "i", $_GET["price-min"]);
  // we don't need to check if it's min because we handle that in /components/filter_menu.php
  if ($_GET["price-max"] != $max_price)
    set_filter("product.price", "<=", "i", $_GET["price-max"]);
  if (!empty($_GET["product-type"]))
    set_filter("product.product_type", "=", "i", $_GET["product-type"]);
  if (!empty($_GET["manufacturer"]))
    set_filter("product.manufacturer", "=", "i", $_GET["manufacturer"]);
?>