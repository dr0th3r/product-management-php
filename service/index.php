<?php
  if (!isset($_GET["sort_order"]) || !in_array($_GET["sort_order"], ["asc", "desc"])) {
    $_GET["sort_order"] = "desc";
  }
  $opposite_sort_order = $_GET["sort_order"] == "asc" ? "desc" : "asc";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Extraueon</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <form method="get">
    <label for="search-code">Search</label>
    <input type="text" id="search-code" name="search">
    <button type="submit">Search</button>
  </form>
  <table>
    <thead>
      <tr>
        <th>
          <?php 
            echo "<a href=\"{$_SERVER['PHP_SELF']}?sort_by=code&sort_order={$opposite_sort_order}\">Code</a>"
          ?>
        </th>
        <th>
          <?php 
            echo "<a href=\"{$_SERVER['PHP_SELF']}?sort_by=price&sort_order={$opposite_sort_order}\">Price</a>"
          ?>
        </th>
        <th>
          <?php 
            echo "<a href=\"{$_SERVER['PHP_SELF']}?sort_by=product_type&sort_order={$opposite_sort_order}\">Product Type</a>"
          ?>
        </th>
        <th>
          <?php 
            echo "<a href=\"{$_SERVER['PHP_SELF']}?sort_by=manufacturer&sort_order={$opposite_sort_order}\">Manufacturer</a>"
          ?>
        </th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $dbname = "extradeon";

        $allowed_sort_columns = [
          "code" => true,
          "price" => true,        
          "product_type" => true,
          "manufacturer" => true,
        ];

        if (!isset($_GET["sort_by"]) || !isset($allowed_sort_columns[$_GET["sort_by"]])) {
          $_GET["sort_by"] = "code";
        }

        $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

        if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        }

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
          $search = "%{$_GET['search']}%";
          $stmt->bind_param("s", $search);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $description_short = substr($row["description"], 0, 100);
            $description_rest = substr($row["description"], 100);
            $description = <<<DESC
              <p>
                <span>{$description_short}</span><span class="show-more-dots">...</span><span class="show-more-text">{$description_rest}</span>
                <button class="show-more-btn">Show More</button>
              </p>
            DESC;
            echo <<<ROW
              <tr>
                <td>{$row["code"]}</td>
                <td>{$row["price"]}</td>
                <td>{$row["product_type_name"]}</td>
                <td>{$row["manufacturer_name"]}</td>
                <td>{$description}</td>
              </tr>
            ROW;
          }
        }

        $conn->close();
      ?>
    </tbody>
  </table>
  <script src="script.js"></script>
</body>
</html>