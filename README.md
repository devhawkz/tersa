# Tersa Shop

Custom WooCommerce tema za **Tersa** — online shop / Custom WooCommerce theme for the **Tersa** online shop.

- **Version:** 1.0.0
- **Text Domain:** `tersa-shop`
- **Author:** Pavle Jovanović / [Marsa Agency](https://marsa.agency/)

---

## 🇭🇷 Opis teme

Tersa Shop je prilagođena WooCommerce tema orijentirana na performanse i višejezičnost, izgrađena za [tersa.hr](https://tersa.hr/). Glavne značajke:

- Optimizirani mini-cart drawer (SSR + WC fragments sessionStorage keš)
- Višejezičnost preko Polylanga s `pll__()` fallbackom na WP gettext
- YITH WooCommerce Wishlist integracija (drawer, header, product kartice)
- Custom post type `eu_project` s arhivom i single predloškom
- Dedicirana sekcija `performance.php` (dequeue emoji, wp-embed, dashicons, pre_option hackovi) i `security.php` (generator, XML-RPC, REST user endpoint hardening, security headers)
- Keširanje ACF settings stranica preko transientova s object-cache–safe invalidacijom

## 🇬🇧 Overview

Tersa Shop is a performance- and i18n-oriented custom WooCommerce theme built for [tersa.hr](https://tersa.hr/). Highlights:

- Optimised mini-cart drawer (SSR + WC fragments sessionStorage cache)
- Multilingual via Polylang with a `pll__()`-then-WP-gettext fallback chain
- YITH WooCommerce Wishlist integration (drawer, header, product cards)
- `eu_project` custom post type with archive and single templates
- Dedicated `performance.php` (emoji/wp-embed/dashicons dequeue, `pre_option_*` hacks) and `security.php` (generator, XML-RPC, REST user endpoint hardening, security headers)
- Transient-cached ACF settings pages with object-cache-safe invalidation

---

## Requirements

| | Minimum | Recommended |
|---|---|---|
| **PHP** | 7.4 | 8.1+ |
| **WordPress** | 6.0 | 6.4+ |
| **WooCommerce** | 7.0 | 8.0+ |
| **MySQL / MariaDB** | 5.7 / 10.3 | 8.0 / 10.6 |

---

## 🇭🇷 Ovisnosti o pluginovima

### Obavezno

| Plugin | Svrha |
|---|---|
| **WooCommerce** | Srž shopa, mini-cart fragments, gettext overrides |
| **Advanced Custom Fields** (ili ACF Pro) | Sve settings stranice i polja sadržaja |
| **Contact Form 7** | Newsletter forma u footeru + kontakt forma |

### Preporučeno

| Plugin | Svrha |
|---|---|
| **Polylang** | Višejezični prijevod stringova, preferirani izvor prijevoda |
| **YITH WooCommerce Wishlist** | Wishlist gumb na karticama proizvoda + drawer |

### Opcionalno

| Plugin | Svrha |
|---|---|
| **Rank Math** | SEO meta + schema |

Ako neki **preporučeni** plugin nedostaje, tema se degradira graciozno (fallback na WP gettext, bez wishlist UI-a). Ako **obavezni** plugin nedostaje — dijelovi fronta neće raditi.

## 🇬🇧 Plugin dependencies

### Required

| Plugin | Purpose |
|---|---|
| **WooCommerce** | Shop core, mini-cart fragments, gettext overrides |
| **Advanced Custom Fields** (or ACF Pro) | All settings pages and content fields |
| **Contact Form 7** | Footer newsletter form + contact page form |

### Recommended

| Plugin | Purpose |
|---|---|
| **Polylang** | Multilingual string translation (primary translation source) |
| **YITH WooCommerce Wishlist** | Wishlist button on product cards + drawer |

### Optional

| Plugin | Purpose |
|---|---|
| **Rank Math** | SEO meta + schema |

If a **recommended** plugin is missing, the theme degrades gracefully (falls back to WP gettext, no wishlist UI). If a **required** plugin is missing — parts of the frontend will not work.

---

## Theme structure

```
tersa-shop/
├── assets/                 # Frontend asset-i (CSS, JS, slike, fontovi)
│   ├── css/
│   ├── js/
│   ├── img/
│   └── fonts/
├── inc/                    # Backend PHP modules
│   ├── setup.php           # Theme supports, image sizes, nav menus
│   ├── enqueue.php         # Asset enqueue + wp_localize_script
│   ├── customizer.php      # Theme Customizer hooks
│   ├── header-helpers.php  # Header settings, cart/wishlist counts
│   ├── footer-helpers.php  # Footer settings, company data
│   ├── shortcodes.php      # tersa_safe_cf7_shortcode_output()
│   ├── schema.php          # JSON-LD output
│   ├── seo-facets.php      # Canonical, robots, OG
│   ├── performance.php     # Dequeue optimizations, pre_option filters
│   ├── security.php        # Hardening headers, XML-RPC off
│   ├── eu-projects.php     # Custom post type registration
│   ├── woocommerce.php     # WooCommerce dispatcher
│   └── woocommerce/        # WC-specifični moduli
│       ├── ajax.php
│       ├── archive.php
│       ├── cache-transients.php
│       ├── cart-drawer.php
│       ├── helpers.php
│       ├── single.php
│       ├── translations-gettext-woocommerce.php
│       ├── translations-register-reviews.php
│       └── wishlist.php
├── template-parts/         # Partial templates (reusable blocks)
│   ├── cards/
│   ├── global/             # header, footer, cart-drawer, navigation
│   ├── home/
│   ├── pages/              # about, contact, ...
│   ├── product/
│   ├── sections/
│   ├── shop/
│   └── woocommerce/
├── woocommerce/            # WC template overrides
│   ├── archive-product.php
│   ├── content-product.php
│   ├── content-single-product.php
│   ├── cart/
│   └── global/
├── page-templates/         # Custom page templates
├── languages/              # .pot / .po / .mo files (currently empty)
├── archive-eu_project.php
├── single-eu_project.php
├── front-page.php
├── index.php
├── page.php
├── 404.php
├── functions.php           # Bootstrap + require_once inc/*
├── style.css               # Theme header
├── screenshot.png          # 1200×704
└── README.md
```

---

## ACF configuration

## 🇭🇷 ACF konfiguracija

Tema očekuje **tri ACF settings stranice** registrirane kao regular Pages sa sljedećim slug-ovima. Slug-ovi su bitni — čitaju se iz koda (`inc/header-helpers.php`, `inc/footer-helpers.php`).

| Stranica (slug) | Svrha | Ključna polja |
|---|---|---|
| `header-settings` | Topbar i header konfiguracija | `topbar_enabled`, `topbar_message`, `topbar_link_text`, `topbar_link_url` |
| `footer-settings` | Footer tekstovi | `footer_newsletter_heading`, `footer_newsletter_text` |
| `global-settings` | Globalni podaci tvrtke | `company_name`, `company_activity`, `company_address`, `company_email`, `company_phone_primary`, `company_phone_secondary`, `contact_cf7_shortcode`, `contact_map_embed` |

Dodatna ACF polja po templateu:

- **Front page:** `show_home_bestsellers_section`, `home_bestsellers_section_title`, `home_bestsellers_badge_color`, `home_bestsellers_product_tag_slug`
- **About page:** `about_hero_title`, `about_hero_image`, `about_work_image`, `about_cta_image`
- **Contact page:** `contact_card_heading`, `contact_call_label`, `contact_call_text`, `contact_write_label`, `contact_email_label`, `contact_hq_label`, `contact_hq_hours_week`, `contact_hq_hours_sat`, `contact_form_heading`, `contact_hero_title`, `contact_hero_image`, `contact_phone`, `contact_phone_second`, `contact_email`, `contact_hq_address`
- **EU project (CPT):** `eu_project_card_title`, `eu_project_card_description`, `eu_project_status`, `eu_project_program`, `eu_project_full_title`, `eu_project_beneficiary`, `eu_project_short_description`, `eu_project_total_value`, `eu_project_eu_funding`, `eu_project_start_date`, `eu_project_end_date`, `eu_project_contact_info`, `eu_project_pdf`, `eu_project_cta_label`, `eu_project_cta_url`, `eu_project_logos_one…five`

## 🇬🇧 ACF configuration

The theme expects **three ACF settings pages** registered as regular Pages with the slugs below. Slugs matter — they are read from code (`inc/header-helpers.php`, `inc/footer-helpers.php`).

| Page (slug) | Purpose | Key fields |
|---|---|---|
| `header-settings` | Topbar & header config | `topbar_enabled`, `topbar_message`, `topbar_link_text`, `topbar_link_url` |
| `footer-settings` | Footer text strings | `footer_newsletter_heading`, `footer_newsletter_text` |
| `global-settings` | Global company info | `company_name`, `company_activity`, `company_address`, `company_email`, `company_phone_primary`, `company_phone_secondary`, `contact_cf7_shortcode`, `contact_map_embed` |

Template-specific ACF fields are listed above (in the Croatian version — same identifiers).

---

## Custom post types

- **`eu_project`** (`inc/eu-projects.php`) — projekti financirani iz EU fondova / EU-funded projects. Ima archive (`archive-eu_project.php`) i single template (`single-eu_project.php`).

## Custom image sizes

Registrirano u `inc/setup.php` / Registered in `inc/setup.php`:

| Name | Dimensions | Crop | Use |
|---|---|---|---|
| `tersa-logo` | 370 × 104 | — | Logo (retina 2×) |
| `tersa-card` | 480 × 600 | yes | Product card on shop/archive |
| `tersa-bestseller` | 756 × 968 | yes | Home "Bestsellers" card |
| `tersa-hero` | 1600 × 0 | no | Hero image (desktop) |
| `tersa-hero-mobile` | 800 × 0 | no | Hero image (mobile) |
| `tersa-banner` | 900 × 700 | yes | Promo banner |
| `tersa-countdown` | 900 × 900 | yes | Promo countdown image |

> **Napomena / Note:** Nakon prve instalacije teme ili izmjene dimenzija, pokreni **Regenerate Thumbnails** (WP-CLI: `wp media regenerate --yes`) kako bi se postojeće slike konvertirale. / After first install or size changes, run **Regenerate Thumbnails** (WP-CLI: `wp media regenerate --yes`) to convert existing media.

## Navigation menus

Registrirano u `inc/setup.php` / Registered in `inc/setup.php`:

- **`primary`** — glavni header meni / main header menu
- **`footer_about`** — O nama linkovi u footeru / About links in the footer
- **`footer_legal`** — Pravni linkovi u footeru / Legal links in the footer

---

## 🇭🇷 Lokalni razvoj

Projekt koristi **Local by Flywheel**. Tema se razvija direktno u `wp-content/themes/tersa-shop/`.

### Preporučeni workflow

1. Aktiviraj Local site `Tersa`.
2. Instaliraj obavezne pluginove (vidi tablicu gore).
3. Uvezi staging bazu ili kreiraj `header-settings`, `footer-settings`, `global-settings` stranice.
4. Registriraj ACF field groupe i popuni podatke.
5. Aktiviraj Polylang, konfiguriraj jezike (minimalno `hr`), preko **Languages → Strings translations** prevedi stringove koje tema registrira u `pll_register_string()` pozivima.

### Build / assets

Trenutno **nema build pipelinea** — CSS/JS se enqueuiraju kao raw fajlovi iz `assets/css/` i `assets/js/`. Verzioniranje se radi preko `wp_get_theme()->get('Version')` (plus ručni bump u `style.css` header-u).

## 🇬🇧 Local development

The project is based on **Local by Flywheel**. Theme is edited directly in `wp-content/themes/tersa-shop/`.

### Recommended workflow

1. Start the Local site `Tersa`.
2. Install required plugins (see table above).
3. Import the staging DB or manually create `header-settings`, `footer-settings`, `global-settings` pages.
4. Register ACF field groups and populate the data.
5. Activate Polylang, configure languages (minimum `hr`), then translate theme strings via **Languages → Strings translations** (strings registered through `pll_register_string()`).

### Build / assets

There is currently **no build pipeline** — CSS and JS are enqueued as raw files from `assets/css/` and `assets/js/`. Versioning uses `wp_get_theme()->get('Version')` (plus a manual bump in the `style.css` header).

---

## 🇭🇷 Deploy checklist

Prije prvog go-livea ili većeg re-deploya:

- [ ] Instalirani i aktivirani svi obavezni pluginovi
- [ ] `header-settings`, `footer-settings`, `global-settings` stranice postoje s ispravnim slug-ovima
- [ ] ACF field groupe popunjene (minimalno `company_email`, `contact_cf7_shortcode`)
- [ ] Contact Form 7 forme kreirane, ID-evi ažurirani u footeru / kontakt stranici
- [ ] Polylang jezici konfigurirani
- [ ] Polylang Strings translations ispunjeni za sve registrirane stringove
- [ ] YITH Wishlist aktiviran ako se koristi
- [ ] Rank Math (ili SEO plugin) konfiguriran
- [ ] Pokrenut `wp media regenerate --yes` nakon prve instalacije
- [ ] Transient keš očišćen (`wp transient delete --all`) nakon deploya
- [ ] Object cache (Redis/Memcached) flush nakon deploya ako je omogućen
- [ ] Provjeren HTTPS redirect + security headers (`inc/security.php`)
- [ ] Testiran cart drawer na logged-in + logged-out stanju
- [ ] Testirana višejezičnost (minimum `hr`, plus dodatni jezici ako postoje)

## 🇬🇧 Deploy checklist

Before first go-live or major re-deploy:

- [ ] All required plugins installed and activated
- [ ] `header-settings`, `footer-settings`, `global-settings` pages exist with correct slugs
- [ ] ACF field groups populated (at minimum `company_email`, `contact_cf7_shortcode`)
- [ ] Contact Form 7 forms created, IDs updated in footer / contact page
- [ ] Polylang languages configured
- [ ] Polylang Strings translations filled in for all registered strings
- [ ] YITH Wishlist activated if used
- [ ] Rank Math (or SEO plugin) configured
- [ ] Run `wp media regenerate --yes` after first install
- [ ] Transient cache purged (`wp transient delete --all`) post-deploy
- [ ] Object cache (Redis/Memcached) flushed post-deploy if enabled
- [ ] HTTPS redirect + security headers verified (`inc/security.php`)
- [ ] Cart drawer tested for both logged-in and logged-out states
- [ ] Multilingual behaviour tested (minimum `hr`, plus any additional languages)

---

## i18n

Tema koristi dualni i18n sistem / The theme uses a dual i18n system:

1. **Polylang Strings translations** — primarni izvor / primary source (`pll__()`).
2. **WP gettext** — fallback (`__( 'String', 'tersa-shop' )`).

Text domain: `tersa-shop`. Domain path: `/languages`.

`languages/tersa-shop.pot` se trenutno ne nalazi u repou jer je Polylang primarni izvor. Ako treba, generiraj ga s **WP-CLI**:

```bash
wp i18n make-pot . languages/tersa-shop.pot --domain=tersa-shop
```

---

## Debug logging

Modul `inc/debug-log.php` hvata ciljane greške iz kritičnih tokova:

- `wp_mail_failed` — SMTP / PHPMailer greške
- `WC_Logger` — Corvus Pay i drugi payment gateway log-ovi (proxy u `debug.log`)
- `tersa_*` AJAX endpoint-i — fatal error-i
- `woocommerce_checkout_order_exception` — uncaught gateway exception-i

Svi zapisi idu u `/wp-content/debug.log` bez obzira na `WP_DEBUG_LOG` vrednost (koristi 3-arg `error_log()`).

### Rotacija

- **Dnevna** preko WP cron-a (`daily` event, hook `tersa_debug_log_rotate`).
- `debug.log` → `debug-YYYY-MM-DD.log`, odmah se kreira svež prazan glavni log.
- Retention: **7 dana** (konfigurabilno preko `TERSA_DEBUG_LOG_RETENTION_DAYS` konstante u `wp-config.php`).
- Fajlovi `debug-*.log` stariji od retention perioda brišu se pri svakoj rotaciji.

Manuelni rotate preko WP-CLI:

```bash
wp tersa debug-log-rotate
```

Da bi se PHP notice/warning-i iz **celog** sajta (tema + plugini) logovali, u `wp-config.php` postavi:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', '0');
```

---

## License

Proprietary / private — property of Tersa & Marsa Agency. Not for redistribution.
