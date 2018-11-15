<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>


<div class="product-list order-summary woocommerce-checkout-review-order-table">
	<div class="section-heading">
		<h4>Order Summary</h4>
	</div>
	

		<?php
			do_action( 'woocommerce_review_order_before_cart_contents' );
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					?>
					<div class="container-nested row item">

						<h3>
							<span class="poppins">	<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?></span>
						</h3>

						<div class="quantity">
							<h4><?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', sprintf($cart_item['quantity'] ), $cart_item, $cart_item_key ); ?></h4>
						</div>
							
						<div class="price">
							<h4><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?></h4>
						</div>

					</div>
					<?php
				}
			}

			do_action( 'woocommerce_review_order_after_cart_contents' );
		?>



		<div class="container-nested row subtotal">
			<h4 class="heading">Subtotal</h4>
			<h4 class="price"><?php wc_cart_totals_subtotal_html(); ?></h4>
		</div>


		<div class="container-nested row delivery">
			<h4 class="heading">
				Delivery
			</h4>

			<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

				<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

				<?php wc_cart_totals_shipping_html(); ?>

				<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

			<?php endif; ?>

		</div>

		<div class="container-nested row total">
			<h3 class="heading">
				Total
			</h3>
		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
			<h3 class="price poppins">
				<?php wc_cart_totals_order_total_html(); ?>
			</h3>
				<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
		</div>

</div>