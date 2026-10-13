# Tworzenie bloków Gutenberg

Ta instrukcja dotyczy agentów zmieniających motyw Spark Agency. Wszystkie własne bloki motywu są samodzielne: nie dodawaj zależności ani kodu Fooz/Wombat.

## Zasada architektury

Każdy blok `sparkagency/{slug}` ma dokładnie ten układ:

```text
src/blocks/{slug}/
├── block.json       # metadane, atrybuty i definicja zasobów
├── index.jsx        # komponent React interfejsu edytora Gutenberg
├── style.scss       # style bloku
└── view.js          # opcjonalny JavaScript front-endu

template-parts/blocks/{slug}.php # opcjonalne renderowanie dynamiczne po stronie PHP
```

`includes/BlocksRegister.php` wykrywa katalogi z `block.json`, rejestruje blok i dołącza wynikowy CSS. Jeżeli istnieje szablon PHP, blok jest renderowany dynamicznie; bez szablonu jest blokiem statycznym, którego HTML zwraca `save()` w JSX. Nie dopisuj ręcznie bloków do bootstrapa, `functions.php` ani osobnych klas rejestrujących.

## Utworzenie bloku

Najpierw użyj generatora z unikalnym slugiem w kebab-case:

```bash
npm run create:block -- --slug=case-study --title="Case study" --description="Opis sekcji"
```

Generator tworzy `src/blocks/case-study/` oraz `template-parts/blocks/case-study.php` dla wariantu dynamicznego. Dla bloku statycznego usuń ten szablon i zaimplementuj `save()` w JSX. Nie uruchamiaj generatora ponownie dla istniejącego sluga, ponieważ celowo kończy się błędem zamiast nadpisać blok.

## Implementacja

1. W `block.json` zachowaj nazwę `sparkagency/{slug}` i text domain `sparkagency`. Dodaj wszystkie atrybuty używane przez edytor lub szablon.
2. W `index.jsx` używaj importów `@wordpress/*` oraz komponentów React/JSX. Zapisuj widok jako JSX (`<Section />`), nie przez `createElement()` ani alias `el()`. Nie korzystaj z globalnego `window.wp`; plik `.asset.php` dla produkcyjnego bundla generuje automatycznie `@wordpress/scripts` w `build/blocks/{slug}/`.
3. Dodaj `view.js` tylko wtedy, gdy blok potrzebuje zachowania po stronie front-endu, a następnie wpisz go jako `viewScript` w `block.json`.
4. Dla bloku dynamicznego w `template-parts/blocks/{slug}.php` traktuj `$attributes`, `$content` i `$block` jako dane wejściowe. Escape'uj dane (`esc_html`, `esc_url`, `wp_kses_post`) i używaj `get_block_wrapper_attributes()` dla głównego elementu. Dla bloku statycznego usuń szablon PHP i zapisz kompletne, walidowalne drzewo front-endu w `save()`.
5. Style umieszczaj wyłącznie w `src/blocks/{slug}/style.scss`. Import tokenów motywu: `@use "../variables" as vars;`.
6. Nie duplikuj kolorów marek. Korzystaj z tokenów CSS z `theme.json` i zmiennych Sass, np. `vars.$color-background` oraz `var(--wp--preset--color--exp)`.

## Kompilacja i kontrola

Po zmianie bloku uruchom:

```bash
npm run build
# tylko dla bloku dynamicznego:
php -l template-parts/blocks/{slug}.php
```

`npm run build` zapisuje CSS bloku jako `assets/css/blocks/spark-{slug}.css` i bundluje JSX do `build/blocks/{slug}/`. Nie edytuj plików w `assets/css/` ani `build/` ręcznie — są wynikami kompilacji.

Przed zakończeniem sprawdź również poprawność `block.json`, zgodność nazwy folderu, sluga i nazwy bloku oraz działanie edytora i front-endu w WordPressie.
