<tbody>
  <?php foreach ($products as $product): ?>
    <tr>
      <td><?= $product["code"] ?></td>
      <td><?= $product["price"] ?></td>
      <td><?= $product["product_type_name"] ?></td>
      <td><?= $product["manufacturer_name"] ?></td>
      <?php
        $descriptionShort = substr($product["description"], 0, 100);
        $descriptionRest = substr($product["description"], 100);
      ?>
      <td>
        <span class="description-short"><?= $descriptionShort ?></span>
        <span class="description-dots visible">...</span>
        <span class="description-rest"><?= $descriptionRest ?></span>
        <button class="show-more-btn">Show More</button>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>