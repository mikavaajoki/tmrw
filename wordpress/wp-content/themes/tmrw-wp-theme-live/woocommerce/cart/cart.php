<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>


<!-- This is the same as product list div -->
 <form class="woocommerce-cart-form product-list basket" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>
			<div class="section-heading"><h4>Basket</h4></div>


			<?php do_action( 'woocommerce_before_cart_contents' ); ?>




<!-- Content Row Start -->
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<div class="container-nested row item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<div class="name">



						<!-- Product Name -->


		<!-- 			<h3>
						Volume N<sup>o</sup> 27<br>
						<span class="poppins">The Innovation Issue</span>
					</h3>
		-->
						<h3>
							<span class="poppins" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>' ) );
						}
						?>
							</span>
						</h3>

					</div>


	<!-- 				<div class="quantity">
						<label class="screen-reader-text" for="">Quantity</label>
						<input type="button" value="-" class="quantity-button minus">
						<input type="text" id="" class="quantity-counter" step="1" min="1" max="100" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" aria-labelledby="">
						<input type="button" value="+" class="quantity-button plus">
					</div> -->


						<!-- Product Quantity -->


						<?php
						if ( $_product->is_sold_individually() ) {
							$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$product_quantity = woocommerce_quantity_input( array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $_product->get_max_purchase_quantity(),
								'min_value'    => '0',
								'product_name' => $_product->get_name(),
							), $_product, false );
						}

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>







						<div class="price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
							<h4>
								<?php
									echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
								?>
							</h4>
						</div>




						<div class="remove">
							<?php
								// @codingStandardsIgnoreLine
								echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
									'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><img src="https://s3-eu-west-1.amazonaws.com/iamdeaneyelid/cross.png"></a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									__( 'Remove this item', 'woocommerce' ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() )
								), $cart_item_key );
							?>
						</div>



					</div>
					<?php
				}
			}
			?>


<!-- Content Row End -->




			<?php do_action( 'woocommerce_cart_contents' ); ?>
				<div class="container-nested row">


					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon-code">
							


 <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> 



 <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply code', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply code', 'woocommerce' ); ?></button>
<?php do_action( 'woocommerce_cart_coupon' ); ?>

						


						</div>
					<?php } ?>



					<div class="update-basket-button">


					<button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update basket', 'woocommerce' ); ?>"><?php esc_html_e( 'Update basket', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</div>


				</div>




			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>


				<div class="container-nested row subtotal">
					<h3 class="heading">
						Subtotal
					</h3>
					<h3 class="price poppins">
						£<?php echo(WC()->cart->get_subtotal()); ?> 
					</h3>
				</div>



				<div class="container-nested row checkout">
					<p class="small">
						Delivery cost presented at checkout, once you have completed your billing details and delivery address.
					</p>
					<div class="checkout-button wc-proceed-to-checkout">
						<a class="wc-forward" href="<?php esc_attr_e(wc_get_checkout_url()); ?>">
							<button formaction="<?php esc_attr_e(wc_get_checkout_url()); ?>">Checkout</button>
						</a>
					</div>
				</div>
		</form>







<?php do_action( 'woocommerce_after_cart' ); ?>
