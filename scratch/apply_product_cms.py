with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/inc/product-cms.php', 'r', encoding='utf-8') as f:
    content = f.read()

target = """function theme_vive_render_product_meta_box( $post ) {
\twp_nonce_field( 'theme_vive_save_product_details', 'theme_vive_product_details_nonce' );
\t$detail_path = (string) get_post_meta( $post->ID, '_theme_vive_detail_path', true );
\t$compatibility = (string) get_post_meta( $post->ID, '_theme_vive_compatibility', true );
\t$highlights = get_post_meta( $post->ID, '_theme_vive_highlights', true );
\t$specs = get_post_meta( $post->ID, '_theme_vive_specs', true );
\t$highlights_text = is_array( $highlights ) ? implode( "\\n", $highlights ) : '';
\t\t\t$data[ $alias ]['purchase_url'] = home_url( $cfg['buy'] );
\t\t} else {
\t\t\t$data[ $alias ] = array(
\t\t\t\t'id'           => 0,
\t\t\t\t'name'         => $alias,
\t\t\t\t'sku'          => 'VIVE-' . strtoupper( substr( md5( $alias ), 0, 8 ) ),
\t\t\t\t'categories'   => array(),
\t\t\t\t'short'        => '',
\t\t\t\t'description'  => '',
\t\t\t\t'image'        => '',
\t\t\t\t'url'          => home_url( $cfg['buy'] ),
\t\t\t\t'purchase_url' => home_url( $cfg['buy'] ),
\t\t\t\t'has_mirror'   => true,
\t\t\t);
\t\t}
\t}

\treturn $data;
}"""

