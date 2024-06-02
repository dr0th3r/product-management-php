<nav>
  <?php if ($pageCount == 1): ?>
    <p>1 strana z 1</p>
  <?php else: ?>
    <ol>
      <?php if (isset($firstPageQuery)): ?>
        <li><a href="?<?= $firstPageQuery ?>">1</a></li>
      <?php endif; ?>
      <?php if (isset($previousPageQuery)): ?>
        <li><a href="?<?= $previousPageQuery ?>"><?= $currentPage - 1 ?></a></li>
      <?php endif; ?>
      <li><?= $currentPage ?></li>
      <?php if (isset($nextPageQuery)): ?>
        <li><a href="?<?= $nextPageQuery ?>"><?= $currentPage + 1 ?></a></li>
      <?php endif; ?>
      <?php if (isset($lastPageQuery)): ?>
        <li><a href="?<?= $lastPageQuery ?>"><?= $pageCount ?></a></li>
      <?php endif; ?>
    </ol>
  <?php endif; ?>
</nav>