# CorvusPay integracija — checklist obaveznih sadržaja i izmjena u temi

> Dokument sadrži **sve što nedostaje** u temi `tersa-shop` da bi web shop bio spreman za:
>
> - aktivaciju CorvusPay payment gateway-a,
> - obavezne sadržaje koje Corvus provjerava prije slanja dokumentacije u banke,
> - opciju plaćanja preko Apple Pay i Google Pay (kroz CorvusPay),
> - usklađenost sa HR Zakonom o zaštiti potrošača, Zakonom o elektroničkoj trgovini i GDPR-om.
>
> **Izvor zahtjeva:** dopis CorvusPay sales, [https://cps.corvuspay.com/documentation/](https://cps.corvuspay.com/documentation/), CorvusPay FAQ ([www.corvuspay.com/en/faq/](https://www.corvuspay.com/en/faq/)), Zakon o zaštiti potrošača NN 19/22 (čl. 79 — pravo na jednostrani raskid), Zakon o fiskalizaciji u prometu gotovinom.
>
> **Status:** trenutno stanje teme audit-irano 2026-05-16. Plugin **CorvusPay WooCommerce nije instaliran**.
>
> **Update 2026-05-20:** Klijent (Tersa d.o.o.) je dostavio podatke o tvrtki + brand kit CorvusPay-a. Implementirano u temu:
>
> - Impressum blok u footeru sa OIB, MBS, sud, uprava (zakonski obavezno — HR ZET čl. 6, ZTD čl. 21).
> - Pravi logotipi kartica (**Mastercard, Maestro, Visa, Diners**) iz CorvusPay brand kit-a u `assets/img/payments/`. Discover i American Express pripremljeni u istom folderu ali nisu prikazani u footeru — uključuju se po potrebi tek kad CorvusPay potvrdi koje kartice će biti aktivirane.
> - CorvusPay logo (negativ verzija) u footeru sa linkom na corvuspay.com.
> - Bijeli "rack" oko kartica jer brand standardi zabranjuju izmjenu pozadina logotipa.
> - Svi logotipi su linkovi koji se otvaraju u novom prozoru (po Standardima logotipa v3.4).
> - Redoslijed: Mastercard, Maestro, Visa, Diners (Mastercard UVIJEK prije Maestra).
> - PBZ Premium Visa obročna otplata logotipi pripremljeni u `assets/img/payments/installments/` (6, 12, 24, 36 rata) — koristiti tek kad CorvusPay potvrdi broj rata.
> - `tersa_get_company_impressum()` helper sa fallback vrijednostima iz dopisa klijenta — radi i prije nego što se popune ACF polja na `global-settings` stranici.
> - Kontakt stranica sada prikazuje puni impressum (naziv, OIB, MBS, sud, direktor, radno vrijeme, e-mail za reklamacije).
> - **Sigurnosni logotipi** (Mastercard Identity Check, Visa Secure, Diners sigurna kupnja) iz brand kit-a u `assets/img/payments/security/`.
> - **Checkout stranica** sada prikazuje payment + security badge-eve preko `woocommerce_review_order_after_payment` hook-a (template-parts/woocommerce/payment-security-badges.php).
> - Reusable helperi `tersa_get_payment_methods()` + `tersa_get_security_badges()` — koriste se i u footeru i u checkout template part-u.

---

## Sadržaj

1. [Faza 1 — obavezne pravne stranice](#1-faza-1--obavezne-pravne-stranice)
2. [Faza 2 — podaci o tvrtki (Impressum)](#2-faza-2--podaci-o-tvrtki-impressum)
3. [Faza 3 — logotipi kartica i CorvusPay logo](#3-faza-3--logotipi-kartica-i-corvuspay-logo)
4. [Faza 4 — Cookie consent i kolačići](#4-faza-4--cookie-consent-i-kolačići)
5. [Faza 5 — WooCommerce konfiguracija (EUR, PDV, stranice)](#5-faza-5--woocommerce-konfiguracija-eur-pdv-stranice)
6. [Faza 6 — Apple Pay i Google Pay (Wallet Pay)](#6-faza-6--apple-pay-i-google-pay-wallet-pay)
7. [Faza 7 — instalacija i konfiguracija CorvusPay plugina](#7-faza-7--instalacija-i-konfiguracija-corvuspay-plugina)
8. [Faza 8 — testiranje (testne kartice + scenariji)](#8-faza-8--testiranje-testne-kartice--scenariji)
9. [Faza 9 — produkcija i go-live](#9-faza-9--produkcija-i-go-live)
10. [Dodatak A — fiskalizacija (HR)](#dodatak-a--fiskalizacija-hr)
11. [Dodatak B — predlošci ACF polja](#dodatak-b--predlošci-acf-polja)
12. [Dodatak C — predlošci tekstova pravnih stranica](#dodatak-c--predlošci-tekstova-pravnih-stranica)
13. [Dodatak D — referenca file-ova u temi koji se moraju mijenjati](#dodatak-d--referenca-file-ova-u-temi-koji-se-moraju-mijenjati)

---

## 1) Faza 1 — obavezne pravne stranice

Sve stranice moraju postojati kao **WordPress Pages** sa slug-om iz tablice. Sve moraju biti linkane iz footera (`footer_legal` menu lokacija). Polylang prijevodi su obavezni ako koristite više jezika.

### 1.1 Lista obaveznih stranica

| # | Slug | Naslov (HR) | Obavezno? | Bilješka |
|---|---|---|---|---|
| 1 | `opci-uvjeti-poslovanja` | Opći uvjeti poslovanja | **DA** | Najvažniji dokument — Corvus ga prvo provjerava |
| 2 | `politika-privatnosti` | Politika privatnosti (GDPR) | **DA** | Već postoji u `$legal_fallback` |
| 3 | `politika-kolacica` | Politika kolačića | **DA** | Mora opisati sve kolačiće koje koristite (analytics, marketing, neophodni) |
| 4 | `uvjeti-dostave` | Uvjeti dostave | **DA** | Načini, troškovi, rokovi, kurirska služba |
| 5 | `nacin-placanja` | Načini plaćanja | **DA** | Mora navesti CorvusPay + sve kartice koje prihvaćate |
| 6 | `pravo-na-jednostrani-raskid` | Pravo na jednostrani raskid ugovora | **DA** | + PDF obrazac za preuzimanje (zakonski rok 14 dana) |
| 7 | `reklamacije` | Reklamacije i prigovori | **DA** | Mora dati način podnošenja prigovora + email/adresa |
| 8 | `izjava-o-sigurnosti-online-placanja` | Izjava o sigurnosti online plaćanja | **DA** | Mora spominjati CorvusPay, SSL 256-bit, PCI DSS Level 1 |
| 9 | `izjava-o-konverziji` | Izjava o konverziji valuta | DA ako prihvaćate strane kartice | Sve transakcije se obračunavaju u EUR; konverzija po tečaju izdavateljske banke |
| 10 | `faq` | Često postavljana pitanja | preporučeno | Već postoji u `$legal_fallback` |
| 11 | `kontakt` | Kontakt | **DA** | Već postoji (template-contact.php) |

> Predlošci teksta za sve stranice — vidi [Dodatak C](#dodatak-c--predlošci-tekstova-pravnih-stranica).

### 1.2 Akcije u temi

- [ ] U WP admin: kreirati svih 11 stranica sa točnim slug-om.
- [ ] U WP admin: kreirati **Footer Legal menu** i u njega dodati stavke 1–9 (+ FAQ + Kontakt).
- [ ] U WP admin: Appearance → Menus → dodijeliti taj meni na `footer_legal` lokaciju.
- [ ] **Update fallback liste** u `footer.php` (linije 74–87) — proširiti `$legal_fallback` na svih 9 obaveznih linkova (za slučaj da menu nije postavljen):

```php
$legal_fallback = [
    ['label' => __('Opći uvjeti poslovanja', 'tersa-shop'),    'url' => $_pll_url('opci-uvjeti-poslovanja')],
    ['label' => __('Politika privatnosti', 'tersa-shop'),       'url' => $_pll_url('politika-privatnosti')],
    ['label' => __('Politika kolačića', 'tersa-shop'),          'url' => $_pll_url('politika-kolacica')],
    ['label' => __('Uvjeti dostave', 'tersa-shop'),             'url' => $_pll_url('uvjeti-dostave')],
    ['label' => __('Načini plaćanja', 'tersa-shop'),            'url' => $_pll_url('nacin-placanja')],
    ['label' => __('Pravo na jednostrani raskid', 'tersa-shop'),'url' => $_pll_url('pravo-na-jednostrani-raskid')],
    ['label' => __('Reklamacije', 'tersa-shop'),                'url' => $_pll_url('reklamacije')],
    ['label' => __('Izjava o sigurnosti', 'tersa-shop'),        'url' => $_pll_url('izjava-o-sigurnosti-online-placanja')],
    ['label' => __('FAQ', 'tersa-shop'),                        'url' => $_pll_url('faq')],
];
```

- [ ] Registrirati labele u **Polylang String translations** (`inc/woocommerce/translations-register-general-shop.php` ili novi `inc/translations-register-legal.php`).
- [ ] Dodati **PDF obrazac** za jednostrani raskid: `assets/pdf/obrazac-jednostrani-raskid.pdf` i linkati ga sa stranice `pravo-na-jednostrani-raskid`.

### 1.3 Page template za pravne stranice (preporuka)

Trenutno tema ima samo `template-about.php` i `template-contact.php`. Preporučljivo je dodati:

- `page-templates/template-legal.php` — uniformni layout za sve pravne stranice (uska kolona, breadcrumbs, tipografija prilagođena dugom tekstu).

```php
<?php
/**
 * Template Name: Legal Page
 * Template Post Type: page
 */
if (!defined('ABSPATH')) { exit; }
get_header(); ?>

<main id="main-content" class="site-main legal-page">
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/global/breadcrumbs'); ?>
        <article class="legal-page__article">
            <div class="container container--narrow">
                <h1 class="legal-page__title"><?php the_title(); ?></h1>
                <div class="legal-page__content">
                    <?php the_content(); ?>
                </div>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<?php get_footer();
```

I pripadajući `assets/css/legal.css` enqueuiran u `inc/enqueue.php` kad je `is_page_template('page-templates/template-legal.php')`.

---

## 2) Faza 2 — podaci o tvrtki (Impressum)

### 2.1 Što nedostaje

Trenutno `inc/footer-helpers.php` linije 171–177 čita samo:
`company_name`, `company_activity`, `company_address`, `company_email`, `footer_newsletter_cf7_shortcode`.

A `footer.php` ispisuje samo activity, address, email.

**Zakon o trgovačkim društvima** (čl. 21) i **Zakon o elektroničkoj trgovini** (čl. 6) traže da budu **dostupni na svakoj stranici**:

- puni naziv tvrtke,
- sjedište + adresa,
- OIB,
- MBS (matični broj subjekta upisa) + naziv suda kod kojeg je upisan,
- iznos temeljnog kapitala (uplaćen u cijelosti),
- imena članova uprave,
- IBAN + naziv banke,
- e-mail i telefon,
- VAT ID (ako je PDV obveznik).

### 2.2 ACF polja koja treba dodati na `global-settings` stranicu

| Field name | Field type | Required | Primjer |
|---|---|---|---|
| `company_full_name` | Text | Da | `Tersa d.o.o.` |
| `company_oib` | Text | Da | `12345678901` |
| `company_mbs` | Text | Da | `030123456` |
| `company_court` | Text | Da | `Trgovački sud u Osijeku` |
| `company_share_capital` | Text | Da | `20.000,00 EUR uplaćen u cijelosti` |
| `company_director` | Text | Da | `Ime Prezime, direktor` |
| `company_iban` | Text | Da | `HR1723600001501234567` |
| `company_bank` | Text | Da | `Zagrebačka banka d.d.` |
| `company_vat_id` | Text | Ako je obveznik | `HR12345678901` |
| `company_phone_primary` | Text | Da | postoji već, ali se ne ispisuje u footeru |
| `company_email` | Email | Da | postoji već |

### 2.3 Izmjena `inc/footer-helpers.php`

Funkcija `tersa_get_company_settings()` (linija 160) treba čitati nova polja:

```php
$settings = [
    'company_name'                    => ($page_id && $get_field) ? (string) get_field('company_name', $page_id) : '',
    'company_full_name'               => ($page_id && $get_field) ? (string) get_field('company_full_name', $page_id) : '',
    'company_activity'                => ($page_id && $get_field) ? (string) get_field('company_activity', $page_id) : '',
    'company_address'                 => ($page_id && $get_field) ? (string) get_field('company_address', $page_id) : '',
    'company_email'                   => ($page_id && $get_field) ? (string) get_field('company_email', $page_id) : '',
    'company_phone_primary'           => ($page_id && $get_field) ? (string) get_field('company_phone_primary', $page_id) : '',
    'company_oib'                     => ($page_id && $get_field) ? (string) get_field('company_oib', $page_id) : '',
    'company_mbs'                     => ($page_id && $get_field) ? (string) get_field('company_mbs', $page_id) : '',
    'company_court'                   => ($page_id && $get_field) ? (string) get_field('company_court', $page_id) : '',
    'company_share_capital'           => ($page_id && $get_field) ? (string) get_field('company_share_capital', $page_id) : '',
    'company_director'                => ($page_id && $get_field) ? (string) get_field('company_director', $page_id) : '',
    'company_iban'                    => ($page_id && $get_field) ? (string) get_field('company_iban', $page_id) : '',
    'company_bank'                    => ($page_id && $get_field) ? (string) get_field('company_bank', $page_id) : '',
    'company_vat_id'                  => ($page_id && $get_field) ? (string) get_field('company_vat_id', $page_id) : '',
    'footer_newsletter_cf7_shortcode' => ($page_id && $get_field) ? (string) get_field('footer_newsletter_cf7_shortcode', $page_id) : '',
];
```

### 2.4 Izmjena `footer.php` — novi Impressum blok

Postojeća `<address class="site-footer__company">` (linije 104–112) treba biti **proširena**, ili se dodaje **novi blok ispod copyright-a** sa pravnim podacima. Predloženi markup:

```php
<section class="site-footer__impressum">
    <h2 class="screen-reader-text"><?php esc_html_e('Podaci o tvrtki', 'tersa-shop'); ?></h2>
    <p>
        <strong><?php echo esc_html($company_settings['company_full_name'] ?: $company_name); ?></strong>,
        <?php echo esc_html($company_settings['company_address']); ?>
    </p>
    <p>
        <?php esc_html_e('OIB:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_oib']); ?>
        &nbsp;·&nbsp;
        <?php esc_html_e('MBS:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_mbs']); ?>
        &nbsp;·&nbsp;
        <?php echo esc_html($company_settings['company_court']); ?>
    </p>
    <p>
        <?php esc_html_e('Temeljni kapital:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_share_capital']); ?>
        &nbsp;·&nbsp;
        <?php esc_html_e('Uprava:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_director']); ?>
    </p>
    <p>
        <?php esc_html_e('IBAN:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_iban']); ?>
        (<?php echo esc_html($company_settings['company_bank']); ?>)
        <?php if ($company_settings['company_vat_id']) : ?>
            &nbsp;·&nbsp;
            <?php esc_html_e('PDV ID:', 'tersa-shop'); ?> <?php echo esc_html($company_settings['company_vat_id']); ?>
        <?php endif; ?>
    </p>
    <p>
        <?php esc_html_e('Telefon:', 'tersa-shop'); ?>
        <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $company_settings['company_phone_primary'])); ?>">
            <?php echo esc_html($company_settings['company_phone_primary']); ?>
        </a>
        &nbsp;·&nbsp;
        <?php esc_html_e('E-mail:', 'tersa-shop'); ?>
        <a href="mailto:<?php echo esc_attr($company_settings['company_email']); ?>">
            <?php echo esc_html($company_settings['company_email']); ?>
        </a>
    </p>
</section>
```

CSS prilog u `assets/css/footer.css` (predloženo): `.site-footer__impressum { font-size: 12px; color: var(--color-text-muted); margin-top: 16px; }` plus media query za stacked mobile prikaz.

### 2.5 Stavke za odluku

- [ ] Hoće li impressum biti na **svakoj stranici** (footer) ili samo na zasebnoj `impressum` stranici? **Preporučeno: footer**, jer čl. 6 ZET-a traži "lako, neposredno i trajno dostupni".
- [ ] Ako tvrtka nije PDV obveznik, ostaje napomena `"Tvrtka nije u sustavu PDV-a"` ispod cijene proizvoda (mora postojati).

---

## 3) Faza 3 — logotipi kartica i CorvusPay logo

### 3.1 Što nedostaje

`footer.php` linije 219–233 trenutno renderira **samo Apple Pay i Google Pay SVG** (inline). To **nije dovoljno** — Corvus banke traže:

- Visa
- Mastercard
- Maestro
- (zavisno od ugovorene banke) Diners, Discover, American Express, JCB
- **CorvusPay logo** sa linkom na `https://www.corvuspay.com/`
- (opcionalno) Verified by Visa / Mastercard SecureCode (3-D Secure badge)

### 3.2 Što treba pripremiti

- [ ] Otvoriti folder `assets/img/payments/` u temi.
- [ ] U njega staviti **SVG-ove** (preuzima se iz Corvus dopisa / brand kit-a banaka):

```text
assets/img/payments/
├── visa.svg
├── mastercard.svg
├── maestro.svg
├── diners.svg              (opcionalno)
├── discover.svg            (opcionalno)
├── amex.svg                (opcionalno)
├── jcb.svg                 (opcionalno)
├── apple-pay.svg           (ako ćete ga ugovoriti)
├── google-pay.svg          (ako ćete ga ugovoriti)
├── corvuspay.svg           (obavezno)
└── 3d-secure.svg           (opcionalno, "Verified by Visa" badge)
```

> **Važno:** ne smiju se koristiti random SVG-ovi sa interneta. Logotipi su zaštićeni žigovi — Corvus šalje brand kit u sklopu dokumentacije, a banke imaju vlastite brand guideline.

### 3.3 Izmjena `footer.php`

Zamijeniti hardcoded SVG-ove (linije 219–233) sa data-driven listom. Predloženi pristup — dodati ACF polje `accepted_payments` (Checkbox / Repeater) na `global-settings`, ili koristiti običan loop nad direktorijem:

```php
<ul class="site-footer__payments" aria-label="<?php esc_attr_e('Prihvaćene metode plaćanja', 'tersa-shop'); ?>">
    <?php
    $tersa_payment_methods = [
        'visa'       => __('Visa', 'tersa-shop'),
        'mastercard' => __('Mastercard', 'tersa-shop'),
        'maestro'    => __('Maestro', 'tersa-shop'),
        'apple-pay'  => __('Apple Pay', 'tersa-shop'),
        'google-pay' => __('Google Pay', 'tersa-shop'),
        // dodati Diners, Discover, Amex po dogovoru sa bankom
    ];
    foreach ($tersa_payment_methods as $slug => $label) :
        $svg_path = get_template_directory() . '/assets/img/payments/' . $slug . '.svg';
        if (!file_exists($svg_path)) { continue; }
        $svg_url = get_template_directory_uri() . '/assets/img/payments/' . $slug . '.svg';
    ?>
        <li class="site-footer__payment-badge" aria-label="<?php echo esc_attr($label); ?>">
            <img src="<?php echo esc_url($svg_url); ?>"
                 alt="<?php echo esc_attr($label); ?>"
                 class="site-footer__payment-icon"
                 loading="lazy"
                 decoding="async"
                 width="40" height="26" />
        </li>
    <?php endforeach; ?>

    <li class="site-footer__payment-badge site-footer__payment-badge--corvus">
        <a href="https://www.corvuspay.com/" target="_blank" rel="noopener noreferrer"
           aria-label="<?php esc_attr_e('CorvusPay — sigurno online plaćanje', 'tersa-shop'); ?>">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/corvuspay.svg'); ?>"
                 alt="CorvusPay"
                 class="site-footer__payment-icon"
                 loading="lazy"
                 decoding="async"
                 width="80" height="26" />
        </a>
    </li>
</ul>
```

### 3.4 Gdje se moraju vidjeti logotipi (osim footera)

- **Checkout stranica** — uz radio button "Plaćanje karticom (CorvusPay)" treba ići mali "strip" sa Visa/MC/Maestro logom. Plugin to obično napravi automatski; ako ne, koristi se hook `woocommerce_gateway_icon`.
- **Stranica `nacin-placanja`** — veće logotipe sa kratkim opisom svake kartice.
- **Stranica `izjava-o-sigurnosti-online-placanja`** — CorvusPay + 3-D Secure logotipi.

---

## 4) Faza 4 — Cookie consent i kolačići

### 4.1 Što nedostaje

Tema **nema** cookie consent banner. GDPR i HR Zakon o elektroničkim komunikacijama (čl. 100) traže **eksplicitnu privolu** prije postavljanja ne-neophodnih kolačića (analytics, marketing, embedded video).

Provjereno: u `template-parts/global/` ne postoji `cookie-banner.php`; u `assets/js/` ne postoji `cookie-banner.js`.

### 4.2 Dvije opcije

| Opcija | Prednosti | Mane |
|---|---|---|
| **A) Plugin** (Complianz, Cookie Notice & Compliance for GDPR/CCPA, CookieYes) | Brzo, automatska skeniranja kolačića, automatski blokira analytics dok korisnik ne pristane | Još jedan plugin, neki imaju paid features |
| **B) Custom** (`template-parts/global/cookie-banner.php` + JS) | Lakša tema, full control nad UX-om, ne sprema treće domene | Mora se ručno integrirati sa GA/FB pixel, više rada |

> **Preporuka:** Plugin **Complianz** (free verzija) — automatski skenira, automatski blokira skripte, automatski generira politiku kolačića. Najbrži put do uskladenosti.

### 4.3 Što treba podesiti

- [ ] Instalirati cookie consent plugin (ili napraviti custom).
- [ ] Konfigurirati 3 kategorije: **Neophodni** (uvijek aktivni), **Analitički**, **Marketinški**.
- [ ] Sve `<script>` za Google Analytics / Meta Pixel / TikTok / itd. **NE** dodavati direktno u `inc/enqueue.php` ili `header.php` — moraju ići kroz consent listener.
- [ ] Cookie banner mora linkati na stranicu `politika-kolacica`.
- [ ] Politika kolačića mora biti **automatski generirana** iz Complianz / CookieYes ili ručno napisana sa popisom svih kolačića (vidi [Dodatak C](#dodatak-c--predlošci-tekstova-pravnih-stranica)).

### 4.4 Custom rješenje (skelet)

Ako ide custom put, struktura:

```text
template-parts/global/cookie-banner.php
assets/css/cookie-banner.css
assets/js/cookie-banner.js
```

`functions.php` ili `inc/setup.php` mora hookovati banner na `wp_footer`:

```php
add_action('wp_footer', static function (): void {
    if (is_admin()) { return; }
    get_template_part('template-parts/global/cookie-banner');
}, 100);
```

JS koristi localStorage / cookie da pamti pristanak (key: `tersa_cookie_consent_v1`, value JSON sa per-category boolean-ima).

---

## 5) Faza 5 — WooCommerce konfiguracija (EUR, PDV, stranice)

### 5.1 Currency

- [ ] **WooCommerce → Settings → General → Currency = Euro (€)**
- [ ] Currency position: `Right with space` (HR standard: `19,99 €`)
- [ ] Thousand separator: `.`
- [ ] Decimal separator: `,`
- [ ] Number of decimals: `2`

> Hrvatska je 2023-01-01 prešla na EUR. Cijene **moraju** biti u EUR-u, ne u HRK.

### 5.2 PDV (Tax)

Trenutno tema **ne ispisuje** "PDV uključen" oznaku uz cijenu. To se mora dodati na:

- **Single product** (`woocommerce/content-single-product.php` linija 295–305):

```php
<div class="product-single__price-row">
    <div class="product-single__price">
        <?php echo wp_kses_post($current_price_html); ?>
    </div>
    <span class="product-single__price-vat">
        <?php
        if ($product->get_price() > 0) {
            esc_html_e('PDV uključen u cijenu', 'tersa-shop');
        }
        ?>
    </span>
    <?php if (!empty($discount_percent)) : ?>
        <div class="product-single__discount">
            <?php echo esc_html('('.$discount_percent.')'); ?>
        </div>
    <?php endif; ?>
</div>
```

- **Shop card** (`woocommerce/content-product.php`) — opcionalno
- **Cart i Checkout** — WC ovo radi automatski ako je `Display prices including tax` aktivan.

WC postavke:
- [ ] **WooCommerce → Settings → Tax → Prices entered with tax = Yes (PDV uključen)**
- [ ] **Display prices in shop = Including tax**
- [ ] **Display prices during cart and checkout = Including tax**
- [ ] Postaviti standardnu poreznu stopu HR 25% (za većinu proizvoda) ili 5%/13% gdje primjenjivo.

> Ako tvrtka **nije PDV obveznik**, gornja oznaka mora biti `"Tvrtka nije u sustavu PDV-a"` i to mora biti na svakom proizvodu, te u footeru/impresumu.

### 5.3 Stranice (WC pages)

- [ ] **WooCommerce → Settings → Advanced → Page setup**:
  - Cart = `/kosarica/` (ili odgovarajući slug)
  - Checkout = `/blagajna/`
  - My Account = `/moj-racun/`
  - Terms and Conditions = `opci-uvjeti-poslovanja` ← važno za checkout checkbox

- [ ] **WooCommerce → Settings → Accounts & Privacy → Privacy policy page = `politika-privatnosti`**
- [ ] **Privacy policy checkbox** na checkout-u: aktivirati `Show a Privacy policy checkbox`

### 5.4 Permalinks

- [ ] **Settings → Permalinks → Post name** (nužno za WC API callback URL `/wc-api/wc_gateway_corvuspay`).

### 5.5 SSL / HTTPS

- [ ] SSL certifikat aktivan i tvrdi HTTPS redirect.
- [ ] `inc/security.php` linije 100–102 već šalje HSTS — provjeriti da je `Site URL` u `Settings → General` https.

### 5.6 Stock / dostava info

- [ ] Svaki proizvod mora imati **stock status** (`Na stanju` / `Trenutačno nedostupno`) — tema već ovo prikazuje (`content-single-product.php` linija 286–292).
- [ ] Rok isporuke — preporučljivo dodati na `single-product` (`"Isporuka: 2–5 radnih dana"`) ili ACF polje per-product.

---

## 6) Faza 6 — Apple Pay i Google Pay (Wallet Pay)

### 6.1 Što je važno znati

- Apple Pay i Google Pay kroz CorvusPay **nisu automatski uključeni** — moraju se **posebno ugovoriti** kroz banku i Corvus.
- Korisnik vidi `Apple Pay` button **samo** na:
  - macOS Safari sa konfiguriranim Apple Pay-om,
  - iOS Safari (svi noviji uređaji).
- Korisnik vidi `Google Pay` button **samo** na:
  - Chrome browseru sa spremljenom karticom u Google profile-u.
- Buttoni se prikazuju **na CorvusPay hosted formi**, ne na vašem checkout-u (osim ako koristite **Direct API integration** koji se rijetko koristi — preporučeno je hosted form).

### 6.2 Što morate ugovoriti

- [ ] U dopisu Corvus-u (na `sales@corvuspay.com`) eksplicitno napisati da želite:
  - Apple Pay
  - Google Pay
- [ ] Banka mora to odobriti (najčešće dolazi u istom ugovoru kao i obične kartice).
- [ ] Apple Pay zahtijeva **Apple Developer Account verification** kroz CorvusPay portal — Corvus vam šalje upute u sklopu aktivacije.
- [ ] Google Pay zahtijeva **Google Pay Business Console** registraciju.

### 6.3 Što morate dodati na site (tema)

- [ ] Apple Pay i Google Pay logotipi u footeru (vidi Faza 3) **tek kad ih ugovorite**.
- [ ] **Apple Pay domain verification file**: Corvus će vam dati fajl `apple-developer-merchantid-domain-association` koji se mora staviti na:

```text
/.well-known/apple-developer-merchantid-domain-association
```

To je **server-level**, ne ide u temu. Mora se postaviti na web root:

```text
sajt/app/public/.well-known/apple-developer-merchantid-domain-association
```

Apple radi GET zahtjev na taj URL i provjerava sadržaj. **Bez ovoga Apple Pay neće raditi.**

- [ ] Provjeriti da `.htaccess` / Nginx config ne blokira `.well-known/` folder:

```apache
# .htaccess — dodati ako ne radi
<FilesMatch "apple-developer-merchantid-domain-association">
    Header set Content-Type "text/plain"
</FilesMatch>
```

- [ ] U `inc/security.php` (X-Frame-Options: SAMEORIGIN) nema utjecaja jer Apple/Google Pay button živi na **CorvusPay hosted formi** (cps.corvuspay.com).

### 6.4 Test

- Testiranje Apple Pay-a moguće je samo na realnom Apple uređaju sa spremljenom karticom (testne kartice ne rade za Apple Pay).
- Google Pay test: dovoljan je Chrome sa spremljenom test/realnom karticom.
- Detalji u CorvusPay **integracija testna skripta** PDF-u.

---

## 7) Faza 7 — instalacija i konfiguracija CorvusPay plugina

### 7.1 Preduvjeti

- Sve iz Faza 1–5 mora biti **gotovo**.
- Corvus mora odobriti obavezne sadržaje (poslati im URL i čekati confirmation).
- Imati pristupne podatke za **test okruženje** (`https://test-merchant.corvuspay.com`).

### 7.2 Instalacija plugina

- [ ] Preuzeti **CorvusPay WooCommerce plugin** sa [`https://cps.corvuspay.com/documentation/`](https://cps.corvuspay.com/documentation/) (sekcija "Web pluginovi").
- [ ] Upload u WP admin → Plugins → Add New → Upload Plugin.
- [ ] Aktivirati.

### 7.3 Konfiguracija plugina (Test mode)

Plugin postavke nalaze se u **WooCommerce → Settings → Payments → CorvusPay**:

- [ ] **Enable** = Yes
- [ ] **Title** = `Kartično plaćanje (CorvusPay)`
- [ ] **Description** = `Sigurno plaćanje karticama Visa, Mastercard, Maestro kroz CorvusPay`
- [ ] **Test Mode** = Yes
- [ ] **Store ID (test)** = (iz `test-merchant.corvuspay.com`)
- [ ] **Store Key (test)** = (iz `test-merchant.corvuspay.com`)
- [ ] **Language** = `hr` (ili dinamički preko Polylang)
- [ ] **Currency** = `EUR`
- [ ] **Installments** (rate) = isključeno za testne kartice; postaviti tek kad banka aktivira (npr. ZABA, OTP)
- [ ] **Order status after success** = `processing` (ili `completed` ovisno o tipu robe)

### 7.4 Callback URL-ovi

Plugin automatski postavlja `Success URL` i `Cancel URL` na:

```text
Success: https://tersa.hr/wc-api/wc_gateway_corvuspay/
Cancel:  https://tersa.hr/checkout/order-pay/{order_id}/?pay_for_order=true&key={order_key}
```

Provjeriti u CorvusPay **Merchant portalu** da su URL-ovi točno upisani **u svakom store profilu** (Test i Production).

### 7.5 Logiranje

Tema već loguje sve `corvus` poruke u `wp-content/debug.log` (`inc/debug-log.php` linije 115–143). Ne treba dodatno podešavati.

Za dodatni debug u pluginu — uključiti **WooCommerce → Status → Logs** i odabrati `corvus` source.

---

## 8) Faza 8 — testiranje (testne kartice + scenariji)

CorvusPay šalje **`CorvusPay integracija testna skripta.pdf`** sa testnim karticama. Najčešće korištene:

| Tip transakcije | Card number | Expiry | CVV | Rezultat |
|---|---|---|---|---|
| Approved | `4111 1111 1111 1111` | bilo koji future | bilo koji | Uspješna |
| Declined | `4000 0000 0000 0002` | bilo koji future | bilo koji | Odbijena |
| 3-D Secure required | `4012 8888 8888 1881` | bilo koji future | bilo koji | Tražit će OTP |

### 8.1 Test scenariji

- [ ] **Approved** → korisnik odlazi na `/cart/order-received/` (WC default), status order-a = `processing`.
- [ ] **Declined** → korisnik se vraća na checkout sa porukom o odbijanju, order ostaje `pending`.
- [ ] **3-D Secure** → korisnik prolazi kroz dodatni step (token/SMS), pa Approved/Declined.
- [ ] **Unprocessed** → korisnik je napustio formu na 3-D Secure step-u; order ostaje `pending`.
- [ ] **Refund** → kroz CorvusPay Merchant portal (ili API ako je certifikat instaliran), provjeriti da je iznos vraćen, order = `refunded`.
- [ ] **Cancel** → korisnik kliknio Cancel na CorvusPay formi → redirect na checkout, order = `cancelled` (ili `pending`).

### 8.2 Što testirati osim plaćanja

- [ ] Multi-language (hr / en) — checkout u oba jezika redirecta na CorvusPay sa odgovarajućim `language` param.
- [ ] Logged-in vs guest checkout.
- [ ] Mobilni preglednik (iOS Safari, Android Chrome) — Apple Pay button vidljiv.
- [ ] Currency u CorvusPay formi = EUR.
- [ ] Email notifikacije (uspješna narudžba, neuspješna).
- [ ] Stock se smanjuje tek nakon `processing` ili `completed`, ne na `pending`.

---

## 9) Faza 9 — produkcija i go-live

### 9.1 Pre-flight checklist

- [ ] Sve iz Faza 1–8 testirano i radi.
- [ ] Corvus poslao **production link za registraciju** (iz dopisa: "produkcijski link za registraciju").
- [ ] Trgovac se registrirao na produkcijskom okruženju (`merchant.corvuspay.com`).
- [ ] Banka aktivirala prodajno mjesto.
- [ ] Corvus konfigurirao kartice (Visa, MC, Maestro, opcionalno Apple/Google Pay).
- [ ] Trgovac dobio **Store ID (production)** i **Store Key (production)**.

### 9.2 Switch na produkciju

- [ ] U WC plugin postavkama: **Test Mode = No**.
- [ ] Unijeti production Store ID + Key.
- [ ] U Merchant portalu provjeriti Success/Cancel URL-ove (sa **production domenom**, ne `test.tersa.hr`).
- [ ] **Apple Pay domain verification file** prebaciti na production server.
- [ ] **Backup baze** i `wp-content/uploads/` prije prelaska.
- [ ] Testirati **jednu realnu transakciju** sa svojom karticom (mali iznos, npr. 1 €) i odmah refund.
- [ ] Monitor `wp-content/debug.log` za prve transakcije.

### 9.3 Dokumentacija slanja u banke

- [ ] Trgovac potpisuje ugovor sa bankom.
- [ ] Trgovac potpisuje ugovor sa Corvus-om (Payment Gateway).
- [ ] Trgovac šalje:
  - obrazac banke (popunjen + ovjeren)
  - **Upitnik A** (samo str. 9 — Part 1a, str. 37 — Part 3b sa potpisom)
  - ako Global Payments: presliku osobne iskaznice ovlaštene osobe + NKD izvadak
- [ ] Sve obrasce slati na Corvus mail (oni prosljeđuju u banku).
- [ ] Za tehnička pitanja: `support@corvuspay.com`
- [ ] Za administrativna: `sales@corvuspay.com`

---

## Dodatak A — fiskalizacija (HR)

> CorvusPay FAQ eksplicitno navodi: "_According to the Cash Transaction Fiscalization Law, cards are a payment method which falls within the scope of cash payments, which means they are subject to fiscalization._"

Tema **nema** integraciju fiskalnog sustava. Ovo je **odvojen projekat**, neovisan od CorvusPay-a:

- [ ] Odabrati fiskalno rješenje:
  - **WP plugin** (npr. Fiskalizacija Hrvatska, Galaxy Fiscalization)
  - **Custom integracija** sa CIS-om (Centralni informacijski sustav Porezne uprave) — koristi SOAP API
- [ ] Pribaviti **fiskalni certifikat** (FINA, T-Com)
- [ ] Konfigurirati **poslovni prostor + naplatni uređaj** u ePorezni
- [ ] Postaviti da se svaki WC order **fiskalizira automatski** kad pređe u `processing`
- [ ] JIR (jedinstveni identifikator računa) + ZKI (zaštitni kod izdavatelja) **moraju biti na svakom računu** koji se šalje kupcu.

> Ako trgovac **nije obveznik fiskalizacije** (npr. neke djelatnosti, mali porezni obveznici sa godišnjim prihodom < 300.000 EUR za fizičke osobe), provjeriti sa knjigovođom.

---

## Dodatak B — predlošci ACF polja

Za **`global-settings` stranicu** (slug: `global-settings`). Field group naziv: `Tersa — Podaci o tvrtki`.

```text
Tab: Osnovni podaci
├── company_name (Text)              — Naziv tvrtke (skraćeni)
├── company_full_name (Text)         — Puni pravni naziv (npr. "Tersa d.o.o.")
├── company_activity (Text)          — Djelatnost (NKD opis)
├── company_address (Textarea)       — Sjedište i adresa

Tab: Pravni podaci
├── company_oib (Text)               — OIB (11 znamenki)
├── company_mbs (Text)               — Matični broj subjekta upisa
├── company_court (Text)             — Trgovački sud upisa
├── company_share_capital (Text)     — Iznos temeljnog kapitala
├── company_director (Text)          — Član uprave / direktor
├── company_vat_id (Text)            — PDV ID (ako je obveznik)

Tab: Bankovni podaci
├── company_iban (Text)              — IBAN poslovnog računa
├── company_bank (Text)              — Naziv banke
├── company_swift (Text)             — SWIFT/BIC (opcionalno)

Tab: Kontakt
├── company_email (Email)
├── company_phone_primary (Text)
├── company_phone_secondary (Text)
├── contact_cf7_shortcode (Text)
├── contact_map_embed (Textarea)

Tab: Plaćanje
├── accepted_payments (Checkbox)     — Visa, Mastercard, Maestro, Diners, Apple Pay, Google Pay
└── footer_newsletter_cf7_shortcode (Text)
```

> Sve `Text` polja koja se prikazuju u footeru registrirati u Polylang String translations kako bi se mogli prevesti.

---

## Dodatak C — predlošci tekstova pravnih stranica

> **VAŽNO:** sljedeći predlošci su **skelet**. Sve mora biti pregledano od strane **pravnika** ili **odvjetnika**, posebno Opći uvjeti i Pravo na jednostrani raskid. **Corvus i banke neće odobriti dokumentaciju ako stranice ne pokrivaju zakonske zahtjeve.**

### C.1 Opći uvjeti poslovanja (`opci-uvjeti-poslovanja`)

Sekcije koje **moraju** biti pokrivene:

1. Uvodne odredbe (tko je trgovac, podaci o tvrtki — full impressum)
2. Definicije pojmova (kupac, korisnik, ugovor, narudžba, isporuka, povrat)
3. Postupak kupovine (registracija, izbor proizvoda, košarica, narudžba, potvrda)
4. Cijene (sve u EUR, PDV uključen / nije obveznik)
5. Načini plaćanja (link na stranicu `nacin-placanja`)
6. Dostava (link na `uvjeti-dostave`)
7. Pravo na jednostrani raskid (link na `pravo-na-jednostrani-raskid`)
8. Reklamacije (link na `reklamacije`)
9. Zaštita osobnih podataka (link na `politika-privatnosti`)
10. Kolačići (link na `politika-kolacica`)
11. Sigurnost online plaćanja (link na `izjava-o-sigurnosti-online-placanja`)
12. Rješavanje sporova (nadležni sud, online platforma EU za rješavanje sporova: `https://ec.europa.eu/consumers/odr/`)
13. Izmjene Općih uvjeta
14. Stupanje na snagu

### C.2 Politika privatnosti (`politika-privatnosti`)

GDPR-compliant struktura:

1. Voditelj obrade (Tersa d.o.o. + kontakt podaci)
2. Vrste osobnih podataka koje prikupljamo
3. Svrhe obrade (izvršenje narudžbe, dostava, marketing samo uz pristanak)
4. Pravna osnova obrade (ugovor, legitimni interes, pristanak)
5. Razdoblje čuvanja podataka
6. Primatelji podataka (kurirska služba, CorvusPay, knjigovodstvo)
7. Prijenos u treće zemlje (ako ima)
8. Vaša prava (pristup, ispravak, brisanje, prenosivost, prigovor)
9. Kako ostvariti prava (kontakt email + obrazac)
10. Pravo na pritužbu AZOP-u
11. Sigurnosne mjere

### C.3 Politika kolačića (`politika-kolacica`)

Mora sadržavati **tablicu** svakog kolačića koji shop koristi. Primjer:

```text
| Naziv         | Tip          | Trajanje  | Svrha                                   |
|---------------|--------------|-----------|------------------------------------------|
| wp-settings-* | Neophodni    | 1 godina  | WP user preferences                      |
| woocommerce_* | Neophodni    | 2 dana    | Košarica i sesija                        |
| _ga, _gid     | Analitički   | 2 godine  | Google Analytics — anonimizirana statist.|
| _fbp          | Marketinški  | 90 dana   | Meta (Facebook) Pixel — remarketing      |
| tersa_cookie_consent_v1 | Neophodni | 1 godina | Pamćenje vaše privole                |
```

### C.4 Uvjeti dostave (`uvjeti-dostave`)

1. Načini dostave (kurirska služba — naziv: GLS, DPD, Hrvatska pošta, Overseas i sl.)
2. Troškovi dostave (po cijeni narudžbe, regiji, težini)
3. Rokovi isporuke (`Hrvatska: 2–5 radnih dana, EU: 7–14 radnih dana`)
4. Praćenje pošiljke (tracking link)
5. Što ako se pošiljka ošteti / izgubi
6. Dostava u inozemstvo (carina, PDV razlike)

### C.5 Načini plaćanja (`nacin-placanja`)

1. **Plaćanje karticom** kroz CorvusPay
   - Prihvaćamo: Visa, Mastercard, Maestro, (Diners, Discover, Amex, JCB — po potrebi)
   - Apple Pay i Google Pay (kad ugovorite)
   - Plaćanje na rate: Mastercard/Visa ZABA (do X rata), OTP (do Y rata) — ovisno o ugovoru
2. **Pouzeće** (cash on delivery) — ako nudite
3. **Bankovna doznaka / virman** — ako nudite
4. **PayPal** — ako nudite
5. Sigurnost plaćanja (link na `izjava-o-sigurnosti-online-placanja`)

### C.6 Pravo na jednostrani raskid (`pravo-na-jednostrani-raskid`)

> Sukladno čl. 79 Zakona o zaštiti potrošača (NN 19/22).

1. Kupac ima pravo, bez navođenja razloga, jednostrano raskinuti ugovor sklopljen na daljinu u roku od **14 dana** od datuma primitka robe.
2. Rok počinje teći danom kad je roba predana potrošaču ili trećoj osobi koju je odredio potrošač.
3. Kako iskoristiti pravo: poslati obavijest e-mailom na `tersa@tersa.hr` ili preporučenom poštom na adresu sjedišta. Može se koristiti **priloženi obrazac**: [obrazac-jednostrani-raskid.pdf](/wp-content/themes/tersa-shop/assets/pdf/obrazac-jednostrani-raskid.pdf).
4. Učinci raskida: trgovac vraća sve uplate u roku od 14 dana od primitka obavijesti.
5. Trošak povrata robe snosi kupac.
6. Iznimke (kad pravo na raskid ne postoji): personalizirana roba, brzo kvarljiva, otpečaćeni higijenski proizvodi…

### C.7 Reklamacije (`reklamacije`)

1. Kako podnijeti reklamaciju (e-mail, telefon, pisanim putem)
2. Rok za odgovor: **15 dana** od primitka prigovora (Zakon o zaštiti potrošača čl. 10)
3. Voditelj reklamacija + kontakt
4. Materijalni nedostatak — 2 godine od preuzimanja robe (Zakon o obveznim odnosima)
5. Pravo na alternativno rješavanje sporova (link na EU ODR platformu)

### C.8 Izjava o sigurnosti online plaćanja (`izjava-o-sigurnosti-online-placanja`)

Tekst koji **Corvus eksplicitno provjerava**:

```text
Tajnost Vaših podataka zaštićena je korištenjem SSL enkripcije.
Stranice za naplatu putem interneta osigurane su korištenjem
Secure Socket Layer (SSL) protokola sa 256-bitnom enkripcijom
podataka. SSL enkripcija je postupak šifriranja podataka radi
sprječavanja neovlaštenog pristupa prilikom njihovog prijenosa.

Time je omogućen siguran prijenos informacija te onemogućen
nedozvoljen pristup podacima prilikom komunikacije između
korisnikovog računala i WebPay servisa, te obratno.

WebPay servis i financijske ustanove razmjenjuju podatke
korištenjem virtualne privatne mreže (VPN), koja je zaštićena
od neautoriziranog pristupa.

Sigurna autorizacija transakcija i naplata kartica obavlja se
korištenjem sustava CorvusPay — sustava za naplatu kartica u
realnom vremenu pružatelja usluge CorvusPay d.o.o. CorvusPay
osigurava potpunu tajnost Vaših kartičnih i osobnih podataka
već od trenutka kada ih upišete u CorvusPay platnu formu.
Podaci potrebni za naplatu prosljeđuju se šifrirano s Vašeg
web preglednika do banke koja je izdala Vašu karticu. Naša
trgovina nikada ne dolazi u kontakt s cjelovitim podacima o
Vašoj kartici. Podaci su nedostupni čak i djelatnicima sustava
CorvusPay. Posebno educiran tim CorvusPaya brine se o sigurnosti
podataka 24 sata na dan, 7 dana u tjednu.

Cjelokupna procedura kartičnog plaćanja certificirana je u
skladu s PCI DSS standardom, najvišeg nivoa zaštite (Level 1),
koji se odnosi na pohranu, obradu i prijenos kartičnih podataka.
```

### C.9 Izjava o konverziji valuta (`izjava-o-konverziji`)

```text
Sva plaćanja na našem web shopu obavljaju se u eurima (EUR).
Iznos koji će biti zadužen na Vašoj kartici dobiva se konverzijom
cijene u eurima u valutu vaše kartice, sukladno tečajnoj listi
banke izdavateljice kartice. Kao rezultat konverzije postoji
mogućnost male razlike u odnosu na originalnu cijenu navedenu na
našoj internetskoj stranici.
```

---

## Dodatak D — referenca file-ova u temi koji se moraju mijenjati

| Fajl | Linije | Akcija |
|---|---|---|
| `footer.php` | 74–87 | Proširiti `$legal_fallback` na 9 linkova (Faza 1.2) |
| `footer.php` | 104–112 | Dodati Impressum blok ili novi `<section class="site-footer__impressum">` (Faza 2.4) |
| `footer.php` | 219–233 | Zamijeniti hardcoded Apple/Google Pay SVG-ove sa data-driven listom (Faza 3.3) |
| `inc/footer-helpers.php` | 171–177 | Proširiti `tersa_get_company_settings()` sa 12 novih polja (Faza 2.3) |
| `inc/setup.php` | 68–72 | Eventualno dodati novu meni lokaciju `payment_methods` (opcionalno) |
| `inc/enqueue.php` | 200–207 | Dodati enqueue za `legal.css` kad je `is_page_template('page-templates/template-legal.php')` |
| `inc/woocommerce/translations-register-general-shop.php` | append | Registrirati 9+ novih Polylang stringova za legal menu labele (Faza 1.2) |
| `woocommerce/content-single-product.php` | 295–305 | Dodati `<span class="product-single__price-vat">PDV uključen</span>` (Faza 5.2) |
| `assets/css/footer.css` | append | Stilovi za `.site-footer__impressum`, `.site-footer__payment-badge--corvus` (Faza 2.4, 3.3) |
| `assets/css/product.css` | append | Stilovi za `.product-single__price-vat` (Faza 5.2) |
| `page-templates/template-legal.php` | nov fajl | Template za pravne stranice (Faza 1.3) |
| `assets/css/legal.css` | nov fajl | Stilovi za legal stranice (Faza 1.3) |
| `assets/img/payments/*.svg` | novi fajlovi | 5–9 logotipa kartica + CorvusPay (Faza 3.2) |
| `assets/pdf/obrazac-jednostrani-raskid.pdf` | nov fajl | Obrazac za jednostrani raskid (Faza 1.2) |
| `template-parts/global/cookie-banner.php` | nov fajl | Cookie consent UI (Faza 4.4) — opcionalno ako se ide custom put |
| `assets/css/cookie-banner.css` | nov fajl | Stilovi za cookie banner (opcionalno) |
| `assets/js/cookie-banner.js` | nov fajl | Logika za pristanak (opcionalno) |
| `.well-known/apple-developer-merchantid-domain-association` | nov fajl | Apple Pay domain verification (Faza 6.3) — **ide na server root, ne u temu** |

---

## Završni rezime — što fali za odobrenje od Corvus-a

Minimalno (bez čega Corvus **neće** proslijediti dokumentaciju u banke):

1. ✗ Opći uvjeti poslovanja stranica + sadržaj (klijent dostavio `Uvjeti kupnje web trgovine.docx` — treba kreirati WP page i ubaciti sadržaj)
2. ✗ Politika privatnosti stranica (klijent dostavio `Politika privatnosti.docx` — treba kreirati WP page i ubaciti sadržaj)
3. ✗ Politika kolačića stranica + cookie consent banner
4. ✗ Uvjeti dostave stranica (Klijent: dostava preko **BoxNow**)
5. ✗ Načini plaćanja stranica (Klijent: jednokratno kartično, kartično obročno, virmansko — dodati i PBZ Premium Visa obročna stikere iz `assets/img/payments/installments/` kad CorvusPay potvrdi broj rata)
6. ✗ Pravo na jednostrani raskid + PDF obrazac
7. ✗ Reklamacije stranica (Klijent dostavio gotov tekst u upitniku — vidi Dodatak E)
8. ✗ Izjava o sigurnosti online plaćanja (Klijent dostavio gotov tekst — vidi Dodatak C.8 + Dodatak E)
9. ✓ **Logotipi Visa/Mastercard/Maestro/Diners/Discover/Amex + CorvusPay u footeru** (urađeno 2026-05-20)
10. ⚠ Impressum u footeru: OIB, MBS, sud, **direktor** — **UPISANO sa fallback vrijednostima iz dopisa klijenta**. ACF polja za `share_capital`, `iban`, `bank`, `vat_id` treba popuniti na `global-settings` page kad klijent dostavi.
11. ✗ EUR currency + "PDV uključen" oznaka

Dodatno (ne blokira odobrenje, ali blokira go-live):

12. ✗ CorvusPay WooCommerce plugin instaliran i konfiguriran (plugin `corvuspay-woocommerce-integration.2.7.3.zip` ima u `corvus pay/`)
13. ✗ Apple Pay domain verification file (ako ide Apple Pay)
14. ✗ Fiskalizacija (Dodatak A) — ako je tvrtka obveznik

---

## Dodatak E — sadržaji koje je klijent već dostavio (Tersa, 2026-05-20)

> Dokumenti: `Popis podataka i sadržaja - upitnik.docx` + `Uvjeti kupnje web trgovine.docx` + `Politika privatnosti.docx`

### E.1 Podaci o tvrtki (popunjeni kao fallback u `tersa_get_company_impressum()`)

| Polje                | Vrijednost                                  |
|----------------------|---------------------------------------------|
| Puni naziv           | Tersa d.o.o.                                |
| Adresa               | Nikole Tesle 71, 31551 Črnkovci             |
| OIB                  | 80835896442                                 |
| MBS                  | 030014012                                   |
| Sudski registar      | Trgovački sud u Osijeku                     |
| Odgovorna osoba      | Vlado Šakić, direktor                       |
| E-mail               | tersa@tersa.hr                              |
| Telefon              | 031/355 900                                 |
| Radno vrijeme podrške | Pon – Pet 08:00 – 14:00                    |
| Djelatnost           | Prerada drva i trgovina drvnim proizvodima  |

### E.2 Nedostaju (klijent nije naveo — pitati ga prije slanja Corvus-u)

- **Temeljni kapital** (obavezan za d.o.o. — npr. "20.000,00 EUR uplaćen u cijelosti")
- **IBAN** + naziv banke
- **PDV ID / VAT ID** (ako je obveznik PDV-a — ako nije, dodati napomenu "Tvrtka nije u sustavu PDV-a" uz cijene)

### E.3 Načini plaćanja (klijent potvrdio)

- Jednokratno kartično plaćanje (CorvusPay — Visa, Mastercard, Maestro itd.)
- Kartično obročno plaćanje (PBZ Premium Visa — broj rata čeka potvrdu CorvusPay-a)
- Virmansko plaćanje (bankovna doznaka)

### E.4 Dostava

- Dostavna služba: **BoxNow**
- Treba upitati klijenta: rok isporuke, troškovi dostave (besplatno preko X EUR?), područje dostave (samo HR ili EU)

### E.5 Tekst za stranicu Reklamacije (gotov, ide direktno u WP page)

> U slučaju kada primite robu s nedostatkom, imate pravo na prigovor ili reklamaciju robe u zakonskom roku prema Zakonu o zaštiti potrošača.
> Sukladno čl. 10 Zakona o zaštiti potrošača, omogućujemo vam da svoje prigovore uputite na e-mail adresu **tersa@tersa.hr**. Na sve zaprimljene prigovore odgovoriti ćemo u što kraćem roku, no najkasnije u roku od **15 dana** od dana zaprimanja prigovora, te ćemo riješiti vaš prigovor na najpovoljniji mogući način.
> Povrat u slučaju reklamacije robe s nedostatkom se vrši na način da nakon zaprimanja prigovora, u dogovoru s vama, šaljemo novi proizvod ili povrat sredstava.
> Reklamacija će se smatrati valjanom ako se pregledom proizvoda i, ukoliko je potrebno, dodatnim vještačenjem, utvrdi da odgovara uvjetima za reklamaciju sukladno Zakonu o obveznim odnosima i Zakonu o zaštiti potrošača.
> Ukoliko se utvrdi da je reklamacija pravovaljana, o našem trošku zamijenit ćemo robu za istovjetnu bez nedostataka ili vratiti cjelokupan iznos koji je plaćen za proizvod, uključivo s troškovima dostave.
> U slučaju da reklamacija nije pravovaljana, tj. ukoliko se prigovor potrošača odbije, kupac koji je uputio prigovor snosit će trošak ponovne dostave kupljenog proizvoda na adresu kupca.

### E.6 Tekst za stranicu Izjava o sigurnosti online plaćanja (gotov, ide direktno u WP page)

> Pri plaćanju na našoj web trgovini koristite **CorvusPay** – napredni sustav za siguran prihvat platnih kartica putem interneta.
>
> CorvusPay sustav osigurava potpunu tajnost Vaših kartičnih i osobnih podataka već od trenutka kada ih upišete u CorvusPay platni formular. Platni podaci prosljeđuju se šifrirano od Vašeg web preglednika do banke koja je izdala Vašu karticu. Naša trgovina nikada ne dolazi u kontakt s cjelovitim podacima o Vašoj platnoj kartici. Također, podaci su nedostupni čak i djelatnicima CorvusPay sustava. Izolirana jezgra samostalno prenosi i upravlja osjetljivim podacima, čuvajući ih pri tom potpuno sigurnima.
>
> Formular za upis platnih podataka osiguran je SSL transportnom šifrom najveće pouzdanosti. Svi skladišteni podaci dodatno su zaštićeni šifriranjem, korištenjem kriptografskog uređaja certificiranog prema FIPS 140-2 Level 3 standardu. CorvusPay zadovoljava sve zahtjeve vezane uz sigurnost on-line plaćanja propisane od strane vodećih kartičnih brandova, odnosno posluje sukladno normi – **PCI DSS Level 1** – najviši sigurnosni standard industrije platnih kartica. Pri plaćanju karticama uvrštenim u 3-D Secure program Vaša banka uz valjanost same kartice dodatno potvrđuje i Vaš identitet pomoću tokena ili lozinke.
>
> Corvus Pay sve prikupljene informacije smatra tajnom i tretira ih u skladu s tim. Informacije se koriste isključivo u svrhe za koje su namijenjene. Vaši osjetljivi podaci u potpunosti su sigurni, a njihova privatnost zajamčena je najmodernijim zaštitnim mehanizmima. Prikupljaju se samo podaci nužni za obavljanje posla sukladno propisanim zahtjevnim procedurama za on-line plaćanje.
>
> Sigurnosne kontrole i operativne procedure primijenjene na našu infrastrukturu osiguravaju trenutnu pouzdanost CorvusPay sustava. Uz to održavanjem stroge kontrole pristupa, redovitim praćenjem sigurnosti i dubinskim provjerama za sprječavanje ranjivosti mreže te planskim provođenjem odredbi o informacijskoj sigurnosti trajno održavaju i unaprjeđuju stupanj sigurnosti sustava zaštitom Vaših kartičnih podataka.

**Po Standardima logotipa v3.4** — na ovu stranicu dodati i logotipe sigurnosnih programa (Visa Secure, Mastercard Identity Check, Diners sigurna kupnja) — fajlovi su u `corvus pay/Obvezna_dokumentacija_za_online_prodajna_mjesta/2_Logo_sigurnost/`.

### E.7 Uvjeti kupnje + Politika privatnosti

Klijent je dostavio u zasebnim `.docx` fajlovima ("u prilogu" napomena u upitniku). **TODO:** kreirati WordPress page-ove sa slug-ovima:
- `opci-uvjeti-poslovanja` — sadržaj iz `Uvjeti kupnje web trgovine.docx`
- `politika-privatnosti` — sadržaj iz `Politika privatnosti.docx`

---

## Dodatak F — logotipi pripremljeni u temi (2026-05-20)

Folder `assets/img/payments/`:

| Fajl                | Brand                          | Format | Izvor (CorvusPay brand kit)                          |
|---------------------|--------------------------------|--------|------------------------------------------------------|
| `mastercard.svg`    | Mastercard                     | SVG    | `1_Logo_prihvat/Mastercard/ma_symbol.svg`            |
| `maestro.svg`       | Maestro                        | SVG    | `1_Logo_prihvat/Maestro/ms_hrz_pos.svg`              |
| `visa.png`          | Visa                           | PNG    | `1_Logo_prihvat/Visa/VBM_Blue_RGB.pdf` (sips PDF→PNG) |
| `diners.png`        | Diners Club                    | PNG    | `1_Logo_prihvat/Diners/Diners.pdf` (sips PDF→PNG)    |
| `discover.png`      | Discover                       | PNG    | `1_Logo_prihvat/Discover/DC_Discover_logo_rgb.pdf` (cropped — original sadrži i Diners i Discover) |
| `amex.png`          | American Express               | PNG    | `1_Logo_prihvat/American Express/AXP_BlueBoxLogo_CMYK.png` |
| `unionpay.png`      | UnionPay (pripremljeno, ne koristi se u footeru) | PNG | `1_Logo_prihvat/UnionPay/UnionPay_logo_CMYK.png` |
| `corvuspay.svg`     | CorvusPay (negativ za tamni footer) | SVG | `3_Logo_CorvusPay/corvus-logo-horizontal-negativ.svg` |
| `corvuspay-light.svg` | CorvusPay (pozitiv za svijetle pozadine) | SVG | `3_Logo_CorvusPay/corvus-logo-horizontal-pozitiv.svg` |

Folder `assets/img/payments/installments/` (za stranicu `nacin-placanja`):

| Fajl                          | Broj rata |
|-------------------------------|-----------|
| `pbz-premium-visa-6-rata.png` | 6         |
| `pbz-premium-visa-12-rata.png`| 12        |
| `pbz-premium-visa-24-rata.png`| 24        |
| `pbz-premium-visa-36-rata.png`| 36        |
| `nexi-pbzcard.png`            | Nexi PBZ Card (izdavatelj) |

**Sigurnosni programi** (još nisu integrirani — idu na stranice `nacin-placanja` i `izjava-o-sigurnosti-online-placanja`):
- Visa Secure: `corvus pay/Obvezna_dokumentacija_za_online_prodajna_mjesta/2_Logo_sigurnost/Visa Secure Logo/`
- Mastercard Identity Check: `2_Logo_sigurnost/Mastercard Identity Check/`
- Diners sigurna kupnja: `2_Logo_sigurnost/Diners sigurna kupnja/logo_sigurna kupnja_2.1.png`

---

**Autor audita:** Cursor agent · **Datum:** 2026-05-20 · **Verzija:** 1.1 (logotipi + impressum implementirani)
