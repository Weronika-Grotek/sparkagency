# Spark Agency

Samodzielny motyw WordPress dla Spark Agency. Zachowuje własne palety kolorów, style SCSS, nagłówek, ustawienia dostępności oraz bloki Gutenberg.

## Wymagania

- WordPress 6.6 lub nowszy
- PHP 8.1 lub nowszy
- Composer 2 oraz Node.js 22.12 lub nowszy — tylko do pracy deweloperskiej

## Uruchomienie

```bash
composer install
npm ci
npm run build
```

Motyw nie wymaga zewnętrznego frameworka ani biblioteki komponentów. Zależność PHP ogranicza się do PHP; kompilacja stylów używa wyłącznie Sass.

## Struktura

- `includes/BlocksRegister.php` — automatyczne wykrywanie i rejestracja bloków;
- `src/blocks/{slug}/` — metadane, komponent React/JSX edytora, skrypt frontu i SCSS każdego bloku;
- `build/blocks/{slug}/` — skompilowane skrypty bloków oraz automatycznie wygenerowane pliki `.asset.php`;
- `template-parts/blocks/{slug}.php` — opcjonalny PHP renderujący blok dynamiczny; bloki statyczne zapisują strukturę w `save()` w JSX;
- `resources/block-templates/standard/` — szablony dla generatora bloków;
- `assets/scss/` — źródłowe style globalne motywu;
- `assets/css/` — skompilowane style ładowane przez WordPress;
- `theme.json` — palety, typografia i ustawienia edytora;
- `bootstrap/sparkagency-bootstrap.php` — inicjalizacja motywu.

## Praca ze stylami

```bash
npm run dev
```

Punkt wejścia stylów globalnych to `assets/scss/main.scss`. Style konkretnego bloku są w `src/blocks/{slug}/style.scss`; skrypt budujący zapisuje je jako `assets/css/blocks/spark-{slug}.css`.

## Nowy blok

```bash
npm run create:block -- --slug=case-study --title="Case study"
```

Generator tworzy spójny komplet plików w `src/blocks/case-study/` oraz odpowiadający mu szablon PHP dla wersji dynamicznej w `template-parts/blocks/case-study.php`. Dla bloku statycznego usuń ten szablon i zwróć strukturę front-endu z `save()` w `index.jsx`. Po `npm run build` blok zostaje automatycznie wykryty — nie trzeba dopisywać go w PHP ani w bootstrapie.

## Generator potrzeb: parametry URL

Blok `sparkagency/needs-generator` kieruje na stronę z blokiem „Kontakt — podsumowanie potrzeb” (`sparkagency/contact-handoff`) z formularzem w środku. Strona docelowa może być w innym serwisie na tym motywie. Adres strony końcowej ustawia się w konfiguracji firmy (pole „URL strony końcowej”); link domyślny bloku działa tylko dla firm bez własnego adresu. Wszystkie wybory trafiają do tej strony.

Do adresu dopisywane są parametry GET:

| Parametr | Treść |
| --- | --- |
| `spark_company`, `spark_company_label` | identyfikator firmy (= klucz tematu w formularzu, np. `exp`) i jej nazwa |
| `spark_information`, `spark_information_label` | dokładna wartość i etykieta z kroku 2 |
| `spark_scope`, `spark_scope_label` | dokładna wartość i etykieta z kroku 3 (brak, gdy firma nie ma zakresów; krok 3 i tak jest widoczny w tabach) |
| `spark_return` | adres generatora z odpowiedziami; link „Zmień odpowiedzi” pokazuje się tylko w tej samej domenie |

Wartości mają maks. 100 znaków, etykiety 150. Formularz kontaktowy dostaje ukryte pola `spark_need_*`, zaznacza temat zgodny ze `spark_company`, a mail zawiera sekcję „Generator potrzeb”. Blok podsumowania przewija się do widoku, gdy adres nie ma kotwicy.

## Formularz kontaktowy: kopie dla firm

W „Wygląd → Formularz kontaktowy” obok adresu odbiorcy można przypisać firmom adresy w kopii (CC, kilka po przecinku). Kopia idzie do każdej firmy, której temat zaznaczono w formularzu skróconym lub pełnym (`spark_contact_topic_keys`), oraz do firmy wybranej w generatorze potrzeb (`spark_need_company`). Klucz firmy = wartość tematu w bloku formularza = identyfikator firmy w generatorze. Stałe wiersze: `warsaw`, `health`, `exp`, `studio`, `works`, `advice`; inne klucze dodaje się w pustych wierszach. Adres odbiorcy nie jest dublowany w kopii.
