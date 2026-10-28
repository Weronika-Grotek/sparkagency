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
$metrics = isset($attributes['metrics']) && is_array($attributes['metrics']) ? $attributes['metrics'] : array();
if (empty($metrics) && $show_counter) {
	$metrics[] = array(
		'value'    => $counter_value,
		'prefix'   => $counter_prefix,
		'suffix'   => $counter_suffix,
		'label'    => $counter_label,
		'duration' => $counter_duration,
	);
}
?>
<section <?php echo wp_kses_data(get_block_wrapper_attributes(array('class' => 'spark-case-study-description-item'))); ?>>
	<?php if (! empty($attributes['title'])) : ?>
		<h2 class="spark-case-study-description-item__title"><?php echo wp_kses_post($attributes['title']); ?></h2>
	<?php endif; ?>
	<div class="spark-case-study-description-item__body">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered and sanitized by Gutenberg.
		?>
	</div>
	<?php if (! empty($metrics)) : ?>
		<div class="spark-case-study-description-item__metrics">
			<?php foreach ($metrics as $metric) :
				$value = isset($metric['value']) && is_numeric($metric['value']) ? (int) $metric['value'] : 0;
				$prefix = isset($metric['prefix']) ? (string) $metric['prefix'] : '';
				$suffix = isset($metric['suffix']) ? (string) $metric['suffix'] : '';
				$label = isset($metric['label']) ? (string) $metric['label'] : '';
				$duration = isset($metric['duration']) && is_numeric($metric['duration']) ? min(5000, max(300, (int) $metric['duration'])) : $counter_duration;
			?>
				<div class="spark-case-study-description-item__metric" data-case-study-counter data-counter-value="<?php echo esc_attr($value); ?>" data-counter-duration="<?php echo esc_attr($duration); ?>">
					<p class="spark-case-study-description-item__counter-value"><span><?php echo esc_html($prefix); ?></span><strong data-case-study-counter-value><?php echo esc_html($value); ?></strong><span><?php echo esc_html($suffix); ?></span></p>
					<?php if ('' !== $label) : ?>
						<p class="spark-case-study-description-item__counter-label"><?php echo wp_kses_post($label); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