replacement = """function theme_vive_render_product_meta_box( $post ) {
\twp_nonce_field( 'theme_vive_save_product_details', 'theme_vive_product_details_nonce' );
\t$detail_path = (string) get_post_meta( $post->ID, '_theme_vive_detail_path', true );
\t$compatibility = (string) get_post_meta( $post->ID, '_theme_vive_compatibility', true );
\t$highlights = get_post_meta( $post->ID, '_theme_vive_highlights', true );
\t$specs = get_post_meta( $post->ID, '_theme_vive_specs', true );
\t$highlights_text = is_array( $highlights ) ? implode( "\\n", $highlights ) : '';
\t$spec_lines = array();
\tif ( is_array( $specs ) ) {
\t\tforeach ( $specs as $label => $value ) { $spec_lines[] = $label . ' | ' . $value; }
\t}
\t?>
\t<style>.theme-vive-admin-field{margin:0 0 18px}.theme-vive-admin-field label{display:block;margin-bottom:6px;font-weight:600}.theme-vive-admin-field input,.theme-vive-admin-field textarea{box-sizing:border-box;width:100%}.theme-vive-admin-field .description{margin-top:5px}</style>
\t<div class="theme-vive-admin-field">
\t\t<label for="theme-vive-detail-path">Đường dẫn trang giới thiệu</label>
\t\t<input id="theme-vive-detail-path" name="theme_vive_detail_path" value="<?php echo esc_attr( $detail_path ); ?>" placeholder="/product/vive-xr-elite/overview/">
\t</div>
\t<div class="theme-vive-admin-field">
\t\t<label for="theme-vive-highlights">Điểm nổi bật</label>
\t\t<textarea id="theme-vive-highlights" name="theme_vive_highlights" rows="6"><?php echo esc_textarea( $highlights_text ); ?></textarea>
\t\t<p class="description">Mỗi dòng là một điểm nổi bật.</p>
\t</div>
\t<div class="theme-vive-admin-field">
\t\t<label for="theme-vive-specs">Thông số kỹ thuật</label>
\t\t<textarea id="theme-vive-specs" name="theme_vive_specs" rows="8"><?php echo esc_textarea( implode( "\\n", $spec_lines ) ); ?></textarea>
\t\t<p class="description">Mỗi dòng theo định dạng: Tên thông số | Giá trị.</p>
\t</div>
\t<div class="theme-vive-admin-field">
\t\t<label for="theme-vive-compatibility">Khả năng tương thích</label>
\t\t<textarea id="theme-vive-compatibility" name="theme_vive_compatibility" rows="4"><?php echo esc_textarea( $compatibility ); ?></textarea>
\t</div>
\t<?php
}

function theme_vive_save_product_meta_box( $post_id ) {
\tif ( ! isset( $_POST['theme_vive_product_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['theme_vive_product_details_nonce'] ) ), 'theme_vive_save_product_details' ) ) { return; }
\tif ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
\tif ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

\t$detail_path = sanitize_text_field( wp_unslash( $_POST['theme_vive_detail_path'] ?? '' ) );
\t$compatibility = sanitize_textarea_field( wp_unslash( $_POST['theme_vive_compatibility'] ?? '' ) );
\t$highlight_lines = preg_split( '/\\R/u', sanitize_textarea_field( wp_unslash( $_POST['theme_vive_highlights'] ?? '' ) ) );
\t$highlights = array_values( array_filter( array_map( 'trim', $highlight_lines ), 'strlen' ) );
\t$spec_lines = preg_split( '/\\R/u', sanitize_textarea_field( wp_unslash( $_POST['theme_vive_specs'] ?? '' ) ) );
\t$specs = array();
\tforeach ( $spec_lines as $line ) {
\t\t$parts = array_map( 'trim', explode( '|', $line, 2 ) );
\tif ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) { $specs[ $parts[0] ] = $parts[1]; }
\t}
\tupdate_post_meta( $post_id, '_theme_vive_detail_path', $detail_path );
\tupdate_post_meta( $post_id, '_theme_vive_highlights', $highlights );
\tupdate_post_meta( $post_id, '_theme_vive_specs', $specs );
\tupdate_post_meta( $post_id, '_theme_vive_compatibility', $compatibility );
}
add_action( 'save_post_product', 'theme_vive_save_product_meta_box' );

/** Give every product a stable reference code when the editor leaves SKU blank. */
function theme_vive_ensure_product_sku( $post_id, $post, $update ) {
\tif ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! $post instanceof WP_Post ) { return; }
\tif ( in_array( $post->post_status, array( 'auto-draft', 'trash' ), true ) ) { return; }
\tif ( '' === (string) get_post_meta( $post_id, '_sku', true ) ) {
\t\tupdate_post_meta( $post_id, '_sku', 'VIVE-' . str_pad( (string) $post_id, 6, '0', STR_PAD_LEFT ) );
\t}
}
add_action( 'save_post_product', 'theme_vive_ensure_product_sku', 20, 3 );

function theme_vive_find_product( $slug ) {
\tif ( ! function_exists( 'wc_get_product' ) ) { return null; }
\t$slug = trim( (string) $slug, '/' );
\tif ( ! $slug ) { return null; }

\t// 1. Check by exact meta slug
\t$posts = get_posts( array(
\t\t'post_type'   => 'product',
\t\t'post_status' => 'publish',
\t\t'numberposts' => 1,
\t\t'meta_key'    => THEME_VIVE_PRODUCT_META,
\t\t'meta_value'  => $slug,
\t) );
\tif ( ! empty( $posts ) ) {
\t\treturn wc_get_product( $posts[0]->ID );
\t}

\t// 2. Check by post_name (slug)
\t$posts = get_posts( array(
\t\t'post_type'   => 'product',
\t\t'post_status' => 'publish',
\t\t'numberposts' => 1,
\t\t'name'        => $slug,
\t) );
\tif ( ! empty( $posts ) ) {
\t\treturn wc_get_product( $posts[0]->ID );
\t}

\t// 3. Check by ID if numeric
\tif ( is_numeric( $slug ) ) {
\t\t$p = wc_get_product( (int) $slug );
\t\tif ( $p && 'publish' === $p->get_status() ) {
\t\t\treturn $p;
\t\t}
\t}

\treturn null;
}

function theme_vive_product_template_file() {
\t$file = get_template_directory() . '/shop-us/products/21071655/index.htm';
\tif ( is_readable( $file ) ) { return $file; }
\t$files = glob( get_template_directory() . '/shop-us/products/*/index.htm' );
\treturn ! empty( $files ) ? $files[0] : false;
}

function theme_vive_product_render_html( $html, $product ) {
\tif ( ! $product instanceof WC_Product ) { return $html; }

\t$id = $product->get_id();
\t$name = $product->get_name();
\t$slug = (string) get_post_meta( $id, THEME_VIVE_PRODUCT_META, true ) ?: $product->get_slug();
\t$sku = $product->get_sku() ?: ( 'VIVE-' . str_pad( (string) $id, 6, '0', STR_PAD_LEFT ) );
\t$price = $product->get_price();
\t$price_formatted = ( $price !== '' && is_numeric( $price ) && (float) $price > 0 )
\t\t? number_format( (float) $price, 0, ',', '.' ) . '₫'
\t\t: '';
\t$short_desc = $product->get_short_description() ?: 'N/A';
\t$full_desc = $product->get_description() ?: ( $short_desc !== 'N/A' ? wpautop( $short_desc ) : '<p>N/A</p>' );

\t// Image
\t$image_url = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : ( get_post_meta( $id, '_theme_vive_image', true ) ?: '' );
\tif ( empty( $image_url ) || strpos( $image_url, 'Asset1-cover' ) !== false ) {
\t\t$local_imgs = glob( get_template_directory() . '/assets/media/products/' . $slug . '.*' );
\t\tif ( ! empty( $local_imgs ) ) {
\t\t\t$image_url = get_template_directory_uri() . '/assets/media/products/' . basename( $local_imgs[0] );
\t\t}
\t}
\tif ( empty( $image_url ) ) {
\t\t$image_url = get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg';
\t}
\tif ( $image_url && function_exists( 'theme_vive_asset_url' ) && ! preg_match( '#^https?://#i', $image_url ) ) {
\t\t$image_url = theme_vive_asset_url( $image_url ) ?: $image_url;
\t}

\t// Categories
\t$terms = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'all' ) );
\t$cat_link = home_url( '/shop/collections/' );
\t$cat_name = 'Danh mục sản phẩm';
\tif ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
\t\t$cat_name = $terms[0]->name;
\t\t$cat_link = home_url( '/shop/collections/' . $terms[0]->slug . '/' );
\t}

\t// 1. Replace Title & Meta
\t$doc_title = esc_html( $name ) . ' | VIVE Việt Nam';
\t$html = preg_replace( '#<title\\b[^>]*>.*?</title>#is', '<title>' . $doc_title . '</title>', $html, 1 );
\t$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):title["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_attr( $name ) . '$2', $html );
\t$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):description["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_attr( wp_strip_all_tags( $short_desc ) ) . '$2', $html );
\t$html = preg_replace( '#(<meta\\b[^>]*(?:property|name)=["\\'](?:og|twitter):image(?::secure_url)?["\\'][^>]*content=["\\'])[^"\\']*(["\\'][^>]*>)#i', '$1' . esc_url( $image_url ) . '$2', $html );

\t// 2. Replace Breadcrumb
\t$breadcrumb_html = '<nav aria-label="breadcrumbs" class="breadcrumb" role="navigation">'
\t\t. '<span class="breadcrumb__link"><a href="' . esc_url( home_url( '/' ) ) . '"><span class="text-animation--underline-thin">Trang chủ</span></a></span><span class="breadcrumb__separator">/</span>'
\t\t. '<span class="breadcrumb__link"><a href="' . esc_url( $cat_link ) . '"><span class="text-animation--underline-thin">' . esc_html( $cat_name ) . '</span></a></span><span class="breadcrumb__separator">/</span>'
\t\t. '<span class="breadcrumb__current">' . esc_html( $name ) . '</span></nav>';
\t$html = preg_replace( '#<nav\\b[^>]*\\baria-label=["\\']breadcrumbs["\\'][^>]*>.*?</nav>#is', $breadcrumb_html, $html );

\t// 3. Replace Main Gallery
\t$gallery_replacement = '<div class="product-gallery product-gallery--slider lap--grid--slider-container" style="--border-radius:0px">'
\t\t. '<div class="product-gallery-item element--border-radius" style="padding-top:100%">'
\t\t. '<figure class="lazy-image lazy-image--background lazy-image--fit apply-gallery-animation lazyloaded" style="padding-top:100%;opacity:1!important;visibility:visible!important;">'
\t\t. '<img alt="' . esc_attr( $name ) . '" class="img lazyloaded" src="' . esc_url( $image_url ) . '" style="object-fit:contain;width:100%;height:100%;opacity:1!important;visibility:visible!important;">'
\t\t. '</figure>'
\t\t. '</div></div>';
\t$html = preg_replace(
\t\t'#<div\\b[^>]*class=["\\']product-gallery[\\s\\S]*?</div>\\s*</div>\\s*(?=<div\\b[^>]*class=["\\'][^"\\']*\\bproduct-text\\b)#i',
\t\t$gallery_replacement . '</div>',
\t\t$html,
\t\t1
\t);

\t// 4. Replace Product Title
\t$html = preg_replace(
\t\t'#<h1\\b[^>]*class=["\\'][^"\\']*\\bproduct__title\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</h1>#is',
\t\t'<h1 class="product__title heading-size--page-title">' . esc_html( $name ) . '</h1>'
\t\t. '<div class="vive-product-sku" style="margin: 6px 0 14px; font-size: 14px; color: #6b7280; font-weight: 500;">Mã sản phẩm: <strong style="color:#111827;">' . esc_html( $sku ) . '</strong></div>',
\t\t$html,
\t\t1
\t);

\t// 5. Replace Price & Stock
\t$price_block = '<div class="main-product-price" style="margin: 16px 0 20px;">'
\t\t. ( $price_formatted ? '<div class="product-price" style="font-size: 26px; font-weight: 800; color: #008fb3; margin-bottom: 8px;">' . esc_html( $price_formatted ) . '</div>' : '' )
\t\t. '<div class="vive-in-stock-badge">Còn hàng</div>'
\t\t'</div>';
\t$html = preg_replace(
\t\t'#<div\\b[^>]*class=["\\'][^"\\']*\\bmain-product-price\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</div>\\s*(?=<div\\b[^>]*class=["\\'][^"\\']*\\bproduct-actions\\b|<product-form)#is',
\t\t$price_block,
\t\t$html,
\t\t1
\t);

\t// 6. Replace Description
\t$desc_replacement = '<div class="product__description rte remove-empty-space" style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;">'
\t\t. '<h2 style="font-size:18px;font-weight:700;margin-bottom:12px;color:#111827;">Mô tả sản phẩm</h2>'
\t\t. $full_desc
\t\t. '</div>';
\t$html = preg_replace(
\t\t'#<div\\b[^>]*class=["\\'][^"\\']*\\bproduct__description\\b[^"\\']*["\\'][^>]*>[\\s\\S]*?</div>\\s*</div>\\s*(?=</show-more>|<div\\b|<section\\b)#is',
\t\t$desc_replacement . '</div>',
\t\t$html,
\t\t1
\t);

\treturn $html;
}

function theme_vive_catalogue_products() {
\tif ( ! function_exists( 'wc_get_products' ) ) { return array(); }
\t$products = wc_get_products( array( 'status'=>'publish', 'limit'=>-1, 'orderby'=>array( 'menu_order'=>'ASC', 'date'=>'ASC' ), 'return'=>'objects' ) );
\t$data = array();
\tforeach ( $products as $product ) {
\t\t$slug = (string) get_post_meta( $product->get_id(), THEME_VIVE_PRODUCT_META, true );
\t\tif ( ! $slug ) { $slug = $product->get_slug(); }
\t\tif ( ! $slug ) { $slug = 'product-' . $product->get_id(); }
\t\t$detail_path = (string) get_post_meta( $product->get_id(), '_theme_vive_detail_path', true );
\t\t$image = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : '';
\t\tif ( ! $image ) {
\t\t\t$source = get_post_meta( $product->get_id(), '_theme_vive_image', true );
\t\t\t// Some legacy URL filters can prepend the theme URI to an already-local URL.
\t\t\tif ( preg_match_all( '#https?://#i', (string) $source ) > 1 ) {
\t\t\t\t$source = substr( $source, strripos( $source, 'http' ) );
\t\t\t}
\t\t\t$image = preg_match( '#^https?://#i', (string) $source ) ? $source : ( function_exists( 'theme_vive_asset_url' ) ? theme_vive_asset_url( $source ) : '' );
\t\t}
\t\t$data[ $slug ] = array(
\t\t\t'id'=>$product->get_id(),
\t\t\t'name'=>$product->get_name(),
\t\t\t'sku'=>$product->get_sku() ?: 'VIVE-' . str_pad( (string) $product->get_id(), 6, '0', STR_PAD_LEFT ),
\t\t\t'categories'=>wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields'=>'names' ) ),
\t\t\t'short'=>wp_strip_all_tags( $product->get_short_description() ),
\t\t\t'description'=>wp_strip_all_tags( $product->get_description() ),
\t\t\t'image'=>$image,
\t\t\t'url'=>$detail_path ? home_url( $detail_path ) : home_url( '/shop/products/' . $slug . '/' ),
\t\t\t'purchase_url'=>home_url( '/shop/products/' . $slug . '/' ),
\t\t\t'has_mirror'=>(bool) $detail_path,
\t\t);
\t}

\t// Aliases for showcase landing pages to map to active WooCommerce products & shop URLs
\t$aliases = array(
\t\t'vive-eagle'          => array( 'target' => 'vive-eagle', 'buy' => '/shop/products/vive-eagle/' ),
\t\t'vive-focus-vision'   => array( 'target' => '21071155', 'buy' => '/shop/products/21071155/' ),
\t\t'vive-xr-elite'       => array( 'target' => 'vive-xr-elite-business-edition', 'buy' => '/shop/products/vive-xr-elite-business-edition/' ),
\t\t'vive-pro-2-full-kit' => array( 'target' => '2104005', 'buy' => '/shop/collections/pro-series/' ),
\t\t'vive-pro2-full-kit'  => array( 'target' => '2104005', 'buy' => '/shop/collections/pro-series/' ),
\t\t'vive-pro-2'          => array( 'target' => '2104034-1', 'buy' => '/shop/collections/pro-series/' ),
\t\t'vive-pro2'           => array( 'target' => '2104034-1', 'buy' => '/shop/collections/pro-series/' ),
\t\t'vive-flow'           => array( 'target' => '21071655', 'buy' => '/shop/products/21071655/' ),
\t);
\tforeach ( $aliases as $alias => $cfg ) {
\t\t$target_key = $cfg['target'];
\t\tif ( isset( $data[ $target_key ] ) ) {
\t\t\t$data[ $alias ] = $data[ $target_key ];
\t\t\t$data[ $alias ]['purchase_url'] = home_url( $cfg['buy'] );
\t\t} else {
\t\t\t$data[ $alias ] = array(
\t\t\t\t'id'           => 0,
\t\t\t\t'name'         => $alias,
\t\t\t\t'sku'          => 'VIVE-' . strtoupper( substr( md5( $alias ), 0, 8 ) ),
\t\t\t\t'categories'   => array(),
\t\t\t\t'short'        => '',
\t\t\t\t'description'  => '',
\t\t\t\t'image'        => '',
\t\t\t\t'url'          => home_url( $cfg['buy'] ),
\t\t\t\t'purchase_url' => home_url( $cfg['buy'] ),
\t\t\t\t'has_mirror'   => true,
\t\t\t);
\t\t}
\t}

\treturn $data;
}"""

if target in content:
    new_content = content.replace(target, replacement, 1)
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/inc/product-cms.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("SUCCESS: product-cms.php updated cleanly!")
else:
    print("Target not found exactly in file!")
