<?php
/** Single WooCommerce product rendered from the product database record. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;
$current_id = get_the_ID();
$slug = $post ? $post->post_name : '';
if ( ! $slug && $current_id ) {
	$slug = get_post_field( 'post_name', $current_id );
}
$shop_slug = get_post_meta( $current_id, THEME_VIVE_PRODUCT_META, true ) ?: $slug;
if ( $shop_slug ) {
	wp_safe_redirect( home_url( '/shop/products/' . $shop_slug . '/' ), 301 );
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : false;
	if ( ! $product ) { continue; }

	$image_id  = $product->get_image_id();
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	$terms      = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields'=>'names' ) );
	$highlights = get_post_meta( $product->get_id(), '_theme_vive_highlights', true );
	$specs      = get_post_meta( $product->get_id(), '_theme_vive_specs', true );
	$compatible = get_post_meta( $product->get_id(), '_theme_vive_compatibility', true );
	if ( ! $terms ) { $terms = array( 'N/A' ); }
	if ( ! is_array( $highlights ) || ! $highlights ) { $highlights = array( 'N/A' ); }
	if ( ! is_array( $specs ) || ! $specs ) { $specs = array( 'Thông tin'=>'N/A' ); }
	if ( ! $compatible ) { $compatible = 'N/A'; }
	if ( ! $product->get_short_description() ) { $product->set_short_description( 'N/A' ); }
	if ( ! $product->get_description() ) { $product->set_description( 'N/A' ); }
	$review_count = $product->get_review_count();
	$average_rating = (float) $product->get_average_rating();
	$stock_status = $product->is_in_stock()
		? ( $product->backorders_allowed() ? 'Cho phép đặt trước' : 'Còn hàng' )
		: 'Hết hàng';
	$reviews = get_comments( array( 'post_id'=>$product->get_id(), 'status'=>'approve', 'type'=>'review', 'orderby'=>'comment_date_gmt', 'order'=>'DESC' ) );
	$back_url   = get_post_meta( $product->get_id(), '_theme_vive_detail_path', true );
	$back_url   = $back_url ? home_url( $back_url ) : home_url( '/product/' );
	?>
	<main class="vive-woo-product" id="main-content">
		<div class="vive-woo-product__bar">
			<a class="vive-woo-product__back" href="<?php echo esc_url( $back_url ); ?>">&larr; Quay lại trang giới thiệu</a>
			<a class="vive-woo-product__catalogue" href="<?php echo esc_url( home_url( '/product/' ) ); ?>">Tất cả sản phẩm</a>
		</div>

		<article class="vive-woo-product__card">
			<div class="vive-woo-product__gallery<?php echo $image_url ? '' : ' vive-woo-product__gallery--empty'; ?>">
				<?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>"><?php else : ?><span>N/A</span><?php endif; ?>
			</div>
			<div class="vive-woo-product__summary">
				<?php if ( $terms ) : ?>
					<div class="vive-woo-product__category"><?php echo esc_html( implode( ' · ', $terms ) ); ?></div>
				<?php endif; ?>
				<h1><?php echo esc_html( $product->get_name() ); ?></h1>
				<a class="vive-woo-product__rating" href="#danh-gia" aria-label="Xem đánh giá sản phẩm">
					<span class="vive-woo-product__stars" aria-hidden="true">★★★★★</span>
					<strong><?php echo esc_html( $review_count ? number_format_i18n( $average_rating, 1 ) : 'Chưa có' ); ?></strong>
					<span><?php echo esc_html( $review_count ? sprintf( '(%d đánh giá)', $review_count ) : '— Hãy là người đầu tiên đánh giá' ); ?></span>
				</a>
				<?php if ( $product->get_sku() ) : ?>
					<div class="vive-woo-product__sku">Mã sản phẩm: <?php echo esc_html( $product->get_sku() ); ?></div>
				<?php else : ?>
					<div class="vive-woo-product__sku">Mã sản phẩm: <?php echo esc_html( 'VIVE-' . str_pad( (string) $product->get_id(), 6, '0', STR_PAD_LEFT ) ); ?></div>
				<?php endif; ?>
				<?php if ( $product->get_short_description() ) : ?>
					<div class="vive-woo-product__short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>
				<div class="vive-woo-product__stock" style="margin: 16px 0 12px; font-size: 16px;">Tình trạng: <strong style="color: #0d8a4f; display: inline-flex; align-items: center; gap: 4px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#e6f4ea;color:#0d8a4f;font-size:12px;">✓</span> Còn hàng</strong></div>

				<?php if ( 'vive-eagle' === $product->get_slug() || 'vive-eagle' === get_post_meta( $product->get_id(), THEME_VIVE_PRODUCT_META, true ) ) : ?>
				<div class="sidebar-widget sidebar-widget--outline" style="margin: 20px 0; padding: 18px; background: #f9fbfc; border: 1px solid #e1e8ed; border-radius: 6px;">
					<span class="sidebar-widget__title" style="font-weight: 700; font-size: 15px; display: block; margin-bottom: 14px; color: #111;">Chọn các biến thể</span>
					<div class="sidebar-widget__content">
						<div class="product-variant" style="margin-bottom: 16px;">
							<label for="vive-eagle-style-select" class="product-variant__name" style="font-weight: 700; display: block; margin-bottom: 6px; font-size: 14px; color: #222;">Chọn kiểu dáng (Style)</label>
							<select class="kt-select product-variant__container--select" id="vive-eagle-style-select" style="box-sizing: border-box; width: 100%; padding: 10px 14px; border: 1px solid #c8d2d6; border-radius: 4px; font-size: 14px; background: #fff; cursor: pointer;">
								<option value="Square (Vuông hiện đại)" selected>Gọng Square (Vuông hiện đại)</option>
								<option value="Pantos (Tròn cổ điển)">Gọng Pantos (Tròn cổ điển)</option>
							</select>
						</div>

						<div class="product-variant" style="margin-bottom: 16px;">
							<label for="vive-eagle-lens-select" class="product-variant__name" style="font-weight: 700; display: block; margin-bottom: 6px; font-size: 14px; color: #222;">Chọn tròng kính (Lens)</label>
							<select class="kt-select product-variant__container--select" id="vive-eagle-lens-select" style="box-sizing: border-box; width: 100%; padding: 10px 14px; border: 1px solid #c8d2d6; border-radius: 4px; font-size: 14px; background: #fff; cursor: pointer;">
								<option value="Transitions (Đổi màu)" selected>Tròng đổi màu Transitions (Cao cấp)</option>
								<option value="Sunglasses (Kính râm)">Kính râm chống nắng Polarized</option>
								<option value="Clear Lens (Trong suốt)">Tròng trong suốt (Lọc ánh sáng xanh)</option>
							</select>
						</div>

						<div class="product-variant" style="margin-bottom: 10px;">
							<span class="product-variant__name" style="font-weight: 700; display: block; margin-bottom: 8px; font-size: 14px; color: #222;">Chọn màu sắc (Color)</span>
							<div class="product-variant__container--radio" style="display: flex; gap: 14px; align-items: center; margin-bottom: 6px;">
								<div class="product-variant__item--color">
									<input type="radio" name="product-color-single" id="single-color-1" value="Đen (Black)" checked style="display: none;">
									<label for="single-color-1" style="cursor: pointer; display: inline-flex; align-items: center;">
										<span class="single-swatch" data-val="Đen (Black)" style="display: inline-block; width: 36px; height: 36px; border-radius: 50%; background-color: rgb(0, 0, 0); border: 2px solid #00b5e2; box-shadow: 0 0 0 3px rgba(0,181,226,.35); transition: transform .2s, border-color .2s;"></span>
									</label>
								</div>
								<div class="product-variant__item--color">
									<input type="radio" name="product-color-single" id="single-color-2" value="Đen than (Charcoal Black)" style="display: none;">
									<label for="single-color-2" style="cursor: pointer; display: inline-flex; align-items: center;">
										<span class="single-swatch" data-val="Đen than (Charcoal Black)" style="display: inline-block; width: 36px; height: 36px; border-radius: 50%; background-color: rgb(33, 33, 33); border: 2px solid #ddd; transition: transform .2s, border-color .2s;"></span>
									</label>
								</div>
								<div class="product-variant__item--color">
									<input type="radio" name="product-color-single" id="single-color-3" value="Nâu trong suốt (Clear Brown)" style="display: none;">
									<label for="single-color-3" style="cursor: pointer; display: inline-flex; align-items: center;">
										<span class="single-swatch" data-val="Nâu trong suốt (Clear Brown)" style="display: inline-block; width: 36px; height: 36px; border-radius: 50%; background-color: rgb(196, 164, 132); border: 2px solid #ddd; transition: transform .2s, border-color .2s;"></span>
									</label>
								</div>
							</div>
							<span style="display: block; font-size: 13px; color: #666;">Đang chọn: <strong id="single-color-name" style="color: #111;">Đen (Black)</strong></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<div class="product-quantity quantity-selector-holder" style="display: inline-flex; align-items: center; gap: 8px; margin: 16px 0 20px;">
					<span style="font-weight: 700; font-size: 14px; color: #111; margin-right: 8px;">Số lượng:</span>
					<button type="button" class="qty-button qty-minus" style="display:inline-flex;align-items:center;justify-content:center;width: 38px; height: 38px; border: 1px solid #c8d2d6; background: #fff; border-radius: 4px; cursor: pointer; font-size: 18px; font-weight:700;" onclick="var q=this.nextElementSibling; if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;">−</button>
					<input type="number" name="quantity" value="1" min="1" max="99" class="qty qty-selector product__quantity" style="width: 64px; height: 38px; text-align: center; border: 1px solid #c8d2d6; border-radius: 4px; font-size: 16px; font-weight: 700; background:#fff;">
					<button type="button" class="qty-button qty-plus" style="display:inline-flex;align-items:center;justify-content:center;width: 38px; height: 38px; border: 1px solid #c8d2d6; background: #fff; border-radius: 4px; cursor: pointer; font-size: 18px; font-weight:700;" onclick="var q=this.previousElementSibling; q.value=parseInt(q.value)+1;">+</button>
				</div>

				<div class="vive-woo-product__actions">
					<a class="vive-woo-product__action vive-woo-product__action--zalo" href="https://zalo.me/0917834532" target="_blank" rel="noopener noreferrer">TƯ VẤN QUA ZALO</a>
					<a class="vive-woo-product__action vive-woo-product__action--buy" href="#dat-hang" data-theme-vive-buy-slug="<?php echo esc_attr( get_post_meta( $product->get_id(), THEME_VIVE_PRODUCT_META, true ) ?: $product->get_slug() ); ?>" data-theme-vive-buy-mode="popup">MUA NGAY</a>
				</div>
				<form class="vive-woo-product__callback" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-vive-callback-form>
					<input type="hidden" name="action" value="theme_vive_submit_callback">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'theme_vive_submit_callback' ) ); ?>">
					<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
					<strong>Hãy để lại số điện thoại, chúng tôi sẽ gọi tư vấn miễn phí!</strong>
					<div><input name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Nhập số điện thoại..." required><button type="submit">Gửi đi</button></div>
					<p class="vive-woo-product__callback-message" aria-live="polite"></p>
				</form>
			</div>
		</article>

		<div class="vive-woo-product__tabs" data-product-tabs>
			<div class="vive-woo-product__tab-list" role="tablist" aria-label="Nội dung sản phẩm">
				<button type="button" role="tab" aria-selected="true" aria-controls="thong-tin-san-pham" id="tab-thong-tin-san-pham" data-product-tab="thong-tin-san-pham">Thông tin sản phẩm</button>
				<?php if ( is_array( $specs ) && $specs ) : ?><button type="button" role="tab" aria-selected="false" aria-controls="thong-so-ky-thuat" id="tab-thong-so-ky-thuat" data-product-tab="thong-so-ky-thuat">Thông số kỹ thuật</button><?php endif; ?>
				<button type="button" role="tab" aria-selected="false" aria-controls="danh-gia" id="tab-danh-gia" data-product-tab="danh-gia">Đánh giá <span><?php echo esc_html( $review_count ); ?></span></button>
			</div>

			<section class="vive-woo-product__tab-panel is-active" id="thong-tin-san-pham" role="tabpanel" aria-labelledby="tab-thong-tin-san-pham">
		<?php if ( $product->get_description() ) : ?>
			<div class="vive-woo-product__description">
				<h2>Thông tin sản phẩm</h2>
				<div><?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $highlights ) && $highlights ) : ?>
			<div class="vive-woo-product__details">
				<h2>Điểm nổi bật</h2>
				<div class="vive-woo-product__highlights">
					<?php foreach ( $highlights as $highlight ) : ?>
						<div class="vive-woo-product__highlight"><span aria-hidden="true">✓</span><?php echo esc_html( $highlight ); ?></div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $compatible ) : ?>
			<div class="vive-woo-product__details vive-woo-product__compatibility">
				<h2>Khả năng tương thích</h2>
				<p><?php echo esc_html( $compatible ); ?></p>
			</div>
		<?php endif; ?>
			</section>

		<?php if ( is_array( $specs ) && $specs ) : ?>
			<section class="vive-woo-product__tab-panel" id="thong-so-ky-thuat" role="tabpanel" aria-labelledby="tab-thong-so-ky-thuat" hidden>
				<h2>Thông số kỹ thuật</h2>
				<dl class="vive-woo-product__specs">
					<?php foreach ( $specs as $label=>$value ) : ?>
						<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			</section>
		<?php endif; ?>

		<section class="vive-woo-product__reviews vive-woo-product__tab-panel" id="danh-gia" role="tabpanel" aria-labelledby="tab-danh-gia" hidden>
			<div class="vive-woo-product__review-heading">
				<div>
					<div class="vive-woo-product__eyebrow">Ý kiến khách hàng</div>
					<h2>Đánh giá sản phẩm</h2>
				</div>
				<div class="vive-woo-product__score">
					<strong><?php echo esc_html( $review_count ? number_format_i18n( $average_rating, 1 ) : '0.0' ); ?></strong>
					<div><span aria-hidden="true">★★★★★</span><small><?php echo esc_html( sprintf( '%d đánh giá', $review_count ) ); ?></small></div>
				</div>
			</div>

			<?php if ( $reviews ) : ?>
				<div class="vive-woo-product__review-list">
					<?php foreach ( $reviews as $review ) : $rating = (int) get_comment_meta( $review->comment_ID, 'rating', true ); ?>
						<article class="vive-woo-product__review">
							<div class="vive-woo-product__review-avatar"><?php echo get_avatar( $review, 52 ); ?></div>
							<div>
								<div class="vive-woo-product__review-top"><strong><?php echo esc_html( get_comment_author( $review ) ); ?></strong><time datetime="<?php echo esc_attr( get_comment_date( 'c', $review ) ); ?>"><?php echo esc_html( get_comment_date( 'd/m/Y', $review ) ); ?></time></div>
								<div class="vive-woo-product__review-stars" aria-label="<?php echo esc_attr( sprintf( '%d trên 5 sao', $rating ) ); ?>"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
								<div class="vive-woo-product__review-text"><?php echo wp_kses_post( wpautop( $review->comment_content ) ); ?></div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="vive-woo-product__empty-review">Chưa có đánh giá nào. Hãy chia sẻ trải nghiệm của bạn về sản phẩm này.</div>
			<?php endif; ?>

			<?php if ( comments_open( $product->get_id() ) ) : ?>
				<div class="vive-woo-product__review-form">
					<?php
					comment_form( array(
						'title_reply'=>'Viết đánh giá của bạn',
						'title_reply_before'=>'<h3 id="reply-title" class="comment-reply-title">',
						'title_reply_after'=>'</h3>',
						'label_submit'=>'Gửi đánh giá',
						'comment_notes_before'=>'<p class="comment-notes">Email của bạn sẽ không được công khai.</p>',
						'comment_field'=>'<p class="comment-form-rating"><label for="rating">Mức đánh giá *</label><select name="rating" id="rating" required><option value="">Chọn số sao</option><option value="5">5 sao — Tuyệt vời</option><option value="4">4 sao — Tốt</option><option value="3">3 sao — Bình thường</option><option value="2">2 sao — Chưa tốt</option><option value="1">1 sao — Không hài lòng</option></select></p><p class="comment-form-comment"><label for="comment">Nhận xét *</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
					), $product->get_id() );
					?>
				</div>
			<?php endif; ?>
		</section>
		</div>
		<?php
		if ( 'vive-eagle' === $product->get_slug() || 'vive-eagle' === get_post_meta( $product->get_id(), THEME_VIVE_PRODUCT_META, true ) ) {
			include get_template_directory() . '/inc/eagle-product-sections.php';
		}
		?>
	</main>
	<script>
	(function(){
		var root=document.querySelector('[data-product-tabs]');
		if(!root)return;
		var tabs=Array.prototype.slice.call(root.querySelectorAll('[data-product-tab]'));
		var panels=Array.prototype.slice.call(root.querySelectorAll('.vive-woo-product__tab-panel'));
		function openTab(id,scroll){
			tabs.forEach(function(tab){var active=tab.getAttribute('data-product-tab')===id;tab.setAttribute('aria-selected',active?'true':'false');tab.classList.toggle('is-active',active);});
			panels.forEach(function(panel){var active=panel.id===id;panel.hidden=!active;panel.classList.toggle('is-active',active);});
			if(scroll)root.scrollIntoView({behavior:'smooth',block:'start'});
		}
		tabs.forEach(function(tab){tab.addEventListener('click',function(){openTab(tab.getAttribute('data-product-tab'),false);});});
		document.querySelectorAll('a[href="#danh-gia"]').forEach(function(link){link.addEventListener('click',function(event){event.preventDefault();openTab('danh-gia',true);});});
		if(location.hash==='#danh-gia')openTab('danh-gia',false);
	})();
	</script>
	<script>window.themeViveProductOrder=<?php echo wp_json_encode( array( 'products'=>theme_vive_catalogue_products(), 'endpoint'=>admin_url( 'admin-post.php' ), 'nonce'=>wp_create_nonce( 'theme_vive_submit_order' ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;</script>
	<script src="<?php echo esc_url( get_template_directory_uri() . '/inc/product-order.js?v=' . ( file_exists( get_template_directory() . '/inc/product-order.js' ) ? filemtime( get_template_directory() . '/inc/product-order.js' ) : '20260907' ) ); ?>"></script>
	<script>
	(function(){
		var form=document.querySelector('[data-vive-callback-form]');
		if(!form)return;
		form.addEventListener('submit',function(event){
			event.preventDefault();
			var button=form.querySelector('button'),message=form.querySelector('.vive-woo-product__callback-message');
			button.disabled=true;button.textContent='Đang gửi…';message.className='vive-woo-product__callback-message';message.textContent='';
			fetch(form.getAttribute('action'),{method:'POST',body:new FormData(form),credentials:'same-origin'}).then(function(response){return response.text().then(function(text){try{return JSON.parse(text);}catch(error){throw new Error('Máy chủ trả về lỗi '+response.status+'.');}});}).then(function(result){
				message.textContent=result.data&&result.data.message?result.data.message:'Đã gửi yêu cầu.';
				if(!result.success)message.classList.add('is-error');else form.querySelector('[name="phone"]').value='';
			}).catch(function(error){message.textContent=error&&error.message?error.message:'Không thể gửi yêu cầu. Vui lòng thử lại.';message.classList.add('is-error');}).finally(function(){button.disabled=false;button.textContent='Gửi đi';});
		});
	})();
	</script>
	<script>
	(function(){
		var swatches = document.querySelectorAll('.single-swatch');
		var nameLabel = document.getElementById('single-color-name');
		swatches.forEach(function(swatch){
			swatch.parentElement.addEventListener('click', function(){
				swatches.forEach(function(s){
					s.style.borderColor = '#ddd';
					s.style.boxShadow = 'none';
					s.style.transform = 'none';
				});
				swatch.style.borderColor = '#00b5e2';
				swatch.style.boxShadow = '0 0 0 3px rgba(0,181,226,.35)';
				swatch.style.transform = 'scale(1.1)';
				if (nameLabel) nameLabel.textContent = swatch.getAttribute('data-val');
			});
		});
	})();
	</script>
	<?php
endwhile;

get_footer();
