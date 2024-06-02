<form method="get">
  <h2>Filtrovat</h2>
  <?php foreach ($hiddenInputs as $name => $value): ?>
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
  <?php endforeach; ?>
  <label>
    Kód produktu
    <input type="text" name="search" placeholder="Kód produktu" value="" id="search">
  </label>
  <label>
    Minimální cena
    <input 
      type="number" 
      name="price_min" 
      id="price-min" 
      <?php if ($priceMin != $filteringOptions['price_min']) echo "value=$priceMin" ?>
      placeholder="Minimální cena"
      max="<?= $filteringOptions['price_max'] ?>"
    >
  </label>
  <label>
    Maximální cena
    <input 
      type="number" 
      name="price_max" 
      id="price-max" 
      <?php if ($priceMax != $filteringOptions['price_max']) echo "value=$priceMax" ?>
      placeholder="Maximální cena"
      min="<?= $filteringOptions['price_min'] ?>"
    >
  </label>
  <div class="searchable-select" id="product_type">
    <input type="hidden" name="product_type" class="id-input" value="<?= $selectedProductTypeId ?>">
    <label>
      Typ produktu
      <input 
        type="text" 
        class="search-input" 
        placeholder="Typ produktu" 
        value="<?= $selectedProductType ?>"
      >
    </label>
    <ul class="search-options">
      <?php foreach ($filteringOptions["product_types"] as $productType): ?>
        <li data-id="<?= $productType["id"] ?>"><?= $productType["name"] ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="searchable-select" id="manufacturer">
    <input type="hidden" name="manufacturer" class="id-input" value="<?= $selectedManufacturerId ?>">
    <label>
      Výrobce
      <input 
        type="text" 
        id="manufacturer-search" 
        class="search-input" 
        placeholder="Výrobce"
        value="<?= $selectedManufacturer ?>"
      >
    </label>
    <ul class="search-options">
      <?php foreach ($filteringOptions["manufacturers"] as $manufacturer): ?>
        <li data-id="<?= $manufacturer["id"] ?>"><?= $manufacturer["name"] ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <button type="submit">Zobrazit výsledky</button>
</form>