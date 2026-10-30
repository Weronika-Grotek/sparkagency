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
$departments = array();
foreach (is_array($attributes['departments'] ?? null) ? $attributes['departments'] : array() as $department) {
	if (! is_array($department)) {
		continue;
	}
	$name  = $text($department['name'] ?? '');
	$email = sanitize_email($text($department['email'] ?? ''));
	$color = sanitize_hex_color($text($department['color'] ?? ''));
	if ($name || $email) {
		$departments[] = array('name' => $name, 'email' => $email, 'color' => $color ?: '#FFFFFF');
	}
}
$map_url = esc_url($text($attributes['mapUrl'] ?? ''));
$email   = sanitize_email($text($attributes['contactEmail'] ?? ''));
$phone   = $text($attributes['contactPhone'] ?? '');
$phone_link = preg_replace('/[^+0-9]/', '', $phone);
?>
<section <?php echo get_block_wrapper_attributes(array('class' => 'spark-contact-full-form', 'id' => $anchor ?: $id)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?> data-spark-contact-full-form>
	<div class="spark-contact-full-form__inner">
		<header>
			<h2><?php echo wp_kses_post($text($attributes['title'] ?? '')); ?></h2>
			<p><?php echo wp_kses_post($text($attributes['lede'] ?? '')); ?></p>
		</header>
		<div class="spark-contact-full-form__layout">
			<div><?php if ('success' === $status) : ?><p class="spark-contact-full-form__notice" role="status"><?php echo esc_html($text($attributes['successMessage'] ?? '')); ?></p><?php endif; ?><form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" novalidate><input type="hidden" name="action" value="sparkagency_submit_contact_form" /><input type="hidden" name="spark_contact_anchor" value="<?php echo esc_attr($anchor ?: $id); ?>" /><input type="hidden" name="spark_contact_topics" data-contact-topics value="" /><input type="hidden" name="spark_contact_topic_keys" data-contact-topic-keys value="" /><?php wp_nonce_field('sparkagency_contact_form', 'spark_contact_nonce'); ?>
					<fieldset>
						<legend><b>01</b>Czego dotyczy projekt</legend>
						<div class="spark-contact-full-form__topics" data-contact-topic-buttons><?php foreach ($topics as $topic) : ?><button type="button" aria-pressed="false" data-topic-key="<?php echo esc_attr($topic['key']); ?>" data-topic-label="<?php echo esc_attr($topic['label']); ?>"><i style="background:<?php echo esc_attr($topic['color']); ?>"></i><?php echo esc_html($topic['label']); ?></button><?php endforeach; ?></div>
					</fieldset>
					<fieldset>
						<legend><b>02</b>O projekcie</legend><label>O czym chcesz porozmawiać? *<textarea name="spark_contact_message" maxlength="300" required rows="5" placeholder="Cel, zakres, rynki, termin i co już macie gotowe."></textarea><small class="spark-contact-full-form__error" data-error-for="spark_contact_message"></small></label><label>Załącz brief · opcjonalnie<input name="spark_contact_attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_attachment"></small></label>
						<div class="spark-contact-full-form__budget"><span class="spark-contact-full-form__field-title">Budżet</span><input type="hidden" name="spark_contact_budget" value="" data-contact-budget /><div class="spark-contact-full-form__budget-options" data-contact-budget-buttons><button type="button" aria-pressed="false" data-budget="do 50 tys. zł">do 50 tys. zł</button><button type="button" aria-pressed="false" data-budget="50–150 tys. zł">50–150 tys. zł</button><button type="button" aria-pressed="false" data-budget="150–500 tys. zł">150–500 tys. zł</button><button type="button" aria-pressed="false" data-budget="powyżej 500 tys. zł">powyżej 500 tys. zł</button><button type="button" aria-pressed="false" data-budget="Jeszcze nie wiem">Jeszcze nie wiem</button></div></div>
						<div class="spark-contact-full-form__grid spark-contact-full-form__select-grid"><label>Planowany start<select name="spark_contact_start">
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
						<div class="spark-contact-full-form__grid"><label>Imię i nazwisko *<input name="spark_contact_name" maxlength="45" required autocomplete="name" placeholder="Anna Kowalska" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_name"></small></label><label>E-mail *<input name="spark_contact_email" type="email" required autocomplete="email" placeholder="anna@firma.pl" /><small class="spark-contact-full-form__error" data-error-for="spark_contact_email"></small></label><label>Telefon<input name="spark_contact_phone" type="tel" autocomplete="tel" placeholder="+48 600 000 000" /></label></div>
					</fieldset>
					<label class="spark-contact-full-form__consent"><input name="spark_contact_consent" type="checkbox" value="1" required />Wyrażam zgodę na przetwarzanie danych w celu odpowiedzi na zapytanie. <a href="<?php echo esc_url($text($attributes['privacyUrl'] ?? '')); ?>">Polityka prywatności</a><small class="spark-contact-full-form__error" data-error-for="spark_contact_consent"></small></label>
					<footer><span>Wymagane: opis, imię, e-mail, zgoda</span><button type="submit">Wyślij wiadomość <b>→</b></button></footer>
				</form>
			</div>
			<aside class="spark-contact-full-form__aside" aria-label="<?php esc_attr_e('Dane kontaktowe', 'sparkagency'); ?>">
				<div class="spark-contact-full-form__aside-group"><span><?php echo esc_html($text($attributes['addressLabel'] ?? 'Adres')); ?></span>
					<p><?php echo esc_html($text($attributes['address'] ?? '')); ?></p>
					<?php if ($map_url && $text($attributes['mapLabel'] ?? '')) : ?><a class="spark-contact-full-form__map-link" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($text($attributes['mapLabel'])); ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
				</div>
				<div class="spark-contact-full-form__aside-group"><span><?php echo esc_html($text($attributes['contactTitle'] ?? 'Nowe projekty')); ?></span>
					<?php if ($email) : ?><p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p><?php endif; ?>
					<?php if ($phone || $text($attributes['contactHours'] ?? '')) : ?><p class="spark-contact-full-form__contact-meta"><?php if ($phone_link) : ?><a href="tel:<?php echo esc_attr($phone_link); ?>"><?php echo esc_html($phone); ?></a><?php else : ?><?php echo esc_html($phone); ?><?php endif; ?><?php if ($phone && $text($attributes['contactHours'] ?? '')) : ?> · <?php endif; ?><?php echo esc_html($text($attributes['contactHours'] ?? '')); ?></p><?php endif; ?>
				</div>
				<?php if ($departments) : ?><div class="spark-contact-full-form__aside-group spark-contact-full-form__teams"><span><?php echo esc_html($text($attributes['teamsTitle'] ?? 'Zespoły')); ?></span><div class="spark-contact-full-form__department-list"><?php foreach ($departments as $department) : ?><div class="spark-contact-full-form__department"><span><i style="--department-color:<?php echo esc_attr($department['color']); ?>"></i><?php echo esc_html($department['name']); ?></span><?php if ($department['email']) : ?><a href="mailto:<?php echo esc_attr($department['email']); ?>"><?php echo esc_html($department['email']); ?></a><?php endif; ?></div><?php endforeach; ?></div></div><?php endif; ?>
			</aside>
		</div>
	</div>
</section>
