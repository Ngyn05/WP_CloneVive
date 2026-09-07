<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../wp-load.php';

try {
    $test_p = theme_vive_find_product('test');
    if (!$test_p) {
        $p = new WC_Product_Simple();
        $p->set_name('Sản phẩm thử nghiệm VIVE Demo');
        $p->set_slug('test');
        $p->set_status('publish');
        $p->set_regular_price('9990000');
        $p->set_price('9990000');
        $p->set_short_description('Đây là sản phẩm mẫu vừa được tạo thử nghiệm trong WooCommerce để kiểm tra template mẫu tự động.');
        $p->set_description('<h2>Đặc điểm nổi bật</h2><p>Sản phẩm này chứng minh rằng bất kỳ khi nào bạn tạo mới một sản phẩm trong WordPress Admin (với tiêu đề, giá, hình ảnh và mô tả), trang chi tiết sản phẩm <strong>/shop/products/{slug}/</strong> sẽ tự động nhận diện và hiển thị đầy đủ giao diện chuẩn VIVE với đầy đủ nút Mua ngay, Tư vấn Zalo và form liên hệ.</p>');
        $id = $p->save();
        update_post_meta($id, THEME_VIVE_PRODUCT_META, 'test');
        echo "Created test product with ID: " . $id . "\n";
    } else {
        echo "Test product already exists with ID: " . $test_p->get_id() . "\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString();
}
