<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shared helpers for WooCommerce/YITH integration.
 *
 * Keeps plugin-specific string mapping isolated from hook files.
 */

/**
 * Detekcija YITH wishlist stranice.
 */
function tersa_is_wishlist_page(): bool {
	if (function_exists('yith_wcwl_is_wishlist_page')) {
		return (bool) yith_wcwl_is_wishlist_page();
	}

	if (function_exists('tersa_get_wishlist_url')) {
		$wishlist_url = tersa_get_wishlist_url();

		if (!empty($wishlist_url)) {
			$current_request = $GLOBALS['wp']->request ?? '';
			$current_url     = home_url(add_query_arg([], $current_request));

			$wishlist_path = wp_parse_url($wishlist_url, PHP_URL_PATH);
			$current_path  = wp_parse_url($current_url, PHP_URL_PATH);

			if ($wishlist_path && $current_path && untrailingslashit($wishlist_path) === untrailingslashit($current_path)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Normalizuje jezik za wishlist UI tekstove.
 */
function tersa_get_wishlist_language_slug(): string {
	$lang = function_exists('tersa_get_current_language_slug') ? tersa_get_current_language_slug() : '';

	if ('' === $lang && function_exists('pll_current_language')) {
		$current_lang = pll_current_language('slug');
		$lang         = is_string($current_lang) ? sanitize_key($current_lang) : '';
	}

	if ('' === $lang && function_exists('determine_locale')) {
		$lang = (string) determine_locale();
	}

	$lang = strtolower(str_replace('_', '-', trim($lang)));
	$lang = $lang ? substr($lang, 0, 2) : '';

	return in_array($lang, ['hr', 'en', 'de'], true) ? $lang : 'hr';
}

/**
 * Wishlist UI tekstovi po jeziku.
 *
 * @return array<string, array<string, string>>
 */
function tersa_get_wishlist_text_labels(): array {
	static $labels = null;

	if (null !== $labels) {
		return $labels;
	}

	$labels = [
		'add'                => [
			'hr' => 'Dodaj na listu želja',
			'en' => 'Add to wishlist',
			'de' => 'Zur Wunschliste hinzufügen',
		],
		'browse'             => [
			'hr' => 'Pregledaj listu želja',
			'en' => 'View wishlist',
			'de' => 'Wunschliste ansehen',
		],
		'added'              => [
			'hr' => 'Dodano na listu želja',
			'en' => 'Added to wishlist',
			'de' => 'Zur Wunschliste hinzugefügt',
		],
		'added_notification' => [
			'hr' => '"%s" je dodano na vašu listu "%s"!',
			'en' => '"%s" has been added to your "%s" list!',
			'de' => '"%s" wurde zu deiner Liste "%s" hinzugefügt!',
		],
		'remove'             => [
			'hr' => 'Ukloni s liste želja',
			'en' => 'Remove from wishlist',
			'de' => 'Von der Wunschliste entfernen',
		],
		'already_in'         => [
			'hr' => 'Proizvod je već na listi želja!',
			'en' => 'The product is already in the wishlist!',
			'de' => 'Das Produkt ist bereits auf der Wunschliste!',
		],
		'title'              => [
			'hr' => 'Lista želja',
			'en' => 'Wishlist',
			'de' => 'Wunschliste',
		],
		'title_mine'         => [
			'hr' => 'Moja lista želja',
			'en' => 'My wishlist',
			'de' => 'Meine Wunschliste',
		],
		'product_name'       => [
			'hr' => 'Naziv proizvoda',
			'en' => 'Product name',
			'de' => 'Produktname',
		],
		'unit_price'         => [
			'hr' => 'Cijena',
			'en' => 'Price',
			'de' => 'Preis',
		],
		'price'              => [
			'hr' => 'Cijena',
			'en' => 'Price',
			'de' => 'Preis',
		],
		'stock'              => [
			'hr' => 'Status zaliha',
			'en' => 'Stock status',
			'de' => 'Lagerstatus',
		],
		'stock_status'       => [
			'hr' => 'Status zaliha',
			'en' => 'Stock status',
			'de' => 'Lagerstatus',
		],
		'add_to_cart'        => [
			'hr' => 'Dodaj u košaricu',
			'en' => 'Add to cart',
			'de' => 'In den Warenkorb',
		],
		'remove_product'     => [
			'hr' => 'Ukloni ovaj proizvod',
			'en' => 'Remove this product',
			'de' => 'Dieses Produkt entfernen',
		],
		'empty'              => [
			'hr' => 'Nema proizvoda na listi želja.',
			'en' => 'No products on the wishlist.',
			'de' => 'Keine Produkte auf der Wunschliste.',
		],
		'in_stock'           => [
			'hr' => 'Na stanju',
			'en' => 'In stock',
			'de' => 'Auf Lager',
		],
	];

	return $labels;
}

/**
 * Wishlist helper za statične HR/EN/DE tekstove.
 */
function tersa_pll_wishlist(string $key): string {
	$labels = tersa_get_wishlist_text_labels();
	$lang   = tersa_get_wishlist_language_slug();

	return $labels[$key][$lang] ?? $labels[$key]['hr'] ?? '';
}

/**
 * Oznaka „Prethodna“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_prev_text(): string {
	$hr = 'Prethodna';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Oznaka „Sljedeća“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_next_text(): string {
	$hr = 'Sljedeća';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Trenutni Polylang jezik kao bezbedan slug.
 *
 * @return string
 */
if (!function_exists('tersa_get_current_language_slug')) {
	function tersa_get_current_language_slug(): string {
		if (!function_exists('pll_current_language')) {
			return '';
		}

		$lang = pll_current_language('slug');

		return is_string($lang) ? sanitize_key($lang) : '';
	}
}

/**
 * Normalizovan jezik za statične UI stringove.
 */
function tersa_get_ui_language_slug(): string {
	$lang = function_exists('tersa_get_current_language_slug') ? tersa_get_current_language_slug() : '';

	if ('' === $lang && function_exists('pll_current_language')) {
		$current_lang = pll_current_language('slug');
		$lang         = is_string($current_lang) ? sanitize_key($current_lang) : '';
	}

	if ('' === $lang && function_exists('determine_locale')) {
		$lang = (string) determine_locale();
	}

	$lang = strtolower(str_replace('_', '-', trim($lang)));
	$lang = $lang ? substr($lang, 0, 2) : '';

	return in_array($lang, ['hr', 'sr', 'en', 'de'], true) ? $lang : 'hr';
}

/**
 * Fallback prevodi za statične shop/breadcrumb stringove.
 *
 * Polylang prevod ima prednost; ovo pokriva slučaj kada string postoji u kodu,
 * ali prevod još nije unesen u Polylang Strings translations.
 *
 * @return array<string, array<string, string>>
 */
function tersa_get_ui_string_fallbacks(): array {
	return [
		'Naslovnica' => [
			'hr' => 'Naslovnica',
			'sr' => 'Početna',
			'en' => 'Home',
			'de' => 'Startseite',
		],
		'Breadcrumb' => [
			'hr' => 'Breadcrumb',
			'sr' => 'Putanja',
			'en' => 'Breadcrumb',
			'de' => 'Breadcrumb',
		],
		'Putanja stranice' => [
			'hr' => 'Putanja stranice',
			'sr' => 'Putanja stranice',
			'en' => 'Page path',
			'de' => 'Seitenpfad',
		],
		'Navigacija' => [
			'hr' => 'Navigacija',
			'sr' => 'Navigacija',
			'en' => 'Navigation',
			'de' => 'Navigation',
		],
		'Bestsellers' => [
			'hr' => 'Najprodavanije',
			'sr' => 'Najprodavanije',
			'en' => 'Bestsellers',
			'de' => 'Bestseller',
		],
		'Najprodavanije' => [
			'hr' => 'Najprodavanije',
			'sr' => 'Najprodavanije',
			'en' => 'Bestsellers',
			'de' => 'Bestseller',
		],
		'Najnovije' => [
			'hr' => 'Najnovije',
			'sr' => 'Najnovije',
			'en' => 'New arrivals',
			'de' => 'Neuheiten',
		],
		'Novo' => [
			'hr' => 'Novo',
			'sr' => 'Novo',
			'en' => 'New',
			'de' => 'Neu',
		],
		'Na sniženju' => [
			'hr' => 'Na sniženju',
			'sr' => 'Na sniženju',
			'en' => 'On sale',
			'de' => 'Im Angebot',
		],
		'Vidi opcije' => [
			'hr' => 'Vidi opcije',
			'sr' => 'Vidi opcije',
			'en' => 'View options',
			'de' => 'Optionen ansehen',
		],
		'Dodaj u košaricu' => [
			'hr' => 'Dodaj u košaricu',
			'sr' => 'Dodaj u korpu',
			'en' => 'Add to cart',
			'de' => 'In den Warenkorb',
		],
		'Na stanju' => [
			'hr' => 'Na stanju',
			'sr' => 'Na stanju',
			'en' => 'In stock',
			'de' => 'Auf Lager',
		],
		'na stanju' => [
			'hr' => 'na stanju',
			'sr' => 'na stanju',
			'en' => 'in stock',
			'de' => 'auf Lager',
		],
		'%s na stanju' => [
			'hr' => '%s na stanju',
			'sr' => '%s na stanju',
			'en' => '%s in stock',
			'de' => '%s auf Lager',
		],
		'Trenutačno nedostupno' => [
			'hr' => 'Trenutačno nedostupno',
			'sr' => 'Trenutno nedostupno',
			'en' => 'Currently unavailable',
			'de' => 'Derzeit nicht verfügbar',
		],
		'Ovaj proizvod trenutačno nije na stanju i nije dostupan.' => [
			'hr' => 'Ovaj proizvod trenutačno nije na stanju i nije dostupan.',
			'sr' => 'Ovaj proizvod trenutno nije na stanju i nije dostupan.',
			'en' => 'This product is currently out of stock and unavailable.',
			'de' => 'Dieses Produkt ist derzeit nicht auf Lager und nicht verfügbar.',
		],
		'Zalihe' => [
			'hr' => 'Zalihe',
			'sr' => 'Zalihe',
			'en' => 'Stock',
			'de' => 'Lagerbestand',
		],
		'Težina' => [
			'hr' => 'Težina',
			'sr' => 'Težina',
			'en' => 'Weight',
			'de' => 'Gewicht',
		],
		'Dimenzije' => [
			'hr' => 'Dimenzije',
			'sr' => 'Dimenzije',
			'en' => 'Dimensions',
			'de' => 'Abmessungen',
		],
		'Dimenzije:' => [
			'hr' => 'Dimenzije:',
			'sr' => 'Dimenzije:',
			'en' => 'Dimensions:',
			'de' => 'Abmessungen:',
		],
		'Moja košarica' => [
			'hr' => 'Moja košarica',
			'sr' => 'Moja korpa',
			'en' => 'My cart',
			'de' => 'Mein Warenkorb',
		],
		'Učitavanje košarice...' => [
			'hr' => 'Učitavanje košarice...',
			'sr' => 'Učitavanje korpe...',
			'en' => 'Loading cart...',
			'de' => 'Warenkorb wird geladen...',
		],
		'Close cart' => [
			'hr' => 'Zatvori košaricu',
			'sr' => 'Zatvori korpu',
			'en' => 'Close cart',
			'de' => 'Warenkorb schließen',
		],
		'Međuzbir' => [
			'hr' => 'Međuzbir',
			'sr' => 'Međuzbir',
			'en' => 'Subtotal',
			'de' => 'Zwischensumme',
		],
		'Blagajna' => [
			'hr' => 'Blagajna',
			'sr' => 'Plaćanje',
			'en' => 'Checkout',
			'de' => 'Kasse',
		],
		'Pogledaj košaricu' => [
			'hr' => 'Pogledaj košaricu',
			'sr' => 'Pogledaj korpu',
			'en' => 'View cart',
			'de' => 'Warenkorb ansehen',
		],
		'Ukupni iznos košarice' => [
			'hr' => 'Ukupni iznos košarice',
			'sr' => 'Ukupan iznos korpe',
			'en' => 'Cart totals',
			'de' => 'Warenkorbsumme',
		],
		'Free shipping' => [
			'hr' => 'Besplatna dostava',
			'sr' => 'Besplatna dostava',
			'en' => 'Free shipping',
			'de' => 'Kostenloser Versand',
		],
		'Shipping options' => [
			'hr' => 'Opcije dostave',
			'sr' => 'Opcije dostave',
			'en' => 'Shipping options',
			'de' => 'Versandoptionen',
		],
		'Payment options' => [
			'hr' => 'Opcije plaćanja',
			'sr' => 'Opcije plaćanja',
			'en' => 'Payment options',
			'de' => 'Zahlungsoptionen',
		],
		'Kartično plaćanje (CorvusPay)' => [
			'hr' => 'Kartično plaćanje (CorvusPay)',
			'sr' => 'Kartično plaćanje (CorvusPay)',
			'en' => 'Card payment (CorvusPay)',
			'de' => 'Kartenzahlung (CorvusPay)',
		],
		'Procesiranje transakcija na internetu.' => [
			'hr' => 'Procesiranje transakcija na internetu.',
			'sr' => 'Procesiranje transakcija na internetu.',
			'en' => 'Online transaction processing.',
			'de' => 'Sichere Zahlungsabwicklung im Internet.',
		],
		'Trenutno nema proizvoda u košarici.' => [
			'hr' => 'Trenutno nema proizvoda u košarici.',
			'sr' => 'Trenutno nema proizvoda u korpi.',
			'en' => 'There are currently no products in the cart.',
			'de' => 'Derzeit befinden sich keine Produkte im Warenkorb.',
		],
		'Šifra proizvoda:' => [
			'hr' => 'Šifra proizvoda:',
			'sr' => 'Šifra proizvoda:',
			'en' => 'SKU:',
			'de' => 'Artikelnummer:',
		],
		'Brendovi:' => [
			'hr' => 'Brendovi:',
			'sr' => 'Brendovi:',
			'en' => 'Brands:',
			'de' => 'Marken:',
		],
		'Kategorija:' => [
			'hr' => 'Kategorija:',
			'sr' => 'Kategorija:',
			'en' => 'Category:',
			'de' => 'Kategorie:',
		],
		'Opis' => [
			'hr' => 'Opis',
			'sr' => 'Opis',
			'en' => 'Description',
			'de' => 'Beschreibung',
		],
		'Dodatne informacije' => [
			'hr' => 'Dodatne informacije',
			'sr' => 'Dodatne informacije',
			'en' => 'Additional information',
			'de' => 'Zusatzinformationen',
		],
		'Recenzije' => [
			'hr' => 'Recenzije',
			'sr' => 'Recenzije',
			'en' => 'Reviews',
			'de' => 'Bewertungen',
		],
		'Recenzije (%d)' => [
			'hr' => 'Recenzije (%d)',
			'sr' => 'Recenzije (%d)',
			'en' => 'Reviews (%d)',
			'de' => 'Bewertungen (%d)',
		],
		'Još nema opisa.' => [
			'hr' => 'Još nema opisa.',
			'sr' => 'Još nema opisa.',
			'en' => 'No description yet.',
			'de' => 'Noch keine Beschreibung.',
		],
		'Nema dodatnih informacija.' => [
			'hr' => 'Nema dodatnih informacija.',
			'sr' => 'Nema dodatnih informacija.',
			'en' => 'No additional information.',
			'de' => 'Keine zusätzlichen Informationen.',
		],
		'Recenzije nisu dostupne.' => [
			'hr' => 'Recenzije nisu dostupne.',
			'sr' => 'Recenzije nisu dostupne.',
			'en' => 'Reviews are not available.',
			'de' => 'Bewertungen sind nicht verfügbar.',
		],
		'Još nema recenzija.' => [
			'hr' => 'Još nema recenzija.',
			'sr' => 'Još nema recenzija.',
			'en' => 'There are no reviews yet.',
			'de' => 'Es gibt noch keine Bewertungen.',
		],
		'Označke proizvoda' => [
			'hr' => 'Označke proizvoda',
			'sr' => 'Oznake proizvoda',
			'en' => 'Product badges',
			'de' => 'Produktkennzeichnungen',
		],
		'Product badges' => [
			'hr' => 'Označke proizvoda',
			'sr' => 'Oznake proizvoda',
			'en' => 'Product badges',
			'de' => 'Produktkennzeichnungen',
		],
		'Product tags' => [
			'hr' => 'Oznake proizvoda',
			'sr' => 'Oznake proizvoda',
			'en' => 'Product tags',
			'de' => 'Produkt-Tags',
		],
		'Slični proizvodi' => [
			'hr' => 'Slični proizvodi',
			'sr' => 'Slični proizvodi',
			'en' => 'Related products',
			'de' => 'Ähnliche Produkte',
		],
		'Sortiraj po najnovijim' => [
			'hr' => 'Sortiraj po najnovijim',
			'sr' => 'Sortiraj po najnovijim',
			'en' => 'Sort by latest',
			'de' => 'Nach neuesten sortieren',
		],
		'Zadano sortiranje' => [
			'hr' => 'Zadano sortiranje',
			'sr' => 'Podrazumevano sortiranje',
			'en' => 'Default sorting',
			'de' => 'Standardsortierung',
		],
		'Sortiraj po cijeni: nisko na visoko' => [
			'hr' => 'Sortiraj po cijeni: nisko na visoko',
			'sr' => 'Sortiraj po ceni: od niže ka višoj',
			'en' => 'Sort by price: low to high',
			'de' => 'Nach Preis sortieren: niedrig nach hoch',
		],
		'Sortiraj po cijeni: visoko na nisko' => [
			'hr' => 'Sortiraj po cijeni: visoko na nisko',
			'sr' => 'Sortiraj po ceni: od više ka nižoj',
			'en' => 'Sort by price: high to low',
			'de' => 'Nach Preis sortieren: hoch nach niedrig',
		],
		'Sortiraj po popularnosti' => [
			'hr' => 'Sortiraj po popularnosti',
			'sr' => 'Sortiraj po popularnosti',
			'en' => 'Sort by popularity',
			'de' => 'Nach Beliebtheit sortieren',
		],
		'Sortiraj po ocjeni' => [
			'hr' => 'Sortiraj po ocjeni',
			'sr' => 'Sortiraj po oceni',
			'en' => 'Sort by rating',
			'de' => 'Nach Bewertung sortieren',
		],
		'Prikaži samo proizvode na popustu' => [
			'hr' => 'Prikaži samo proizvode na popustu',
			'sr' => 'Prikaži samo proizvode na popustu',
			'en' => 'Show only discounted products',
			'de' => 'Nur reduzierte Produkte anzeigen',
		],
		'Primijeni' => [
			'hr' => 'Primijeni',
			'sr' => 'Primeni',
			'en' => 'Apply',
			'de' => 'Anwenden',
		],
		'Primijeni filtre' => [
			'hr' => 'Primijeni filtre',
			'sr' => 'Primeni filtere',
			'en' => 'Apply filters',
			'de' => 'Filter anwenden',
		],
		'Poništi filtre' => [
			'hr' => 'Poništi filtre',
			'sr' => 'Poništi filtere',
			'en' => 'Reset filters',
			'de' => 'Filter zurücksetzen',
		],
		'Prikaži sve proizvode' => [
			'hr' => 'Prikaži sve proizvode',
			'sr' => 'Prikaži sve proizvode',
			'en' => 'Show all products',
			'de' => 'Alle Produkte anzeigen',
		],
		'Kategorija' => [
			'hr' => 'Kategorija',
			'sr' => 'Kategorija',
			'en' => 'Category',
			'de' => 'Kategorie',
		],
		'Boja' => [
			'hr' => 'Boja',
			'sr' => 'Boja',
			'en' => 'Color',
			'de' => 'Farbe',
		],
		'Materijal' => [
			'hr' => 'Materijal',
			'sr' => 'Materijal',
			'en' => 'Material',
			'de' => 'Material',
		],
		'Dimenzija' => [
			'hr' => 'Dimenzija',
			'sr' => 'Dimenzija',
			'en' => 'Size',
			'de' => 'Größe',
		],
		'Patterns & Textures' => [
			'hr' => 'Uzorci i teksture',
			'sr' => 'Uzorci i teksture',
			'en' => 'Patterns & Textures',
			'de' => 'Muster & Texturen',
		],
		'Product filters' => [
			'hr' => 'Filteri proizvoda',
			'sr' => 'Filteri proizvoda',
			'en' => 'Product filters',
			'de' => 'Produktfilter',
		],
		'Product view' => [
			'hr' => 'Prikaz proizvoda',
			'sr' => 'Prikaz proizvoda',
			'en' => 'Product view',
			'de' => 'Produktansicht',
		],
		'List view' => [
			'hr' => 'Prikaz liste',
			'sr' => 'Prikaz liste',
			'en' => 'List view',
			'de' => 'Listenansicht',
		],
		'Grid view' => [
			'hr' => 'Prikaz mreže',
			'sr' => 'Prikaz mreže',
			'en' => 'Grid view',
			'de' => 'Rasteransicht',
		],
		'Sort products' => [
			'hr' => 'Sortiraj proizvode',
			'sr' => 'Sortiraj proizvode',
			'en' => 'Sort products',
			'de' => 'Produkte sortieren',
		],
		'Products' => [
			'hr' => 'Proizvodi',
			'sr' => 'Proizvodi',
			'en' => 'Products',
			'de' => 'Produkte',
		],
		'Pročitaj više' => [
			'hr' => 'Pročitaj više',
			'sr' => 'Pročitaj više',
			'en' => 'Read more',
			'de' => 'Mehr lesen',
		],
		'Sažmi opis' => [
			'hr' => 'Sažmi opis',
			'sr' => 'Sakrij opis',
			'en' => 'Collapse description',
			'de' => 'Beschreibung einklappen',
		],
		'Aktivni filter kategorije' => [
			'hr' => 'Aktivni filter kategorije',
			'sr' => 'Aktivni filter kategorije',
			'en' => 'Active category filter',
			'de' => 'Aktiver Kategoriefilter',
		],
		'Nema pronađenih proizvoda za odabrane filtere.' => [
			'hr' => 'Nema pronađenih proizvoda za odabrane filtere.',
			'sr' => 'Nema pronađenih proizvoda za odabrane filtere.',
			'en' => 'No products found for the selected filters.',
			'de' => 'Keine Produkte für die ausgewählten Filter gefunden.',
		],
		'Navigacija kroz proizvode' => [
			'hr' => 'Navigacija kroz proizvode',
			'sr' => 'Navigacija kroz proizvode',
			'en' => 'Product navigation',
			'de' => 'Produktnavigation',
		],
		'Prethodna' => [
			'hr' => 'Prethodna',
			'sr' => 'Prethodna',
			'en' => 'Previous',
			'de' => 'Vorherige',
		],
		'Sljedeća' => [
			'hr' => 'Sljedeća',
			'sr' => 'Sledeća',
			'en' => 'Next',
			'de' => 'Nächste',
		],
		'Greška 404' => [
			'hr' => 'Greška 404',
			'sr' => 'Greška 404',
			'en' => 'Error 404',
			'de' => 'Fehler 404',
		],
		'Stranica nije pronađena' => [
			'hr' => 'Stranica nije pronađena',
			'sr' => 'Stranica nije pronađena',
			'en' => 'Page not found',
			'de' => 'Seite nicht gefunden',
		],
		'Nažalost, stranica koju tražite ne postoji, premještena je ili je privremeno nedostupna.' => [
			'hr' => 'Nažalost, stranica koju tražite ne postoji, premještena je ili je privremeno nedostupna.',
			'sr' => 'Nažalost, stranica koju tražite ne postoji, premeštena je ili je privremeno nedostupna.',
			'en' => 'Unfortunately, the page you are looking for does not exist, has been moved, or is temporarily unavailable.',
			'de' => 'Leider existiert die gesuchte Seite nicht, wurde verschoben oder ist vorübergehend nicht verfügbar.',
		],
		'Mogućnosti navigacije' => [
			'hr' => 'Mogućnosti navigacije',
			'sr' => 'Opcije navigacije',
			'en' => 'Navigation options',
			'de' => 'Navigationsoptionen',
		],
		'Povratak na početnu' => [
			'hr' => 'Povratak na početnu',
			'sr' => 'Povratak na početnu',
			'en' => 'Back to homepage',
			'de' => 'Zur Startseite',
		],
		'Idi u trgovinu' => [
			'hr' => 'Idi u trgovinu',
			'sr' => 'Idi u prodavnicu',
			'en' => 'Go to shop',
			'de' => 'Zum Shop',
		],
	];
}

/**
 * Polylang-aware prevod statičnih UI stringova.
 */
function tersa_translate_ui_string(string $string): string {
	$translated = function_exists('pll__')
		? (string) pll__($string)
		: (string) __($string, 'tersa-shop');

	if ($translated !== $string) {
		return $translated;
	}

	$fallbacks = tersa_get_ui_string_fallbacks();
	$lang      = tersa_get_ui_language_slug();

	return $fallbacks[$string][$lang] ?? $fallbacks[$string]['hr'] ?? $translated;
}

/**
 * Polylang-aware label for known product tags used as visual badges.
 */
function tersa_translate_product_tag_label($term_or_name): string {
	$name = $term_or_name instanceof WP_Term ? (string) $term_or_name->name : (string) $term_or_name;
	$name = trim($name);

	if ($name === '') {
		return '';
	}

	$translated = tersa_translate_ui_string($name);
	if ($translated !== $name) {
		return $translated;
	}

	$known_badge_labels = [
		'najprodavanije' => 'Najprodavanije',
		'bestseller'     => 'Najprodavanije',
		'bestsellers'    => 'Najprodavanije',
		'best-sellers'   => 'Najprodavanije',
		'najnovije'      => 'Najnovije',
		'new-arrivals'   => 'Najnovije',
		'novo'           => 'Novo',
		'new'            => 'Novo',
	];
	$normalized = sanitize_title($name);

	return isset($known_badge_labels[$normalized])
		? tersa_translate_ui_string($known_badge_labels[$normalized])
		: $translated;
}

/**
 * Translates WooCommerce attribute labels that are used in variation pickers and cart item data.
 */
function tersa_translate_product_attribute_label($label, $name = '', $product = null): string {
	$label = is_string($label) ? trim($label) : '';
	if ($label === '') {
		return '';
	}

	$source_label = rtrim($label, " \t\n\r\0\x0B:");
	$translated   = tersa_translate_ui_string($source_label);

	if ($translated !== $source_label) {
		return substr($label, -1) === ':' ? $translated . ':' : $translated;
	}

	$name = is_string($name) ? $name : '';
	if ($name !== '') {
		$attribute_key = sanitize_title(str_replace(['attribute_', 'pa_'], '', $name));
		$known_labels  = [
			'dimenzije' => 'Dimenzije',
			'dimensions' => 'Dimenzije',
			'abmessungen' => 'Dimenzije',
		];

		if (isset($known_labels[$attribute_key])) {
			return tersa_translate_ui_string($known_labels[$attribute_key]);
		}
	}

	return $label;
}
add_filter('woocommerce_attribute_label', 'tersa_translate_product_attribute_label', 20, 3);

/**
 * Keeps cart/mini-cart variation labels translated even if item data is injected by a plugin.
 */
function tersa_translate_cart_item_data_labels(array $item_data, array $cart_item): array {
	foreach ($item_data as $index => $data) {
		foreach (['key', 'name'] as $label_key) {
			if (empty($data[$label_key]) || !is_string($data[$label_key])) {
				continue;
			}

			$source = rtrim(trim(wp_strip_all_tags($data[$label_key])), " \t\n\r\0\x0B:");
			if ($source === '') {
				continue;
			}

			$item_data[$index][$label_key] = tersa_translate_ui_string($source);
		}
	}

	return $item_data;
}
add_filter('woocommerce_get_item_data', 'tersa_translate_cart_item_data_labels', 20, 2);

/**
 * Argument za Polylang-aware WP/Woo query-je.
 *
 * @return array{lang?: string}
 */
function tersa_get_current_language_query_arg(): array {
	$lang = tersa_get_current_language_slug();

	return $lang !== '' ? ['lang' => $lang] : [];
}

/**
 * Dohvata term po slug-u u trenutnom Polylang jeziku.
 *
 * @param string $slug
 * @param string $taxonomy
 * @return WP_Term|null
 */
function tersa_get_current_language_term_by_slug(string $slug, string $taxonomy): ?WP_Term {
	$slug     = sanitize_title($slug);
	$taxonomy = sanitize_key($taxonomy);

	if ($slug === '' || $taxonomy === '' || !taxonomy_exists($taxonomy)) {
		return null;
	}

	$args = [
		'taxonomy'   => $taxonomy,
		'slug'       => $slug,
		'hide_empty' => false,
		'number'     => 1,
	];

	$lang = tersa_get_current_language_slug();
	if ($lang !== '') {
		$args['lang'] = $lang;
	}

	$terms = get_terms($args);

	if (!is_wp_error($terms) && !empty($terms) && $terms[0] instanceof WP_Term) {
		return $terms[0];
	}

	$term = get_term_by('slug', $slug, $taxonomy);
	if (!$term instanceof WP_Term) {
		return null;
	}

	if ($lang !== '' && function_exists('pll_get_term_language')) {
		$term_lang = pll_get_term_language($term->term_id, 'slug');

		if ($term_lang === $lang) {
			return $term;
		}

		if (function_exists('pll_get_term')) {
			$translated_id = absint(pll_get_term($term->term_id, $lang));
			if ($translated_id) {
				$translated = get_term($translated_id, $taxonomy);
				return $translated instanceof WP_Term && !is_wp_error($translated) ? $translated : null;
			}
		}

		return null;
	}

	return $term;
}

/**
 * Vraća product ID preveden u trenutni jezik; 0 ako proizvod ne pripada jeziku.
 *
 * @param int $product_id
 * @return int
 */
function tersa_get_current_language_product_id(int $product_id): int {
	static $cache = [];

	$product_id = absint($product_id);
	if (!$product_id) {
		return 0;
	}

	$lang = tersa_get_current_language_slug();
	if ($lang === '' || !function_exists('pll_get_post')) {
		return $product_id;
	}

	$cache_key = $lang . '|' . $product_id;
	if (isset($cache[$cache_key])) {
		return $cache[$cache_key];
	}

	$translated_id = absint(pll_get_post($product_id, $lang));
	if ($translated_id) {
		return $cache[$cache_key] = $translated_id;
	}

	if (function_exists('pll_get_post_language')) {
		$post_lang = pll_get_post_language($product_id, 'slug');
		return $cache[$cache_key] = ($post_lang === $lang ? $product_id : 0);
	}

	return $cache[$cache_key] = $product_id;
}

/**
 * Normalizuje listu product ID-jeva na trenutni jezik i uklanja duplikate.
 *
 * @param array<int, int|string> $product_ids
 * @return array<int, int>
 */
function tersa_filter_product_ids_for_current_language(array $product_ids): array {
	$filtered = [];

	foreach ($product_ids as $product_id) {
		$translated_id = tersa_get_current_language_product_id(absint($product_id));

		if ($translated_id) {
			$filtered[$translated_id] = $translated_id;
		}
	}

	return array_values($filtered);
}

/**
 * Cached wishlist button markup for product cards.
 * Avoids repeated shortcode parsing/rendering inside loops.
 */
function tersa_get_wishlist_button_markup(int $product_id, string $link_class): string {
	static $has_shortcode = null;
	static $cache = [];

	if ($product_id <= 0) {
		return '';
	}

	if ($has_shortcode === null) {
		$has_shortcode = function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist');
	}

	if (!$has_shortcode) {
		return '';
	}

	$cache_key = $product_id . '|' . $link_class;
	if (!isset($cache[$cache_key])) {
		$cache[$cache_key] = (string) do_shortcode(
			sprintf(
				'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="%s"]',
				$product_id,
				esc_attr($link_class)
			)
		);
	}

	return $cache[$cache_key];
}

/**
 * Returns translated product badge rules keyed by current-language term ID.
 *
 * Base slugs are intentionally Croatian because hr is the default language. When
 * Polylang is active, each base term is mapped to the current language with
 * pll_get_term(), so EN/DE badge detection does not depend on translated names.
 *
 * @return array<int, bool> term_id => primary badge flag
 */
function tersa_get_product_badge_term_rules(): array {
	static $cache = [];

	$lang      = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	$cache_key = $lang ?: '_default';

	if (isset($cache[$cache_key])) {
		return $cache[$cache_key];
	}

	$base_rules = [
		'najprodavanije' => true,
		'najnovije'      => false,
		'novo'           => false,
	];
	$base_rules = (array) apply_filters('tersa_product_badge_base_tag_rules', $base_rules);

	$rules = [];

	foreach ($base_rules as $base_slug => $is_primary) {
		$terms = get_terms([
			'taxonomy'   => 'product_tag',
			'slug'       => sanitize_title($base_slug),
			'hide_empty' => false,
			'number'     => 1,
			'fields'     => 'ids',
			'lang'       => '',
		]);

		if (is_wp_error($terms) || empty($terms)) {
			continue;
		}

		$term_id = absint($terms[0]);
		if (!$term_id) {
			continue;
		}

		if (function_exists('pll_get_term')) {
			$translated_id = $lang ? pll_get_term($term_id, $lang) : pll_get_term($term_id);
			if ($translated_id) {
				$term_id = absint($translated_id);
			}
		}

		if ($term_id) {
			$rules[$term_id] = (bool) $is_primary;
		}
	}

	return $cache[$cache_key] = $rules;
}

/**
 * Builds product badges from product_tag terms in the current language.
 *
 * @param int $product_id Product ID.
 * @param int $limit      Maximum number of taxonomy badges, before sale badge is appended.
 * @return array<int, array{label:string,primary:bool}>
 */
function tersa_get_product_tag_badges(int $product_id, int $limit = 2): array {
	if ($product_id <= 0) {
		return [];
	}

	$badge_rules = tersa_get_product_badge_term_rules();
	if (empty($badge_rules)) {
		return [];
	}

	$terms = get_the_terms($product_id, 'product_tag');
	if (is_wp_error($terms) || !is_array($terms) || empty($terms)) {
		return [];
	}

	$badges = [];
	foreach ($terms as $term) {
		if (!$term instanceof WP_Term) {
			continue;
		}

		$term_id = (int) $term->term_id;
		if (!array_key_exists($term_id, $badge_rules)) {
			continue;
		}

		$badges[] = [
			'label'   => tersa_translate_product_tag_label($term),
			'primary' => (bool) $badge_rules[$term_id],
		];
	}

	if (empty($badges)) {
		return [];
	}

	usort($badges, static function (array $a, array $b): int {
		return (int) $b['primary'] <=> (int) $a['primary'];
	});

	return array_slice($badges, 0, max(1, $limit));
}
