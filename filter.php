<?php
/*
Plugin Name: Filter for acf 
Version: 1.0
Author: Tomas Jasenas
*/

require "FilterForWoo.php";


function insert_my_footer() {

	?>
	<div class=" modal hidden"><div class="modal-inner">
    <div class="filter-heading">
      <h2>Filtras</h2>
      <button class="close-modal">&times;</button>
    </div>
    <div class="posts-filtering">
    </div>
    <div class="btn-wrapper">
    <button class="filter-btn wp-block-button__link"><span class="btn-qty"></span> Filtruoti</button>
    <button class="clear-filters wp-block-button__link">Išvalyti filtrą</button>
    </div>
      <div class="spinner-wrapper hidden">
        <div class="loader"></div>
      </div>
    </div>
    </div>
    <div class="overlay hidden"></div>
	<?php
}

add_action('wp_footer', 'insert_my_footer');


function anesta_action_after() {

	$page_title = 'Patarimas';
	$post_type = get_post_type();
	$per_page = 9;
	$addedtags = [];
	$prev_added_tags = [];
	$open_page = 1;


	$terms = get_terms(array(
		'hide_empty' => false,
	) );




	$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$url_components = parse_url( $url, $component = -1 );
	parse_str($url_components['query'], $params);


	foreach( $params as $key => $param ) {
		if( $key === 'pg')  {
			$open_page = +$param;
			continue;
		};
		array_push($addedtags, $param);
		array_push($prev_added_tags, $param);
	}
	
	$posts = get_posts(array(
		'posts_per_page'    => -1,
		'post_type'         => $post_type,
	));
	
	$page_posts = [];


	
	foreach( $posts as $post ) {
		$taxonomies=get_taxonomies();
		$tags = wp_get_post_terms($post->ID, $taxonomies, array( 'fields' => 'slugs' ));

		$has_tags = count(array_intersect($addedtags, $tags)) == count($addedtags);
		
		if($has_tags && $post->ID !== get_the_ID()) {
			array_push( $page_posts, $post);
		}
	} 

	$html = '';

	$html .= '<h3>Daugiau patarimu</h3>';

	$html .='<div class="ctx-wrappers">';

	if( $page_posts ) :

		$html .='<ul class="active-tags hidden"  data-post-type="'.$post_type.'" data-title="'.$page_title.'" >';
		foreach( $params as $key => $param ): 

			if( $key === 'pg') continue;

			$obj = '';

			foreach($terms as $term) {
				if($term->slug === $param) {
					$obj = $term;
				}
			}

			$html .='<li>'.$obj->name.' <span class="remove-tag" data-name="'.$obj->name.'" data-parent="'.$key.'" data-slug="'.$obj->slug.'">X</span></li>';

		endforeach; 

		
		$html .='<li class="remove-all">Išvalyti filtrus</li></ul>';

		$html .='<div class="ctx-posts">';

			$i = 0;
			$par = empty($url_components['query']) ? '' : '?'.$url_components['query'];
			foreach( $page_posts as $post ): 


				$html .='<article class="post_layout_band">';
					$html .='<div class="post_featured with_thumb hover_none">';
						$html .='<a href="'.get_permalink($post->ID).$par.' " rel="bookmark">'.get_the_post_thumbnail( $post->ID, 'full').'</a>';
					$html .='</div>';

					$html .='<div class="post_content_wrap">';
						$html .='<div class="post_header entry-header">';
						$html .='<div class="post_meta"><span class="post_meta_item post_categories cat_sep">'.$page_title.'</span></div>';
						$html .='<h3 class="post_title entry-title"><a href="'.get_permalink($post->ID).'" rel="bookmark">'.$post->post_title.'</a></h3>';
						$html .='</div>';

						$html .='<div class="post_content entry-content"><div class="post_content_inner">'.mb_strimwidth(get_field("trumpas_eksperto_aprasymas", $post->ID), 0, 270, '...').'</div>';
						
						if($post_type !== 'ekspertai') {
							$html .='<div class="post_meta post_meta_other">';
							$html .='<span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">'.do_shortcode("[wp_ulike_counter id=".$post->ID."]").'</span></span>';
							$html .='<a href="'.get_permalink($post->ID).'#comments" class="post_meta_item post_meta_comments icon-comment-light inited">';
							$html .='<span class="post_meta_number">'.get_comments_number($post->ID).'</span>';
							$html .='<span class="post_meta_label">Comments</span>';
							$html .='</a>';
							$html .='</div>';
						}

					$html .='</div>';
				$html .='</article>';

				if ( ++$i === $open_page * $per_page ) {
					break;
				}
			endforeach; 
		$html .='</div>';
	endif;


	$toggle_class = count($page_posts) > $open_page * $per_page ? '' : 'hidden';

	$html .='<div class="button-wrapper '.$toggle_class.'" ><button class="load-more wp-block-button__link" data-per-page="'.$per_page.'">Gauti daugiau rezultatu</button></div>';
	$html .='</div>';
	echo $html;
}

