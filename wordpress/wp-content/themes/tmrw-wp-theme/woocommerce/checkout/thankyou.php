<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
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
 * @version     3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );

?>



<div class="container-nested order-confirmation">
	<h1>
		Thanks for <span class="poppins">Your Order</span>
	</h1>
	
	<h3>
		Order N<sup>o</sup> <?php echo $order->get_order_number(); ?>
	</h3>

<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
	<p>
		Your order has been succesfully received. You will receive an email confirmation shortly at <?php echo $order->get_billing_email(); ?>.
	</p>
	
	<?php endif; ?>
</div>


<div class="product-list order-summary">
	<div class="section-heading">
		<h4>Order Summary</h4>
	</div>


	

		<?php



		foreach ( $order_items as $item_id => $item ) {

								$product = $item->get_product();


			 {
					?>
					<div class="container-nested row item">

						<h3>
							<span class="poppins">	<?php echo ($product->get_name()) ?></span>
						</h3>

						<div class="quantity">
							<h4>	<?php echo ($item->get_quantity()) ?></h4>
						</div>
							
						<div class="price">
							<h4>£<?php echo ($product->get_price()) ?></h4>
						</div>

					</div>

					<?php
				}
			}
		?>



		<div class="container-nested row subtotal">
			<h4 class="heading">Subtotal</h4>
			<h4 class="price"><?php echo $order->get_subtotal(); ?></h4>
		</div>


		<div class="container-nested row delivery">
			<h4 class="heading">
				Delivery
			</h4>
			<h4 class="price">

				<?php echo $order->get_total_shipping(); ?>


			</h4>
		</div>

		<div class="container-nested row total">
			<h3 class="heading">
				Total
			</h3>
			<h3 class="price poppins">
				<?php echo $order->get_formatted_order_total(); ?>
			</h3>
		</div>

</div>