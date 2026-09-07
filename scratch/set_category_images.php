<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$category_to_handle = array(
    'vive-xr-elite-for-business'     => 'vive-xr-elite-business-edition',
    'xr-series'                      => 'xr-elite-with-deluxe-pack-business-edition',
    'vive-ultimate-tracker'          => 'vive-ultimate-tracker-3-1-kit',
    'vive-pro-series-for-business'   => '2104033',
    'pro-series'                     => '2104005',
    'vive-mars-series'               => '21045003',
    'vive-focus-series-for-business' => '21068055',
    'focus-series'                   => '21071155',
    'vive-flow-for-business'         => 'flow-for-business',
    'vive-flow'                      => '21071655',
    'vive-eagle-out-now'             => 'vive-eagle',
    'vive-eagle-1'                   => 'htc-vive-eagle-pantos-clear-lens-charcoal-black',
    'accessories'                    => '21063055',
    'headsets'                       => '21071155',
    'on-sale'                        => 'vive-focus-vision-pc-vr-wired-bundle',
    'battery_included'               => '21045035',
);

$terms = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
) );

echo "Found " . count($terms) . " product categories in database.\n\n";

foreach ( $terms as $term ) {
    $slug = $term->slug;
    $target_handle = $category_to_handle[ $slug ] ?? '';

    // If no direct handle mapping, try finding any product in this category
    $attachment_id = 0;
    if ( $target_handle ) {
        $p_posts = get_posts( array(
            'post_type'   => 'product',
            'post_status' => 'any',
            'numberposts' => 1,
            'meta_key'    => '_theme_vive_product',
            'meta_value'  => $target_handle,
        ) );
        if ( ! empty( $p_posts ) ) {
            $attachment_id = (int) get_post_thumbnail_id( $p_posts[0]->ID );
        }
    }

    if ( ! $attachment_id ) {
        // Fallback: get first product in term
        $p_posts = get_posts( array(
            'post_type'   => 'product',
            'post_status' => 'any',
            'numberposts' => 1,
            'tax_query'   => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ),
            ),
        ) );
        if ( ! empty( $p_posts ) ) {
            $attachment_id = (int) get_post_thumbnail_id( $p_posts[0]->ID );
        }
    }

    if ( $attachment_id ) {
        update_term_meta( $term->term_id, 'thumbnail_id', $attachment_id );
        $img_url = wp_get_attachment_url( $attachment_id );
        echo "✓ Category [ID: {$term->term_id}] {$term->name} ({$term->slug}) -> Set Thumbnail ID: {$attachment_id} ({$img_url})\n";
    } else {
        echo "✗ Category [ID: {$term->term_id}] {$term->name} ({$term->slug}) -> NO THUMBNAIL FOUND!\n";
    }
}

echo "\nCompleted category image assignment!\n";
