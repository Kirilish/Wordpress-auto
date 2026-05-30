<div class="wrap ap-admin"><h1>Быстро добавить запчасть</h1>
<form class="ap-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
<input type="hidden" name="action" value="autoparts_quick_add"><?php wp_nonce_field('autoparts_quick_add'); ?>
<label>Название<input required name="part_name" placeholder="Фара левая Kia K5"></label><label>Марка<input name="brand" placeholder="Kia"></label><label>Модель<input name="model" placeholder="K5"></label><label>OEM<input name="oem" placeholder="92101-L2100"></label><label>Цена<input name="price" type="number" step="0.01"></label><label>Телефон<input name="phone"></label><label>Описание<textarea name="description"></textarea></label><button class="button button-primary">Создать карточку</button>
</form></div>
