<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <meta name="description" content="$description">
  <?php foreach ($styles as $style): ?>
    <link rel="stylesheet" href="<?= $style ?>">
  <?php endforeach; ?>
</head>