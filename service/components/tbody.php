<tbody>
  <?php
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

    $param_types = "";
    $prepared_params = [];

    function set_filter(string $column, string $comparison_type, string $param_type, $param_value) {
      global $sql, $param_types, $prepared_params;

      static $is_where_set = false;
      if (!$is_where_set) {
        $sql .= "WHERE ";
        $is_where_set = true;
      } else {
        $sql .= "AND ";
      }

      $param_types .= $param_type;
      $prepared_params[] = $param_value;
      $sql .= "{$column} {$comparison_type} ? ";
    }

    if (!empty($_GET["search"]))
      set_filter("product.code", "LIKE", "s", "%{$_GET['search']}%");
    if ($_GET["price-min"] != $min_price)
      set_filter("product.price", ">=", "i", $_GET["price-min"]);
    if ($_GET["price-max"] != $max_price)
      set_filter("product.price", "<=", "i", $_GET["price-max"]);
    if (!empty($_GET["product-type"]))
      set_filter("product.product_type", "=", "i", $_GET["product-type"]);
    if (!empty($_GET["manufacturer"]))
      set_filter("product.manufacturer", "=", "i", $_GET["manufacturer"]);
    
    $sql .= "
    ORDER BY
      {$_GET["sort_by"]} {$_GET["sort_order"]}
    LIMIT 10;
    ";

    $stmt = $conn->prepare($sql);

    if (count($prepared_params) > 0) {
      $stmt->bind_param($param_types, ...$prepared_params);
    }

    $stmt->execute();

    $result = $stmt->get_result();
  ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <?php
      $description_short = substr($row["description"], 0, 100);
      $description_rest = substr($row["description"], 100);
      $description = <<<DESC
        <p>
          <span>{$description_short}</span><span class="show-more-dots">...</span><span class="show-more-text">{$description_rest}</span>
          <button class="show-more-btn">Show More</button>
        </p>
      DESC;
    ?>
    <tr>
      <td><?= $row["code"] ?></td>
      <td><?= $row["price"] ?></td>
      <td><?= $row["product_type_name"] ?></td>
      <td><?= $row["manufacturer_name"] ?></td>
      <td><?= $description ?></td>
    </tr>
  <?php 
    endwhile; 
    $stmt->close();
  ?>
</tbody>