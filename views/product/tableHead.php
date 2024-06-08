<thead>
  <tr>
    <?php 
      foreach($orderByColumns as $key => $column): 
      $isCurrentlyOrderedByThisColumn = $currentOrderByColumn === $key;
    ?>
      <th>
        <a href="?<?= $commonQuery ?><?= $individualQueries[$key] ?>">
          <?= $column ?>
          <?php if ($isCurrentlyOrderedByThisColumn && $isCurrentAsc): ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M182.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-9.2 9.2-11.9 22.9-6.9 34.9s16.6 19.8 29.6 19.8H288c12.9 0 24.6-7.8 29.6-19.8s2.2-25.7-6.9-34.9l-128-128z"/></svg>
          <?php elseif ($isCurrentlyOrderedByThisColumn): ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 320 320 320"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M182.6 470.6c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128z"/></svg>
          <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M137.4 41.4c12.5-12.5 32.8-12.5 45.3 0l128 128c9.2 9.2 11.9 22.9 6.9 34.9s-16.6 19.8-29.6 19.8H32c-12.9 0-24.6-7.8-29.6-19.8s-2.2-25.7 6.9-34.9l128-128zm0 429.3l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128c-12.5 12.5-32.8 12.5-45.3 0z"/></svg>
          <?php endif; ?>
        </a>
      </th>
    <?php endforeach; ?>
    <?php foreach($otherColumns as $key => $column): ?>
      <th><?= $column ?></th>
    <?php endforeach; ?>
  <tr>
</thead>