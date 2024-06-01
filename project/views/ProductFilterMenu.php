<form method="get">
  <?php foreach ($hiddenInputs as $hiddenInput): ?>
    <!-- <input type="hidden" name="<?= $hiddenInput ?>" value="<?= $_GET[$hiddenInput] ?>"> -->
  <?php endforeach; ?>
  <input type="text" name="search" placeholder="Search" value="">
  <input type="number" name="price_min" id="price-min" value="<?= $filteringOptions['price_min'] ?>">
  <input type="number" name="price_max" id="price-max" value="<?= $filteringOptions['price_max'] ?>">
  <select name="product_type">
    <option value="">All product types</option>
    <?php foreach ($filteringOptions["product_types"] as $productType): ?>
      <option 
        value="<?= $productType["id"] ?>"
        <?php if ($selectedProductType == $productType["id"]) echo "selected" ?>
      >
        <?= $productType["name"] ?>
      </option>
    <?php endforeach; ?>
  </select>
  <select name="manufacturer">
    <option value="">All manufacturers</option>
    <?php foreach ($filteringOptions["manufacturers"] as $manufacturer): ?>
      <option 
        value="<?= $manufacturer["id"] ?>"
        <?php if ($selectedManufacturer == $manufacturer["id"]) echo "selected" ?>
      >
        <?= $manufacturer["name"] ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Filter</button>
</form>