function wpb_demo_shortcode($atts) { 
  
	$page_slug = $atts['page-slug'];
	$page_title = $atts['page-title'];
	$post_type = $atts['post-type'];
	$filters = $atts['filters'];
	// $remove_filter = $atts['remove-filter'];
	$per_page = $atts['per-page'];
	$addedtags = empty($page_slug) ? [] : [$page_slug];
	$prev_added_tags = [];
	$open_page = 1;


	$terms = get_terms(array(
		'hide_empty' => false,
	) );




	$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$url_components = parse_url( $url, $component = -1 );
	parse_str($url_components['query'], $params);


	foreach( $params as $key => $param ) {
		if( $key === 'pg')  {
			$open_page = +$param;
			continue;
		};
		array_push($addedtags, $param);
		array_push($prev_added_tags, $param);
	}
	
	$posts = get_posts(array(
		'posts_per_page'    => -1,
		'post_type'         => $post_type,
	));
	
	$page_posts = [];


	
	foreach( $posts as $post ) {
		$taxonomies=get_taxonomies();
		$tags = wp_get_post_terms($post->ID, $taxonomies, array( 'fields' => 'slugs' ));

		$has_tags = count(array_intersect($addedtags, $tags)) == count($addedtags);
		
		if($has_tags) {
			array_push( $page_posts, $post);
		}
	} 



	$html = '';
	
	
	$html .='<div class="posts_sorting">';
	$html .='<p>Gauti rezultatai:(<span class="posts-qty">'.count($page_posts).'</span>)</p>';
	$html .='<button class="open-filter wp-block-button__link show-modal" data-filters="'.$filters.'">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
			<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
		</svg>
		Filtruoti
	</button>';
	$html .='</div>';
	
	$html .='<div class="ctx-wrappers">';

	if( $page_posts ) :

		$active_filter = count($prev_added_tags) ? '' : 'hidden';

		$html .='<ul class="active-tags '.$active_filter.'"  data-post-type="'.$post_type.'" data-page="'.$page_slug.'" data-title="'.$page_title.'" >';
		foreach( $params as $key => $param ): 

			if( $key === 'pg') continue;

			$obj = '';

			foreach($terms as $term) {
				if($term->slug === $param) {
					$obj = $term;
				}
			}

			$html .='<li>'.$obj->name.' <span class="remove-tag" data-name="'.$obj->name.'" data-parent="'.$key.'" data-slug="'.$obj->slug.'">X</span></li>';

		endforeach; 

		
		$html .='<li class="remove-all">Išvalyti filtrus</li></ul>';

		$html .='<div class="ctx-posts">';

			$i = 0;
			$par = empty($url_components['query']) ? '' : '?'.$url_components['query'];
			foreach( $page_posts as $post ): 


				$html .='<article class="post_layout_band">';
					$html .='<div class="post_featured with_thumb hover_none">';
						$html .='<a href="'.get_permalink($post->ID).$par.' " rel="bookmark">'.get_the_post_thumbnail( $post->ID, 'full').'</a>';
					$html .='</div>';

					$html .='<div class="post_content_wrap">';
						$html .='<div class="post_header entry-header">';
						$html .='<div class="post_meta"><span class="post_meta_item post_categories cat_sep">'.$page_title.'</a></span></div>';
						$html .='<h3 class="post_title entry-title"><a href="'.get_permalink($post->ID).$par.'" rel="bookmark">'.$post->post_title.'</a></h3>';
						$html .='</div>';

						$html .='<div class="post_content entry-content"><div class="post_content_inner">'.mb_strimwidth(get_field("trumpas_eksperto_aprasymas", $post->ID), 0, 270, '...').'</div>';
						
						if($post_type !== 'ekspertai') {
							$html .='<div class="post_meta post_meta_other">';
							$html .='<span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">'.do_shortcode("[wp_ulike_counter id=".$post->ID."]").'</span></span>';
							$html .='<a href="'.get_permalink($post->ID).'#comments" class="post_meta_item post_meta_comments icon-comment-light inited">';
							$html .='<span class="post_meta_number">'.get_comments_number($post->ID).'</span>';
							$html .='<span class="post_meta_label">Comments</span>';
							$html .='</a>';
							$html .='</div>';
						}

					$html .='</div>';
				$html .='</article>';

				if ( ++$i === $open_page * $per_page ) {
					break;
				}
			endforeach; 
		$html .='</div>';
	endif;


	$toggle_class = count($page_posts) > $open_page * $per_page ? '' : 'hidden';

	$html .='<div class="button-wrapper '.$toggle_class.'" ><button class="load-more wp-block-button__link" data-per-page="'.$per_page.'">Gauti daugiau rezultatu</button></div>';
	
	$html .='</div>';

	return $html;
	

}
add_shortcode('filter', 'wpb_demo_shortcode');



