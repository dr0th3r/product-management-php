<!DOCTYPE html>
<html lang="en">
<?php require "../views/layout/head.php"; ?>
<body>
  <div class="container">
    <aside>
      <div>
        <?php
          $product::getFilterMenu();
          $product::getNav($filters);
        ?>
      </div>
      <div class="edit-export-btns">
        <button id="edit-btn">Upravit</button>
        <button id="cancel-btn" class="hidden">Neuložit</button>
        <a href="<?= $exportUrl ?>" id="export-btn">Export</a>
      </div>
    </aside>
    <table>
      <?php
        $product::getTableHead();
        $product::getTableBody($filters);
      ?>
    </table>
  </div>
  <!-- this would probably be in a footer if there was one  -->
  <?php foreach ($scripts as $script): ?>
    <script src="<?= $script ?>"></script>
  <?php endforeach; ?>
</body>
</html>