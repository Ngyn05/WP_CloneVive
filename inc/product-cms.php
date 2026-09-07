<?php
/** WooCommerce-backed catalogue for the mirrored VIVE product listing. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const THEME_VIVE_PRODUCT_META = '_theme_vive_product';

/** Import and synchronize exactly 93 shop products and 15 clean categories into WooCommerce Database. */
function theme_vive_seed_all_shop_products() {
	if ( get_option( 'theme_vive_all_products_seed_v12' ) === 'yes' ) { return; }
	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Simple' ) ) { return; }

	$json_file = get_template_directory() . '/shop-us/products_vi.json';
	if ( ! is_readable( $json_file ) ) {
		$json_file = get_template_directory() . '/shop-us/all_products.json';
	}
	if ( ! is_readable( $json_file ) ) { return; }

	$data = json_decode( file_get_contents( $json_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( empty( $data ) || ! is_array( $data ) ) { return; }

	// Load collection mapping
	$map_file = get_template_directory() . '/shop-us/collections_and_products.json';
	$collections_meta = array();
	$product_to_cats = array();
	if ( is_readable( $map_file ) ) {
		$map_data = json_decode( file_get_contents( $map_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$collections_meta = $map_data['categories'] ?? array();
		$product_to_cats = $map_data['product_to_categories'] ?? array();
	}

	// 1. Create / Update official product categories in Vietnamese
	$cat_id_map = array();
	foreach ( $collections_meta as $slug => $c_info ) {
		$c_name = $c_info['name'] ?? $slug;
		$c_desc = $c_info['desc'] ?? '';
		$c_order = (int) ( $c_info['order'] ?? 0 );

		$term = term_exists( $c_name, 'product_cat' );
		if ( ! $term ) {
			$term = term_exists( $slug, 'product_cat' );
		}

		if ( $term ) {
			$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			wp_update_term( $term_id, 'product_cat', array(
				'name'        => $c_name,
				'slug'        => $slug,
				'description' => $c_desc,
			) );
		} else {
			$created = wp_insert_term( $c_name, 'product_cat', array(
				'slug'        => $slug,
				'description' => $c_desc,
			) );
			if ( ! is_wp_error( $created ) ) {
				$term_id = (int) $created['term_id'];
			} else {
				$term_id = 0;
			}
		}

		if ( ! empty( $term_id ) ) {
			update_term_meta( $term_id, 'order', $c_order );
			$cat_id_map[ $c_name ] = $term_id;
			$cat_id_map[ $slug ] = $term_id;
		}
	}

	// 2. Track valid handles
	$valid_handles = array();

	// Insert or update all 93 shop products with 100% Vietnamese info and VND price
	foreach ( $data as $item ) {
		$handle = sanitize_title( $item['handle'] ?? '' );
		if ( ! $handle ) { continue; }
		$valid_handles[ $handle ] = true;

		$title = ! empty( $item['title_vi'] ) ? $item['title_vi'] : ( $item['title'] ?? $handle );
		$body_html = ! empty( $item['body_html'] ) ? $item['body_html'] : '';
		$short_desc = ! empty( $item['short_desc'] ) ? $item['short_desc'] : wp_trim_words( wp_strip_all_tags( $body_html ), 30 );
		$price_vnd = ! empty( $item['price_vnd'] ) ? (string) $item['price_vnd'] : '';

		$sku = ! empty( $item['sku'] ) ? $item['sku'] : ( 'VIVE-' . strtoupper( substr( md5( $handle ), 0, 8 ) ) );
		$image_url = '';
		if ( ! empty( $item['images'][0]['src'] ) ) {
			$image_url = $item['images'][0]['src'];
		}

		$existing = get_posts( array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => 1,
			'meta_key'    => THEME_VIVE_PRODUCT_META,
			'meta_value'  => $handle,
		) );

		if ( ! $existing ) {
			$existing = get_posts( array(
				'post_type'   => 'product',
				'post_status' => 'any',
				'name'        => $handle,
				'numberposts' => 1,
			) );
		}

		if ( $existing ) {
			$p = wc_get_product( $existing[0]->ID );
			if ( $p ) {
				$p->set_name( $title );
				$p->set_status( 'publish' );
				$p->set_description( $body_html );
				$p->set_short_description( $short_desc );
				if ( $price_vnd !== '' ) {
					$p->set_regular_price( $price_vnd );
					$p->set_price( $price_vnd );
				}
				$p->set_manage_stock( false );
				$p->set_stock_status( 'instock' );
				if ( $sku ) {
					try {
						$p->set_sku( $sku );
					} catch ( Throwable $e ) {
						try {
							$p->set_sku( $sku . '-' . $handle );
						} catch ( Throwable $e2 ) {
							// Ignore duplicate SKU
						}
					}
				}
				$p->save();
				$id = $p->get_id();
			} else {
				$id = $existing[0]->ID;
				wp_update_post( array(
					'ID'           => $id,
					'post_title'   => $title,
					'post_content' => $body_html,
					'post_excerpt' => $short_desc,
					'post_status'  => 'publish',
				) );
				if ( $price_vnd !== '' ) {
					update_post_meta( $id, '_regular_price', $price_vnd );
					update_post_meta( $id, '_price', $price_vnd );
				}
				update_post_meta( $id, '_stock_status', 'instock' );
				update_post_meta( $id, '_manage_stock', 'no' );
			}
		} else {
			$p = new WC_Product_Simple();
			$p->set_name( $title );
			$p->set_slug( $handle );
			$p->set_status( 'publish' );
			$p->set_catalog_visibility( 'visible' );
			$p->set_description( $body_html );
			$p->set_short_description( $short_desc );
			if ( $price_vnd !== '' ) {
				$p->set_regular_price( $price_vnd );
				$p->set_price( $price_vnd );
			}
			$p->set_manage_stock( false );
			$p->set_stock_status( 'instock' );
			if ( $sku ) {
				try {
					$p->set_sku( $sku );
				} catch ( Throwable $e ) {
					try {
						$p->set_sku( $sku . '-' . $handle );
					} catch ( Throwable $e2 ) {
						// Ignore duplicate SKU
					}
				}
			}
			$id = $p->save();
		}

		if ( $id ) {
			update_post_meta( $id, THEME_VIVE_PRODUCT_META, $handle );
			update_post_meta( $id, '_theme_vive_detail_path', '/shop/products/' . $handle . '/' );
			if ( $image_url ) {
				update_post_meta( $id, '_theme_vive_image', $image_url );
			}

			// Assign category terms
			$assigned_cats = $product_to_cats[ $handle ] ?? array();
			if ( empty( $assigned_cats ) ) {
				$assigned_cats = array( 'Phụ kiện VIVE' );
			}

			$term_ids = array();
			foreach ( $assigned_cats as $c_name ) {
				if ( isset( $cat_id_map[ $c_name ] ) ) {
					$term_ids[] = (int) $cat_id_map[ $c_name ];
				} else {
					$t = term_exists( $c_name, 'product_cat' );
					if ( $t ) {
						$term_ids[] = (int) ( is_array( $t ) ? $t['term_id'] : $t );
					}
				}
			}

			$term_ids = array_filter( array_unique( $term_ids ) );
			if ( ! empty( $term_ids ) ) {
				wp_set_object_terms( $id, $term_ids, 'product_cat' );
			}
		}
	}

	// 5. Assign official thumbnail images to each category (for WooCommerce Admin & Archives)
	$category_thumb_handles = array(
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

	foreach ( $category_thumb_handles as $cat_slug => $prod_handle ) {
		$term_id = $cat_id_map[ $cat_slug ] ?? 0;
		if ( ! $term_id ) {
			$term_obj = get_term_by( 'slug', $cat_slug, 'product_cat' );
			if ( $term_obj ) { $term_id = (int) $term_obj->term_id; }
		}
		if ( ! $term_id ) { continue; }

		$p_post = get_posts( array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => 1,
			'meta_key'    => THEME_VIVE_PRODUCT_META,
			'meta_value'  => $prod_handle,
		) );

		if ( ! empty( $p_post ) ) {
			$thumb_id = (int) get_post_thumbnail_id( $p_post[0]->ID );
			if ( $thumb_id ) {
				update_term_meta( $term_id, 'thumbnail_id', $thumb_id );
			}
		}
	}

	update_option( 'theme_vive_all_products_seed_v13', 'yes', false );
}
add_action( 'init', 'theme_vive_seed_all_shop_products', 21 );

/** Import all 93 product images into Media Library and assign as Featured Image (Thumbnail). */
function theme_vive_import_all_product_thumbnails() {
	if ( get_option( 'theme_vive_product_thumbnails_v3' ) === 'yes' ) { return; }

	$products_dir = get_template_directory() . '/assets/media/products';
	if ( ! is_dir( $products_dir ) ) { return; }

	$upload_dir = wp_upload_dir();
	$upload_path = $upload_dir['path'];
	$upload_url = $upload_dir['url'];
	if ( ! is_dir( $upload_path ) ) {
		wp_mkdir_p( $upload_path );
	}

	$all_products = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	) );

	foreach ( $all_products as $post_obj ) {
		$handle = (string) get_post_meta( $post_obj->ID, THEME_VIVE_PRODUCT_META, true );
		if ( ! $handle ) { $handle = $post_obj->post_name; }

		// Find image file on disk
		$matching_files = glob( $products_dir . '/' . $handle . '.*' );
		if ( empty( $matching_files ) ) { continue; }

		$source_file = $matching_files[0];
		if ( ! is_readable( $source_file ) || filesize( $source_file ) < 500 ) { continue; }

		// Check if attachment already exists
		$existing = get_posts( array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'meta_key'    => '_theme_vive_product_thumb_handle',
			'meta_value'  => $handle,
		) );

		if ( ! empty( $existing ) ) {
			$attachment_id = (int) $existing[0]->ID;
		} else {
			$filename = sanitize_file_name( basename( $source_file ) );
			$dest_file = $upload_path . '/' . $filename;
			copy( $source_file, $dest_file );

			$filetype = wp_check_filetype( $dest_file );
			$attachment_id = wp_insert_attachment( array(
				'post_mime_type' => $filetype['type'] ?: 'image/png',
				'post_title'     => get_the_title( $post_obj->ID ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => $upload_url . '/' . $filename,
			), $dest_file, $post_obj->ID );

			if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {
				update_post_meta( $attachment_id, '_theme_vive_product_thumb_handle', $handle );
				update_attached_file( $attachment_id, $dest_file );
			} else {
				$attachment_id = 0;
			}
		}

		if ( $attachment_id ) {
			update_post_meta( $post_obj->ID, '_thumbnail_id', $attachment_id );
			update_post_meta( $post_obj->ID, '_theme_vive_image', wp_get_attachment_url( $attachment_id ) );
		}
	}

	update_option( 'theme_vive_product_thumbnails_v3', 'yes', false );
}
// add_action( 'init', 'theme_vive_import_all_product_thumbnails', 22 );

/** Remove TinyMCE selection bookmarks accidentally persisted in product text. */
function theme_vive_strip_editor_bookmarks( $html ) {
	return preg_replace( '#<span\b[^>]*class=["\'][^"\']*\bmce_SELRES_(?:start|end)\b[^"\']*["\'][^>]*>.*?</span>#is', '', (string) $html );
}

function theme_vive_clean_product_editor_data( $data, $postarr ) {
	if ( 'product' !== ( $data['post_type'] ?? '' ) ) { return $data; }
	$data['post_content'] = theme_vive_strip_editor_bookmarks( $data['post_content'] ?? '' );
	$data['post_excerpt'] = theme_vive_strip_editor_bookmarks( $data['post_excerpt'] ?? '' );
	return $data;
}
add_filter( 'wp_insert_post_data', 'theme_vive_clean_product_editor_data', 20, 2 );

function theme_vive_cleanup_existing_product_bookmarks() {
	if ( '1' === get_option( 'theme_vive_editor_bookmarks_cleaned' ) ) { return; }
	$product_ids = get_posts( array( 'post_type'=>'product', 'post_status'=>'any', 'numberposts'=>-1, 'fields'=>'ids' ) );
	foreach ( $product_ids as $product_id ) {
		$post = get_post( $product_id );
		$content = theme_vive_strip_editor_bookmarks( $post->post_content );
		$excerpt = theme_vive_strip_editor_bookmarks( $post->post_excerpt );
		if ( $content !== $post->post_content || $excerpt !== $post->post_excerpt ) {
			wp_update_post( array( 'ID'=>$product_id, 'post_content'=>$content, 'post_excerpt'=>$excerpt ) );
		}
	}
	update_option( 'theme_vive_editor_bookmarks_cleaned', '1', false );
}
add_action( 'admin_init', 'theme_vive_cleanup_existing_product_bookmarks' );

/** Keep product text visible inside the classic TinyMCE visual editor. */
function theme_vive_product_tinymce_settings( $settings ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) { return $settings; }
	$visible_css = 'html,body#tinymce{background:#fff!important;color:#1d2327!important;}body#tinymce.mce-content-body,body#tinymce.mce-content-body p,body#tinymce.mce-content-body span,body#tinymce.mce-content-body div{color:#1d2327!important;opacity:1!important;visibility:visible!important;}';
	$settings['content_style'] = ( $settings['content_style'] ?? '' ) . $visible_css;
	return $settings;
}
add_filter( 'tiny_mce_before_init', 'theme_vive_product_tinymce_settings' );

