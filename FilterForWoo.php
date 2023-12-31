<?php


function filtered_products($request) {
    
    $product_html = '';
    $body = json_decode($request->get_body());
    $tags = [];


    $has_price_filter = false;
    $price_range_low = -1;
    $price_range_high = 99999;
    
    foreach( $body->tags as $tag) {
        if ($tag->parent === 'kaina') {
            $price_range = explode('-',$tag->tag);
            $price_range_low = +$price_range[0];
            $price_range_high = +$price_range[1];
            $has_price_filter = true;
            continue;
        }
        array_push($tags, $tag->tag);
    }
    
    $args = array(
        'post_status' => 'publish',
        'limit' => -1,
        'category' => $request['filter'],
    );


    $products = wc_get_products($args);
    $filters = [];
    $index = 0;
    $init = false;

    $price_h = 0;
    $price_l = 0;

    $init = false;

    foreach($products as $product ) {
        $attrs = $product->get_attributes();
        $product_attrs = [];
        $price = (float)$product->get_price();

       
        if(!$init) {
            $price_h = $price;
            $price_l = $price;
            $init = true;
        }

        if( $price <  $price_l ) {
            $price_l = $price;
        }
        
        if($price > $price_h) {
            $price_h = $price;
        }



        if( $price >= $price_range_low && $price <= $price_range_high ) {

            foreach($attrs as  $attr) {
                $tax_name = $attr->get_name();
                $options = get_the_terms($product->get_id(), $tax_name);
                foreach($options as $option) {
                    array_push($product_attrs, $option->name);
                }
            }
            
            $has_tags = count(array_intersect( $tags, $product_attrs)) == count($tags);


            if (!$has_tags)  continue;

            foreach( $attrs as  $attr) {

                $tax_name = $attr->get_name();
                $options = get_the_terms($product->get_id(), $tax_name);
                
    
    
                $tax_index = -1;
    
                foreach($filters as $key => $item) {
                    if($item['name'] === $tax_name ) {
                        $tax_index = $key;
                    }
                }
    
    
                if($tax_index === -1) {
                    array_push($filters, [ 'name' => $tax_name, 'attr' => [] ]);
                    $tax_index = array_key_last($filters);
                } 
    
                foreach($options as $option) {
                    $tax_attr_index = -1;
                    
                    foreach($filters[$tax_index]['attr'] as $key => $item) {
                        if($option->name === $item['title']) {
                            $tax_attr_index = $key;
                        }
                    }
                
                    if($tax_attr_index > -1) {
                        $filters[$tax_index]['attr'][$tax_attr_index]['qty']++;
                    } else {
                        array_push($filters[$tax_index]['attr'], ['title' => $option->name, 'qty' => 1]);
                    }
                }

            }
        
            
            if ( $index <= 9 ) {
          
                $product_html .= '
                <li class="product type-product post-'.$product->get_id().' status-publish first instock product_cat-accessories has-post-thumbnail taxable shipping-taxable purchasable product-type-simple without-images wishlist_decorated">
                    <div class="post_item post_layout_thumbs">

                        <div class="post_featured hover_shop_buttons">
                            <a href="'.$product->get_permalink().'">'.$product->get_image( 'woocommerce_thumbnail', array(), true ).'</a>
                            <div class="mask"></div>
                            
                            <div class="icons">
                                <a rel="nofollow" href="?add-to-cart='.$product->get_id().'" aria-hidden="true" data-quantity="1" data-product_id="14" data-product_sku="" class="shop_cart icon-cart-2 button add_to_cart_button product_type_simple product_in_stock ajax_add_to_cart">Buy now</a>
                                <a href="'.$product->get_permalink().'" aria-hidden="true" class="shop_link button icon-link">
                                Details
                                </a>
                            </div>
                        </div>

                        <div class="post_data">
                            <div class="post_data_inner">
                                <div class="post_header entry-header">
                                    <h2 class="woocommerce-loop-product__title"><a href="'.$product->get_permalink().'">Krepselis</a></h2>			
                                </div>
                                <div class="price_wrap">
                                    <span class="price">
                                    '.$product->get_price_html().'
                                    </span>
                                </div>
                                <a href="?add-to-cart='.$product->get_id().'" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart sc_button_hover_slide_left" data-product_id="14" data-product_sku="" aria-label="Add “Krepselis” to your cart" aria-describedby="" rel="nofollow">Buy now</a>				
                            </div>
                        </div>
                    </div>
                </li>';
            }

            $index++;
        }
    }
    

    if($has_price_filter) {
        array_unshift($filters, ['name'=>'Kaina', 'lowest_price' => $price_range_low, 'hight_price' =>  $price_range_high ]);
    } else {
        array_unshift($filters, ['name'=>'Kaina', 'lowest_price' => $price_l, 'hight_price' => $price_h ]);
    }



    if (empty($product_html)) {
		return new WP_Error( 'empty_category', 'Pagal nurodytus filtrus produktu neturime', array('status' => 200) );
	}
	
	$response = new WP_REST_Response(['products' =>  $product_html, 'filters' => $filters, 'qty' => $index, 'lowest_price' =>  $price_l, 'high_price' => $price_h]);
	$response->set_status(200);
	return $response;
}




