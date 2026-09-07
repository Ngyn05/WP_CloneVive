<?php
/** Accessible 404 response for routes that are not present in the mirror. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="theme-vive-error-page">
	<h1>Không tìm thấy trang</h1>
	<p>Đường dẫn bạn truy cập không tồn tại hoặc đã được thay đổi.</p>
	<nav aria-label="Liên kết hỗ trợ">
		<a class="theme-vive-error-page__primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a>
		<a href="<?php echo esc_url( home_url( '/product/' ) ); ?>">Xem sản phẩm</a>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Xem bài viết</a>
	</nav>
</main>
<?php get_footer(); ?>
