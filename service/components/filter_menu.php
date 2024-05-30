<form method="get" class="filter-form">
  <label for="search-code">Search</label>
  <input type="text" id="search-code" name="search">
  <input type="number" id="price-min" name="price-min" placeholder="Price Min" min="$min_price">
  <input type="number" id="price-max" name="price-max" placeholder="Price Max" max="$max_price">
  <select id="product-type" name="product-type">
    <option value="">Product Type</option>
    <?php foreach ($product_types as $product_type): ?>
      <option value="<?= $product_type["id"] ?>"><?= $product_type["name"] ?></option>
    <?php endforeach; ?>
  </select>
  <select id="manufacturer" name="manufacturer">
    <option value="">Manufacturer</option>
    <?php foreach ($manufacturers as $manufacturer): ?>
      <option value="<?= $manufacturer["id"] ?>"><?= $manufacturer["name"] ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Search</button>
</form>