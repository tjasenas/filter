<?php



function get_category_products($request) {
    $args = array(
        'post_status' => 'publish',
        'limit' => -1,
        'category' => $request['category'],
    );
    $products = wc_get_products($args);

    $product_html = '';
    $filters = [];

    $index = 0;

    foreach($products as $product ) {

        $attrs = $product->get_attributes();
        
        foreach( $attrs as  $attr) {
            $tax_name = $attr->get_name();
            $options = get_the_terms($product->get_id(), $tax_name);
            $arr = [];

            
            // Check if we have filter added
            $tax_index = -1;

            foreach($filters as $key => $item) {
                if($item['name'] === $tax_name ) {
                    $tax_index = $key;
                }
            }

            if( $tax_index !== -1 ) {
                // Increase attribute qty
                foreach($options as $option) {
                    foreach($filters[$tax_index]['attr'] as $key => $tax) {
                        if($tax['title'] === $option->name ) {
                            $filters[$tax_index]['attr'][$key]['qty'] += 1;
                        }
                    }
                }

            } else {
                //Add new tax and attributes
                $arr = [ 'name' => $tax_name, 'attr' => [] ];
                foreach($options as $option) {
                    if( $option->name === Null) break;
                    array_push($arr['attr'], ['title' => $option->name, 'qty' => 1]); 
                }
                array_push($filters, $arr );
            }
        }

        
        if ( $index <= 10 ) {
            $product_html .= 
            '<li class="product type-product">
                <div class="post_item post_layout_list">
                    <div class="post_featured hover_none">
                    <a href="'.$product->get_permalink().'">
                        '.$product->get_image( 'woocommerce_thumbnail', array(), true ).'
                    </a>
                    </div>
                    <div class="post_data">
                    <div class="post_data_inner">
                        <div class="post_header entry-header">
                        <h2 class="woocommerce-loop-product__title"><a href="'.$product->get_permalink().'">'.$product->get_title().'</a></h2>
                        <div class="star-rating" role="img" aria-label="Įvertinimas: 4.83 iš 5">
                            <span style="width: 96.6%">Įvertinimas: <strong class="rating">4.83</strong> iš 5</span>
                        </div>
                        </div>
                        <div class="post_content entry-content"></div>
                        '.$product->get_price_html().'
                    </div>
                    </div>
                </div>
            </li>';
        }

        $index++;
    }


	if (empty($product_html)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response(['products' =>  $product_html, 'filters' => $filters, 'qty' => $index]);
	$response->set_status(200);
	return $response;
}





function before_shop_loop() {

    ?>
    <button class="open-products-filter wp-block-button__link" data-category="<?php echo single_term_title(); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"></path>
        </svg>
        Filtruoti
    </button>
    <?php
   
}

add_action('woocommerce_before_shop_loop', 'before_shop_loop', 10 );