function ekspert_info() {


	$str = trim(get_field( "eksperto_id", get_the_ID()));
	$str_arr = preg_split ("/\,/", $str);

	$content .= '<div class="expert-wrapper">';

	foreach ($str_arr as $id) {
	$content .= '<div>';
	$content .= '<a class="sidebar-img" href="'.get_permalink($id).'">';
	$content .= get_the_post_thumbnail( $id, 'medium' ); 
	$content .= '</a>';
	$content .= '<ul >';
	$content .= '<li><strong>Vardas:</strong> '.get_field( "vardas", $id).'</li>';
	$content .= '<li><strong>Pavardė:</strong> '.get_field( "pavarde", $id).'</li>';
	$content .= '<li><strong>Kompanija:</strong> '.get_field( "kompanija", $id).'</li>';
	$content .= '</ul>';
	// $content .= '<h5>Aprašymas</h5>';
	// $content .= '<p>'.mb_strimwidth(get_field("eksperto_aprasymas",  $id), 0, 120, '...').'</p>';
	$content .= '<a href="'.get_permalink($id).'" class="sc_button sc_button_default sc_button_size_small"><span class="sc_button_text"><span class="sc_button_title">Apie ekspertą</span></span></a>';
	$content .= '</div>';
	}
	$content .= '</div>';

	return $content;
}

add_shortcode('ekspert', 'ekspert_info');



