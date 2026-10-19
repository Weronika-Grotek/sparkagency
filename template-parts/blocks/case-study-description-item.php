<?php

/**
 * Sekcja opisu case study block template.
 *
 * @package SparkAgency
 */

defined('ABSPATH') || exit;

$show_counter     = ! empty($attributes['showCounter']);
$counter_value    = isset($attributes['counterValue']) && is_numeric($attributes['counterValue']) ? (int) $attributes['counterValue'] : 0;
$counter_prefix   = isset($attributes['counterPrefix']) ? (string) $attributes['counterPrefix'] : '';
$counter_suffix   = isset($attributes['counterSuffix']) ? (string) $attributes['counterSuffix'] : '';
$counter_label    = isset($attributes['counterLabel']) ? (string) $attributes['counterLabel'] : '';
$counter_duration = isset($attributes['counterDuration']) && is_numeric($attributes['counterDuration']) ? (int) $attributes['counterDuration'] : 1200;
$counter_duration = min(5000, max(300, $counter_duration));
?>
<section <?php echo wp_kses_data(get_block_wrapper_attributes(array('class' => 'spark-case-study-description-item'))); ?>>
	<?php if (! empty($attributes['title'])) : ?>
		<h2 class="spark-case-study-description-item__title"><?php echo wp_kses_post($attributes['title']); ?></h2>
	<?php endif; ?>
	<div class="spark-case-study-description-item__body">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered and sanitized by Gutenberg.
		?>
	</div>
	<?php if ($show_counter) : ?>
		<div class="spark-case-study-description-item__counter" data-case-study-counter data-counter-value="<?php echo esc_attr($counter_value); ?>" data-counter-duration="<?php echo esc_attr($counter_duration); ?>">
			<p class="spark-case-study-description-item__counter-value"><span><?php echo esc_html($counter_prefix); ?></span><strong data-case-study-counter-value><?php echo esc_html($counter_value); ?></strong><span><?php echo esc_html($counter_suffix); ?></span></p>
			<?php if ('' !== $counter_label) : ?>
				<p class="spark-case-study-description-item__counter-label"><?php echo wp_kses_post($counter_label); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
