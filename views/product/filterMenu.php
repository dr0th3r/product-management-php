<form method="get" autocomplete="off">
  <h2>Filtrovat</h2>
  <?php foreach ($hiddenInputs as $name => $value): ?>
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
  <?php endforeach; ?>
  <label for="search">
    Kód produktu
  </label>
  <input type="text" name="search" placeholder="Kód produktu" value="" id="search">
  <label for="price-min">Minimální cena</label>
  <input 
    type="number" 
    name="price_min" 
    id="price-min" 
    <?php if ($currentPriceMin != $filteringOptions['price_min']) echo "value=$currentPriceMin" ?>
    placeholder="Minimální cena"
    max="<?= $filteringOptions['price_max'] ?>"
  >
  <label for="price-max">Maximální cena</label>
  <input 
    type="number" 
    name="price_max" 
    id="price-max" 
    <?php if ($currentPriceMax != $filteringOptions['price_max']) echo "value=$currentPriceMax" ?>
    placeholder="Maximální cena"
    min="<?= $filteringOptions['price_min'] ?>"
  >
  <div class="searchable-select" id="product_type">
    <label for="product-type-search">Typ produktu</label>
    <input type="hidden" name="product_type" class="id-input" value="<?= $selectedProductTypeId ?>">
    <input 
      type="text" 
      id="product-type-search" 
      class="search-input" 
      placeholder="Typ produktu" 
      value="<?= $selectedProductTypeName ?>"
    >
    <ul class="search-options">
      <?php foreach ($filteringOptions["product_types"] as $id => $name): ?>
        <li data-id="<?= $id ?>"><?= $name ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="searchable-select" id="manufacturer">
    <label for="manufacturer-search">Výrobce</label>
    <input type="hidden" name="manufacturer" class="id-input" value="<?= $selectedManufacturerId ?>">
    <input 
      type="text" 
      id="manufacturer-search" 
      class="search-input" 
      placeholder="Výrobce"
      value="<?= $selectedManufacturerName ?>"
    >
    <ul class="search-options">
      <?php foreach ($filteringOptions["manufacturers"] as $id => $name): ?>
        <li data-id="<?= $id ?>"><?= $name ?></li>
      <?php endforeach; ?>
    </ul>
  </div>  
  <button type="submit" id="show-results-btn">Zobrazit výsledky</button>
</form>