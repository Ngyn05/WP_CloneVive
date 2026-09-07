<?php
/**
 * Clean VIVE order summary for WooCommerce emails.
 * Prices and product images are intentionally omitted.
 *
 * @package Theme_Vive
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

$order_url = $sent_to_admin ? $order->get_edit_order_url() : '';
?>
<div style="margin:30px 0 36px">
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0;border:1px solid #dfe4e7;border-radius:10px;overflow:hidden;background:#fff">
		<tr>
			<td colspan="2" style="padding:20px 22px;background:#111;color:#fff;border-top:4px solid #00b5e2">
				<div style="font-size:12px;line-height:1.4;letter-spacing:1.4px;color:#00b5e2;font-weight:700">VIVE VIỆT NAM</div>
				<div style="margin-top:5px;font-size:22px;line-height:1.35;font-weight:700">
					<?php echo esc_html( sprintf( 'Tóm tắt đơn hàng #%s', $order->get_order_number() ) ); ?>
				</div>
				<div style="margin-top:5px;color:#c8cdd0;font-size:13px">
					<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="col" style="padding:14px 22px;background:#f4f7f8;color:#60676c;text-align:left;font-size:13px;border-bottom:1px solid #dfe4e7">Sản phẩm</th>
			<th scope="col" style="padding:14px 22px;background:#f4f7f8;color:#60676c;text-align:center;font-size:13px;border-bottom:1px solid #dfe4e7;width:90px">Số lượng</th>
		</tr>
		<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
			<?php
			$product = $item->get_product();
			$sku     = $product ? $product->get_sku() : '';
			?>
			<tr>
				<td style="padding:17px 22px;border-bottom:1px solid #edf0f2;vertical-align:top">
					<div style="font-size:15px;line-height:1.5;font-weight:700;color:#202124"><?php echo esc_html( $item->get_name() ); ?></div>
					<?php if ( $sent_to_admin && $sku ) : ?>
						<div style="margin-top:3px;color:#747b80;font-size:12px"><?php echo esc_html( 'Mã sản phẩm: ' . $sku ); ?></div>
					<?php endif; ?>
					<?php
					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );
					wc_display_item_meta( $item, array(
						'label_before' => '<strong style="color:#60676c">',
						'label_after'  => ':</strong> ',
						'separator'    => '<br>',
					) );
					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
					?>
				</td>
				<td style="padding:17px 22px;border-bottom:1px solid #edf0f2;text-align:center;vertical-align:top;font-size:15px;font-weight:700;color:#202124">×<?php echo esc_html( $item->get_quantity() ); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if ( $order->get_customer_note() ) : ?>
			<tr>
				<td colspan="2" style="padding:17px 22px;background:#f9fbfb;color:#404548;line-height:1.55">
					<strong style="display:block;margin-bottom:4px">Ghi chú của khách hàng</strong>
					<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?>
				</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php if ( $order_url ) : ?>
		<p style="margin:20px 0 0">
			<a href="<?php echo esc_url( $order_url ); ?>" style="display:inline-block;padding:12px 20px;border-radius:5px;background:#00b5e2;color:#fff;font-weight:700;text-decoration:none">Xem đơn hàng trong quản trị</a>
		</p>
	<?php endif; ?>
</div>
<?php
do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
