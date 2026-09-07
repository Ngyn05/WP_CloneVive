<?php
/** Contextual translation workflow stored in WordPress. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function theme_vive_register_translation_type() {
	register_post_type( 'vive_translation', array(
		'labels' => array(
			'name' => 'Bản dịch VIVE', 'singular_name' => 'Bản dịch',
			'add_new_item' => 'Thêm bản dịch theo ngữ cảnh', 'edit_item' => 'Biên tập bản dịch',
		),
		'public' => false, 'show_ui' => true, 'show_in_menu' => true,
		'menu_icon' => 'dashicons-translation', 'supports' => array( 'title' ),
		'capability_type' => 'post', 'map_meta_cap' => true,
	) );
}
add_action( 'init', 'theme_vive_register_translation_type' );

function theme_vive_translation_metaboxes() {
	add_meta_box( 'vive_translation_content', 'Nội dung và trạng thái', 'theme_vive_translation_box', 'vive_translation', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'theme_vive_translation_metaboxes' );

function theme_vive_translation_box( $post ) {
	wp_nonce_field( 'theme_vive_save_translation', 'theme_vive_translation_nonce' );
	$source = get_post_meta( $post->ID, '_vive_source', true );
	$vi = get_post_meta( $post->ID, '_vive_vi', true );
	$section = get_post_meta( $post->ID, '_vive_section', true ) ?: 'global';
	$status = get_post_meta( $post->ID, '_vive_status', true ) ?: 'draft';
	$locked = '1' === get_post_meta( $post->ID, '_vive_locked', true );
	?>
	<p><label><strong>Section</strong><br><select name="vive_section">
	<?php foreach ( array( 'global'=>'Giao diện chung', 'header'=>'Header/Menu', 'footer'=>'Footer', 'home'=>'Trang chủ', 'product'=>'Sản phẩm', 'accessory'=>'Phụ kiện', 'support'=>'Hỗ trợ', 'business'=>'Doanh nghiệp', 'legal'=>'Pháp lý' ) as $key=>$label ) : ?>
	<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $section, $key ); ?>><?php echo esc_html( $label ); ?></option>
	<?php endforeach; ?></select></label></p>
	<p><label><strong>Bản gốc</strong><br><textarea name="vive_source" rows="5" class="widefat" <?php disabled( $locked ); ?>><?php echo esc_textarea( $source ); ?></textarea></label></p>
	<p><label><strong>Bản Việt theo ngữ cảnh</strong><br><textarea name="vive_vi" rows="5" class="widefat" <?php disabled( $locked ); ?>><?php echo esc_textarea( $vi ); ?></textarea></label></p>
	<p><label><strong>Trạng thái</strong><br><select name="vive_status" <?php disabled( $locked ); ?>>
	<?php foreach ( array( 'draft'=>'Bản nháp', 'review'=>'Chờ duyệt', 'approved'=>'Đã duyệt' ) as $key=>$label ) : ?>
	<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
	<?php endforeach; ?></select></label></p>
	<p><label><input type="checkbox" name="vive_locked" value="1" <?php checked( $locked ); ?>> <strong>Khóa bản dịch đã duyệt</strong></label></p>
	<?php if ( $locked ) : ?><p><em>Bỏ chọn “Khóa” và cập nhật bài để mở khóa trước khi sửa.</em></p><?php endif;
}

function theme_vive_save_translation( $post_id ) {
	if ( ! isset( $_POST['theme_vive_translation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['theme_vive_translation_nonce'] ) ), 'theme_vive_save_translation' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$was_locked = '1' === get_post_meta( $post_id, '_vive_locked', true );
	$keep_locked = isset( $_POST['vive_locked'] );
	if ( $was_locked ) {
		if ( ! $keep_locked ) { update_post_meta( $post_id, '_vive_locked', '0' ); delete_transient( 'theme_vive_translation_map' ); }
		return;
	}
	update_post_meta( $post_id, '_vive_source', sanitize_textarea_field( wp_unslash( $_POST['vive_source'] ?? '' ) ) );
	update_post_meta( $post_id, '_vive_vi', sanitize_textarea_field( wp_unslash( $_POST['vive_vi'] ?? '' ) ) );
	update_post_meta( $post_id, '_vive_section', sanitize_key( $_POST['vive_section'] ?? 'global' ) );
	update_post_meta( $post_id, '_vive_status', $keep_locked ? 'approved' : sanitize_key( $_POST['vive_status'] ?? 'draft' ) );
	update_post_meta( $post_id, '_vive_locked', $keep_locked ? '1' : '0' );
	delete_transient( 'theme_vive_translation_map' );
}
add_action( 'save_post_vive_translation', 'theme_vive_save_translation' );

function theme_vive_translation_map( $include_auto = true ) {
	static $cached_maps = array();
	$cache_key = $include_auto ? 'full' : 'curated';
	if ( isset( $cached_maps[ $cache_key ] ) ) { return $cached_maps[ $cache_key ]; }

	$map = array();
	$auto_php = get_template_directory() . '/inc/translations-auto-vi.php';
	$auto_json = get_template_directory() . '/inc/translations-auto-vi.json';

	if ( $include_auto && is_readable( $auto_php ) ) {
		$map = require $auto_php;
	} elseif ( $include_auto && is_readable( $auto_json ) ) {
		$map = json_decode( file_get_contents( $auto_json ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( ! is_array( $map ) ) { $map = array(); }

	// Keep the large, frequently updated Eagle product copy isolated from the
	// global UI dictionary while still allowing curated global wording to win.
	$eagle_file = get_template_directory() . '/inc/translations-vive-eagle-vi.php';
	if ( is_readable( $eagle_file ) ) {
		$eagle = require $eagle_file;
		if ( is_array( $eagle ) ) {
			$map = array_merge( $map, $eagle );
		}
	}

	// Curated translations always take precedence over automatic drafts.
	$curated_file = get_template_directory() . '/inc/translations-vi.php';
	if ( is_readable( $curated_file ) ) {
		$curated = require $curated_file;
		if ( is_array( $curated ) ) {
			$map = array_merge( $map, $curated );
		}
	}

	$ids = get_posts( array( 'post_type'=>'vive_translation', 'post_status'=>'publish', 'numberposts'=>-1, 'fields'=>'ids', 'no_found_rows'=>true ) );
	foreach ( $ids as $id ) {
		$status = get_post_meta( $id, '_vive_status', true );
		$source = get_post_meta( $id, '_vive_source', true );
		$vi = get_post_meta( $id, '_vive_vi', true );
		if ( $source && $vi && in_array( $status, array( 'approved' ), true ) ) { $map[ $source ] = $vi; }
	}

	// Product and model names are trademarks, not translatable copy. Keep these
	// canonical even when an older approved/automatic translation says otherwise.
	$map = array_merge( $map, array(
		'VIVEPORT'          => 'VIVEPORT',
		'VIVEPORT ↗'        => 'VIVEPORT ↗',
		'VIVE Arts'         => 'VIVE Arts',
		'VIVE Arts ↗'       => 'VIVE Arts ↗',
		'VIVE Mars'         => 'VIVE Mars',
		'VIVE Sync'         => 'VIVE Sync',
		'VIVE X'            => 'VIVE X',
		'VIVE Eagle'        => 'VIVE Eagle',
		'VIVE Focus Vision' => 'VIVE Focus Vision',
		'VIVE Focus 3'      => 'VIVE Focus 3',
		'VIVE XR Elite'     => 'VIVE XR Elite',
		'VIVE Pro 2'        => 'VIVE Pro 2',
		'Pro 2'             => 'VIVE Pro 2',
		'Pro 2 Headset'     => 'VIVE Pro 2 Headset',
		'VIVE Flow'         => 'VIVE Flow',
		'VIVE Wireless Dongle' => 'Bộ thu phát không dây VIVE',
		'VIVE Wireless Dongle is sold separately.' => 'Bộ thu phát không dây VIVE được bán riêng.',
		'VIVE Wireless Dongle is sold separately. Each VIVE Wireless Dongle can connect with one to five (1–5) VIVE Ultimate Trackers.' => 'Bộ thu phát không dây VIVE được bán riêng. Mỗi bộ thu phát có thể kết nối với từ một đến năm (1–5) thiết bị VIVE Ultimate Tracker.',
		'Media'             => 'Thư viện truyền thông',
		'Tell me more'      => 'Thông tin thêm',
	) );

	$cached_maps[ $cache_key ] = $map;
	return $cached_maps[ $cache_key ];
}

function theme_vive_seed_translations() {
	if ( get_option( 'theme_vive_translations_seeded_32' ) ) { return; }
	theme_vive_register_translation_type();
	foreach ( require get_template_directory() . '/inc/translations-vi.php' as $source=>$vi ) {
		$existing = get_posts( array( 'post_type'=>'vive_translation', 'meta_key'=>'_vive_source', 'meta_value'=>$source, 'fields'=>'ids', 'numberposts'=>1 ) );
		if ( $existing ) { continue; }
		$id = wp_insert_post( array( 'post_type'=>'vive_translation', 'post_status'=>'publish', 'post_title'=>wp_trim_words( $source, 10, '…' ) ) );
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_vive_source', $source ); update_post_meta( $id, '_vive_vi', $vi );
			update_post_meta( $id, '_vive_section', 'global' ); update_post_meta( $id, '_vive_status', 'approved' ); update_post_meta( $id, '_vive_locked', '1' );
		}
	}
	update_option( 'theme_vive_translations_seeded_32', 1, false );
	delete_transient( 'theme_vive_translation_map' );
}
add_action( 'after_switch_theme', 'theme_vive_seed_translations' );

add_filter( 'manage_vive_translation_posts_columns', static function ( $columns ) {
	return array( 'cb'=>$columns['cb'], 'title'=>'Chuỗi nguồn', 'vive_section'=>'Section', 'vive_status'=>'Trạng thái', 'vive_locked'=>'Khóa', 'date'=>$columns['date'] );
} );
add_action( 'manage_vive_translation_posts_custom_column', static function ( $column, $id ) {
	if ( 'vive_section' === $column ) { echo esc_html( get_post_meta( $id, '_vive_section', true ) ); }
	if ( 'vive_status' === $column ) { echo esc_html( get_post_meta( $id, '_vive_status', true ) ); }
	if ( 'vive_locked' === $column ) { echo '1' === get_post_meta( $id, '_vive_locked', true ) ? '🔒' : '—'; }
}, 10, 2 );