function theme_vive_product_editor_admin_css() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) { return; }
	echo '<style>#postdivrich .wp-editor-container,#postexcerpt .wp-editor-container{background:#fff}.mce-edit-area iframe{background:#fff!important}</style>';
}
add_action( 'admin_head-post.php', 'theme_vive_product_editor_admin_css' );
add_action( 'admin_head-post-new.php', 'theme_vive_product_editor_admin_css' );

function theme_vive_repair_product_visual_editor() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) { return; }
	?>
	<script>
	(function(){
		function repairEditor(editor){
			if(!editor||!editor.getBody())return;
			var body=editor.getBody();
			body.style.setProperty('background','#fff','important');
			body.style.setProperty('color','#1d2327','important');
			body.style.setProperty('opacity','1','important');
			body.style.setProperty('visibility','visible','important');
			body.querySelectorAll('.mce_SELRES_start,.mce_SELRES_end').forEach(function(node){node.remove();});
			body.querySelectorAll('*').forEach(function(node){
				node.style.setProperty('color','#1d2327','important');
				node.style.setProperty('opacity','1','important');
				node.style.setProperty('visibility','visible','important');
				if(node.style.fontSize==='0px')node.style.removeProperty('font-size');
				if(node.style.lineHeight==='0px')node.style.removeProperty('line-height');
			});
		}
		function repairAll(){
			if(!window.tinymce)return;
			window.tinymce.editors.forEach(repairEditor);
		}
		if(window.tinymce){window.tinymce.on('AddEditor',function(event){event.editor.on('init SetContent',function(){repairEditor(event.editor);});});}
		document.addEventListener('DOMContentLoaded',function(){setTimeout(repairAll,300);setTimeout(repairAll,1200);});
	})();
	</script>
	<?php
}
add_action( 'admin_footer-post.php', 'theme_vive_repair_product_visual_editor', 99 );
add_action( 'admin_footer-post-new.php', 'theme_vive_repair_product_visual_editor', 99 );

/** Use the reliable source editor for products when TinyMCE renders blank. */
function theme_vive_product_plain_editor( $settings, $editor_id ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type || ! in_array( $editor_id, array( 'content', 'excerpt' ), true ) ) { return $settings; }
	$settings['tinymce'] = false;
	$settings['quicktags'] = true;
	$settings['media_buttons'] = true;
	return $settings;
}
add_filter( 'wp_editor_settings', 'theme_vive_product_plain_editor', 99, 2 );

/** Structured product details collected from the mirrored VIVE product pages. */
function theme_vive_product_extra_data() {
	return array(
		'vive-eagle'=>array(
			'highlights'=>array( 'Trợ lý AI rảnh tay và VIVE AI Notes', 'Dịch hội thoại trực tiếp nhiều ngôn ngữ', 'Camera góc siêu rộng 12 MP', 'Âm thanh open-ear và kết nối Bluetooth', 'Thiết kế nhẹ khoảng 49 g, chống bụi nước IP54' ),
			'specs'=>array( 'Bộ xử lý'=>'Qualcomm Snapdragon AR1', 'Bộ nhớ'=>'4 GB RAM, 32 GB lưu trữ', 'Camera'=>'12 MP góc siêu rộng', 'Âm thanh'=>'Loa open-ear và cụm micro tích hợp', 'Kết nối'=>'Bluetooth, Wi-Fi', 'Trọng lượng'=>'Khoảng 49 g', 'Kháng bụi nước'=>'IP54' ),
			'compatibility'=>'Kết nối với điện thoại thông qua ứng dụng VIVE Connect để quản lý nội dung, trợ lý AI, cuộc gọi và phát nhạc.',
		),
		'vive-focus-vision'=>array(
			'highlights'=>array( 'Độ phân giải tổng hợp 5K', 'Theo dõi mắt tích hợp', 'Camera xuyên thấu màu cho mixed reality', 'Kết nối DisplayPort với PC VR', 'Theo dõi inside-out không cần trạm gốc', 'Pin có thể thay nóng để duy trì phiên sử dụng' ),
			'specs'=>array( 'Màn hình'=>'LCD kép, 2448 × 2448 pixel mỗi mắt', 'Độ phân giải tổng'=>'4896 × 2448 pixel', 'Tần số quét'=>'Lên đến 120 Hz ở chế độ DisplayPort', 'Góc nhìn'=>'Tối đa 120°', 'Bộ xử lý'=>'Qualcomm Snapdragon XR2', 'Bộ nhớ'=>'12 GB RAM, 128 GB lưu trữ; hỗ trợ microSD', 'Theo dõi'=>'Inside-out 6DoF, theo dõi mắt và tay', 'Kết nối PC'=>'DisplayPort và phát trực tuyến không dây' ),
			'compatibility'=>'Hỗ trợ nội dung độc lập, VIVE Business Streaming và PC VR/SteamVR qua DisplayPort hoặc kết nối không dây.',
		),
		'vive-xr-elite'=>array(
			'highlights'=>array( 'Thiết kế XR tất cả trong một có thể chuyển đổi', 'Mixed reality với camera xuyên thấu màu', 'Điều chỉnh độ cận trực tiếp trên từng thấu kính', 'Theo dõi tay và chuyển động 6DoF', 'Có thể dùng độc lập hoặc kết nối PC VR' ),
			'specs'=>array( 'Màn hình'=>'1920 × 1920 pixel mỗi mắt', 'Tần số quét'=>'90 Hz', 'Góc nhìn'=>'Tối đa 110°', 'Bộ xử lý'=>'Qualcomm Snapdragon XR2', 'Bộ nhớ'=>'12 GB RAM, 128 GB lưu trữ', 'Camera'=>'Camera RGB 16 MP cho passthrough', 'Theo dõi'=>'4 camera góc rộng, 6DoF và theo dõi tay', 'Pin'=>'Khoảng 2 giờ; hỗ trợ thay nóng qua nguồn USB-C' ),
			'compatibility'=>'Dùng độc lập với nội dung VIVEPORT hoặc kết nối máy tính bằng VIVE Streaming Cable và Wi-Fi để sử dụng PC VR.',
		),
		'vive-pro-2-full-kit'=>array(
			'highlights'=>array( 'Bộ PC VR hoàn chỉnh gồm kính, tay cầm và trạm gốc', 'Độ phân giải 5K sắc nét', 'Tần số quét lên đến 120 Hz', 'Theo dõi SteamVR chính xác quy mô phòng', 'Tai nghe Hi-Res tích hợp và thiết kế cân bằng' ),
			'specs'=>array( 'Trong hộp'=>'Kính VIVE Pro 2, 2 Base Station 2.0 và 2 Controller 2.0', 'Màn hình'=>'LCD RGB kép, 2448 × 2448 pixel mỗi mắt', 'Độ phân giải tổng'=>'4896 × 2448 pixel', 'Tần số quét'=>'90/120 Hz', 'Góc nhìn'=>'Tối đa 120°', 'Theo dõi'=>'SteamVR Tracking 2.0, hỗ trợ khu vực quy mô phòng', 'Âm thanh'=>'Tai nghe Hi-Res được chứng nhận, hỗ trợ âm thanh không gian 3D', 'Kết nối'=>'DisplayPort 1.2 và USB 3.0' ),
			'compatibility'=>'Dành cho PC VR. Máy tính cần DisplayPort, USB 3.0 và cấu hình đồ họa đáp ứng yêu cầu của VIVE Console/SteamVR.',
		),
		'vive-pro-2'=>array(
			'highlights'=>array( 'Độ phân giải tổng hợp 5K', 'Tần số quét lên đến 120 Hz', 'Góc nhìn rộng 120°', 'Theo dõi SteamVR độ chính xác cao', 'Tai nghe Hi-Res tháo rời tích hợp' ),
			'specs'=>array( 'Màn hình'=>'LCD RGB kép, 2448 × 2448 pixel mỗi mắt', 'Độ phân giải tổng'=>'4896 × 2448 pixel', 'Tần số quét'=>'90/120 Hz', 'Góc nhìn'=>'Tối đa 120°', 'Theo dõi'=>'SteamVR Tracking 1.0 và 2.0', 'Âm thanh'=>'Tai nghe Hi-Res, hỗ trợ âm thanh không gian 3D', 'Điều chỉnh'=>'IPD và khoảng cách mắt có thể điều chỉnh', 'Kết nối'=>'DisplayPort 1.2 và USB 3.0' ),
			'compatibility'=>'Chỉ gồm kính headset; cần máy tính tương thích và hệ thống Base Station/Controller tương thích để sử dụng đầy đủ.',
		),
		'vive-flow'=>array(
			'highlights'=>array( 'Kính VR gập gọn với trọng lượng khoảng 189 g', 'Màn hình độ phân giải 3.2K', 'Điều chỉnh độ cận riêng cho từng mắt', 'Loa stereo và âm thanh không gian tích hợp', 'Theo dõi tay, điều khiển bằng điện thoại hoặc tay cầm' ),
			'specs'=>array( 'Màn hình'=>'1600 × 1600 pixel mỗi mắt (3.2K tổng)', 'Tần số quét'=>'75 Hz', 'Góc nhìn'=>'Tối đa 100°', 'Bộ xử lý'=>'Qualcomm Snapdragon XR1', 'Bộ nhớ'=>'4 GB RAM, 64 GB lưu trữ', 'Trọng lượng'=>'Khoảng 189 g', 'Điều chỉnh thị lực'=>'0 đến -6.0D trên từng mắt', 'Nguồn điện'=>'USB-C; cần nguồn điện ngoài tương thích' ),
			'compatibility'=>'Tương thích với một số điện thoại Android, iPhone và VIVE Flow Controller; khả năng truyền màn hình phụ thuộc thiết bị.',
		),
	);
}

/** Store the enriched details in WooCommerce so administrators can retain them. */
function theme_vive_migrate_product_extra_data() {
	if ( '2' === get_option( 'theme_vive_product_extra_version' ) || ! function_exists( 'wc_get_product' ) ) { return; }
	foreach ( theme_vive_product_extra_data() as $slug=>$details ) {
		$posts = get_posts( array( 'post_type'=>'product', 'post_status'=>'any', 'numberposts'=>1, 'meta_key'=>THEME_VIVE_PRODUCT_META, 'meta_value'=>$slug ) );
		if ( ! $posts ) { continue; }
		update_post_meta( $posts[0]->ID, '_theme_vive_highlights', $details['highlights'] );
		update_post_meta( $posts[0]->ID, '_theme_vive_specs', $details['specs'] );
		update_post_meta( $posts[0]->ID, '_theme_vive_compatibility', $details['compatibility'] );
		wp_update_post( array( 'ID'=>$posts[0]->ID, 'comment_status'=>'open' ) );
	}
	update_option( 'theme_vive_product_extra_version', '2', false );
}
add_action( 'init', 'theme_vive_migrate_product_extra_data', 24 );

