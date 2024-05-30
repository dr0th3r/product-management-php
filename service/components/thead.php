<thead>
  <?php 
    $search = isset($_GET["search"]) ? htmlspecialchars($_GET["search"]) : "";
  ?>
  <?php foreach (SORT_COLUMNS as $column => $column_name): ?>
    <?php
      $sort_order = DEFAULT_SORT_ORDER;
      //we don't need to check if it's set because we handle that in /index.php
      if ($_GET["sort_by"] === $column) {
        $sort_order = $_GET["sort_order"] === "desc" ? "asc" : "desc";
      }
    ?>
    <th>
      <a href="?sort_by=<?= $column ?>&sort_order=<?= $sort_order ?>&search=<?= $search ?>">
        <?= $column_name ?>
      </a>
    </th>
  <?php endforeach; ?> 
  <th>Description</th>
</thead>