function get_category_products($request) {
    $args = array(
        'post_status' => 'publish',
        'limit' => -1,
        'category' => $request['category'],
    );
    $products = wc_get_products($args);
    
    $lowest_price = 0;
    $high_price = 0;
    $init = false;

    $filters = [];
    $index = 0;

    foreach($products as $product ) {

        $price = (float)$product->get_price();


        if(!$init) {
            $lowest_price =  $price;
            $high_price =  $price;
            $init = true;
        }

        if( $price <  $lowest_price ) {
            $lowest_price =  $price;
        }
        
        if($price > $high_price) {
            $high_price =  $price;
        }



        $attrs = $product->get_attributes();
        
        foreach( $attrs as  $attr) {
            $tax_name = $attr->get_name();
            $options = get_the_terms($product->get_id(), $tax_name);
            


            $tax_index = -1;

            foreach($filters as $key => $item) {
                if($item['name'] === $tax_name ) {
                    $tax_index = $key;

                    // foreach($item['attr'] as $key => $item) {
                    //     $tax_attr_index = $key
                    // }
                }
            }


            if($tax_index === -1) {
                array_push($filters, [ 'name' => $tax_name, 'attr' => [] ]);
                $tax_index = array_key_last($filters);
            } 

            foreach($options as $option) {
                $tax_attr_index = -1;
                
                foreach($filters[$tax_index]['attr'] as $key => $item) {
                    if($option->name === $item['title']) {
                        $tax_attr_index = $key;
                    }
                }
            
                if($tax_attr_index > -1) {
                    $filters[$tax_index]['attr'][$tax_attr_index]['qty']++;
                } else {
                    array_push($filters[$tax_index]['attr'], ['title' => $option->name, 'qty' => 1]);
                }
            }
        }

        $index++;
    }

    array_unshift($filters, ['name'=>'Kaina', 'lowest_price' => $lowest_price, 'hight_price' => $high_price]);


	if (empty($filters)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response(['filters' => $filters, 'qty' => $index, 'lowest_price' =>  $lowest_price, 'high_price' => $high_price]);
	$response->set_status(200);
	return $response;
}




function get_category_with_cleared_filters($request) {
    $args = array(
        'post_status' => 'publish',
        'limit' => -1,
        'category' => $request['category'],
    );
    $products = wc_get_products($args);

    $filters = [['name'=>'Kaina', 'lowest_price' => 10000, 'hight_price' => 0 ]];

    $index = 0;
    $product_html = '';

    foreach($products as $product ) {

        $price = $product->get_price();

        if( $price <  $filters[0]['lowest_price']) {
            $filters[0]['lowest_price'] = $price;
        }
        if( $price > $filters[0]['hight_price']) {
            $filters[0]['hight_price'] = $price;
        }



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
            $product_html .= '
            <li class="product type-product post-'.$product->get_id().' status-publish first instock product_cat-accessories has-post-thumbnail taxable shipping-taxable purchasable product-type-simple without-images wishlist_decorated">
                <div class="post_item post_layout_thumbs">

                    <div class="post_featured hover_shop_buttons">
                        <a href="'.$product->get_permalink().'">'.$product->get_image( 'woocommerce_thumbnail', array(), true ).'</a>
                        <div class="mask"></div>
                        
                        <div class="icons">
                            <a rel="nofollow" href="?add-to-cart='.$product->get_id().'" aria-hidden="true" data-quantity="1" data-product_id="14" data-product_sku="" class="shop_cart icon-cart-2 button add_to_cart_button product_type_simple product_in_stock ajax_add_to_cart">Buy now</a>
                            <a href="'.$product->get_permalink().'" aria-hidden="true" class="shop_link button icon-link">
                            Details
                            </a>
                        </div>
                    </div>

                    <div class="post_data">
                        <div class="post_data_inner">
                            <div class="post_header entry-header">
                                <h2 class="woocommerce-loop-product__title"><a href="'.$product->get_permalink().'">Krepselis</a></h2>			
                            </div>
                            <div class="price_wrap">
                                <span class="price">
                                '.$product->get_price_html().'
                                </span>
                            </div>
                            <a href="?add-to-cart='.$product->get_id().'" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart sc_button_hover_slide_left" data-product_id="14" data-product_sku="" aria-label="Add “Krepselis” to your cart" aria-describedby="" rel="nofollow">Buy now</a>				
                        </div>
                    </div>
                </div>
            </li>';
        }

        $index++;
    }


	if (empty($filters)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response(['products' =>  $product_html, 'filters' => $filters, 'qty' => $index]);
	$response->set_status(200);
	return $response;
}


function before_shop_loop() {


        $term = get_queried_object();
        if(!isset($term->slug)) return;

        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$url_components = parse_url( $url, PHP_URL_QUERY);
	   
		$params = [];
		
		if(isset($url_components)) {
			$raw_querys = explode("&" ,$url_components);
			foreach($raw_querys as $query) {
				$q = explode('=', $query);
				array_push($params, $q);
			}
		}
		?>

		<div class="category-sorting">
			<ul class="active-tags category-tags <?php echo count($params) ? '' : 'hidden'; ?>" >
				<?php 

				if(count($params)) :
				foreach( $params as  $param_arr ): 

					$title = $param_arr[0];
					
					if(str_contains($title , 'pa_')) {
						$str = str_replace('pa_', '', $title);
						$explode = explode('-', $str);
						$title  = join(' ', $explode);
					}

					?>
					<li><?php echo $title; ?> - <?php echo $param_arr[1]; ?><span class="remove-tag" data-name="<?php echo $param_arr[1]; ?>" >X</span></li>
					<?php
				endforeach; 
				endif;
				?>
				<li class="remove-all">Išvalyti filtrus</li>
			</ul>

			<button class="open-products-filter wp-block-button__link" data-category="<?php echo $term->slug; ?>" >
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"></path>
				</svg>
				Filtruoti
			</button>
		</div>
		<?php

}
add_action('woocommerce_before_shop_loop', 'before_shop_loop', 10 );



