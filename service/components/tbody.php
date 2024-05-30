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

    if (isset($_GET["search"])) {
      $sql .= "WHERE product.code LIKE ?";
    }

    $sql .= "
    ORDER BY
      {$_GET["sort_by"]} {$_GET["sort_order"]}
    LIMIT 10;
    ";

    $stmt = $conn->prepare($sql);

    if (isset($_GET["search"])) {
      $search = "%{$search}%";
      $stmt->bind_param("s", $search);
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