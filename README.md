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
