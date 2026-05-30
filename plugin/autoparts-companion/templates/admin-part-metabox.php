<div class="ap-metabox-grid">
<?php foreach (\AutoParts\Meta::PART_FIELDS as $key => $type) : $value = get_post_meta($post->ID, $key, true); ?>
    <label><?php echo esc_html(str_replace('_ap_', '', $key)); ?>
        <?php if ('textarea' === $type) : ?><textarea name="<?php echo esc_attr($key); ?>"><?php echo esc_textarea((string) $value); ?></textarea>
        <?php else : ?><input name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $value); ?>" type="<?php echo 'number' === $type || 'integer' === $type ? 'number' : 'text'; ?>">
        <?php endif; ?>
    </label>
<?php endforeach; ?>
</div>
