with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/inc/product-cms.php', 'r', encoding='utf-8') as f:
    content = f.read()

import re

new_render_func = """function theme_vive_product_render_html( $html, $product ) {
	if ( ! $product instanceof WC_Product ) { return $html; }

	$id = $product->get_id();
	$name = $product->get_name();
	$slug = (string) get_post_meta( $id, THEME_VIVE_PRODUCT_META, true ) ?: $product->get_slug();
	$sku = $product->get_sku() ?: ( 'VIVE-' . str_pad( (string) $id, 6, '0', STR_PAD_LEFT ) );
	$price = $product->get_price();
	$price_formatted = ( $price !== '' && is_numeric( $price ) && (float) $price > 0 )
		? number_format( (float) $price, 0, ',', '.' ) . '₫'
		: '';
	$short_desc = $product->get_short_description() ?: 'N/A';
	$full_desc = $product->get_description() ?: ( $short_desc !== 'N/A' ? wpautop( $short_desc ) : '<p>N/A</p>' );

	// Image
	$image_url = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : ( get_post_meta( $id, '_theme_vive_image', true ) ?: '' );
	if ( empty( $image_url ) || strpos( $image_url, 'Asset1-cover' ) !== false ) {
		$local_imgs = glob( get_template_directory() . '/assets/media/products/' . $slug . '.*' );
		if ( ! empty( $local_imgs ) ) {
			$image_url = get_template_directory_uri() . '/assets/media/products/' . basename( $local_imgs[0] );
		}
	}
	if ( empty( $image_url ) ) {
		$image_url = get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg';
	}
	if ( $image_url && function_exists( 'theme_vive_asset_url' ) && ! preg_match( '#^https?://#i', $image_url ) ) {
		$image_url = theme_vive_asset_url( $image_url ) ?: $image_url;
	}

	// Categories
	$terms = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'all' ) );
	$cat_link = home_url( '/shop/collections/' );
	$cat_name = 'Danh mục sản phẩm';
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$cat_name = $terms[0]->name;
		$cat_link = home_url( '/shop/collections/' . $terms[0]->slug . '/' );
	}

	// 1. Replace Title & Meta
	$doc_title = esc_html( $name ) . ' | VIVE Việt Nam';
	$html = preg_replace( '#<title\\b[^>]*>.*?</title>#is', '<title>' . $doc_title . '</title>', $html, 1 );
	$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):title["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_attr( $name ) . '$2', $html );
	$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):description["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_attr( wp_strip_all_tags( $short_desc ) ) . '$2', $html );
	$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):image(?::secure_url)?["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_url( $image_url ) . '$2', $html );

	// 2. Replace Breadcrumb
	$breadcrumb_html = '<nav aria-label="breadcrumbs" class="breadcrumb" role="navigation">'
		. '<span class="breadcrumb__link"><a href="' . esc_url( home_url( '/' ) ) . '"><span class="text-animation--underline-thin">Trang chủ</span></a></span><span class="breadcrumb__separator">/</span>'
		. '<span class="breadcrumb__link"><a href="' . esc_url( $cat_link ) . '"><span class="text-animation--underline-thin">' . esc_html( $cat_name ) . '</span></a></span><span class="breadcrumb__separator">/</span>'
		. '<span class="breadcrumb__current">' . esc_html( $name ) . '</span></nav>';
	$html = preg_replace( '#<nav\\b[^>]*\\baria-label=["\\']breadcrumbs["\\'][^>]*>.*?</nav>#is', $breadcrumb_html, $html );

	// 3. Replace Main Gallery
	$gallery_replacement = '<div class="product-gallery product-gallery--slider lap--grid--slider-container" style="--border-radius:0px">'
		. '<div class="product-gallery-item element--border-radius" style="padding-top:100%">'
		. '<figure class="lazy-image lazy-image--background lazy-image--fit apply-gallery-animation lazyloaded" style="padding-top:100%;opacity:1!important;visibility:visible!important;">'
		. '<img alt="' . esc_attr( $name ) . '" class="img lazyloaded" src="' . esc_url( $image_url ) . '" style="object-fit:contain;width:100%;height:100%;opacity:1!important;visibility:visible!important;">'
		. '</figure>'
		. '</div></div>';
	$html = preg_replace(
		'#<div\\b[^>]*class=["\\'][^"\\']*\\bproduct-gallery\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</div>\\s*</div>\\s*(?=<div\\b[^>]*class=["\\'][^"\\']*\\bproduct-text\\b)#i',
		$gallery_replacement . '</div>',
		$html,
		1
	);

	// 4. Replace Product Title & SKU
	$html = preg_replace(
		'#<h1\\b[^>]*class=["\\'][^"\\']*\\bproduct__title\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</h1>#is',
		'<h1 class="product__title heading-size--page-title">' . esc_html( $name ) . '</h1>'
		. '<div class="vive-product-sku" style="margin: 6px 0 14px; font-size: 14px; color: #6b7280; font-weight: 500;">Mã sản phẩm: <strong style="color:#111827;">' . esc_html( $sku ) . '</strong></div>',
		$html,
		1
	);

	// 5. Replace Price & Stock
	$price_block = '<div class="main-product-price" style="margin: 16px 0 20px;">'
		. ( $price_formatted ? '<div class="product-price" style="font-size: 26px; font-weight: 800; color: #008fb3; margin-bottom: 8px;">' . esc_html( $price_formatted ) . '</div>' : '' )
		. '<div class="vive-in-stock-badge">Còn hàng</div>'
		. '</div>';
	$html = preg_replace(
		'#<div\\b[^>]*class=["\\'][^"\\']*\\bmain-product-price\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</div>\\s*(?=<div\\b[^>]*class=["\\'][^"\\']*\\bproduct-actions\\b|<product-form)#is',
		$price_block,
		$html,
		1
	);

	// 6. Replace Description and REMOVE the entire <show-more> wrapper and old template texts
	$desc_replacement = '<div class="product__description rte remove-empty-space" style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;">'
		. '<h2 style="font-size:18px;font-weight:700;margin-bottom:12px;color:#111827;">Mô tả sản phẩm</h2>'
		. $full_desc
		. '</div>';

	// Replace the entire <show-more>...</show-more> block
	$html = preg_replace(
		'#<show-more\\b[^>]*>[\\s\\S]*?</show-more>#is',
		$desc_replacement,
		$html,
		1
	);

	// Remove sticky add to cart and old template extra sections
	$html = preg_replace( '#<sticky-add-to-cart\\b[^>]*>[\\s\\S]*?</sticky-add-to-cart>#is', '', $html );
	$html = preg_replace( '#<div\\b[^>]*class=["\\'][^"\\']*\\bshopify-section\\b[^"\\']*["\\'][^>]*\\bid=["\\']shopify-section-template--[0-9]+__product-recommendations["\\'][\\s\\S]*?</product-recommendations>\\s*(?:<style>[\\s\\S]*?</style>)?\\s*</div>#is', '', $html );

	// Hide description toggle script / button for dynamic products
	$html .= '<style>.theme-vive-product-description-toggle,.product-text show-more>.js-show-more,.show-more_toggler{display:none!important}.product-text .theme-vive-product-description-wide.is-collapsed:not(.is-expanded){display:block!important;height:auto!important;max-height:none!important;overflow:visible!important;visibility:visible!important;opacity:1!important}</style>';

	return $html;
}"""

content = re.sub(r'function theme_vive_product_render_html\([\s\S]*?\n\}', new_render_func, content, count=1)

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/inc/product-cms.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated theme_vive_product_render_html successfully!")
