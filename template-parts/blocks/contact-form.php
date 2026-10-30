<?php

/** Quick contact form for the homepage. @package SparkAgency */
defined('ABSPATH') || exit;
$text = static fn($v): string => is_string($v) ? trim($v) : '';
$value = static function (string $key, string $default = '') use ($attributes, $text): string {
	$stored = $text($attributes[$key] ?? '');
	return '' !== $stored ? $stored : $default;
};
$topics = array();
foreach (is_array($attributes['topics'] ?? null) ? $attributes['topics'] : array() as $topic) {
	if (! is_array($topic)) {
		continue;
	}
	$label = $text($topic['label'] ?? '');
	$key = sanitize_key($text($topic['value'] ?? ''));
	$color = sanitize_hex_color($text($topic['color'] ?? ''));
	if ('' !== $label && '' !== $key) {
		$topics[] = array('label' => $label, 'key' => $key, 'color' => $color ?: '#FFFFFF');
	}
}
$id = wp_unique_id('spark-contact-quick-');
$anchor = sanitize_title($text($attributes['anchor'] ?? ''));
$status = sanitize_key(wp_unslash($_GET['spark_contact_status'] ?? ''));
$form_started_at = (string) time();
$form_started_signature = wp_hash($form_started_at . '|sparkagency_contact_form');
?>
<section <?php echo get_block_wrapper_attributes(array('class' => 'spark-contact-form', 'id' => $anchor ?: $id)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?> data-spark-contact-form data-spark-contact-quick>
	<div class="spark-contact-form__inner">
		<div class="spark-contact-form__layout">
			<div class="spark-contact-form__intro"><span class="spark-contact-form__eyebrow"><?php echo esc_html($value('eyebrow')); ?></span>
				<h2><?php echo wp_kses_post($value('title')); ?></h2>
				<p class="spark-contact-form__lede"><?php echo wp_kses_post($value('lede')); ?></p>
				<p><a class="spark-contact-form__full-link" href="<?php echo esc_url($value('fullFormUrl', '/kontakt/')); ?>" data-contact-full-link><?php echo esc_html($value('fullFormLabel')); ?> <span aria-hidden="true">→</span></a></p>
			</div>
			<div><?php if ('success' === $status) : ?><p class="spark-contact-form__notice" role="status" tabindex="-1" data-spark-contact-notice><?php echo esc_html($value('successMessage')); ?></p><?php elseif ('limited' === $status) : ?><p class="spark-contact-form__notice is-error" role="alert" tabindex="-1" data-spark-contact-notice><?php esc_html_e('Wysłano już kilka wiadomości. Odczekaj 10 minut i spróbuj ponownie.', 'sparkagency'); ?></p><?php elseif (in_array($status, array('invalid', 'upload', 'mail'), true)) : ?><p class="spark-contact-form__notice is-error" role="alert" tabindex="-1" data-spark-contact-notice><?php esc_html_e('Nie udało się wysłać wiadomości. Sprawdź formularz i spróbuj ponownie.', 'sparkagency'); ?></p><?php endif; ?>
				<form class="spark-contact-form__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" novalidate><input type="hidden" name="action" value="sparkagency_submit_contact_form" /><input type="hidden" name="spark_contact_anchor" value="<?php echo esc_attr($anchor ?: $id); ?>" /><input type="hidden" name="spark_contact_topics" value="" data-contact-topics /><input type="hidden" name="spark_contact_topic_keys" value="" data-contact-topic-keys /><input type="hidden" name="spark_contact_started_at" value="<?php echo esc_attr($form_started_at); ?>" /><input type="hidden" name="spark_contact_started_signature" value="<?php echo esc_attr($form_started_signature); ?>" /><?php wp_nonce_field('sparkagency_contact_form', 'spark_contact_nonce'); ?>
					<div class="spark-contact-form__honeypot" aria-hidden="true"><label for="<?php echo esc_attr($id . '-website'); ?>">Pozostaw to pole puste<input id="<?php echo esc_attr($id . '-website'); ?>" name="spark_contact_website" type="text" value="" tabindex="-1" autocomplete="off" /></label></div>
					<fieldset>
						<legend><?php echo esc_html($value('topicsLabel')); ?></legend>
						<div class="spark-contact-form__topics" data-contact-topic-buttons><?php foreach ($topics as $topic) : ?><button type="button" aria-pressed="false" data-topic-key="<?php echo esc_attr($topic['key']); ?>" data-topic-label="<?php echo esc_attr($topic['label']); ?>"><i style="background:<?php echo esc_attr($topic['color']); ?>"></i><?php echo esc_html($topic['label']); ?></button><?php endforeach; ?></div>
					</fieldset>
				<div class="spark-contact-form__grid"><label class="spark-contact-form__field"><span><?php echo esc_html($value('nameLabel')); ?> *</span><input name="spark_contact_name" maxlength="45" required autocomplete="name" placeholder="Anna Kowalska" /><small class="spark-contact-form__error" data-error-for="spark_contact_name"></small></label><label class="spark-contact-form__field"><span><?php echo esc_html($value('emailLabel')); ?> *</span><input name="spark_contact_email" type="email" maxlength="254" required autocomplete="email" placeholder="anna@firma.pl" /><small class="spark-contact-form__error" data-error-for="spark_contact_email"></small></label><label class="spark-contact-form__field"><span><?php echo esc_html($value('companyLabel')); ?></span><input name="spark_contact_company" maxlength="50" autocomplete="organization" placeholder="Firma sp. z o.o." /><small class="spark-contact-form__error" data-error-for="spark_contact_company"></small></label><label class="spark-contact-form__field spark-contact-form__field--full"><span><?php echo esc_html($value('messageLabel')); ?> *</span><textarea name="spark_contact_message" rows="3" maxlength="300" required placeholder="Krótko opisz projekt, cel i termin."></textarea><small class="spark-contact-form__error" data-error-for="spark_contact_message"></small></label></div>
				<label class="spark-contact-form__consent"><input name="spark_contact_consent" type="checkbox" value="1" required /><span><?php echo esc_html($value('consentText')); ?> <a href="<?php echo esc_url($value('privacyUrl')); ?>"><?php echo esc_html($value('privacyLabel')); ?></a></span><small class="spark-contact-form__error" data-error-for="spark_contact_consent"></small></label><label class="spark-contact-form__field"><span><?php echo esc_html($value('attachmentLabel')); ?></span><input name="spark_contact_attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" /><small class="spark-contact-form__error" data-error-for="spark_contact_attachment"></small></label>
					<div class="spark-contact-form__foot"><span><?php esc_html_e('Wymagane: imię, e-mail, wiadomość, zgoda', 'sparkagency'); ?></span><button type="submit"><?php echo esc_html($value('submitLabel')); ?> <b aria-hidden="true">→</b></button></div>
				</form>
			</div>
		</div>
	</div>
</section>
