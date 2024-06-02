<form method="get">
  <?php foreach ($hiddenInputs as $name => $value): ?>
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
  <?php endforeach; ?>
  <input type="text" name="search" placeholder="Search" value="">
  <input 
    type="number" 
    name="price_min" 
    id="price-min" 
    <?php if ($priceMin != $filteringOptions['price_min']) echo "value=$priceMin" ?>
    placeholder="Min price"
    max="<?= $filteringOptions['price_max'] ?>"
  >
  <input 
    type="number" 
    name="price_max" 
    id="price-max" 
    <?php if ($priceMax != $filteringOptions['price_max']) echo "value=$priceMax" ?>
    placeholder="Max price"
    min="<?= $filteringOptions['price_min'] ?>"
  >
  <select name="product_type" id="product-type">
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
  <select name="manufacturer" id="manufacturer">
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
<?php if (isset($previousPageQuery)): ?>
  <a href="?<?= $previousPageQuery ?>">Previous page</a>
<?php endif; ?>
<?php if (isset($nextPageQuery)): ?>
  <a href="?<?= $nextPageQuery ?>">Next page</a>
<?php endif; ?>