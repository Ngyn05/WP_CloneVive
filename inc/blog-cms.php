<?php
/** WordPress-backed CMS for the mirrored VIVE blog. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const THEME_VIVE_BLOG_META = '_theme_vive_blog';

function theme_vive_blog_parent_category() {
	$term = term_exists( 'Blog VIVE', 'category' );
	if ( ! $term ) { $term = wp_insert_term( 'Blog VIVE', 'category', array( 'slug'=>'blog-vive' ) ); }
	return is_wp_error( $term ) ? 0 : (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

function theme_vive_blog_is_post( $post_id ) {
	if ( get_post_meta( $post_id, THEME_VIVE_BLOG_META, true ) ) { return true; }
	return theme_vive_blog_has_category( $post_id );
}

function theme_vive_blog_has_category( $post_id ) {
	$parent = theme_vive_blog_parent_category();
	foreach ( wp_get_post_categories( $post_id ) as $term_id ) {
		if ( $parent && ( $parent === (int) $term_id || term_is_ancestor_of( $parent, $term_id, 'category' ) ) ) { return true; }
	}
	return false;
}

function theme_vive_blog_value( $post_id, $key, $fallback = 'N/A' ) {
	$value = get_post_meta( $post_id, '_vive_blog_' . $key, true );
	if ( ! is_scalar( $value ) ) { return $fallback; }
	$value = trim( (string) $value );
	if ( '' === $fallback && in_array( strtolower( $value ), array( 'n/a', 'na', 'null', 'undefined' ), true ) ) { return ''; }
	return '' !== $value ? $value : $fallback;
}

function theme_vive_blog_public_image( $url ) {
	$url = trim( (string) $url );
	$path = wp_parse_url( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), PHP_URL_PATH );
	if ( is_string( $path ) && preg_match( '#^/(?:static|media|express_static|express_media)/#i', $path ) ) {
		return get_template_directory_uri() . '/assets' . $path;
	}
	return $url;
}

/** Point links to vive.com (including its subdomains) back to this site's home. */
function theme_vive_blog_replace_vive_links( $html ) {
	return preg_replace_callback( '#(<a\b[^>]*?\bhref\s*=\s*["\'])(?<url>https?:\/\/[^"\']+)(["\'])#i', static function ( $match ) {
		$host = strtolower( (string) wp_parse_url( html_entity_decode( $match['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), PHP_URL_HOST ) );
		if ( 'vive.com' === $host || str_ends_with( $host, '.vive.com' ) ) {
			return $match[1] . esc_url( home_url( '/' ) ) . $match[3];
		}
		return $match[0];
	}, (string) $html );
}

/** Use the single local contact address for visible emails and mail links. */
function theme_vive_blog_replace_emails( $html ) {
	$html = preg_replace( '#\bmailto:[^"\'\s<>]+#i', 'mailto:contact@vive.com.vn', (string) $html );
	return preg_replace( '#\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b#i', 'contact@vive.com.vn', $html );
}

function theme_vive_blog_normalize_content( $html ) {
	return theme_vive_blog_replace_emails( theme_vive_blog_replace_vive_links( $html ) );
}

add_filter( 'wp_insert_post_data', static function ( $data, $postarr ) {
	if ( 'post' !== ( $data['post_type'] ?? '' ) ) { return $data; }
	$post_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
	if ( $post_id && ! theme_vive_blog_is_post( $post_id ) ) { return $data; }
	if ( isset( $data['post_content'] ) ) {
		$data['post_content'] = theme_vive_blog_normalize_content( $data['post_content'] );
	}
	return $data;
}, 20, 2 );

function theme_vive_blog_import_image( $item ) {
	foreach ( array( 'cover_url', 'cover_thumbnail', 'cover' ) as $key ) {
		$value = $item[ $key ] ?? '';
		if ( is_string( $value ) && trim( $value ) ) { return $value; }
		if ( is_array( $value ) ) {
			foreach ( array( 'url', 'full_url', 'download_url', 'src' ) as $field ) {
				if ( ! empty( $value[ $field ] ) && is_string( $value[ $field ] ) ) { return $value[ $field ]; }
			}
		}
	}
	return 'N/A';
}

function theme_vive_blog_path( $post ) {
	$legacy = trim( theme_vive_blog_value( $post->ID, 'legacy_path', '' ), '/' );
	return $legacy ?: get_the_date( 'Y/m/d', $post ) . '/' . $post->post_name;
}

function theme_vive_blog_url( $post ) { return home_url( '/blog/' . theme_vive_blog_path( $post ) . '/' ); }

add_filter( 'post_type_link', static function ( $url, $post ) {
	return 'post' === $post->post_type && theme_vive_blog_is_post( $post->ID ) ? theme_vive_blog_url( $post ) : $url;
}, 10, 2 );

// Use WordPress' native visual block editor for blog content.
add_filter( 'use_block_editor_for_post_type', static function ( $use, $post_type ) {
	return 'post' === $post_type ? true : $use;
}, 999, 2 );
add_filter( 'use_block_editor_for_post', static function ( $use, $post ) {
	return $post instanceof WP_Post && 'post' === $post->post_type ? true : $use;
}, 999, 2 );

add_action( 'enqueue_block_editor_assets', static function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->post_type ) { return; }
	$css = '.editor-styles-wrapper{background:#f3f4f6;padding:36px!important}'
		. '.editor-styles-wrapper .is-root-container,.editor-styles-wrapper .wp-block-freeform{box-sizing:border-box;max-width:860px!important;margin:0 auto!important;padding:38px 46px;background:#fff;box-shadow:0 2px 14px rgba(0,0,0,.09);font-family:Arial,"Noto Sans","Be Vietnam Pro",sans-serif;font-size:16px;line-height:1.65;color:#212529}'
		. '.editor-styles-wrapper p{font-size:16px;line-height:1.65;margin-bottom:1.25rem}.editor-styles-wrapper img{max-width:100%;height:auto}.editor-styles-wrapper a{color:#007fa8}';
	wp_add_inline_style( 'wp-edit-blocks', $css );

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	$post = $post_id ? get_post( $post_id ) : null;
	if ( ! $post instanceof WP_Post ) { return; }
	$legacy_html = theme_vive_blog_normalize_content( trim( (string) $post->post_content ) );
	$has_native_blocks = false !== strpos( $legacy_html, '<!-- wp:' )
		&& false === strpos( $legacy_html, '<!-- wp:freeform' )
		&& false === strpos( $legacy_html, '<!-- wp:html' );
	if ( '' === $legacy_html || $has_native_blocks ) { return; }
	$legacy_html = preg_replace( '#^\s*<!--\s+wp:(?:freeform|html)(?:\s+\{.*?\})?\s*-->#s', '', $legacy_html );
	$legacy_html = preg_replace( '#<!--\s+/wp:(?:freeform|html)\s*-->\s*$#s', '', $legacy_html );
	$script = 'wp.domReady(function(){'
		. 'if(!window.wp||!wp.blocks||!wp.blocks.rawHandler||!wp.data)return;'
		. 'var current=wp.data.select("core/block-editor").getBlocks();'
		. 'var broken=current.length===1&&(current[0].name==="core/freeform"||current[0].name==="core/html");'
		. 'if(current.length&& !broken)return;'
		. 'var blocks=wp.blocks.rawHandler({HTML:' . wp_json_encode( $legacy_html, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '});'
		. 'if(blocks&&blocks.length)wp.data.dispatch("core/block-editor").resetBlocks(blocks);'
		. '});';
	wp_add_inline_script( 'wp-blocks', $script, 'after' );
} );
add_filter( 'user_can_richedit', '__return_true', 99 );
add_filter( 'wp_default_editor', static function () { return 'tinymce'; }, 99 );
add_filter( 'format_for_editor', static function ( $content, $editor ) {
	if ( ! is_admin() || 'tinymce' !== $editor ) { return $content; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) { return $content; }
	// WordPress escapes editor content for its source textarea. In this Classic
	// Editor setup that payload is escaped once more before TinyMCE consumes it,
	// which makes real tags appear as text in Visual mode. Remove one layer only.
	return html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, get_option( 'blog_charset' ) );
}, 99, 2 );
add_filter( 'tiny_mce_before_init', static function ( $settings ) {
	// Match the public blog article column as closely as possible while keeping
	// editor-only affordances (outlines and media labels) useful to authors.
	$css = 'html{background:#f3f4f6} body#tinymce{box-sizing:border-box;background:#fff;font-family:Arial,"Noto Sans","Be Vietnam Pro",sans-serif;font-size:16px;line-height:1.65;color:#212529;width:min(860px,calc(100% - 40px));min-height:100%;margin:24px auto;padding:38px 46px;box-shadow:0 2px 14px rgba(0,0,0,.09)} '
		. 'body#tinymce p{margin:0 0 1.25rem;font-size:16px;line-height:1.65;color:#212529} '
		. 'body#tinymce h2{font-size:30px;line-height:1.25;font-weight:700;color:#111;margin:2.2rem 0 1rem} '
		. 'body#tinymce h3{font-size:24px;line-height:1.3;font-weight:700;color:#111;margin:1.9rem 0 .8rem} '
		. 'body#tinymce h4{font-size:20px;line-height:1.35;font-weight:700;color:#111;margin:1.6rem 0 .7rem} '
		. 'body#tinymce blockquote{font-size:18px;line-height:1.7;margin:1.8rem 0;padding:18px 24px;border-left:4px solid #00b5e2;background:#f5f7f8;color:#333} '
		. 'body#tinymce ul,body#tinymce ol{padding-left:28px;margin:0 0 1.25rem} body#tinymce li{margin-bottom:7px;line-height:1.65} '
		. 'body#tinymce img{display:block;width:auto;max-width:100%;height:auto;margin:28px auto} '
		. 'body#tinymce img[data-mce-selected]{outline:3px solid #00b5e2} body#tinymce figure{max-width:100%;margin:28px auto} '
		. 'body#tinymce iframe,body#tinymce video{display:block;max-width:100%;margin:28px auto;background:#111;outline:2px dashed #00b5e2} '
		. 'body#tinymce strong,body#tinymce b{font-weight:700;color:#111} body#tinymce a{color:#007fa8;text-decoration:underline} '
		. 'body#tinymce hr{border:0;border-top:1px solid #ddd;margin:32px 0} body#tinymce pre{white-space:pre-wrap;background:#f6f8fa;padding:16px;border-radius:4px} '
		. '@media(max-width:700px){body#tinymce{width:100%;margin:0;padding:24px 20px;box-shadow:none}}';

	$settings['content_style']         = ( $settings['content_style'] ?? '' ) . ' ' . $css;
	$settings['toolbar1']              = 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,fullscreen,wp_adv';
	$settings['toolbar2']              = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help';
	$settings['wordpress_adv_hidden']  = false;
	$settings['block_formats']         = 'Đoạn văn=p;Tiêu đề 2=h2;Tiêu đề 3=h3;Tiêu đề 4=h4;Trích dẫn=blockquote;Định dạng trước=pre';
	$settings['paste_as_text']         = false;
	$settings['wpautop']               = true;
	$settings['resize']                = true;
	$settings['min_height']            = 620;
	return $settings;
} );

add_action( 'admin_head-post.php', static function () {
	global $post;
	if ( ! $post || 'post' !== $post->post_type ) { return; }
	?>
	<style>
	#wp-content-editor-container{border-color:#c9cdd1;box-shadow:0 1px 4px rgba(0,0,0,.06)}
	#content_ifr{min-height:680px!important;background:#f3f4f6}
	#wp-content-wrap.tmce-active:before{content:'Xem truoc noi dung bai viet';display:inline-block;margin:8px 0 0 10px;padding:4px 9px;border-radius:12px;background:#e8f8fc;color:#006b82;font:600 11px/1.4 Arial,sans-serif;letter-spacing:.02em}
	</style>
	<?php
} );

add_action( 'admin_footer-post.php', static function () {
	global $post;
	if ( ! $post || 'post' !== $post->post_type ) { return; }
	$visual_content = $post->post_content;
	?>
	<script>
	(function(){
		var viveVisualContent = <?php echo wp_json_encode( $visual_content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;
		var viveVisualLoaded = false;
		function loadVisualContent(editor){
			if (viveVisualLoaded || !editor || editor.id !== 'content') return;
			viveVisualLoaded = true;
			editor.setContent(viveVisualContent, { format: 'raw' });
			editor.nodeChanged();
			editor.setDirty(false);
		}
		if (window.jQuery) {
			jQuery(document).on('tinymce-editor-init', function(event, editor){ loadVisualContent(editor); });
		}
		window.setTimeout(function(){
			if (window.tinymce) loadVisualContent(tinymce.get('content'));
		}, 800);
		try {
			if (window.sessionStorage) {
				for (var k in sessionStorage) {
					if (k && (k.indexOf('wp-autosave') !== -1 || k.indexOf('autosave') !== -1)) {
						sessionStorage.removeItem(k);
					}
				}
			}
			if (window.localStorage) {
				for (var k in localStorage) {
					if (k && (k.indexOf('wp-autosave') !== -1 || k.indexOf('autosave') !== -1)) {
						localStorage.removeItem(k);
					}
				}
			}
			var notice = document.querySelector('.autosave-restore-notice, .wp-autosave-notice, #lost-connection-notice');
			if (notice) notice.remove();
		} catch(e){}
	})();
	</script>
	<?php
} );

function theme_vive_blog_find_post( $path ) {
	$path = trim( (string) wp_parse_url( $path, PHP_URL_PATH ), '/' );
	if ( ! $path || 0 === strpos( $path, 'tag/' ) ) { return null; }
	$posts = get_posts( array( 'post_type'=>'post', 'post_status'=>'publish', 'numberposts'=>1, 'meta_key'=>'_vive_blog_legacy_path', 'meta_value'=>$path ) );
	if ( $posts ) { return $posts[0]; }
	$posts = get_posts( array( 'post_type'=>'post', 'post_status'=>'publish', 'numberposts'=>1, 'name'=>basename( $path ) ) );
	return $posts && theme_vive_blog_is_post( $posts[0]->ID ) ? $posts[0] : null;
}

function theme_vive_blog_cards() {
	$cached = get_transient( 'theme_vive_blog_cards_v5' );
	if ( is_array( $cached ) ) { return $cached; }

	// The archive only needs four post columns. WP_Query/get_posts() hydrates
	// complete WP_Post objects, including every article's post_content; with a
	// large imported archive that can exhaust a production PHP worker before it
	// returns any HTML. Keep this listing query deliberately lightweight.
	global $wpdb;
	$posts = $wpdb->get_results( $wpdb->prepare(
		"SELECT p.ID, p.post_title, p.post_name, p.post_date
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} marker
			ON marker.post_id = p.ID AND marker.meta_key = %s AND marker.meta_value = '1'
		WHERE p.post_type = 'post' AND p.post_status = 'publish'
		ORDER BY p.post_date DESC",
		THEME_VIVE_BLOG_META
	) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$post_ids = array_map( 'intval', wp_list_pluck( $posts, 'ID' ) );
	if ( $post_ids ) { update_meta_cache( 'post', $post_ids ); }
	$cards = array();
	foreach ( $posts as $post ) {
		$terms = wp_get_post_terms( $post->ID, 'category', array( 'fields'=>'names' ) );
		$terms = array_map( static function ( $name ) {
			return html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}, $terms );
		$terms = array_values( array_filter( $terms, static function ( $name ) { return 'Blog VIVE' !== $name; } ) );
		if ( ! $terms ) { $terms = array( 'N/A' ); }
		$thumbnail_id = (int) get_post_meta( $post->ID, '_thumbnail_id', true );
		$image = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : theme_vive_blog_value( $post->ID, 'cover_url', '' );
		$image = theme_vive_blog_public_image( $image );
		$legacy_path = trim( theme_vive_blog_value( $post->ID, 'legacy_path', '' ), '/' );
		$slug = $legacy_path ?: mysql2date( 'Y/m/d', $post->post_date ) . '/' . $post->post_name;
		$title = apply_filters( 'the_title', $post->post_title, $post->ID );
		$cards[] = array( 'slug'=>$slug, 'title'=>$title ?: '', 'tags'=>$terms, 'tags_str'=>implode( ' | ', $terms ), 'author'=>theme_vive_blog_value( $post->ID, 'author', '' ), 'date'=>mysql2date( 'Y-m-d', $post->post_date ), 'read_time'=>theme_vive_blog_value( $post->ID, 'read_time', '' ), 'image'=>$image ?: '' );
	}
	set_transient( 'theme_vive_blog_cards_v5', $cards, DAY_IN_SECONDS );
	return $cards;
}

function theme_vive_blog_prepare_html( $html, $file ) {
	if ( preg_match( '#[\\/]blog\.vive\.com[\\/]us[\\/]index\.html?$#i', $file ) ) {
		$cards = theme_vive_blog_cards();
		if ( $cards ) {
			$start = strpos( $html, 'var allBlogPosts = [' );
			$end = false !== $start ? strpos( $html, '];', $start ) : false;
			if ( false !== $start && false !== $end ) {
				$replacement = 'var allBlogPosts = ' . wp_json_encode( $cards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';';
				$html = substr( $html, 0, $start ) . $replacement . substr( $html, $end + 2 );
			}
		}
		$html = str_replace( '<img src="${item.image}"', '<img loading="lazy" decoding="async" src="${item.image}"', $html );
	}
	return $html;
}

function theme_vive_blog_api_response() {
	$cards = theme_vive_blog_cards(); if ( ! $cards ) { return null; }
	$limit = isset( $_GET['limit'] ) ? max( 1, (int) $_GET['limit'] ) : 10;
	$offset = isset( $_GET['offset'] ) ? max( 0, (int) $_GET['offset'] ) : 0;
	$items = array();
	foreach ( array_slice( $cards, $offset, $limit ) as $card ) {
		$items[] = array( 'title'=>$card['title'], 'publish_date'=>$card['date'], 'x_minute_read'=>$card['read_time'], 'cover_url'=>$card['image'], 'cover_thumbnail'=>$card['image'], 'author'=>array( 'name'=>$card['author'] ), 'tags'=>array_map( static function ( $tag ) { return array( 'title'=>$tag ); }, $card['tags'] ), 'meta'=>array( 'slug'=>basename( $card['slug'] ), 'html_url'=>home_url( '/blog/' . $card['slug'] . '/' ) ) );
	}
	return array( 'meta'=>array( 'total_count'=>count( $cards ) ), 'items'=>$items );
}

function theme_vive_blog_template_file() {
	$files = glob( get_template_directory() . '/footer-sites/blog.vive.com/us/*/index.htm' );
	return $files ? $files[0] : false;
}

function theme_vive_blog_render_html( $raw, $post ) {
	$terms = wp_get_post_terms( $post->ID, 'category', array( 'fields'=>'names' ) );
	$terms = array_map( static function ( $name ) { return html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ); }, $terms );
	$terms = array_values( array_filter( $terms, static function ( $name ) { return 'Blog VIVE' !== $name; } ) );
	$image = has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'full' ) : theme_vive_blog_value( $post->ID, 'cover_url', '' );
	$author = theme_vive_blog_value( $post->ID, 'author', '' );
	$read_time = theme_vive_blog_value( $post->ID, 'read_time', '' );
	$content = theme_vive_blog_normalize_content( trim( (string) $post->post_content ) );
	$content = preg_replace( '#^\s*(?:<!--[\s\S]*?-->\s*)?\{\s*"@context"[\s\S]*?\n\s*\}\s*(?:\]\s*\}\s*)?#i', '', $content );
	$meta_html = '<div class="meta"><div class="meta-author">' . ( $author ? esc_html( $author ) . ' • ' : '' ) . esc_html( get_the_date( 'd/m/Y', $post ) ) . '</div></div>';
	$image_html = $image ? '<div class="img-wrapper text-center my-3"><img class="w-100 img-fluid" src="' . esc_url( $image ) . '" alt=""></div>' : '';
	$terms_html = $terms ? '<h4 class="article-tag">' . esc_html( implode( ' | ', $terms ) ) . '</h4>' : '';
	$read_time_html = $read_time ? '<p class="font-weight-bold">' . esc_html( $read_time ) . '</p>' : '';
	$content_html = $content ? '<div class="rich-text"><div id="modal-ready">' . apply_filters( 'the_content', $content ) . '</div></div>' : '';
	$article = '<div class="pt-4 container article-container"><a class="mt-4 back-to-button" href="' . esc_url( home_url( '/blog/' ) ) . '">&lt;&lt; Quay lại Blog</a><h1 class="mt-4">' . esc_html( get_the_title( $post ) ) . '</h1>' . $meta_html . $image_html . $terms_html . $read_time_html . $content_html . '</div>';
	$start_marker = '<div class="pt-4 container article-container">';
	$start = strpos( $raw, $start_marker );
	// New posts reuse an imported article file for the surrounding header/footer.
	// Replace the complete article section so none of the template article or
	// its unrelated-post cards can leak into a newly-created post.
	$end = false !== $start ? strpos( $raw, '</section>', $start ) : false;
	if ( false !== $start && false !== $end ) {
		return substr( $raw, 0, $start ) . $article . "\n" . substr( $raw, $end );
	}
	return $raw;
}

add_action( 'save_post_post', static function ( $post_id ) {
	if ( ! isset($_POST['theme_vive_blog_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['theme_vive_blog_nonce'])),'theme_vive_blog_save') || wp_is_post_revision($post_id) || ! current_user_can('edit_post',$post_id) || ! theme_vive_blog_is_post($post_id) ) { return; }
	update_post_meta($post_id,THEME_VIVE_BLOG_META,'1');
	foreach(array('author','read_time','cover_url','legacy_path') as $key){$field='vive_blog_'.$key;$value=isset($_POST[$field])?sanitize_text_field(wp_unslash($_POST[$field])):'';if('legacy_path'===$key){$value=trim($value,'/');}update_post_meta($post_id,'_vive_blog_'.$key,$value?:('legacy_path'===$key?'':'N/A'));}
	delete_transient( 'theme_vive_blog_cards_v2' );
	delete_transient( 'theme_vive_blog_cards_v3' );
	delete_transient( 'theme_vive_blog_cards_v4' );
	delete_transient( 'theme_vive_blog_cards_v5' );
} );

// Gutenberg-created posts do not submit the legacy blog metabox nonce. Keep
// blog membership in sync from the normalized Blog VIVE category tree.
add_action( 'save_post_post', static function ( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! $post instanceof WP_Post ) { return; }
	if ( theme_vive_blog_has_category( $post_id ) ) {
		update_post_meta( $post_id, THEME_VIVE_BLOG_META, '1' );
		if ( ! get_post_meta( $post_id, '_vive_blog_legacy_path', true ) ) {
			update_post_meta( $post_id, '_vive_blog_legacy_path', get_the_date( 'Y/m/d', $post ) . '/' . $post->post_name );
		}
		$default_category = (int) get_option( 'default_category' );
		$categories = array_map( 'intval', wp_get_post_categories( $post_id ) );
		if ( count( $categories ) > 1 && in_array( $default_category, $categories, true ) ) {
			wp_remove_object_terms( $post_id, $default_category, 'category' );
		}
	} else {
		delete_post_meta( $post_id, THEME_VIVE_BLOG_META );
	}
	delete_transient( 'theme_vive_blog_cards_v2' );
	delete_transient( 'theme_vive_blog_cards_v3' );
	delete_transient( 'theme_vive_blog_cards_v4' );
	delete_transient( 'theme_vive_blog_cards_v5' );
}, 30, 2 );

function theme_vive_blog_import() {
	$file=get_template_directory().'/inc/data/blog-articles-full.json'; $data=is_readable($file)?json_decode(file_get_contents($file),true):null; if(empty($data['items']))return array(0,0);
	$parent=theme_vive_blog_parent_category();$created=0;$updated=0;
	foreach($data['items'] as $item){$meta=$item['meta']??array();$source=!empty($meta['html_url'])?wp_parse_url($meta['html_url'],PHP_URL_PATH):'/'.($meta['slug']??'');$legacy=trim(preg_replace('#^/us(?:/|$)#i','/',(string)$source),'/');if(!$legacy)continue;
		$found=get_posts(array('post_type'=>'post','post_status'=>'any','numberposts'=>1,'meta_key'=>'_vive_blog_legacy_path','meta_value'=>$legacy));$cats=array();foreach((array)($item['tags']??array()) as $tag){$name=trim((string)($tag['title']??''));if(!$name)continue;$term=term_exists($name,'category');if(!$term)$term=wp_insert_term($name,'category',array('parent'=>$parent));if(!is_wp_error($term))$cats[]=(int)(is_array($term)?$term['term_id']:$term);}if(!$cats)$cats=array($parent);
		$postarr=array('ID'=>$found?$found[0]->ID:0,'post_type'=>'post','post_status'=>'publish','post_title'=>trim((string)($item['title']??''))?:'N/A','post_name'=>sanitize_title($meta['slug']??basename($legacy)),'post_content'=>!empty($item['content'])?$item['content']:'<p>N/A</p>','post_excerpt'=>trim((string)($meta['search_description']??''))?:'N/A','post_date'=>!empty($item['publish_date'])?$item['publish_date'].' 12:00:00':current_time('mysql'),'post_category'=>$cats);
		$id=wp_insert_post(wp_slash($postarr),true);if(is_wp_error($id))continue;$found?$updated++:$created++;update_post_meta($id,THEME_VIVE_BLOG_META,'1');update_post_meta($id,'_vive_blog_legacy_path',$legacy);update_post_meta($id,'_vive_blog_author',trim((string)($item['author']['name']??''))?:'N/A');update_post_meta($id,'_vive_blog_read_time',trim((string)($item['x_minute_read']??''))?:'N/A');update_post_meta($id,'_vive_blog_cover_url',theme_vive_blog_import_image($item));}
	// Import legacy articles that exist as snapshots but are absent from the API dump.
	$base=get_template_directory().'/footer-sites/blog.vive.com/us';
	if(is_dir($base)){
		$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));
		foreach($iterator as $snapshot){if(!$snapshot->isFile()||!preg_match('#^index\.html?$#i',$snapshot->getFilename()))continue;$relative=str_replace('\\','/',substr($snapshot->getPath(),strlen($base)+1));if(!$relative||0===strpos($relative,'tag/'))continue;
			$found=get_posts(array('post_type'=>'post','post_status'=>'any','numberposts'=>1,'meta_key'=>'_vive_blog_legacy_path','meta_value'=>$relative));if($found)continue;$html=file_get_contents($snapshot->getPathname());if(false===strpos($html,'article-container'))continue;
			$title=preg_match('#<h1[^>]*>(.*?)</h1>#is',$html,$m)?trim(wp_strip_all_tags(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'))):'N/A';$content=preg_match('#<div class="rich-text"><div id="modal-ready">(.*?)</div></div>#is',$html,$m)?$m[1]:'';
			if(empty($content)||'<p>N/A</p>'===$content||strlen(trim(strip_tags($content)))<20)continue;
			$author='N/A';if(preg_match('#class="meta-author"[^>]*>(.*?)</div>#is',$html,$m)){$author=trim(preg_split('#\s*(?:•|&bull;|â€¢)\s*#',wp_strip_all_tags($m[1]))[0])?:'N/A';}
			$read=preg_match('#<p class="font-weight-bold"[^>]*>(.*?)</p>#is',$html,$m)?trim(wp_strip_all_tags($m[1])):'N/A';$image=preg_match('#class="img-wrapper[^>]*>\s*<img[^>]+src="([^"]+)"#is',$html,$m)?html_entity_decode($m[1]):'N/A';
			$cats=array();if(preg_match_all('#class="article-tag"[^>]*>(.*?)</a>#is',$html,$matches)){foreach($matches[1] as $raw_name){$name=trim(wp_strip_all_tags(html_entity_decode($raw_name,ENT_QUOTES|ENT_HTML5,'UTF-8')));if(!$name)continue;$term=term_exists($name,'category');if(!$term)$term=wp_insert_term($name,'category',array('parent'=>$parent));if(!is_wp_error($term))$cats[]=(int)(is_array($term)?$term['term_id']:$term);}}if(!$cats)$cats=array($parent);
			$date=preg_match('#^(\d{4})/(\d{2})/(\d{2})/#',$relative,$m)?$m[1].'-'.$m[2].'-'.$m[3].' 12:00:00':current_time('mysql');$id=wp_insert_post(wp_slash(array('post_type'=>'post','post_status'=>'publish','post_title'=>$title,'post_name'=>sanitize_title(basename($relative)),'post_content'=>$content,'post_excerpt'=>'N/A','post_date'=>$date,'post_category'=>$cats)),true);if(is_wp_error($id))continue;$created++;update_post_meta($id,THEME_VIVE_BLOG_META,'1');update_post_meta($id,'_vive_blog_legacy_path',$relative);update_post_meta($id,'_vive_blog_author',$author);update_post_meta($id,'_vive_blog_read_time',$read?:'N/A');update_post_meta($id,'_vive_blog_cover_url',$image?:'N/A');
		}
	}
	delete_transient( 'theme_vive_blog_cards_v2' );delete_transient( 'theme_vive_blog_cards_v3' );delete_transient( 'theme_vive_blog_cards_v4' );delete_transient( 'theme_vive_blog_cards_v5' );update_option('theme_vive_blog_imported_v1',time(),false);return array($created,$updated);
}

function theme_vive_blog_translate_database() {
	$posts=get_posts(array('post_type'=>'post','post_status'=>'any','numberposts'=>-1,'meta_key'=>THEME_VIVE_BLOG_META,'meta_value'=>'1'));$updated=0;$card_titles=array();$index_file=get_template_directory().'/footer-sites/blog.vive.com/us/index.htm';
	if(is_readable($index_file)){$index=file_get_contents($index_file);$start=strpos($index,'var allBlogPosts = [');$end=false!==$start?strpos($index,'];',$start):false;if(false!==$start&&false!==$end){$json=substr($index,$start+strlen('var allBlogPosts = '),$end+1-($start+strlen('var allBlogPosts = ')));$cards=json_decode($json,true);foreach((array)$cards as $card){if(!empty($card['slug'])&&!empty($card['title']))$card_titles[trim($card['slug'],'/')]=$card['title'];}}}
	foreach($posts as $post){$legacy=trim((string)get_post_meta($post->ID,'_vive_blog_legacy_path',true),'/');$title=!empty($card_titles[$legacy])?$card_titles[$legacy]:trim(wp_strip_all_tags(theme_vive_translate_html('<span>'.$post->post_title.'</span>')));$content=theme_vive_translate_html($post->post_content);$excerpt=theme_vive_translate_html($post->post_excerpt);if($title!==$post->post_title||$content!==$post->post_content||$excerpt!==$post->post_excerpt){wp_update_post(wp_slash(array('ID'=>$post->ID,'post_title'=>$title?:'N/A','post_content'=>$content?:'<p>N/A</p>','post_excerpt'=>$excerpt?:'N/A')));$updated++;}}
	$parent=theme_vive_blog_parent_category();foreach(get_term_children($parent,'category') as $term_id){$term=get_term($term_id,'category');if(!$term||is_wp_error($term))continue;$translated=trim(wp_strip_all_tags(theme_vive_translate_html('<span>'.$term->name.'</span>')));if($translated&&$translated!==$term->name)wp_update_term($term_id,'category',array('name'=>$translated));}
	delete_transient('theme_vive_blog_cards_v2');delete_transient('theme_vive_blog_cards_v3');delete_transient('theme_vive_blog_cards_v4');delete_transient('theme_vive_blog_cards_v5');update_option('theme_vive_blog_translated_v1',time(),false);return $updated;
}

add_action( 'admin_init', static function () { if(!get_option('theme_vive_blog_imported_v1')&&current_user_can('manage_options'))theme_vive_blog_import(); } );
