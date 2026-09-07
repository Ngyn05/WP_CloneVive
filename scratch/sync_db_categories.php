<?php
/** Sync WooCommerce categories and product assignments from collections_and_products.json */
define( 'WP_USE_THEMES', false );
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! class_exists( 'WooCommerce' ) ) {
	echo "WooCommerce is not active.\n";
	exit( 1 );
}

$json_file = get_template_directory() . '/shop-us/collections_and_products.json';
if ( ! file_exists( $json_file ) ) {
	echo "File not found: $json_file\n";
	exit( 1 );
}

$data = json_decode( file_get_contents( $json_file ), true );
$categories = $data['categories'] ?? array();
$product_to_cats = $data['product_to_categories'] ?? array();

echo "=== 1. SYNCING CATEGORIES (product_cat) ===\n";
$cat_id_map = array();

foreach ( $categories as $slug => $cat_info ) {
	$name = $cat_info['name'];
	$desc = $cat_info['desc'] ?? '';
	$order = $cat_info['order'] ?? 0;

	$term = term_exists( $name, 'product_cat' );
	if ( ! $term ) {
		$term = term_exists( $slug, 'product_cat' );
	}

	if ( $term ) {
		$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		wp_update_term( $term_id, 'product_cat', array(
			'name'        => $name,
			'slug'        => $slug,
			'description' => $desc,
		) );
		echo "Updated category: [ID: $term_id] $name ($slug)\n";
	} else {
		$created = wp_insert_term( $name, 'product_cat', array(
			'slug'        => $slug,
			'description' => $desc,
		) );
		if ( is_wp_error( $created ) ) {
			echo "Error creating category $name: " . $created->get_error_message() . "\n";
			continue;
		}
		$term_id = (int) $created['term_id'];
		echo "Created category: [ID: $term_id] $name ($slug)\n";
	}

	update_term_meta( $term_id, 'order', $order );
	$cat_id_map[ $name ] = $term_id;
	$cat_id_map[ $slug ] = $term_id;
}

echo "\n=== 2. ASSIGNING CATEGORIES TO PRODUCTS ===\n";
$updated_count = 0;

foreach ( $product_to_cats as $handle => $cat_names ) {
	$posts = get_posts( array(
		'post_type'   => 'product',
		'post_status' => 'any',
		'numberposts' => 1,
		'meta_key'    => '_theme_vive_product',
		'meta_value'  => $handle,
	) );

	if ( ! $posts ) {
		$posts = get_posts( array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'name'        => $handle,
			'numberposts' => 1,
		) );
	}

	if ( ! $posts ) {
		echo "Product not found in DB: $handle\n";
		continue;
	}

	$product_id = $posts[0]->ID;
	$term_ids = array();

	foreach ( $cat_names as $cat_name ) {
		if ( isset( $cat_id_map[ $cat_name ] ) ) {
			$term_ids[] = (int) $cat_id_map[ $cat_name ];
		} else {
			$t = term_exists( $cat_name, 'product_cat' );
			if ( $t ) {
				$term_ids[] = (int) ( is_array( $t ) ? $t['term_id'] : $t );
			}
		}
	}

	$term_ids = array_unique( $term_ids );
	if ( ! empty( $term_ids ) ) {
		wp_set_object_terms( $product_id, $term_ids, 'product_cat' );
		echo "Assigned [ID: $product_id] $handle => " . implode( ', ', $cat_names ) . "\n";
		$updated_count++;
	}
}

// Clean up old empty placeholder categories if needed
$empty_terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'name'       => array( 'Simple', 'Phụ kiện' ),
) );
foreach ( $empty_terms as $et ) {
	// If it has 0 count or only old imports
	if ( 0 === (int) $et->count ) {
		wp_delete_term( $et->term_id, 'product_cat' );
		echo "Deleted empty temporary category: {$et->name}\n";
	}
}

echo "\nCOMPLETED: Updated $updated_count products with new category structure.\n";
