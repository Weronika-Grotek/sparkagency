# Autoload PHP i budowanie SCSS dla motywu WordPress

Autor: dev.

To konfiguracja do włączenia w istniejący aktywny motyw (najlepiej potomny), a nie samodzielny motyw ani wtyczka. Zachowaj swój theme.json z poprzedniego kroku; paczka go nie zastępuje. Nie zawiera plików fontów.

## Wymagania

- PHP 8.1 lub nowszy i Composer 2.
- Node.js 22.12 lub nowszy; .nvmrc wybiera Node 24.
- Istniejący motyw WordPress.

## Podłączenie

1. Skopiuj zawartość paczki do głównego katalogu aktywnego motywu.
2. Jeśli motyw ma już composer.json, package.json lub .gitignore, scal odpowiednie wpisy. Zachowaj dotychczasowy kod functions.php i style.css.
3. W functions.php, wewnątrz PHP, dodaj:

```php
require_once get_stylesheet_directory() . '/bootstrap/dev-bootstrap.php';
```

4. Uruchom w katalogu motywu:

```bash
composer install
npm ci
npm run build
```

Gdy scalasz konfigurację npm z istniejącym projektem, wykonaj npm install, żeby zaktualizować jego package-lock.json, a kolejne instalacje przez npm ci.

## Praca ze stylami

```bash
npm run dev
```

Sass obserwuje assets/scss i kompiluje po zapisaniu pliku. Tryb deweloperski ma czytelny CSS i osadzoną mapę źródeł. W razie problemów z obserwowaniem plików w DDEV/Docker użyj npm run dev:poll. Zatrzymanie: Ctrl+C. Nie ma automatycznego odświeżania przeglądarki.

- Punkt wejścia: assets/scss/main.scss.
- Moduły: assets/scss/components/_*.scss, dołączane przez @use.
- Wynik: assets/css/main.css.
- npm run build: minifikacja i brak mapy źródeł.
- Dodatkowe pliki SCSS bez podkreślenia też zostaną skompilowane, ale wymagają osobnej rejestracji w Assets.php.
- Sass nie dodaje automatycznie prefiksów przeglądarek.

Klasa Dev\Theme\Assets ładuje main.css na froncie przez wp_enqueue_style. Wersją CSS jest czas modyfikacji pliku, co zmienia URL po przebudowaniu. Style te nie są ładowane do edytora blokowego.

Przykładowe klasy: dev-accent--exp, dev-accent--warsaw, dev-accent--health, dev-accent--studio i dev-accent--works. Używają zmiennych CSS z wcześniejszego theme.json; nie duplikują kolorów w SCSS.

## Autoload PHP

Mapowanie Composer PSR-4: Dev\Theme\ → src/.

Przykład: klasa Dev\Theme\Setup musi być w src/Setup.php z namespace Dev\Theme. Autoload ładuje klasę przy użyciu; sam nie wywołuje jej metod ani hooków. Zarejestruj ją w bootstrap/dev-bootstrap.php.

Po zmianie mapowania namespace wykonaj composer dump-autoload. Nie edytuj vendor/autoload.php ręcznie. Wielkość liter nazw klas i plików musi się zgadzać.

## Wdrożenie

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Wgraj kod motywu, vendor/ oraz assets/css/main.css. node_modules/ nie jest potrzebne na serwerze. Katalogi wynikowe są ignorowane przez Git, więc proces wdrożenia musi je zbudować lub dołączyć. Zachowaj pliki composer.lock (powstanie po pierwszym composer install) i package-lock.json w repozytorium.

## DDEV

Możesz użyć ddev ssh, przejść do katalogu motywu i uruchomić tam powyższe komendy. Sprawdź wersję PHP i Node w kontenerze.

## Źródła

- https://getcomposer.org/doc/04-schema.md#autoload
- https://sass-lang.com/documentation/cli/dart-sass/
- https://developer.wordpress.org/reference/functions/wp_enqueue_style/

## Weryfikacja paczki

Sprawdzono instalację zależności npm, build produkcyjny, mapy źródeł w trybie dev oraz ponowną kompilację po zmianie pliku częściowego SCSS w trybie dev:poll. PHP, Composer i WordPress nie były dostępne w środowisku przygotowania paczki; autoloader i hook WordPressa wymagają sprawdzenia w projekcie docelowym.
