# Team Sync — Hub i Connector

Zestaw wtyczek do jednokierunkowego rozsyłania wpisów CPT `team` pomiędzy stronami WordPress.

- `spark-team-sync-hub` instaluj na jednej, centralnej stronie.
- `spark-team-sync-connector` instaluj na każdej stronie, która ma wysyłać lub odbierać zespoły.
- Nie aktywuj Huba i Connectora na tej samej stronie.

## Domyślna konfiguracja

Wtyczki używają typu wpisu `team` i taksonomii `team-brand`. Motyw Spark Agency rejestruje je w `functions.php`. Terminy marki są kluczami routingu: Hub mapuje każdą markę na strony docelowe.

## Uruchomienie

1. Aktywuj Hub i w **Team Sync Hub → Strony** dodaj strony odbiorców.
2. Skopiuj wygenerowany klucz połączenia.
3. Aktywuj Connector na stronie docelowej i w **Team Sync → Połączenie** wklej klucz.
4. W Hubie, w **Mapowanie marek**, przypisz strony docelowe do odpowiednich terminów `team-brand`.
5. Opublikuj wpis `team` z co najmniej jedną marką. Wtyczka wyśle go raz; ponowne wysłanie i synchronizacja zbiorcza są dostępne w panelu wtyczki.

Synchronizowane są tytuł, treść, zajawka, slug, data publikacji, marki, obrazek wyróżniający, obrazy osadzone w treści oraz pola meta dodane do białej listy w ustawieniach. Odbiorca rozpoznaje kopie po UUID i nie wysyła ich dalej.

REST API:

- Hub: `/wp-json/team-sync-hub/v1/`
- Connector: `/wp-json/team-sync/v1/`

Żądania są podpisywane HMAC-SHA256. Do przetwarzania kolejki produkcyjna instalacja powinna uruchamiać `wp-cron.php` co minutę.
