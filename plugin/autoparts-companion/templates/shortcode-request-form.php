<form class="ap-request-form" data-ap-request-form>
    <input type="hidden" name="part_id" value="<?php echo esc_attr((string) $part_id); ?>">
    <input type="hidden" name="page_url" value="<?php echo esc_url(get_permalink()); ?>">
    <label>Имя<input name="client_name" autocomplete="name" required></label>
    <label>Телефон<input name="client_phone" autocomplete="tel" required></label>
    <label>Email<input name="client_email" type="email" autocomplete="email"></label>
    <label>Комментарий<textarea name="comment" placeholder="Уточните авто, VIN или вопрос"></textarea></label>
    <button type="submit">Отправить заявку</button><p class="ap-form-status" aria-live="polite"></p>
</form>
