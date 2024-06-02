<thead>
  <tr>
    <?php 
      $i = 0;
      foreach($columns as $column): 
    ?>
      <th>
        <?php if (array_key_exists($i, $queries) && $query = $queries[$i]): ?>
          <a href="?<?= $query ?>"><?= $column ?></a>
          <?php if ($orders[$i]): ?>
            <span>▲</span>
          <?php else: ?>
            <span>▼</span>
          <?php endif; ?>
        <?php else: ?>
          <?= $column ?>
        <?php endif; ?>
    <?php 
      $i++;
      endforeach; 
    ?>
  <tr>
</thead>