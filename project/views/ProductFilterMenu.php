<form method="get">
  <?php foreach ($hiddenInputs as $hiddenInput): ?>
    <!-- <input type="hidden" name="<?= $hiddenInput ?>" value="<?= $_GET[$hiddenInput] ?>"> -->
  <?php endforeach; ?>
  <input type="text" name="search" placeholder="Search" value="">
  <input type="number" name="price-min" id="price-min" value="<?= $filteringOptions['price-min'] ?>">
  <input type="number" name="price-max" id="price-max" value="<?= $filteringOptions['price-max'] ?>">
  <select name="product-type">
    <option value="">All product types</option>
    <?php foreach ($filteringOptions["product-types"] as $productType): ?>
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