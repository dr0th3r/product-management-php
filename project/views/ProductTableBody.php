<tbody>
  <?php foreach ($products as $product): ?>
    <tr>
      <td><?= $product["code"] ?></td>
      <td><?= $product["price"] ?></td>
      <td><?= $product["product_type_name"] ?></td>
      <td><?= $product["manufacturer_name"] ?></td>
      <td><?= $product["description"] ?></td>
    </tr>
  <?php endforeach; ?>
</tbody>