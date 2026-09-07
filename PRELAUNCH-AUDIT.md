# VIVE Việt Nam — biên bản hậu kiểm trước khi đưa lên host

Ngày kiểm tra local: 2026-09-03  
Nguồn tiêu chí: `SOP_Hau_Trien_Khai_Website_Cho_Nhan_Vien.pdf` và `Huong_dan_kiem_tra_SEO_GEO_website_truoc_ban_giao_cho_dev.pdf`.

## Đã hoàn thành trên local

- Router trả 404 thật cho URL không tồn tại; không còn chuyển hướng mềm về trang sản phẩm.
- Cho WooCommerce tự render cửa hàng, sản phẩm, danh mục sản phẩm, giỏ hàng, thanh toán và tài khoản.
- Sitemap chỉ giữ các URL công khai mà website render được; bỏ trang WooCommerce riêng tư, bản ghi hỏng, taxonomy bài viết và author archive không dùng.
- Mỗi URL trong sitemap có đúng một title, meta description và canonical; không có URL noindex trong sitemap.
- Chuẩn hóa Open Graph/Twitter image, metadata riêng cho `/blog/`, và mô tả cho product category.
- Loại JSON-LD cũ bị dịch sai; dùng schema graph hợp lệ do Yoast tạo.
- Bổ sung H1 truy cập được cho template bài viết cũ không có heading.
- Gỡ GTM/Google Analytics của bản snapshot nguồn để không gửi dữ liệu sang tài khoản bên thứ ba.
- Gỡ script video sai MIME, thêm tương thích jQuery cho bundle cũ, sửa tràn ngang mobile và thêm favicon/404 template.
- PHP lint: 19 tệp, 0 lỗi cú pháp. Không phát hiện file secret/backup/database trong theme và không thấy mẫu PHP thực thi nguy hiểm phổ biến.

## Kết quả crawler local cuối

- Sitemap index: HTTP 200, 4 sitemap con.
- 200 URL được khai báo: 200 URL HTTP 200, 0 redirect, 0 lỗi.
- 0 thiếu title, 0 thiếu description, 0 thiếu canonical, 0 thiếu H1.
- 0 thiếu Open Graph image, 0 noindex, 0 JSON-LD sai cú pháp.
- URL ngẫu nhiên không tồn tại: HTTP 404.
- Kiểm tra Chrome ở 390 px và 1440 px: các trang chính, shop, sản phẩm, blog và 404 render đúng; không còn tràn ngang ở mẫu mobile sau bản sửa cuối.

## Việc bắt buộc làm sau khi đưa lên host

1. Đổi `home`/`siteurl` sang domain HTTPS chính thức, buộc HTTP → HTTPS và thống nhất www/non-www bằng 301.
2. Đặt `WP_ENVIRONMENT_TYPE=production`; vào Yoast SEO chạy **Tối ưu hóa dữ liệu SEO** để tạo indexables trên production.
3. Xóa toàn bộ cache WordPress/PHP/CDN, lưu lại Permalinks một lần, rồi quét lại sitemap bằng domain thật.
4. Tạo Google Search Console Domain Property, xác minh DNS, gửi `sitemap_index.xml`, kiểm tra URL Inspection cho trang chủ/sản phẩm/blog.
5. Cài GA4 hoặc GTM của chủ website; kiểm thử page_view, form/contact và các sự kiện WooCommerce cần dùng. Không tái sử dụng mã đo lường từ website nguồn.
6. Kiểm tra TLS, security headers, WAF/rate limit, backup và phục hồi; gửi thử SMTP, form, tài khoản, giỏ hàng/thanh toán trên môi trường thật.
7. Chạy PageSpeed Insights, Rich Results Test, schema validator và social-share debugger bằng URL public; theo dõi Coverage/Core Web Vitals sau khi Google crawl.
8. Rà soát nội dung pháp lý/doanh nghiệp thực tế (đơn vị sở hữu, địa chỉ, MST, chính sách bảo mật/đổi trả/bảo hành) trước khi công khai. Không điền dữ liệu giả trên local.

## Công cụ kiểm tra kèm theme

- `tools/audit-http.mjs`: quét sitemap, HTTP status và SEO HTML.
- `tools/audit-inline-scripts.mjs`: kiểm tra cú pháp JavaScript inline.
- `tools/audit-local-site.php`: kiểm kê dữ liệu WordPress khi chạy bằng Local shell có đủ extension MySQL.
- `tools/sync-sitemap-visibility.php`: đồng bộ cờ loại khỏi sitemap cho bản ghi không render được.
