<?php
/**
 * Content wrappper start — WooCommerce template override.
 *
 * Postavlja id="main-content" koji je cilj skip linka u header.php.
 * Koristi se na svim WooCommerce stranicama koje prolaze kroz
 * woocommerce_before_main_content hook (single product, taxonomy, search…).
 *
 * @see header.php <a class="skip-link" href="#main-content">
 */
defined('ABSPATH') || exit;

wc_print_notices();
?>
<main id="main-content" class="site-main" role="main">
