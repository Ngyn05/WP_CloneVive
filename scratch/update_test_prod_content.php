define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';

$p = wc_get_product(2688);
if ($p) {
    $p->set_description('<p>Đây là sản phẩm mẫu vừa được tạo trong hệ thống quản trị WooCommerce của VIVE Việt Nam.</p><p>Trang chi tiết sản phẩm này được render tự động với đầy đủ thông tin tên, giá, mã SKU, trạng thái còn hàng, danh mục và form đặt hàng.</p>');
    $p->set_short_description('Sản phẩm thử nghiệm VIVE Demo chất lượng cao.');
    $p->save();
    echo "Updated product 2688 successfully!\n";
} else {
    echo "Product 2688 not found.\n";
}
