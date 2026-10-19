<?php

/** Full contact form template. @package SparkAgency */
defined('ABSPATH') || exit;
$text = static fn($v): string => is_string($v) ? trim($v) : '';
$topics = array();
foreach (is_array($attributes['topics'] ?? null) ? $attributes['topics'] : array() as $topic) {
	if (! is_array($topic)) {
		continue;
	}
	$label = $text($topic['label'] ?? '');
	$key = sanitize_key($text($topic['value'] ?? ''));
	$color = sanitize_hex_color($text($topic['color'] ?? ''));
	if ($label && $key) {
		$topics[] = array('label' => $label, 'key' => $key, 'color' => $color ?: '#FFFFFF');
	}
}
$id = wp_unique_id('spark-contact-full-');
$anchor = sanitize_title($text($attributes['anchor'] ?? ''));
$status = sanitize_key(wp_unslash($_GET['spark_contact_status'] ?? ''));
?>
<section <?php echo get_block_wrapper_attributes(array('class' => 'spark-contact-full-form', 'id' => $anchor ?: $id)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?> data-spark-contact-full-form>
	<div class="spark-contact-full-form__inner">
		<header>
			<h2><?php echo wp_kses_post($text($attributes['title'] ?? '')); ?></h2>
			<p><?php echo wp_kses_post($text($attributes['lede'] ?? '')); ?></p>
		</header>
		<div class="spark-contact-full-form__layout">
			<div><?php if ('success' === $status) : ?><p class="spark-contact-full-form__notice" role="status"><?php echo esc_html($text($attributes['successMessage'] ?? '')); ?></p><?php endif; ?><form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" novalidate><input type="hidden" name="action" value="sparkagency_submit_contact_form" /><input type="hidden" name="spark_contact_anchor" value="<?php echo esc_attr($anchor ?: $id); ?>" /><input type="hidden" name="spark_contact_topics" data-contact-topics value="" /><?php wp_nonce_field('sparkagency_contact_form', 'spark_contact_nonce'); ?>
					<fieldset>
						<legend><b>01</b>Czego dotyczy projekt</legend>
						<div class="spark-contact-full-form__topics" data-contact-topic-buttons><?php foreach ($topics as $topic) : ?><button type="button" aria-pressed="false" data-topic-key="<?php echo esc_attr($topic['key']); ?>" data-topic-label="<?php echo esc_attr($topic['label']); ?>"><i style="background:<?php echo esc_attr($topic['color']); ?>"></i><?php echo esc_html($topic['label']); ?></button><?php endforeach; ?></div>
					</fieldset>
					<fieldset>
						<legend><b>02</b>O projekcie</legend><label>Opis *<textarea name="spark_contact_message" maxlength="300" required rows="5" placeholder="Cel, zakres, grupa docelowa, co już macie gotowe."></textarea><small class="spark-contact-full-form__error" data-error-for="spark_contact_message"></small></label><label>Załącz brief · opcjonalnie<input name="spark_contact_attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_attachment"></small></label>
						<div class="spark-contact-full-form__grid"><label>Budżet<select name="spark_contact_budget">
									<option value="">Wybierz opcję</option>
									<option>do 50 tys. zł</option>
									<option>50–150 tys. zł</option>
									<option>150–500 tys. zł</option>
									<option>powyżej 500 tys. zł</option>
									<option>Jeszcze nie wiem</option>
								</select></label><label>Planowany start<select name="spark_contact_start">
									<option>Jak najszybciej</option>
									<option>W ciągu miesiąca</option>
									<option>W ciągu kwartału</option>
									<option>Później / rozpoznanie</option>
								</select></label><label>Skąd o nas wiesz<select name="spark_contact_source">
									<option>Polecenie</option>
									<option>Wyszukiwarka</option>
									<option>LinkedIn</option>
									<option>Wydarzenie branżowe</option>
									<option>Inne</option>
								</select></label></div>
					</fieldset>
					<fieldset>
						<legend><b>03</b>Dane kontaktowe</legend>
						<div class="spark-contact-full-form__grid"><label>Imię i nazwisko *<input name="spark_contact_name" maxlength="45" required autocomplete="name" placeholder="Anna Kowalska" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_name"></small></label><label>Firma<input name="spark_contact_company" maxlength="50" autocomplete="organization" placeholder="Firma sp. z o.o." /><small class="spark-contact-full-form__error" data-error-for="spark_contact_company"></small></label><label>E-mail *<input name="spark_contact_email" type="email" required autocomplete="email" placeholder="anna@firma.pl" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_email"></small></label><label>Telefon<input name="spark_contact_phone" type="tel" autocomplete="tel" placeholder="+48 600 000 000" /></label></div>
					</fieldset>
					<label class="spark-contact-full-form__consent"><input name="spark_contact_consent" type="checkbox" value="1" required />Wyrażam zgodę na przetwarzanie danych w celu odpowiedzi na zapytanie. <a href="<?php echo esc_url($text($attributes['privacyUrl'] ?? '')); ?>">Polityka prywatności</a><small class="spark-contact-full-form__error" data-error-for="spark_contact_consent"></small></label>
					<footer><span>Wymagane: opis, imię, e-mail, zgoda</span><button type="submit">Wyślij zapytanie <b>→</b></button></footer>
				</form>
			</div>
			<aside>
				<div><span>Adres</span>
					<p><?php echo esc_html($text($attributes['address'] ?? '')); ?></p>
				</div>
				<div><span>Nowe projekty</span>
					<p><?php echo esc_html($text($attributes['contactInfo'] ?? '')); ?></p>
				</div>
			</aside>
		</div>
	</div>
</section>
