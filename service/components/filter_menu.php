<?php
    $min_price_param = intval($_GET["price-min"] ?? $min_price);
    $max_price_param = intval($_GET["price-max"] ?? $max_price);

    $error = "";

    if ($min_price_param > $max_price_param) {
      $min_price_param = $min_price;
      $max_price_param = $max_price;
      $error = "Price Min must be less than or equal to Price Max";
    }

    $_GET["price-min"] = $min_price_param;
    $_GET["price-max"] = $max_price_param;

    include "db_filters.php";

    $sql = "SELECT COUNT(*) as product_count FROM product $db_filters;";

    $stmt = $conn->prepare($sql);

    if (count($prepared_params) > 0) {
      $stmt->bind_param($param_types, ...$prepared_params);
    }

    $stmt->execute();

    $result = $stmt->get_result();
    
    $product_count = $result->fetch_assoc()["product_count"];

    echo $product_count;
?>

<form method="get" class="filter-form">
  <label for="search-code">Search</label>
  <input type="text" id="search-code" name="search">
  <input 
    type="number" 
    id="price-min" 
    name="price-min" 
    placeholder="Price Min" 
    min="$min_price" 
    max="$max_price"
    value="<?= $min_price_param ?>"
  >
  <input 
    type="number" 
    id="price-max" 
    name="price-max" 
    placeholder="Price Max" 
    min="$min_price"
    max="$max_price"
    value="<?= $max_price_param ?>"
  >
  <select id="product-type" name="product-type">
    <option value="">Product Type</option>
    <?php 
      $selected_product_type = $_GET["product-type"] ?? "";
      foreach ($product_types as $product_type): 
    ?>
      <option 
        value="<?= $product_type["id"] ?>"
        <?php if ($product_type["id"] == $selected_product_type) echo "selected"; ?>
      >
        <?= $product_type["name"] ?>
      </option>
    <?php endforeach; ?>
  </select>
  <select id="manufacturer" name="manufacturer">
    <option value="">Manufacturer</option>
    <?php 
      $selected_manufacturer = $_GET["manufacturer"] ?? "";
      foreach ($manufacturers as $manufacturer): 
    ?>
      <option 
        value="<?= $manufacturer["id"] ?>" 
        <?php if ($manufacturer["id"] == $selected_manufacturer) echo "selected"; ?>
      >
        <?= $manufacturer["name"] ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Search</button>
  <?php
    if (!empty($error))
      echo "<p>{$error}</p>";

    if ($product_count > PRODUCTS_PER_PAGE) {
      $page_count = ceil($product_count / PRODUCTS_PER_PAGE);
      $current_page = $_GET["page"] ?? 1;
      $_GET["page"] = $current_page;
      $prev_page = $current_page - 1;
      $next_page = $current_page + 1;
      if ($prev_page > 0) {
        echo "<a href='?page={$prev_page}'>Prev</a>";
      }
      if ($next_page <= $page_count) {
        echo "<a href='?page={$next_page}'>Next</a>";
      }
    }
  ?>
</form>