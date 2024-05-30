<?php
  const DB_HOST = "localhost";
  const DB_USER = "root";
  const DB_PASSWORD = "";
  const DB_NAME = "extradeon";

  $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if (!isset($_GET["sort_order"]) || !in_array($_GET["sort_order"], ["asc", "desc"])) {
    $_GET["sort_order"] = "desc";
  }

  const SORT_COLUMNS = [
    "code" => "Code",
    "price" => "Price",
    "product_type" => "Product Type",
    "manufacturer" => "Manufacturer",
  ];
        
  if (!isset($_GET["sort_by"]) || !isset(SORT_COLUMNS[$_GET["sort_by"]])) {
    $_GET["sort_by"] = "code";
  }
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
    <?php include "components/thead.php" ?>
    <?php include "components/tbody.php" ?>
  </table>
  <script src="script.js"></script>
</body>
</html>
<?php $conn->close(); ?>