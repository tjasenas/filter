<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

?>
<header class="woocommerce-products-header">
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>
</header>
<?php
if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

		
	woocommerce_product_loop_start();


	$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$url_components = parse_url( $url, PHP_URL_QUERY);

	$params = [];

	$min_price = -1;
	$max_price = 999999;
	$i = 0;
	

	if(isset($url_components)) {
		$raw_querys = explode("&" ,$url_components);

		foreach($raw_querys as $query) {
			$q = explode('=', $query);
			
			if($q[0] === 'kaina') {
				$price_range = explode('-',  $q[1]);
				$min_price = +$price_range[0];
				$max_price = +$price_range[1];
				continue;
			}
			array_push($params, $q[1]);
		}

	}

	$total_products = wc_get_loop_prop( 'total' ) ;

	if ( $total_products) {

		if(count($params) || $min_price > -1 ) {

			while ( have_posts() ) {
				the_post();
				
				global $product;
				$attrs = $product->get_attributes();
				$price = (float)$product->get_price();

				$product_attrs = [];


				if( $price >= $min_price && $price <= $max_price ) {

					foreach( $attrs as  $attr) {
						$tax_name = $attr->get_name();
						$options = get_the_terms($product->get_id(), $tax_name);
						foreach($options as $option) {
							array_push($product_attrs, $option->name);
						}
					}
					
					$has_tags = count(array_intersect( $params, $product_attrs)) == count($params);

					if (!$has_tags)  continue;


					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );
		
					wc_get_template_part( 'content', 'product' );

					$i++;
				}

			}

		} else {			
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );
	
				wc_get_template_part( 'content', 'product' );
				$i++;
			}
		}
	}

	woocommerce_product_loop_end();
	
	
	if( $i === 0 ) {
		echo '<div class="woocommerce-info">Pagal nurodytus filtrus produktų nerasta.</div>';
	}


	$html ='<div class="button-wrapper" ><button class="get-more-products wp-block-button__link" data-post-type="patarimai" data-id="" data-per-page="">Gauti daugiau rezultatų</button></div>';
	$html .='</div>';
	echo $html;
	
	

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