function auto_insert_after_post($content){
	$html = '';
	$addtional = '';
	$per_page = 9;

	add_action('action_after_comments','anesta_action_after');



	if (get_post_type() !== 'post' && get_post_type() !== 'page' ) {

		$this_post = get_post();

		$posts = get_posts(array(
			'posts_per_page'    => -1,
			'post_type'         => 'patarimai',
			'order_by' => 'date',
            'post_status' => 'publish',

		));
		
		$page_posts = [];


		foreach( $posts as $post ) {
			$str = get_field( "eksperto_id", $post->ID);
			$str_arr = preg_split ("/\,/", $str);  
	
			if(in_array($this_post->ID, $str_arr)) {
				array_push( $page_posts, $post);
			}
		} 


	
	
		if(count($page_posts)) {
		
			$html = '<h3>Visi patarimai ('.count($page_posts).')</h3>';
			$html .='<div class="ctx-wrappers">';
		
			if( $page_posts ) :
		
				$html .='<div class="ctx-posts">';
		
					$i = 0;
					foreach( array_slice($page_posts, 0, 9) as $post ): 
		
						$html .='<article class="post_layout_band">';
							$html .='<div class="post_featured with_thumb hover_none">';
								$html .='<a href="'.get_permalink($post->ID).'" rel="bookmark">'.get_the_post_thumbnail( $post->ID, 'medium').'</a>';
							$html .='</div>';
		
		
							$html .='<div class="post_content_wrap">';
								$html .='<div class="post_header entry-header">';
								$html .='<h3 class="post_title entry-title"><a href="'.get_permalink($post->ID).'" rel="bookmark">'.$post->post_title.'</a></h3>';
								$html .='</div>';
		
								$html .='<div class="post_content entry-content"><div class="post_content_inner">'.mb_strimwidth(get_field("aprasymas", $post->ID), 0, 180, '...').'</div>';
								$html .='<div class="post_meta post_meta_other">';
								$html .='<span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">'.do_shortcode("[wp_ulike_counter id=".$post->ID."]").'</span></span>';
								$html .='<a href="'.get_permalink($post->ID).'#comments" class="post_meta_item post_meta_comments icon-comment-light inited">';
								$html .='<span class="post_meta_number">'.get_comments_number($post->ID).'</span>';
								$html .='<span class="post_meta_label">Comments</span>';
								$html .='</a>';
								$html .='</div>';
							$html .='</div>';
						$html .='</article>';
					endforeach; 
				$html .='</div>';
			endif;
		
		
			$toggle_class = count($page_posts) >= $per_page ? '' : 'hidden';
			$html .='<div class="button-wrapper '.$toggle_class.'" ><button class="get-more wp-block-button__link" data-post-type="patarimai" data-id="'.$this_post->ID.'" data-per-page="'.$per_page.'">Gauti daugiau rezultatų</button></div>';
			$html .='</div>';

		}

	}

	if (get_post_type() === 'patarimai' ) {

		$content .= '<div class="posts_header">';
		$content .= '<div class="post_content_title sc_layouts_title_title">';
		$content .= '<h1 class="sc_layouts_title_caption">'.get_the_title().'</h1>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<iframe width="560" height="315" src="'.get_field( "video", get_the_ID()).'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
		$content .= '<p class="post-dec">'.get_field( "aprasymas", get_the_ID()).'</p>';
		$content .= do_shortcode("[wp_ulike]");

		

	}


	if (get_post_type() === 'ekspertai' ) {
		$content .= '<div class="posts_header">';
		$content .= '<div class="post_content_title sc_layouts_title_title">';
		$content .= '<h1 class="sc_layouts_title_caption">'.get_the_title().'</h1>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="ekspert-details">';
		$content .= '<figure class="wp-block-image size-full">';
		$content .= get_the_post_thumbnail( get_the_ID(), 'medium' ); 
		$content .= '</figure>';
		$content .= '<ul class="pesonal-info">';

		$content .= '<li><strong>Vardas:</strong> '.get_field( "vardas", get_the_ID()).'</li>';
		$content .= '<li><strong>Pavardė:</strong> '.get_field( "pavarde", get_the_ID()).'</li>';
		$content .= '<li><strong>Kompanija:</strong> '.get_field( "kompanija", get_the_ID()).'</li>';
		$content .= '<li><strong>Telefonas:</strong> <a href="tel:'.get_field( "telefonas", get_the_ID()).'">'.get_field( "telefonas", get_the_ID()).'</a></li>';
		$content .= '<li><strong>El. paštas:</strong> <a href="mailto:'.get_field( "el_pastas", get_the_ID()).'">'.get_field( "el_pastas", get_the_ID()).'</a></li>';
		$content .= '<li><strong>Tinklapis:</strong> <a href="'.get_field( "tinklapis", get_the_ID()).'">'.get_field( "tinklapis", get_the_ID()).'</a></li>';
		$content .= '<li><strong>Adresas:</strong> '.get_field( "adresas", get_the_ID()).'</li>';
		$content .= '<li>';
			$content .= '<div class="social-links">';
			if(get_field( "facebook", get_the_ID())) {
				$content .= '<a href="'.get_field( "facebook", get_the_ID()).'"><img src="'.plugin_dir_url( __FILE__ ).'img/facebook.png'.'" alt="Facebook" /></a>';
			}
			if(get_field( "instagram", get_the_ID())) {
				$content .= '<a href="'.get_field( "instagram", get_the_ID()).'"><img src="'.plugin_dir_url( __FILE__ ).'img/instagram.png'.'" alt="Instagram" /></a>';
			}
			if(get_field( "linkedin", get_the_ID())) {
				$content .= '<a href="'.get_field( "linkedin", get_the_ID()).'"><img src="'.plugin_dir_url( __FILE__ ).'img/linkedin.png'.'" alt="Linkedin" /></a>';
			}
			if(get_field( "tiktok", get_the_ID())) {
				$content .= '<a href="'.get_field( "tiktok", get_the_ID()).'"><img src="'.plugin_dir_url( __FILE__ ).'img/tik-tok.png'.'" alt="tik-tok" /></a>';
			}
			$content .= '</div>';
		$content .= '</li>';
		$content .= '</ul>';
		$content .= '</div>';


		$content .= '<div class="post_tags_single">';
		$content .= '<span class="post_meta_label">Tags:</span>';

		foreach( get_post_taxonomies(get_the_ID()) as $tax ) {
			$terms = wp_get_post_terms(get_the_ID(),  $tax, array( 'fields' => 'names' ));

			foreach( $terms as $term ) {
				$content .= '<a href="#" rel="tag">'.$term.'</a>';
			}
		}
		$content .= '</div>';


		$content .= '<h3>Aprašymas</h3>';
		$content .= get_field( "ilgas_eksperto_aprasymas", get_the_ID());
	}


	return $content.$html;
	// return $content.do_shortcode("[wp_ulike]").$html;
}

