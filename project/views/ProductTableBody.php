<tbody>
  <?php foreach ($products as $product): ?>
    <tr id="<?= $product["id"] ?>">
      <td class="code"><?= $product["code"] ?></td>
      <td class="price"><?= $product["price"] ?></td>
      <td class="product-type"><?= $product["product_type_name"] ?></td>
      <td class="manufacturer"><?= $product["manufacturer_name"] ?></td>
      <?php
        $descriptionShort = substr($product["description"], 0, 100);
        $descriptionRest = substr($product["description"], 100);
      ?>
      <td class="description">
        <span class="description-short"><?= $descriptionShort ?></span>
        <span class="description-dots visible">...</span>
        <span class="description-rest"><?= $descriptionRest ?></span>
        <button class="show-more-btn">Zobrazit více</button>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>