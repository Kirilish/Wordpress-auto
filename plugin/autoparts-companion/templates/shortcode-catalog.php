<section class="ap-catalog" data-ap-catalog>
    <button class="ap-filter-toggle" type="button" data-ap-filter-toggle>Фильтры</button>
    <form class="ap-filters" data-ap-filters>
        <input name="search" placeholder="Название или OEM">
        <input name="brand" placeholder="Марка">
        <input name="model" placeholder="Модель">
        <input name="oem" placeholder="OEM">
        <select name="condition"><option value="">Состояние</option><option>новая</option><option>б/у</option><option>восстановленная</option><option>под ремонт</option></select>
        <input name="price_min" type="number" placeholder="Цена от"><input name="price_max" type="number" placeholder="Цена до">
        <select name="sort"><option value="">Сначала новые</option><option value="price_asc">Цена ↑</option><option value="price_desc">Цена ↓</option></select>
        <button>Показать</button>
    </form>
    <div class="ap-grid" data-ap-results><div class="ap-skeleton"></div><div class="ap-skeleton"></div><div class="ap-skeleton"></div></div>
</section>