add_filter( 'the_content', 'auto_insert_after_post' );



function boilerplate_load_assets() {
	wp_enqueue_script('ourmainjs',	plugin_dir_url( __FILE__ ).'js/index.js', array('wp-element'), '1.5', true);
	wp_enqueue_style('ourmaincss',  plugin_dir_url( __FILE__ ).'css/plugin.css', array(), '1.5', 'all');
	wp_enqueue_style('ourmaincsss', '/wp-content/plugins/trx_addons/components/api/woocommerce/woocommerce.css', array(), '1.5', 'all');

}
add_action('wp_enqueue_scripts', 'boilerplate_load_assets', 99999);


function get_sorted_posts($request) {
	$body = json_decode($request->get_body());
    $categories = get_object_taxonomies($body->postType, 'objects');
	$taxonomies=get_taxonomies();
	$per_page = $body->perPage;
	
	
	// Get filter tags from front-end
	$filter_tags = [];


	if(count($body->tags) > 0) {
		foreach($body->tags as $tag) {
			array_push($filter_tags, $tag->slug);
		}
	}


	//  1. Get all term by category
	$all_terms = [];
	foreach( $categories as $category  ) {
		$terms = get_terms([
			'taxonomy' => $category->name,
			'hide_empty' => false,
		]);
		foreach( $terms as $term ) {
			$term->count = 0;
			array_push($all_terms, $term);
		}
	}


	// Get post with tags
	$updated_post = [];
    $all_posts = get_posts(array('post_type' => $body->postType, 'posts_per_page' => -1));

	$gdd = [];


    foreach( $all_posts as $post ) {
		$post_tags = wp_get_post_terms($post->ID, $taxonomies, array( 'fields' => 'slugs' ));
		$post_has_tags = count(array_intersect($filter_tags, $post_tags)) == count($filter_tags);

		if($post_has_tags) {

			foreach($post_tags as $tag) {

				if(array_key_exists($tag, $gdd)) {
					$gdd[$tag]['has_posts'] += 1;
				} else {
					$gdd[$tag] = ['has_posts' => 1 ];
				}
			}

			if(!in_array($post->ID, $updated_post)) {
				$item = array (
					"id"=> $post-> ID, 
					"title"=> $post-> post_title, 
					"url"=>  get_permalink($post->ID), 
					"img"=> get_the_post_thumbnail($post->ID, 'full'), 
					"shortContent" => mb_strimwidth(get_field("trumpas_eksperto_aprasymas", $post->ID), 0, 270, '...'),
					"comments" => get_comments_number($post->ID),
					"likes" => do_shortcode("[wp_ulike_counter id=".$post->ID."]"),
				);
				array_push($updated_post, $item);
			};
		};
    };

	
    $arr = array();

	foreach( $categories as $category  ) {


		$terms = get_terms([
			'taxonomy' => $category->name,
			'hide_empty' => false,
			'parent' => 0,
		]);


		$grand_parent = [
			'parent-slug' => $category->name, 
			'parent-name'=> $category->labels->singular_name, 
			'childrens' => [],
		];


		foreach( $terms as $term  ) {

			$termsa = get_terms([
				'taxonomy' => $category->name,
				'hide_empty' => false,
				'parent' => $term->term_id,
			]);
	
			
			if(  count($termsa) ) {
				$childrens = [];
	
				foreach($termsa as $child) {

					$get_qty = $gdd[$child->slug]['has_posts'] > 0 ? $gdd[$child->slug]['has_posts'] : 0;

					array_push($childrens, [ 'ID'=> $child->term_id, 
					'name' => $child->name, 
					'slug' => $child->slug, 
					'has_posts' => $get_qty, 
					'parent' => $term->name, '']);
				}
	
				array_push($grand_parent['childrens'],[ 
						'ID'=> $term->term_id, 
						'name' => $term->name, 
						'slug' => $term->slug, 
						'has_posts' => 0, 
						'parent' => $category->name, 
						'childrens' => $childrens
						]);
			} else {

				$get_qty = $gdd[$term->slug]['has_posts'] > 0 ? $gdd[$term->slug]['has_posts'] : 0;

				array_push($grand_parent['childrens'], [ 
					'ID'=> $term->term_id, 
					'name' => $term->name, 
					'slug' => $term->slug, 
					'has_posts' => $get_qty, 
					'parent' => $category->name, 
					'childrens' => []
					]);
			}
	
		}

		array_push($arr, $grand_parent);
	}

	if (empty($arr)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response([ "filters"=> $arr,  "posts" => array_slice($updated_post, $body->startFrom, $body->perPage), 'allPosts' => count($updated_post) ]);
	$response->set_status(200);
	return $response;
};

function get_more_posts ($request) {

	$body = json_decode($request->get_body());
	$taxonomies=get_taxonomies();


	// Get filter tags from front-end
	$filter_tags = [];
	foreach($body->tags as $tag) {
		array_push($filter_tags, $tag->slug);
	}

	$updated_post = [];
	$all_posts = get_posts(array('post_type' => $body->postType, 'posts_per_page' => -1));

	foreach( $all_posts as $post ) {
		$post_tags = wp_get_post_terms($post->ID, $taxonomies, array( 'fields' => 'slugs' ));
		$post_has_tags = count(array_intersect($filter_tags, $post_tags)) == count($filter_tags);

		if($post_has_tags) {
			if(!in_array($post->ID, $updated_post)) {
				$item = array (
					"id"=> $post-> ID, 
					"title"=> $post-> post_title, 
					"url"=>  get_permalink($post->ID), 
					"img"=> get_the_post_thumbnail($post->ID, 'full'), 
					"shortContent" => mb_strimwidth(get_field("trumpas_eksperto_aprasymas", $post->ID), 0, 270, '...'),
					"comments" => get_comments_number($post->ID),
					"likes" => do_shortcode("[wp_ulike_counter id=".$post->ID."]"),
				);
				array_push($updated_post, $item);
			};
		};
    };

	if (empty($updated_post)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response(['posts' => array_slice($updated_post, $body->startFrom, $body->perPage), 'allPosts' => count($updated_post)]);
	$response->set_status(200);
	return $response;
}


function get_more_user_posts ($request) {

	$body = json_decode($request->get_body());

	$posts = get_posts(array(
		'posts_per_page'    => -1,
		'post_type'         => $body->postType,
		'orderby'			=> 'date',
		'post_status' => 'publish',
	));

	$updated_post = [];

	
	
	foreach( $posts as $post ) {
		$value = +get_field( "eksperto_id", $post->ID);
		if($value === $body->postId) {
			if(!in_array($post->ID, $updated_post)) {
				$item = array (
					"id"=> $post-> ID, 
					"title"=> $post-> post_title, 
					"url"=>  get_permalink($post->ID), 
					"img"=> get_the_post_thumbnail($post->ID, 'full'), 
					"shortContent" => mb_strimwidth(get_field("trumpas_eksperto_aprasymas", $post->ID), 0, 270, '...'),
					"comments" => get_comments_number($post->ID),
					"likes" => do_shortcode("[wp_ulike_counter id=".$post->ID."]"),
				);
				array_push($updated_post, $item);
			};
		};
    };

	if (empty($updated_post)) {
		return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
	}
	
	$response = new WP_REST_Response(['posts' => array_slice($updated_post, $body->startFrom, $body->perPage), 'allPosts' => count($updated_post)]);
	$response->set_status(200);
	return $response;
}


add_action('rest_api_init', function () {
  register_rest_route( 'posts/v1', 'sortBy/(?P<filter>[a-zA-Z0-9-]+)',array(
                'methods'  => 'POST',
                'callback' => 'get_sorted_posts'
    ));

	register_rest_route( 'posts/v1', 'getMore/(?P<posts>[a-zA-Z0-9-]+)',array(
		'methods'  => 'POST',
		'callback' => 'get_more_posts'
	));	

	register_rest_route( 'posts/v1', 'getMoreUserPosts/(?P<posts>[a-zA-Z0-9-]+)',array(
		'methods'  => 'POST',
		'callback' => 'get_more_user_posts'
	));
	register_rest_route( 'category/products/v1', '(?P<category>[a-zA-Z0-9-]+)',array(
		'methods'  => 'GET',
		'callback' => 'get_category_products'
	));
	register_rest_route( 'product/filter/v1', '(?P<filter>[a-zA-Z0-9-]+)',array(
		'methods'  => 'POST',
		'callback' => 'filtered_products'
	));
});