/** Expose the VIVE-specific product metadata in the WooCommerce editor. */
function theme_vive_add_product_meta_box() {
	add_meta_box(
		'theme-vive-product-details',
		'Thông tin sản phẩm VIVE',
		'theme_vive_render_product_meta_box',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_product', 'theme_vive_add_product_meta_box' );

function theme_vive_render_product_meta_box( $post ) {
	wp_nonce_field( 'theme_vive_save_product_details', 'theme_vive_product_details_nonce' );
	$detail_path = (string) get_post_meta( $post->ID, '_theme_vive_detail_path', true );
	$compatibility = (string) get_post_meta( $post->ID, '_theme_vive_compatibility', true );
	$highlights = get_post_meta( $post->ID, '_theme_vive_highlights', true );
	$specs = get_post_meta( $post->ID, '_theme_vive_specs', true );
	$highlights_text = is_array( $highlights ) ? implode( "\n", $highlights ) : '';
	$spec_lines = array();
	if ( is_array( $specs ) ) {
		foreach ( $specs as $label => $value ) { $spec_lines[] = $label . ' | ' . $value; }
	}
	?>
	<style>.theme-vive-admin-field{margin:0 0 18px}.theme-vive-admin-field label{display:block;margin-bottom:6px;font-weight:600}.theme-vive-admin-field input,.theme-vive-admin-field textarea{box-sizing:border-box;width:100%}.theme-vive-admin-field .description{margin-top:5px}</style>
	<div class="theme-vive-admin-field">
		<label for="theme-vive-detail-path">Đường dẫn trang giới thiệu</label>
		<input id="theme-vive-detail-path" name="theme_vive_detail_path" value="<?php echo esc_attr( $detail_path ); ?>" placeholder="/product/vive-xr-elite/overview/">
	</div>
	<div class="theme-vive-admin-field">
		<label for="theme-vive-highlights">Điểm nổi bật</label>
		<textarea id="theme-vive-highlights" name="theme_vive_highlights" rows="6"><?php echo esc_textarea( $highlights_text ); ?></textarea>
		<p class="description">Mỗi dòng là một điểm nổi bật.</p>
	</div>
	<div class="theme-vive-admin-field">
		<label for="theme-vive-specs">Thông số kỹ thuật</label>
		<textarea id="theme-vive-specs" name="theme_vive_specs" rows="8"><?php echo esc_textarea( implode( "\n", $spec_lines ) ); ?></textarea>
		<p class="description">Mỗi dòng theo định dạng: Tên thông số | Giá trị.</p>
	</div>
	<div class="theme-vive-admin-field">
		<label for="theme-vive-compatibility">Khả năng tương thích</label>
		<textarea id="theme-vive-compatibility" name="theme_vive_compatibility" rows="4"><?php echo esc_textarea( $compatibility ); ?></textarea>
	</div>
	<?php
}

function theme_vive_save_product_meta_box( $post_id ) {
	if ( ! isset( $_POST['theme_vive_product_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['theme_vive_product_details_nonce'] ) ), 'theme_vive_save_product_details' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$detail_path = sanitize_text_field( wp_unslash( $_POST['theme_vive_detail_path'] ?? '' ) );
	$compatibility = sanitize_textarea_field( wp_unslash( $_POST['theme_vive_compatibility'] ?? '' ) );
	$highlight_lines = preg_split( '/\R/u', sanitize_textarea_field( wp_unslash( $_POST['theme_vive_highlights'] ?? '' ) ) );
	$highlights = array_values( array_filter( array_map( 'trim', $highlight_lines ), 'strlen' ) );
	$spec_lines = preg_split( '/\R/u', sanitize_textarea_field( wp_unslash( $_POST['theme_vive_specs'] ?? '' ) ) );
	$specs = array();
	foreach ( $spec_lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
	if ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) { $specs[ $parts[0] ] = $parts[1]; }
	}
	update_post_meta( $post_id, '_theme_vive_detail_path', $detail_path );
	update_post_meta( $post_id, '_theme_vive_highlights', $highlights );
	update_post_meta( $post_id, '_theme_vive_specs', $specs );
	update_post_meta( $post_id, '_theme_vive_compatibility', $compatibility );
}
add_action( 'save_post_product', 'theme_vive_save_product_meta_box' );

/** Give every product a stable reference code when the editor leaves SKU blank. */
function theme_vive_ensure_product_sku( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! $post instanceof WP_Post ) { return; }
	if ( in_array( $post->post_status, array( 'auto-draft', 'trash' ), true ) ) { return; }
	if ( '' === (string) get_post_meta( $post_id, '_sku', true ) ) {
		update_post_meta( $post_id, '_sku', 'VIVE-' . str_pad( (string) $post_id, 6, '0', STR_PAD_LEFT ) );
	}
}
add_action( 'save_post_product', 'theme_vive_ensure_product_sku', 20, 3 );

function theme_vive_find_product( $slug ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return null; }
	$slug = trim( (string) $slug, '/' );
	if ( ! $slug ) { return null; }

	// 1. Check by exact meta slug
	$posts = get_posts( array(
		'post_type'   => 'product',
		'post_status' => array( 'publish', 'private', 'draft' ),
		'numberposts' => 1,
		'meta_key'    => THEME_VIVE_PRODUCT_META,
		'meta_value'  => $slug,
	) );
	if ( ! empty( $posts ) ) {
		return wc_get_product( $posts[0]->ID );
	}

	// 2. Check by get_page_by_path
	$page = get_page_by_path( $slug, OBJECT, 'product' );
	if ( $page ) {
		return wc_get_product( $page->ID );
	}

	// 3. Check by post_name (slug)
	$posts = get_posts( array(
		'post_type'   => 'product',
		'post_status' => 'any',
		'numberposts' => 1,
		'name'        => $slug,
	) );
	if ( ! empty( $posts ) ) {
		return wc_get_product( $posts[0]->ID );
	}

	// 4. Check directly in wpdb by post_name
	global $wpdb;
	if ( $wpdb ) {
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' LIMIT 1", $slug ) );
		if ( $post_id ) {
			return wc_get_product( (int) $post_id );
		}
	}

	// 5. Check by SKU
	$sku_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $slug ) : 0;
	if ( $sku_id ) {
		return wc_get_product( $sku_id );
	}

	// 6. Check by ID if numeric
	if ( is_numeric( $slug ) ) {
		$p = wc_get_product( (int) $slug );
		if ( $p ) {
			return $p;
		}
	}

	return null;
}

function theme_vive_product_template_file() {
	$file = get_template_directory() . '/shop-us/products/21071655/index.htm';
	if ( is_readable( $file ) ) { return $file; }
	$files = glob( get_template_directory() . '/shop-us/products/*/index.htm' );
	return ! empty( $files ) ? $files[0] : false;
}

function theme_vive_product_render_html( $html, $product ) {
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
	$html = preg_replace( '#<title\b[^>]*>.*?</title>#is', '<title>' . $doc_title . '</title>', $html, 1 );
	$html = preg_replace( '#(<meta\b[^>]*(?:property|name)=["\'](?:og|twitter):title["\'][^>]*content=["\'])[^"\']*(["\'][^>]*>)#i', '$1' . esc_attr( $name ) . '$2', $html );
	$html = preg_replace( '#(<meta\b[^>]*(?:property|name)=["\'](?:og|twitter):description["\'][^>]*content=["\'])[^"\']*(["\'][^>]*>)#i', '$1' . esc_attr( wp_strip_all_tags( $short_desc ) ) . '$2', $html );
	$html = preg_replace( '#(<meta\b[^>]*(?:property|name)=["\'](?:og|twitter):image(?::secure_url)?["\'][^>]*content=["\'])[^"\']*(["\'][^>]*>)#i', '$1' . esc_url( $image_url ) . '$2', $html );

	// 2. Replace Breadcrumb
	$breadcrumb_html = '<nav aria-label="breadcrumbs" class="breadcrumb" role="navigation">'
		. '<span class="breadcrumb__link"><a href="' . esc_url( home_url( '/' ) ) . '"><span class="text-animation--underline-thin">Trang chủ</span></a></span><span class="breadcrumb__separator">/</span>'
		. '<span class="breadcrumb__link"><a href="' . esc_url( $cat_link ) . '"><span class="text-animation--underline-thin">' . esc_html( $cat_name ) . '</span></a></span><span class="breadcrumb__separator">/</span>'
		. '<span class="breadcrumb__current">' . esc_html( $name ) . '</span></nav>';
	$html = preg_replace( '#<nav\b[^>]*\baria-label=["\']breadcrumbs["\'][^>]*>.*?</nav>#is', $breadcrumb_html, $html );

	// 3. Replace Main Gallery
	$gallery_replacement = '<div class="product-gallery product-gallery--slider lap--grid--slider-container" style="--border-radius:0px">'
		. '<div class="product-gallery-item element--border-radius" style="padding-top:100%">'
		. '<figure class="lazy-image lazy-image--background lazy-image--fit apply-gallery-animation lazyloaded" style="padding-top:100%;opacity:1!important;visibility:visible!important;">'
		. '<img alt="' . esc_attr( $name ) . '" class="img lazyloaded" src="' . esc_url( $image_url ) . '" style="object-fit:contain;width:100%;height:100%;opacity:1!important;visibility:visible!important;">'
		. '</figure>'
		. '</div></div>';
	$html = preg_replace(
		'#<div\b[^>]*class=["\']product-gallery[\s\S]*?</div>\s*</div>\s*(?=<div\b[^>]*class=["\'][^"\']*\bproduct-text\b)#i',
		$gallery_replacement . '</div>',
		$html,
		1
	);

	// 4. Replace Product Title
	$html = preg_replace(
		'#<h1\b[^>]*class=["\'][^"\']*\bproduct__title\b[^"\']*["\'][^>]*>[\s\S]*?</h1>#is',
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
		'#<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>\s*(?=<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b|<product-form)#is',
		$price_block,
		$html,
		1
	);

	// 6. Replace Description: Remove entire <show-more> block and replace with clean description
	$desc_replacement = '<div class="product__description rte remove-empty-space" style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;">'
		. '<h2 style="font-size:18px;font-weight:700;margin-bottom:12px;color:#111827;">Mô tả sản phẩm</h2>'
		. $full_desc
		. '</div>';

	// Replace the entire <show-more>...</show-more> container
	$html = preg_replace(
		'#<show-more\b[^>]*>[\s\S]*?</show-more>#is',
		$desc_replacement,
		$html,
		1
	);

	// Remove any extra sidebar product recommendations or sticky cart from the template
	$html = preg_replace( '#<product-recommendations\b[^>]*>[\s\S]*?</product-recommendations>#is', '', $html );
	$html = preg_replace( '#<sticky-add-to-cart\b[^>]*>[\s\S]*?</sticky-add-to-cart>#is', '', $html );
	$html = preg_replace( '#<div\b[^>]*id=["\']shopify-section-template--[0-9]+__product-recommendations["\'][^>]*>[\s\S]*?</div>\s*(?=<div\b[^>]*id=["\']shopify-section-sections|<footer|\z)#is', '', $html );

	// Clean up any remaining show-more buttons or toggles
	$html .= '<style>.theme-vive-product-description-toggle,show-more>.js-show-more,.show-more_toggler{display:none!important}</style>';

	return $html;
}

function theme_vive_catalogue_products() {
	if ( ! function_exists( 'wc_get_products' ) ) { return array(); }
	$products = wc_get_products( array( 'status'=>'publish', 'limit'=>-1, 'orderby'=>array( 'menu_order'=>'ASC', 'date'=>'ASC' ), 'return'=>'objects' ) );
	$data = array();
	foreach ( $products as $product ) {
		$slug = (string) get_post_meta( $product->get_id(), THEME_VIVE_PRODUCT_META, true );
		if ( ! $slug ) { $slug = $product->get_slug(); }
		if ( ! $slug ) { $slug = 'product-' . $product->get_id(); }
		$detail_path = (string) get_post_meta( $product->get_id(), '_theme_vive_detail_path', true );
		$image = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : '';
		if ( ! $image ) {
			$source = get_post_meta( $product->get_id(), '_theme_vive_image', true );
			// Some legacy URL filters can prepend the theme URI to an already-local URL.
			if ( preg_match_all( '#https?://#i', (string) $source ) > 1 ) {
				$source = substr( $source, strripos( $source, 'http' ) );
			}
			$image = preg_match( '#^https?://#i', (string) $source ) ? $source : ( function_exists( 'theme_vive_asset_url' ) ? theme_vive_asset_url( $source ) : '' );
		}
		$data[ $slug ] = array(
			'id'=>$product->get_id(),
			'name'=>$product->get_name(),
			'sku'=>$product->get_sku() ?: 'VIVE-' . str_pad( (string) $product->get_id(), 6, '0', STR_PAD_LEFT ),
			'categories'=>wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields'=>'names' ) ),
			'short'=>wp_strip_all_tags( $product->get_short_description() ),
			'description'=>wp_strip_all_tags( $product->get_description() ),
			'image'=>$image,
			'url'=>$detail_path ? home_url( $detail_path ) : home_url( '/shop/products/' . $slug . '/' ),
			'purchase_url'=>home_url( '/shop/products/' . $slug . '/' ),
			'has_mirror'=>(bool) $detail_path,
		);
	}

	// Aliases for showcase landing pages to map to active WooCommerce products & shop URLs
	$aliases = array(
		'vive-eagle'          => array( 'target' => 'vive-eagle', 'buy' => '/shop/products/vive-eagle/' ),
		'vive-focus-vision'   => array( 'target' => '21071155', 'buy' => '/shop/products/21071155/' ),
		'vive-xr-elite'       => array( 'target' => 'vive-xr-elite-business-edition', 'buy' => '/shop/products/vive-xr-elite-business-edition/' ),
		'vive-pro-2-full-kit' => array( 'target' => '2104005', 'buy' => '/shop/collections/pro-series/' ),
		'vive-pro2-full-kit'  => array( 'target' => '2104005', 'buy' => '/shop/collections/pro-series/' ),
		'vive-pro-2'          => array( 'target' => '2104034-1', 'buy' => '/shop/collections/pro-series/' ),
		'vive-pro2'           => array( 'target' => '2104034-1', 'buy' => '/shop/collections/pro-series/' ),
		'vive-flow'           => array( 'target' => '21071655', 'buy' => '/shop/products/21071655/' ),
	);
	foreach ( $aliases as $alias => $cfg ) {
		$target_key = $cfg['target'];
		if ( isset( $data[ $target_key ] ) ) {
			$data[ $alias ] = $data[ $target_key ];
			$data[ $alias ]['purchase_url'] = home_url( $cfg['buy'] );
		} else {
			$data[ $alias ] = array(
				'id'           => 0,
				'name'         => $alias,
				'sku'          => 'VIVE-' . strtoupper( substr( md5( $alias ), 0, 8 ) ),
				'categories'   => array(),
				'short'        => '',
				'description'  => '',
				'image'        => '',
				'url'          => home_url( $cfg['buy'] ),
				'purchase_url' => home_url( $cfg['buy'] ),
				'has_mirror'   => true,
			);
		}
	}

	return $data;
}

/** Inject current WooCommerce values while preserving the original mirrored layout/filter UI. */
function theme_vive_products_prepare_html( $html, $file ) {
	$normalized = str_replace( '\\', '/', (string) $file );
	$is_listing = (bool) preg_match( '#/(?:pages/us|footer-sites/[^/]+)/product/index\.html?$#i', $normalized ) || (bool) preg_match( '#/product/index\.html?$#i', $normalized );
	$is_collection = (bool) preg_match( '#/(?:shop-us|shop-us\.vive\.com)/collections(?:/[^/]+)?/index\.html?$#i', $normalized );
	$detail_slug = '';
	$is_shop_product = false;

	if ( preg_match( '#/(?:shop-us|shop-us\.vive\.com)/products/(?<slug>[^/]+)/index\.html?$#i', $normalized, $shop_match ) ) {
		$detail_slug = strtolower( $shop_match['slug'] );
		$is_shop_product = true;
	} elseif ( preg_match( '#/(?:pages/us|footer-sites/[^/]+)/product/(?<folder>[^/]+)(?:/[^/]+)*/index\.html?$#i', $normalized, $detail_match ) ) {
		$detail_slug = strtolower( $detail_match['folder'] );
		$detail_slug = array_key_exists( $detail_slug, array( 'vive-pro2-full-kit'=>true, 'vive-pro2'=>true ) )
			? array( 'vive-pro2-full-kit'=>'vive-pro-2-full-kit', 'vive-pro2'=>'vive-pro-2' )[ $detail_slug ]
			: $detail_slug;
	}

	$catalogue = theme_vive_catalogue_products();

	$in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>';

	// Tự động dịch các chuỗi banner shop tiếng Anh
	$shop_strings = array(
		'Get the right setup — VIVE VR headsets, glasses, and accessories for immersive virtual and mixed reality experiences.' => 'Lựa chọn thiết bị phù hợp — Kính thực tế ảo VIVE VR, kính thông minh AI và phụ kiện chính hãng cho trải nghiệm thực tế ảo &amp; thực tế hỗn hợp đỉnh cao.',
		'Browse VIVE Store' => 'Khám phá VIVE Store',
		'Shop by category for VIVE Eagle AI Glasses, VR headsets, Full body trackers, and more.' => 'Mua sắm theo danh mục: Kính AI VIVE Eagle, Kính thực tế ảo VR, Thiết bị theo dõi chuyển động và Phụ kiện chính hãng.',
		'>Categories<' => '>Danh mục sản phẩm<',
		'Browse all products ›' => 'Xem tất cả sản phẩm ›',
		'Browse all business products ›' => 'Xem tất cả sản phẩm Doanh nghiệp ›',
		'>Consumer<' => '>Cá nhân &amp; Giải trí<',
		'>Business<' => '>Doanh nghiệp<',
		'>On Sale<' => '>Ưu đãi nổi bật<',
		'Get our best VR deals!' => 'Khám phá các ưu đãi VR tốt nhất!',
		'>Featured headset<' => '>Kính thực tế ảo nổi bật<',
		'Financing options available' => 'Hỗ trợ trả góp linh hoạt',
		'View all products' => 'Xem tất cả sản phẩm',
		'View in your space' => 'Xem trong không gian 3D AR',
		'View complete product details ›' => 'Xem chi tiết sản phẩm ›',
		'Take PC VR to the next level with the hybrid standalone VIVE Focus Vision.' => 'Nâng tầm trải nghiệm PC VR với kính thực tế ảo độc lập lai ghép VIVE Focus Vision.',
		'Tell me more' => 'Tìm hiểu thêm',
		'Shop our best VR deals!' => 'Khám phá các ưu đãi VR tốt nhất từ VIVE!',
		'HTC Privacy Policy' => 'Chính sách bảo mật HTC',
		'Privacy Policy' => 'Chính sách bảo mật',
		'VIVE Store | United States' => 'VIVE Store Việt Nam',
		'was added to your cart' => 'đã được thêm vào giỏ hàng',
		'View Cart' => 'Xem giỏ hàng',
		'Vive Focus Vision Wired Bundle - VR Headset with DisplayPort PC Streaming Kit' => 'VIVE Focus Vision Wired Bundle - Kính thực tế ảo VR kèm Bộ kết nối PC DisplayPort',
	);
	$html = str_replace( array_keys( $shop_strings ), array_values( $shop_strings ), $html );

	if ( $is_collection ) {
		// Price sorting is intentionally unavailable. Remove the options from
		// the markup and normalize stale/query-driven selections to best-selling.
		$html = preg_replace( '#<option\b[^>]*value=["\']price-(?:ascending|descending)["\'][^>]*>.*?</option>#is', '', $html );
		// Collection CTAs should navigate to the product detail page instead of
		// invoking Shopify's unavailable quick-add/variant picker.
		$html = preg_replace_callback(
			'#<quick-view-product\b[^>]*>(?<content>.*?)</quick-view-product>#is',
			static function ( $match ) {
				$content = preg_replace( '#\sdata-js-product-add-to-cart(?:=["\'][^"\']*["\'])?#i', '', $match['content'] );
				$content = preg_replace( '#\sdata-id=["\'][^"\']*["\']#i', '', $content );
				$content = preg_replace( '#(<span\b[^>]*class=["\'][^"\']*\bbutton__text\b[^"\']*["\'][^>]*>).*?(</span>)#is', '$1Mua ngay$2', $content );
				return '<div class="theme-vive-collection-buy">' . $content . '</div>';
			},
			$html
		);

		// Dynamically query WooCommerce database for products in this collection
		if ( function_exists( 'wc_get_products' ) ) {
			preg_match( '#/(?:shop-us|shop-us\.vive\.com)/collections(?:/(?<col_slug>[^/]+))?/index\.html?$#i', $normalized, $col_match );
			$col_slug = ! empty( $col_match['col_slug'] ) ? strtolower( trim( $col_match['col_slug'] ) ) : 'all';

			$wc_args = array(
				'status'   => 'publish',
				'limit'    => -1,
				'orderby'  => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
				'return'   => 'objects',
			);

			if ( 'all' !== $col_slug && 'index' !== $col_slug && '' !== $col_slug ) {
				$term = get_term_by( 'slug', $col_slug, 'product_cat' );
				if ( ! $term ) {
					$term = get_term_by( 'name', $col_slug, 'product_cat' );
				}
				if ( $term ) {
					$wc_args['category'] = array( $term->slug );
				}
			}

			$wc_products = wc_get_products( $wc_args );
			if ( ! empty( $wc_products ) ) {
				$cards = array();
				$thumb_dir = get_template_directory() . '/assets/media/products/';
				$thumb_uri = get_template_directory_uri() . '/assets/media/products/';

				foreach ( $wc_products as $p ) {
					$id = $p->get_id();
					$name = $p->get_name();
					$p_slug = (string) get_post_meta( $id, THEME_VIVE_PRODUCT_META, true ) ?: $p->get_slug();

					// Ẩn các biến thể SKU con của VIVE Eagle (đã được gom vào bên trong trang VIVE Eagle chính)
					if ( preg_match( '#^(?:htc-)?vive-eagle-(?:pantos|square|glossy)-#i', $p_slug ) ) {
						continue;
					}

					$p_url = home_url( '/shop/products/' . $p_slug . '/' );

					// Resolve product thumbnail image
					$img_url = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'large' ) : ( get_post_meta( $id, '_theme_vive_image', true ) ?: '' );
					if ( empty( $img_url ) || strpos( $img_url, 'Asset1-cover' ) !== false ) {
						$local_imgs = glob( $thumb_dir . $p_slug . '.*' );
						if ( ! empty( $local_imgs ) ) {
							$img_url = $thumb_uri . basename( $local_imgs[0] );
						}
					}
					if ( $img_url && function_exists( 'theme_vive_asset_url' ) && ! preg_match( '#^https?://#i', $img_url ) ) {
						$img_url = theme_vive_asset_url( $img_url ) ?: $img_url;
					}

					$short_desc = $p->get_short_description() ?: wp_trim_words( wp_strip_all_tags( $p->get_description() ), 14 );

					$cards[] = '<div class="product-item" data-js-product-component="" data-js-product-item="" id="product-item-' . esc_attr( $id ) . '">'
						. '<a class="product-item__image" href="' . esc_url( $p_url ) . '" style="padding-top:75.00018750046875%" tabindex="-1">'
						. '<figure class="lazy-image lazy-image--background lazy-image--fit product-item__image-figure lazy-image--zoom-animation product-item__image-no-slider lazyloaded" data-crop="true" data-ratio="1.33333" style="padding-top:75.00018750046875%;opacity:1!important;visibility:visible!important;">'
						. '<img alt="' . esc_attr( $name ) . '" class="img lazyloaded" data-ratio="1.33333" height="389" loading="lazy" src="' . esc_url( $img_url ) . '" style="object-fit:contain;width:100%;height:100%;opacity:1!important;visibility:visible!important;">'
						. '</figure></a>'
						. '<div class="card__text product-item__text gutter-top--regular gutter-bottom--regular spacing--xlarge remove-empty-space text-align--left">'
						. '<div class="product-item__price text-size--small equalize-white-space"><div class="remove-line-height-space" data-js-product-item-price=""><div class="product-price"><span class="vive-in-stock-badge">Còn hàng</span></div></div></div>'
						. '<a class="product-item__title" href="' . esc_url( $p_url ) . '" title="' . esc_attr( $name ) . '">'
						. '<div class="remove-line-height-space--small"><span class="text-animation--underline-thin text-size--large text-weight--bold">' . esc_html( $name ) . '</span></div>'
						. '</a>'
						. '<div class="product-item__excerpt text-size--small"><div class="product-item__excerpt-text">' . esc_html( $short_desc ) . '</div></div>'
						. '<div class="theme-vive-collection-buy quick-add-to-cart"><a href="' . esc_url( $p_url ) . '" class="button button--solid button--regular button--fullwidth"><span class="button__text">Mua ngay</span></a></div>'
						. '</div></div>';
				}

				if ( ! empty( $cards ) ) {
					$grid_inner = implode( "\n", $cards );
					$html = preg_replace(
						'#<div\b[^>]*\bid=["\']main-collection-product-grid["\'][^>]*>[\s\S]*?</div>\s*(?=<div\b[^>]*data-js-product-item-skeleton|<sidebar-drawer|</main>)#i',
						'<div class="collection grid" id="main-collection-product-grid">' . $grid_inner . '</div>',
						$html,
						1
					);
					$html = preg_replace(
						'#<span\b[^>]*\bid=["\']CollectionProductCount["\'][^>]*>.*?</span>#is',
						'<span class="collection-product-count" id="CollectionProductCount" role="status">Hiển thị ' . count( $cards ) . ' sản phẩm</span>',
						$html,
						1
					);
				}
			}
		}

		$sort_guard = '<script id="theme-vive-collection-sort-guard">(function(){function normalize(){var forced=false,url;try{url=new URL(location.href);forced=/^price-/.test(url.searchParams.get("sort_by")||"");}catch(e){}document.querySelectorAll("select[name=sort_by]").forEach(function(select){if(forced||/^price-/.test(select.value)||!select.querySelector("option[value=\""+select.value+"\"]")){select.value="best-selling";}});if(forced&&url){url.searchParams.delete("sort_by");history.replaceState(null,"",url.pathname+url.search+url.hash);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",normalize);}else{normalize();}new MutationObserver(normalize).observe(document.documentElement,{childList:true,subtree:true});})();</script>';
		$card_guard = '<script id="theme-vive-collection-card-guard">(function(){document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest(".product-item a,.theme-vive-collection-buy a,a.product-item__title,a.product-item__image");if(a&&a.href&&!a.getAttribute("target")){e.stopPropagation();}},true);function enhance(){document.querySelectorAll(".product-item,[data-js-product-item]").forEach(function(card){var title=card.querySelector("a.product-item__title"),image=card.querySelector("a.product-item__image"),buyBtn=card.querySelector(".theme-vive-collection-buy a,.quick-add-to-cart a");if(!title||!title.href)return;if(image&&(!image.href||image.href.indexOf("javascript")!==-1)){image.href=title.href;image.removeAttribute("tabindex");}if(buyBtn&&(!buyBtn.href||buyBtn.href.indexOf("javascript")!==-1)){buyBtn.href=title.href;}if(!card.querySelector(".quick-add-to-cart")&&!card.querySelector(".theme-vive-collection-buy")){var wrap=document.createElement("div");wrap.className="theme-vive-collection-buy quick-add-to-cart";wrap.innerHTML="<a class=\"button button--solid button--regular button--fullwidth\" href=\""+title.href+"\"><span class=\"button__text\">Mua ngay</span></a>";var text=card.querySelector(".product-item__text");if(text)text.appendChild(wrap);}});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",enhance);}else{enhance();}new MutationObserver(enhance).observe(document.documentElement,{childList:true,subtree:true});})();</script>';
		$html = preg_replace( '#</body>#i', $sort_guard . $card_guard . '</body>', $html, 1 );
	}

	// Handle product listing page (/product/) to keep all flagship devices visible with direct shop buy links
	if ( $is_listing ) {
		$listing_map = array(
			'vive-eagle'          => array( 'price' => 'Từ 12.490.000₫', 'detail' => home_url( '/shop/products/vive-eagle/' ), 'buy' => home_url( '/shop/products/vive-eagle/' ), 'has_buy' => true ),
			'vive-focus-vision'   => array( 'price' => '28.990.000₫', 'detail' => home_url( '/product/vive-focus-vision/overview/' ), 'buy' => home_url( '/shop/products/21071155/' ), 'has_buy' => true ),
			'vive-xr-elite'       => array( 'price' => '', 'detail' => home_url( '/product/vive-xr-elite/overview/' ), 'buy' => home_url( '/shop/collections/xr-series/' ), 'has_buy' => true ),
			'vive-pro-2-full-kit' => array( 'price' => '36.990.000₫', 'detail' => home_url( '/product/vive-pro2-full-kit/overview/' ), 'buy' => home_url( '/shop/collections/pro-series/' ), 'has_buy' => true ),
			'vive-pro2-full-kit'  => array( 'price' => '36.990.000₫', 'detail' => home_url( '/product/vive-pro2-full-kit/overview/' ), 'buy' => home_url( '/shop/collections/pro-series/' ), 'has_buy' => true ),
			'vive-pro-2'          => array( 'price' => '21.990.000₫', 'detail' => home_url( '/product/vive-pro2/overview/' ), 'buy' => home_url( '/shop/collections/pro-series/' ), 'has_buy' => true ),
			'vive-pro2'           => array( 'price' => '21.990.000₫', 'detail' => home_url( '/product/vive-pro2/overview/' ), 'buy' => home_url( '/shop/collections/pro-series/' ), 'has_buy' => true ),
			'vive-flow'           => array( 'price' => '<span style="text-decoration:line-through;color:#9ca3af;margin-right:8px;font-weight:400;font-size:14px;">12.490.000₫</span> <span style="font-weight:700;color:#111827;">4.990.000₫</span>', 'detail' => home_url( '/product/vive-flow/overview/' ), 'buy' => home_url( '/shop/products/21071655/' ), 'has_buy' => true ),
		);

		$html = preg_replace_callback(
			'#<div\b[^>]*class=["\'][^"\']*\bitem-block\b[^"\']*["\'][^>]*data-slug=["\'](?P<slug>[^"\']+)["\'][^>]*>.*?</div>#is',
			static function ( $m ) use ( $listing_map ) {
				$slug = strtolower( trim( $m['slug'] ) );
				$info = $listing_map[ $slug ] ?? array(
					'price'   => '',
					'detail'  => home_url( '/shop/products/' . $slug . '/' ),
					'buy'     => home_url( '/shop/products/' . $slug . '/' ),
					'has_buy' => true,
				);
				$out = '<div class="theme-vive-listing-item-actions">';
				if ( ! empty( $info['price'] ) ) {
					$out .= '<div class="theme-vive-listing-price">' . $info['price'] . '</div>';
				}
				if ( ! empty( $info['has_buy'] ) && ! empty( $info['buy'] ) ) {
					$out .= '<a class="theme-vive-buy-button" href="' . esc_url( $info['buy'] ) . '">Mua ngay</a>';
				}
				$out .= '<a class="theme-vive-detail-button" href="' . esc_url( $info['detail'] ) . '">Xem chi tiết ›</a>';
				$out .= '</div>';
				return $out;
			},
			$html
		);
	}

	// 1. Clean out the top price block (main-product-price) cleanly without leaving any orphaned closing tags
	if ( preg_match( '#<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b#i', $html ) ) {
		$html = preg_replace(
			'#<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>.*?class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>\s*</span>\s*</div>#is',
			'<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' . $in_stock_html . '</div></div>',
			$html,
			1
		);
	}

	// If rendering a shop product page, replace the Shopify Add to Cart & Buy with shop buttons with the custom 3-button block
	if ( $is_shop_product && $detail_slug ) {
		$product_info = $catalogue[ $detail_slug ] ?? array( 'id' => 0, 'name' => $detail_slug );
		$zalo_url = 'https://zalo.me/0917834532';
		$quantity_html = '<div class="product-quantity quantity-selector-holder" style="display:flex!important;align-items:center!important;margin:18px 0 20px!important;gap:10px!important;flex-wrap:nowrap!important;">'
			. '<span class="product-quantity__title" style="white-space:nowrap!important;font-weight:700!important;font-size:14px!important;color:#111!important;margin-right:8px!important;flex-shrink:0!important;">Số lượng:</span>'
			. '<button type="button" class="qty-button qty-minus" aria-label="Giảm số lượng">−</button>'
			. '<input type="number" name="quantity" value="1" min="1" max="99" class="qty qty-selector product__quantity" aria-label="Số lượng">'
			. '<button type="button" class="qty-button qty-plus" aria-label="Tăng số lượng">+</button>'
			. '</div>';
		$actions_html = $quantity_html
			. '<div class="vive-woo-product__actions">'
			. '<a class="vive-woo-product__action vive-woo-product__action--zalo" href="' . esc_url( $zalo_url ) . '" target="_blank" rel="noopener noreferrer">TƯ VẤN QUA ZALO</a>'
			. '<a class="vive-woo-product__action vive-woo-product__action--buy" href="#dat-hang" data-theme-vive-buy-slug="' . esc_attr( $detail_slug ) . '" data-theme-vive-buy-mode="popup">MUA NGAY</a>'
			. '</div>'
			. '<form class="vive-woo-product__callback" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" data-vive-callback-form>'
			. '<input type="hidden" name="action" value="theme_vive_submit_callback">'
			. '<input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'theme_vive_submit_callback' ) ) . '">'
			. '<input type="hidden" name="product_id" value="' . esc_attr( $product_info['id'] ?? 0 ) . '">'
			. '<input type="hidden" name="product_slug" value="' . esc_attr( $detail_slug ) . '">'
			. '<strong>Hãy để lại số điện thoại, chúng tôi sẽ gọi tư vấn miễn phí!</strong>'
			. '<div><input name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Nhập số điện thoại..." required><button type="submit">GỬI ĐI</button></div>'
			. '<p class="vive-woo-product__callback-message" aria-live="polite"></p>'
			. '</form>';

		// Cleanly replace product-actions with actions_html
		if ( preg_match( '#<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>[\s\S]*?</product-form>\s*</div>#is', $html ) ) {
			$html = preg_replace(
				'#<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>[\s\S]*?</product-form>\s*</div>#is',
				'<div class="product-actions">' . $actions_html . '</div>',
				$html,
				1
			);
		} elseif ( preg_match( '#<product-form\b[^>]*>.*?</product-form>#is', $html ) ) {
			$html = preg_replace( '#<product-form\b[^>]*>.*?</product-form>#is', $actions_html, $html, 1 );
		} elseif ( preg_match( '#<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>.*?</form>#is', $html ) ) {
			$html = preg_replace( '#<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>.*?</form>#is', $actions_html, $html, 1 );
		}

		// Clean up try-on CTA, try-on modal, and model viewer elements from DOM
		$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\b(?:tryon__cta|tryon__modal|tryon-block)\b[^"\']*["\'][^>]*>[\s\S]*?</div>\s*</div>#is', '', $html );
		$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\btryon__cta\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '', $html );
		$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\btryon__modal\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '', $html );
		$html = preg_replace( '#<script\b[^>]*>\s*\(function\(\)\{\s*\'use strict\';\s*var RELEASE_ON_CLOSE[\s\S]*?</script>#is', '', $html );
		$html = preg_replace_callback( '#<script\b[^>]*>.*?</script>#is', static function ( $m ) {
			return ( false !== strpos( $m[0], '_RestockRocketConfig' ) || false !== strpos( $m[0], 'RestockRocket' ) || false !== strpos( $m[0], 'BndlrScriptAppended' ) ) ? '' : $m[0];
		}, (string) $html );

		// Disable Shopify variant page-redirect behavior so users can switch variants in-place
		$html = preg_replace( '#\bdata-product-url=["\'][^"\']*["\']#i', 'data-product-url=""', $html );

		// Clean up any remaining payment buttons / installment terms / preorder shipping notices
		$html = preg_replace( '#<shopify-payment-terms\b[^>]*>.*?</shopify-payment-terms>#is', '', $html );
		$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bshopify-payment-button\b[^"\']*["\'][^>]*>.*?</div>#is', '', $html );
		$html = preg_replace( '#<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>#is', '', $html );
		$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\b(?:restock-rocket|stoq|preorder-button-description|shipping-info)\b[^"\']*["\'][^>]*>.*?</div>#is', '', $html );

		// Ensure breadcrumb shows full Vietnamese product title and valid link to collections
		$product_full_name = ! empty( $product_info['name'] ) ? $product_info['name'] : ( $catalogue[ $detail_slug ]['name'] ?? $detail_slug );
		$html = preg_replace(
			'#<span\b[^>]*class=["\'][^"\']*\bbreadcrumb__current\b[^"\']*["\'][^>]*>.*?</span>#is',
			'<span class="breadcrumb__current">' . esc_html( $product_full_name ) . '</span>',
			$html
		);
		$html = preg_replace(
			'#href=["\']/(?:collections|collections/all)["\']#i',
			'href="' . esc_url( home_url( '/shop/collections/' ) ) . '"',
			$html
		);

		// Keep the long description in the purchase column, but collapse it so it
		// does not make the product hero excessively tall.
		// Remove Shopify's native toggle; the theme adds its own toggle below.
		$html = preg_replace( '#<span\b[^>]*class=["\'][^"\']*\bjs-show-more\b[^"\']*["\'][^>]*>.*?</span>#is', '', $html );
		$description_layout = '<script id="theme-vive-product-description-layout">(function(){function layout(){var page=document.querySelector("product-page.main-product"),description=page&&page.querySelector(".product-text .product__description");if(!page||!description||description.classList.contains("theme-vive-product-description-wide"))return;description.classList.add("theme-vive-product-description-wide","is-collapsed");var button=document.createElement("button");button.type="button";button.className="theme-vive-product-description-toggle";button.setAttribute("aria-expanded","false");button.textContent="Xem thêm";description.insertAdjacentElement("afterend",button);button.addEventListener("click",function(){var expanded=description.classList.toggle("is-expanded");description.classList.toggle("is-collapsed",!expanded);button.setAttribute("aria-expanded",expanded?"true":"false");button.textContent=expanded?"Thu gọn":"Xem thêm";if(!expanded)description.scrollIntoView({behavior:"smooth",block:"start"});});function visibility(){button.hidden=description.scrollHeight<=description.clientHeight+8;}requestAnimationFrame(visibility);window.addEventListener("load",visibility,{once:true});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",layout);}else{layout();}})();</script>';
		$description_layout = '<style id="theme-vive-single-description-toggle">.product-text show-more>.js-show-more{display:none!important}.product-text show-more,.product-text show-more>.show-more_panel{height:auto!important;max-height:none!important;overflow:visible!important}.product-text .theme-vive-product-description-wide.is-collapsed:not(.is-expanded){display:block!important;height:0!important;max-height:0!important;min-height:0!important;overflow:hidden!important;visibility:hidden!important;opacity:0!important;margin:0!important;padding:0!important;border:0!important}.product-text .theme-vive-product-description-wide.is-expanded{height:auto!important;max-height:none!important;overflow:visible!important;visibility:visible!important;opacity:1!important}.product-text .theme-vive-product-description-toggle,.product-text .theme-vive-product-description-toggle[hidden]{display:block!important}</style>' . $description_layout;
		$html = preg_replace( '#</body>#i', $description_layout . '</body>', $html, 1 );
	}

	// Replace all other price displays across product cards, collections, and sticky bar with "Còn hàng"
	// Collection cards place title/excerpt after the price, while homepage
	// cards place them before it. Keep separate boundaries so neither template
	// loses its text or leaves later cards outside the grid.
	if ( $is_collection ) {
		$html = preg_replace(
			'#<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>\s*<div\b[^>]*data-js-product-item-price[^>]*>\s*<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>.*?</div>\s*</div>\s*</div>#is',
			'<div class="product-item__price">' . $in_stock_html . '</div>',
			$html
		);
	} else {
		$html = preg_replace(
			'#<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>.*?(?=<span\b[^>]*data-js-product-default-price-data)#is',
			'<div class="product-item__price">' . $in_stock_html . '</div></div>',
			$html
		);
	}
	$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>.*?</div>#is', '<div class="sticky-add-to-cart__price">' . $in_stock_html . '</div>', $html );
	$html = preg_replace( '#<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>#is', '<span class="sidebar-widget__title">Tình trạng</span>', $html );
	$html = preg_replace( '#>\s*(?:Đang được bán từ\s*)?\$[0-9]+[0-9\.,]*\s*(?:USD)?\s*<#iu', '>' . $in_stock_html . '<', $html );

	if ( ! $is_listing && '' === $detail_slug ) { return $html; }

	if ( $detail_slug && isset( $catalogue[ $detail_slug ] ) && ! $is_shop_product ) {
		$buy_button = '<a class="theme-vive-detail-buy-button theme-vive-buy-button" href="' . esc_url( $catalogue[ $detail_slug ]['purchase_url'] ) . '">Mua ngay</a>';
		$html = preg_replace( '#(<div\b[^>]*class=["\'][^"\']*\bsub-header-link-list\b[^"\']*["\'][^>]*>)#i', '$1' . $buy_button, $html, 1 );
		$purchase_url = $catalogue[ $detail_slug ]['purchase_url'];
		$html = preg_replace_callback( '#<a\b(?<attrs>[^>]*)>(?<label>.*?)</a>#is', static function ( $match ) use ( $purchase_url ) {
			$label = trim( wp_strip_all_tags( html_entity_decode( $match['label'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
			if ( ! preg_match( '/^(?:Mua ngay|Buy now)$/iu', $label ) ) { return $match[0]; }
			$attrs = preg_match( '#\bhref\s*=#i', $match['attrs'] )
				? preg_replace( '#\bhref\s*=\s*(["\']).*?\1#is', 'href="' . esc_url( $purchase_url ) . '"', $match['attrs'], 1 )
				: $match['attrs'] . ' href="' . esc_url( $purchase_url ) . '"';
			return '<a' . $attrs . '>' . $match['label'] . '</a>';
		}, $html );
	}
	$style = '<style id="theme-vive-woo-product-buttons">.tryon__cta,.tryon__modal,.tryon-block,[class*="tryon"],[id*="tryon"],.shopify-model-viewer-ui,[data-shopify-model-viewer-ui],model-viewer,.model-viewer,[class*="restock"],[id*="restock"],[class*="stoq"],[id*="stoq"],.preorder-button-description,[class*="preorder-button-description"],[class*="shipping-text"],[class*="preorder-badge"],[class*="preorder_button"],.shipping-info,[data-shipping],[data-preorder],.product-form__shipping{display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;height:0!important;width:0!important;overflow:hidden!important;margin:0!important;padding:0!important}.vive-in-stock-badge{display:inline-flex!important;align-items:center!important;gap:6px!important;color:#0d8a4f!important;font-weight:700!important;font-size:18px!important;line-height:1.3!important}.vive-in-stock-badge::before{content:"✓";display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#e6f4ea;color:#0d8a4f;font-size:13px;font-weight:900}.theme-vive-product-actions{display:flex!important;align-items:center;flex-wrap:wrap;gap:12px;margin-top:22px}.theme-vive-product-actions a{display:inline-flex!important;align-items:center;justify-content:center;box-sizing:border-box;min-width:132px;padding:11px 24px;border:1px solid #00b5e2;border-radius:2px;font:600 15px/1.35 "Be Vietnam Pro",Arial,sans-serif;text-decoration:none!important;transition:background .2s,border-color .2s,color .2s}.theme-vive-detail-button{background:#fff;color:#008fb3!important}.theme-vive-buy-button{background:#00b5e2;color:#fff!important}.theme-vive-detail-buy-button{display:inline-flex!important;align-items:center;justify-content:center;align-self:center;box-sizing:border-box;margin:0 22px 0 0;padding:8px 20px;border:1px solid #00b5e2;border-radius:2px;background:#00b5e2;color:#fff!important;font:600 14px/1.35 "Be Vietnam Pro",Arial,sans-serif;white-space:nowrap;cursor:pointer;transition:background .2s,border-color .2s}.theme-vive-detail-buy-button:hover,.theme-vive-detail-buy-button:focus{border-color:#008fb3;background:#008fb3}.theme-vive-product-actions a:hover,.theme-vive-product-actions a:focus{border-color:#008fb3;background:#008fb3;color:#fff!important}.product-list .theme-vive-product-actions{justify-content:center;margin-top:16px}.theme-vive-order-modal[hidden]{display:none!important}.theme-vive-order-modal{position:fixed;z-index:2147483640;inset:0;display:flex!important;align-items:center;justify-content:center;padding:20px;background:rgba(0,0,0,.62)}.theme-vive-order-dialog{position:relative;box-sizing:border-box;width:min(560px,100%);max-height:calc(100vh - 40px);overflow:auto;padding:30px;border-radius:8px;background:#fff;color:#202124;box-shadow:0 20px 60px rgba(0,0,0,.35)}.theme-vive-order-close{position:absolute;top:10px;right:12px;border:0;background:transparent;font-size:30px;line-height:1;cursor:pointer}.theme-vive-order-dialog h2{margin:0 35px 20px 0;font-size:25px}.theme-vive-order-product{display:grid;grid-template-columns:118px 1fr;gap:16px;align-items:center;margin-bottom:18px;padding:14px;border-radius:6px;background:#f5f7f8}.theme-vive-order-product img{display:block;width:118px;height:90px;margin:0!important;object-fit:contain}.theme-vive-order-product h3{margin:0 0 5px;font-size:19px}.theme-vive-order-product p{margin:0 0 5px;font-size:14px;line-height:1.45}.theme-vive-order-product-meta{color:#697077;font-size:12px}.theme-vive-order-dialog label{display:block;margin:12px 0 5px;font-weight:600}.theme-vive-order-dialog input,.theme-vive-order-dialog textarea{box-sizing:border-box;width:100%;padding:11px 12px;border:1px solid #c8cdd0;border-radius:4px;font:inherit}.theme-vive-order-quantity{max-width:120px}.theme-vive-order-submit{width:100%;margin-top:20px;padding:12px;border:0;border-radius:4px;background:#00b5e2;color:#fff;font-weight:700;cursor:pointer}.theme-vive-order-message{margin:14px 0 0}.theme-vive-order-message.is-error{color:#b42318}@media(max-width:767px){.theme-vive-product-actions{margin-top:16px}.theme-vive-product-actions a{padding:10px 20px}.theme-vive-detail-buy-button{margin-right:10px;padding:7px 12px;font-size:12px}.theme-vive-order-dialog{padding:24px 20px}.theme-vive-order-product{grid-template-columns:88px 1fr}.theme-vive-order-product img{width:88px;height:76px}}</style>';
	// Actions, variants & callback styling
	$style .= '<style id="theme-vive-shop-actions-style">'
		. '.show-block-if-variant-selected,.product-variants,.product-variants.no-js-hidden,.sidebar-widget,.sidebar-widget--outline,.sidebar-widget__content,.product-variant,.product-actions{display:block!important;visibility:visible!important;opacity:1!important}'
		. '.sidebar-widget--outline{border:1px solid #e1e8ed!important;background:#f9fbfc!important;padding:18px!important;border-radius:6px!important;margin:18px 0!important}'
		. '.sidebar-widget__title{font-weight:700!important;font-size:15px!important;display:block!important;margin-bottom:14px!important;color:#111!important}'
		. '.product-variant{margin-bottom:16px!important}'
		. '.product-variant__name{display:block!important;font-weight:700!important;margin-bottom:6px!important;color:#222!important;font-size:14px!important}'
		. '.product-variant select,.product-variant .kt-select,.product-variant__container--select{display:block!important;box-sizing:border-box!important;width:100%!important;padding:10px 14px!important;border:1px solid #c8d2d6!important;border-radius:4px!important;font-size:14px!important;background:#fff!important;cursor:pointer!important;color:#222!important;outline:none!important}'
		. '.product-variant select:focus{border-color:#00b5e2!important;box-shadow:0 0 0 3px rgba(0,181,226,.15)!important}'
		. '.product-variant__container--radio{display:flex!important;flex-wrap:wrap!important;gap:14px!important;margin-top:8px!important;align-items:center!important}'
		. '.product-variant__item--color{display:inline-flex!important;align-items:center!important;margin:0!important}'
		. '.product-variant__item-swatch{display:inline-block!important;width:36px!important;height:36px!important;border-radius:50%!important;border:2px solid #ddd!important;cursor:pointer!important;transition:transform .2s,border-color .2s,box-shadow .2s!important}'
		. '.product-variant__item-swatch:hover{transform:scale(1.1)!important}'
		. '.product-variant__input:checked+.product-variant__label .product-variant__item-swatch,.product-variant__item--color input:checked+label .product-variant__item-swatch{border-color:#00b5e2!important;box-shadow:0 0 0 3px rgba(0,181,226,.35)!important;transform:scale(1.12)!important}'
		. '.product-variant__label{cursor:pointer!important;display:inline-flex!important;align-items:center!important}'
		. '.product-variant__item-text-label{display:block!important;font-size:13px!important;color:#666!important;margin-top:6px!important}'
		. '.product-quantity,product-quantity,.product-quantity.hide{display:flex!important;align-items:center!important;gap:8px!important;margin:18px 0 20px!important;visibility:visible!important;opacity:1!important;flex-wrap:nowrap!important;width:auto!important;}'
		. '.product-quantity__title{white-space:nowrap!important;display:inline-block!important;font-weight:700!important;font-size:14px!important;color:#111!important;margin-right:8px!important;flex-shrink:0!important;}'
		. '.product-quantity .qty-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:38px!important;height:38px!important;border:1px solid #c8d2d6!important;background:#fff!important;border-radius:4px!important;cursor:pointer!important;font-size:18px!important;font-weight:700!important;transition:border-color .2s,background .2s}'
		. '.product-quantity .qty-button:hover{border-color:#00b5e2!important;background:#f0f9fc!important}'
		. '.product-quantity input.product__quantity{display:inline-block!important;width:64px!important;height:38px!important;text-align:center!important;border:1px solid #c8d2d6!important;border-radius:4px!important;font-size:16px!important;font-weight:700!important;background:#fff!important}'
		. '.product-quantity .qty-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:38px!important;height:38px!important;border:1px solid #c8d2d6!important;background:#fff!important;border-radius:4px!important;cursor:pointer!important;font-size:18px!important;font-weight:700!important;transition:border-color .2s,background .2s}'
		. '.product-quantity .qty-button:hover{border-color:#00b5e2!important;background:#f0f9fc!important}'
		. '.product-quantity input.product__quantity{display:inline-block!important;width:64px!important;height:38px!important;text-align:center!important;border:1px solid #c8d2d6!important;border-radius:4px!important;font-size:16px!important;font-weight:700!important;background:#fff!important}'
		. '.vive-woo-product__actions{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:20px;margin-bottom:20px}'
		. '.vive-woo-product__action{display:flex;align-items:center;justify-content:center;min-height:50px;padding:0 22px;border:2px solid #00b5e2;border-radius:4px;font-weight:800;font-size:15px;text-transform:uppercase;text-decoration:none!important;cursor:pointer;transition:background .2s,border-color .2s,color .2s,transform .2s}'
		. '.vive-woo-product__action--zalo{background:#fff;color:#008fb3!important}'
		. '.vive-woo-product__action--buy{background:#00b5e2;color:#fff!important}'
		. '.vive-woo-product__action:hover,.vive-woo-product__action:focus{border-color:#008fb3;background:#008fb3;color:#fff!important;transform:translateY(-1px)}'
		. '.vive-woo-product__callback{margin-top:20px;margin-bottom:25px;padding:22px;border:1px solid #dce7eb;border-top:4px solid #00b5e2;border-radius:4px;background:#f5f8f9;color:#202124;box-shadow:0 4px 16px rgba(17,25,30,.05)}'
		. '.vive-woo-product__callback strong{display:block;margin-bottom:12px;font-size:15px;line-height:1.5;color:#111}'
		. '.vive-woo-product__callback>div{display:grid;grid-template-columns:1fr auto;gap:8px}'
		. '.vive-woo-product__callback input{min-width:0;padding:13px 16px;border:1px solid #c8d2d6;border-radius:4px;background:#fff;font:inherit;outline:none}'
		. '.vive-woo-product__callback input:focus{border-color:#00b5e2;box-shadow:0 0 0 3px rgba(0,181,226,.14)}'
		. '.vive-woo-product__callback button{padding:0 26px;border:1px solid #00b5e2;border-radius:4px;background:#00b5e2;color:#fff;font-weight:800;font-size:14px;text-transform:uppercase;cursor:pointer;transition:background .2s,border-color .2s}'
		. '.vive-woo-product__callback button:hover,.vive-woo-product__callback button:focus{border-color:#008fb3;background:#008fb3}'
		. '.vive-woo-product__callback-message{margin:10px 0 0;color:#087d9d;font-weight:600}'
		. '.vive-woo-product__callback-message.is-error{color:#b42318}'
		. '@media(max-width:600px){.vive-woo-product__actions{grid-template-columns:1fr}.vive-woo-product__callback>div{grid-template-columns:1fr}.vive-woo-product__callback button{min-height:44px}}'
		. '.breadcrumb-main,.breadcrumb-main--scroll,.breadcrumb-main--smaller,.breadcrumb-main--margin,.breadcrumb-container,.breadcrumb{overflow:visible!important;white-space:normal!important;width:auto!important;max-width:100%!important;mask-image:none!important;-webkit-mask-image:none!important}'
		. '.breadcrumb{display:flex!important;flex-wrap:wrap!important;align-items:center!important;gap:4px 6px!important;line-height:1.5!important;padding:4px 0!important}'
		. '.breadcrumb__current,.breadcrumb__link,.breadcrumb__separator{display:inline-flex!important;align-items:center!important;white-space:normal!important;text-overflow:unset!important;overflow:visible!important;max-width:none!important;width:auto!important;word-break:normal!important;font-size:13px!important;line-height:1.4!important}'
		. '.breadcrumb__current{color:#697077!important;font-weight:500!important}'
		. '.breadcrumb__link a{color:#008fb3!important;text-decoration:none!important;font-weight:500!important}'
		. '.breadcrumb__link a:hover{text-decoration:underline!important}'
		. '.product__description,.product__description.rte,.vive-woo-product__description,.vive-product-description-vi{font-family:inherit!important;color:#374151!important;font-size:15px!important;line-height:1.65!important;text-align:left!important}'
		. '.product__description .add-to-cart-button,.product__description button.slug-button,.product__description .vive20-btn,.product__description button[data-gtm="add-to-cart-button"],.vive-woo-product__description .add-to-cart-button,.vive-product-description-vi .add-to-cart-button{display:none!important}'
		. '.product__description h1{font-size:24px!important;font-weight:700!important;line-height:1.35!important;color:#111827!important;margin-top:24px!important;margin-bottom:12px!important}'
		. '.product__description h2,.product__description .type20-pdp-title,.product__description .word-title,.product__description .text-title,.product__description .typography-headline,.product__description h3.video-area,.product__description h3.word-title,.product__description h3.typography-headline,.product__description .type20-promote-head strong{font-size:19px!important;font-weight:700!important;line-height:1.4!important;color:#111827!important;margin-top:24px!important;margin-bottom:10px!important;display:block!important}'
		. '.product__description h3{font-size:17px!important;font-weight:700!important;line-height:1.4!important;color:#1f2937!important;margin-top:20px!important;margin-bottom:8px!important}'
		. '.product__description .font-thin{font-weight:700!important;font-size:inherit!important;color:inherit!important}'
		. '.product__description p,.product__description .type20-pdp-copy,.product__description .word-text,.product__description .text-copy,.product__description .typography-description,.product__description .section-kv-banner__wording-area__description-block p{font-size:14.5px!important;line-height:1.65!important;color:#4b5563!important;margin-bottom:14px!important;font-weight:400!important}'
		. '.product__description sup{font-size:10px!important;vertical-align:super!important;line-height:0!important;color:#6b7280!important}'
		. '.product__description .txt-color-white,.product__description .txt-m-color-black{color:#111827!important}'
		. '.product__description img{max-width:100%!important;height:auto!important;display:block!important;margin:24px auto!important;border-radius:8px!important}'
		. '.product__description .product-spec-list-item{margin-top:14px!important;margin-bottom:14px!important}'
		. '.product__description h2.product-spec-list-item{font-size:19px!important;font-weight:700!important;color:#111827!important;margin-top:26px!important;margin-bottom:12px!important;padding:0!important;background:transparent!important;border:none!important}'
		. '.product__description .product-spec-list-item p{font-size:14px!important;line-height:1.6!important;color:#374151!important;margin-bottom:8px!important}'
		. '.product__description .product-spec-list-item strong{color:#111827!important;font-weight:700!important}'
		. '.product__description ol,.product__description ul{padding-left:20px!important;margin:12px 0 16px 0!important}'
		. '.product__description li{font-size:14px!important;line-height:1.6!important;color:#4b5563!important;margin-bottom:8px!important}'
		. '.product__description .table-wrapper{overflow-x:auto!important;margin:20px 0!important}'
		. '.product__description table{width:100%!important;border-collapse:collapse!important;font-size:14px!important}'
		. '.product__description th,.product__description td{padding:10px 14px!important;border-bottom:1px solid #e5e7eb!important;text-align:left!important}'
		. '.product__description th{background:#f9fafb!important;font-weight:700!important;color:#111827!important}'
		. '</style>';

	// Legacy mirrored styles can shift the button label vertically. Keep the
	// purchase labels centered regardless of the source page's anchor styles.
	$style .= '<style id="theme-vive-buy-button-alignment">.theme-vive-product-actions a,.theme-vive-detail-buy-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;box-sizing:border-box!important;min-height:48px!important;padding-top:10px!important;padding-bottom:10px!important;font-family:"Be Vietnam Pro",Arial,sans-serif!important;line-height:1.2!important;text-align:center!important;vertical-align:middle!important;text-indent:0!important}.theme-vive-detail-buy-button{min-height:44px!important;padding-top:8px!important;padding-bottom:8px!important}</style>';
	$config = array(
		'products'=>$catalogue,
		'endpoint'=>admin_url( 'admin-post.php' ),
		'nonce'=>wp_create_nonce( 'theme_vive_submit_order' ),
	);
	$script = '<script id="theme-vive-woo-products">window.themeViveProductOrder=' . wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';</script><script src="' . esc_url( get_template_directory_uri() . '/inc/product-order.js?v=' . ( file_exists( get_template_directory() . '/inc/product-order.js' ) ? filemtime( get_template_directory() . '/inc/product-order.js' ) : '20260907' ) ) . '" defer></script>';
	return preg_replace( '#</body>#i', $style . $script . '</body>', $html, 1 );
}

function theme_vive_submit_product_order() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'theme_vive_submit_order' ) ) {
		wp_send_json_error( array( 'message'=>'Phiên gửi đã hết hạn. Vui lòng tải lại trang.' ), 403 );
	}
	$product_id = absint( $_POST['product_id'] ?? 0 );
	$product_slug = sanitize_title( wp_unslash( $_POST['product_slug'] ?? '' ) );

	$product = false;
	if ( $product_id && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $product_id );
	}
	if ( ( ! $product || 'publish' !== $product->get_status() ) && $product_slug && function_exists( 'wc_get_product' ) ) {
		$posts = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'meta_key' => THEME_VIVE_PRODUCT_META, 'meta_value' => $product_slug, 'numberposts' => 1 ) );
		if ( ! $posts ) {
			$posts = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'name' => $product_slug, 'numberposts' => 1 ) );
		}
		if ( $posts ) {
			$product = wc_get_product( $posts[0]->ID );
		}
	}

	if ( ! $product || 'publish' !== $product->get_status() ) {
		wp_send_json_error( array( 'message'=>'Sản phẩm không hợp lệ.' ), 400 );
	}
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$address = sanitize_textarea_field( wp_unslash( $_POST['address'] ?? '' ) );
	$note = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	$quantity = max( 1, min( 99, absint( $_POST['quantity'] ?? 1 ) ) );
	if ( ! preg_match( '/^[0-9+().\s-]{8,20}$/', $phone ) ) {
		wp_send_json_error( array( 'message'=>'Vui lòng nhập số điện thoại hợp lệ.' ), 422 );
	}
	if ( ! function_exists( 'wc_create_order' ) ) {
		wp_send_json_error( array( 'message'=>'WooCommerce chưa sẵn sàng để tạo đơn hàng.' ), 500 );
	}
	$order = wc_create_order( array( 'created_via'=>'theme-vive-order-form', 'customer_id'=>get_current_user_id() ) );
	if ( is_wp_error( $order ) ) {
		wp_send_json_error( array( 'message'=>'Chưa tạo được đơn hàng. Vui lòng thử lại.' ), 500 );
	}
	$order->add_product( $product, $quantity );
	$name_parts = preg_split( '/\s+/u', trim( $name ), 2 );
	$order->set_billing_first_name( $name_parts[0] ?? '' );
	$order->set_billing_last_name( $name_parts[1] ?? '' );
	$order->set_billing_phone( $phone );
	$order->set_billing_email( $email );
	$order->set_billing_address_1( $address );
	$order->set_customer_note( $note );
	$order->update_meta_data( '_theme_vive_order_source', 'popup' );
	$order->calculate_totals();
	$order->update_status( 'on-hold', 'Đơn được gửi từ popup mua hàng VIVE. Sale cần gọi xác nhận qua số ' . $phone . '.' );
	$order_id = $order->get_id();

	$subject = '[VIVE Việt Nam] Đơn hàng #' . $order_id . ': ' . $product->get_name();
	$order_url = admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order_id );
	$message = '<!doctype html><html lang="vi"><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f3f6f8;font-family:Arial,Helvetica,sans-serif;color:#202124">'
		. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6f8;padding:28px 12px"><tr><td align="center">'
		. '<table role="presentation" width="620" cellspacing="0" cellpadding="0" style="width:100%;max-width:620px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.08)">'
		. '<tr><td style="padding:24px 30px;background:#111;color:#fff;border-top:5px solid #00b5e2"><div style="font-size:13px;letter-spacing:1.5px;color:#00b5e2;font-weight:700">VIVE VIỆT NAM</div><h1 style="margin:8px 0 0;font-size:25px;line-height:1.3">Có đơn hàng mới #' . esc_html( $order_id ) . '</h1></td></tr>'
		. '<tr><td style="padding:28px 30px"><p style="margin:0 0 22px;color:#5f6368;line-height:1.6">Khách hàng vừa gửi yêu cầu mua sản phẩm. Vui lòng liên hệ xác nhận sớm.</p>'
		. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e1e5e8;border-radius:6px">'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Thời gian đặt</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8">' . esc_html( current_time( 'H:i d/m/Y' ) . ' (GMT+7)' ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;width:145px;border-bottom:1px solid #e1e5e8">Sản phẩm</td><td style="padding:12px 14px;font-weight:700;border-bottom:1px solid #e1e5e8">' . esc_html( $product->get_name() ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Số lượng</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8">' . esc_html( $quantity ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Khách hàng</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8">' . esc_html( $name ?: 'Không cung cấp' ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Điện thoại</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8"><a href="tel:' . esc_attr( $phone ) . '" style="color:#008fb3;font-weight:700;text-decoration:none">' . esc_html( $phone ) . '</a></td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Email</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8">' . ( $email ? '<a href="mailto:' . esc_attr( $email ) . '" style="color:#008fb3;text-decoration:none">' . esc_html( $email ) . '</a>' : 'Không cung cấp' ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078;border-bottom:1px solid #e1e5e8">Địa chỉ</td><td style="padding:12px 14px;border-bottom:1px solid #e1e5e8">' . esc_html( $address ?: 'Không cung cấp' ) . '</td></tr>'
		. '<tr><td style="padding:12px 14px;background:#f7f9fa;color:#687078">Ghi chú</td><td style="padding:12px 14px">' . esc_html( $note ?: 'Không có' ) . '</td></tr>'
		. '</table>'
		. '<div style="margin-top:24px;text-align:center"><a href="' . esc_url( $order_url ) . '" style="display:inline-block;padding:12px 28px;background:#00b5e2;color:#fff;text-decoration:none;border-radius:4px;font-weight:700">Xem đơn hàng trong quản trị</a></div>'
		. '</td></tr>'
		. '<tr><td style="padding:16px 30px;background:#f7f9fa;color:#8a949e;font-size:12px;text-align:center;border-top:1px solid #e1e5e8">Email tự động từ hệ thống VIVE Việt Nam.</td></tr>'
		. '</table></td></tr></table></body></html>';

	$admin_email = sanitize_email( get_option( 'admin_email' ) );
	$headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: VIVE Việt Nam <contact@vive.com.vn>' );
	try {
		if ( $admin_email ) { wp_mail( $admin_email, $subject, $message, $headers ); }
	} catch ( Throwable $error ) {
		// Suppress mail error
	}

	wp_send_json_success( array(
		'message'  => 'Đặt hàng thành công! Nhân viên VIVE sẽ sớm liên hệ xác nhận đơn hàng với bạn qua số ' . $phone . '.',
		'order_id' => $order_id,
	) );
}
add_action( 'admin_post_theme_vive_submit_order', 'theme_vive_submit_product_order' );
add_action( 'admin_post_nopriv_theme_vive_submit_order', 'theme_vive_submit_product_order' );

/** Keep callback requests in WordPress so they are not lost when email is unavailable. */
function theme_vive_register_callback_request_type() {
	register_post_type( 'vive_callback', array(
		'labels'       => array(
			'name'          => 'Yêu cầu tư vấn',
			'singular_name' => 'Yêu cầu tư vấn',
			'menu_name'     => 'Tư vấn cuộc gọi',
			'all_items'     => 'Tất cả yêu cầu',
			'view_item'     => 'Xem yêu cầu',
			'search_items'  => 'Tìm yêu cầu',
			'not_found'     => 'Chưa có yêu cầu tư vấn.',
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'woocommerce',
		'supports'     => array( 'title' ),
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'theme_vive_register_callback_request_type' );

function theme_vive_submit_callback() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'theme_vive_submit_callback' ) ) {
		wp_send_json_error( array( 'message'=>'Phiên gửi đã hết hạn. Vui lòng tải lại trang.' ), 403 );
	}
	$product_id = absint( $_POST['product_id'] ?? 0 );
	$product_slug = sanitize_title( wp_unslash( $_POST['product_slug'] ?? '' ) );

	$product = false;
	if ( $product_id && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $product_id );
	}
	if ( ( ! $product || 'publish' !== $product->get_status() ) && $product_slug && function_exists( 'wc_get_product' ) ) {
		$posts = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'meta_key' => THEME_VIVE_PRODUCT_META, 'meta_value' => $product_slug, 'numberposts' => 1 ) );
		if ( ! $posts ) {
			$posts = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'name' => $product_slug, 'numberposts' => 1 ) );
		}
		if ( $posts ) {
			$product = wc_get_product( $posts[0]->ID );
			$product_id = $posts[0]->ID;
		}
	}

	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$product_name = $product ? $product->get_name() : ( $product_slug ?: 'Sản phẩm VIVE' );
	if ( ! preg_match( '/^[0-9+().\s-]{8,20}$/', $phone ) ) {
		wp_send_json_error( array( 'message'=>'Vui lòng nhập số điện thoại hợp lệ.' ), 422 );
	}
	$request_id = wp_insert_post( array(
		'post_type'   => 'vive_callback',
		'post_status' => 'private',
		'post_title'  => $phone . ' — ' . $product_name,
	), true );
	if ( is_wp_error( $request_id ) ) {
		wp_send_json_error( array( 'message'=>'Chưa lưu được yêu cầu. Vui lòng thử lại.' ), 500 );
	}
	update_post_meta( $request_id, '_theme_vive_phone', $phone );
	update_post_meta( $request_id, '_theme_vive_product_id', $product_id );
	update_post_meta( $request_id, '_theme_vive_page_url', get_permalink( $product_id ) );

	$time_str = current_time( 'H:i d/m/Y' ) . ' (GMT+7)';
	$subject = '[VIVE Việt Nam] Yêu cầu tư vấn: ' . $product->get_name();
	$message = "Mã yêu cầu: #{$request_id}\nThời gian: {$time_str}\nSản phẩm: {$product->get_name()}\nSố điện thoại: {$phone}\nTrang: " . get_permalink( $product_id );
	$email_sent = false;
	$admin_email = sanitize_email( get_option( 'admin_email' ) );
	$headers = array( 'From: VIVE Việt Nam <contact@vive.com.vn>' );
	try {
		$email_sent = $admin_email ? (bool) wp_mail( $admin_email, $subject, $message, $headers ) : false;
	} catch ( Throwable $error ) {
		update_post_meta( $request_id, '_theme_vive_mail_error', sanitize_text_field( $error->getMessage() ) );
	}
	update_post_meta( $request_id, '_theme_vive_email_sent', $email_sent ? 'yes' : 'no' );
	wp_send_json_success( array(
		'message'     => 'Đã ghi nhận yêu cầu #' . $request_id . '. Chúng tôi sẽ liên hệ với bạn sớm.',
		'request_id'  => $request_id,
		'email_sent'  => $email_sent,
	) );
}
add_action( 'admin_post_theme_vive_submit_callback', 'theme_vive_submit_callback' );
add_action( 'admin_post_nopriv_theme_vive_submit_callback', 'theme_vive_submit_callback' );

// Chuẩn hóa định dạng tiền tệ và hiển thị giá WooCommerce theo chuẩn Việt Nam
add_filter( 'woocommerce_currency', static function () { return 'VND'; } );
add_filter( 'woocommerce_currency_symbol', static function ( $symbol, $currency ) {
	return 'VND' === $currency ? '₫' : $symbol;
}, 10, 2 );
add_filter( 'woocommerce_price_thousand_sep', static function () { return '.'; } );
add_filter( 'woocommerce_price_decimal_sep', static function () { return ','; } );
add_filter( 'woocommerce_price_num_decimals', static function () { return 0; } );
add_filter( 'woocommerce_price_format', static function () { return '%1$s&nbsp;%2$s'; } );

