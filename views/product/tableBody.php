<tbody>
  <?php foreach ($products as $product): ?>
    <tr id="<?= $product["id"] ?>">
      <td class="code"><?= $product["code"] ?></td>
      <td class="price"><?= $product["price"] ?></td>
      <!-- there is product_type with underscore becaue that's the name in the db -->
      <td class="product_type"><?= $product["product_type_name"] ?></td>
      <td class="manufacturer"><?= $product["manufacturer_name"] ?></td>
      <?php
        $descriptionShort = substr($product["description"], 0, 100);
        $descriptionRest = substr($product["description"], 100);
      ?>
      <td class="description">
        <span class="description-short"><?= $descriptionShort ?></span>
        <span 
          class="description-dots <?= $descriptionRest ? "" : "hidden" ?>"
        >...</span>
        <span class="description-rest hidden"><?= $descriptionRest ?></span>
        <button class="show-more-btn <?= $descriptionRest ? "" : "hidden" ?>">Zobrazit více</button>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>