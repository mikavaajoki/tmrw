<?php
class SLP_Premier_Category_Walker_Checklist extends Walker {
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	public $tree_type = 'category';

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker:start_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		if ( empty( $args['taxonomy'] ) ) {
			$taxonomy = 'category';
		} else {
			$taxonomy = $args['taxonomy'];
		}

		if ( $taxonomy == 'category' ) {
			$name = 'post_category';
		} else {
			$name = 'tax_input[' . $taxonomy . ']';
		}

		$args['popular_cats'] = empty( $args['popular_cats'] ) ? array() : $args['popular_cats'];
		$class = in_array( $category->term_id, $args['popular_cats'] ) ? ' class="popular-category"' : '';

		$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];

		$data = array(
			"data-term-id='{$category->term_id}'",
		);
		$data_html = join( ' ' , $data );

		if ( ! empty( $args['list_only'] ) ) {
			$aria_checked = 'false';
			$inner_class = 'category';

			if ( in_array( $category->term_id, $args['selected_cats'] ) ) {
				$inner_class .= ' selected';
				$aria_checked = 'true';
			}

			/** This filter is documented in wp-includes/category-template.php */
			$output .= "\n<li {$class} {$data_html}>" .
			            '<div class="' . $inner_class . '" ' . $data_html . ' tabindex="0" role="checkbox" aria-checked="' . $aria_checked . '">' .
			                esc_html( apply_filters( 'the_category', $category->name, '', '' ) ) .
			           '</div>';
		} else {
			/** This filter is documented in wp-includes/category-template.php */
			$text = esc_html( apply_filters( 'the_category', $category->name, '', '' ) );
			$checked = checked( in_array( $category->term_id, $args['selected_cats'] ), true, false );
			$category_icon = $this->get_icon( $category->term_id );
			$output .=<<<HTML
				<li id='{$taxonomy}-{$category->term_id}' {$class} {$data_html}>
	                <label class='selectit' {$data_html}>
						{$category_icon}<input type='checkbox' value='{$category->term_id}' name='{$name}[]' id='in-{$taxonomy}-{$category->term_id}' {$data_html} {$checked}>{$text}
	                </label>
HTML;
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}

	/**
	 * Get the icon.
	 *
	 * @param int $term_id
	 *
	 * @return string
	 */
	private function get_icon( $term_id ) {
		global $slplus;
		$selector_class = $slplus->Premier_Category_UI->get_class();
		if ( $selector_class !== 'button_bar' ) return '';

		/**
		 * @var SLPPower $power
		 */
		$power = $slplus->addon( 'Power' );
		$category = $power->get_TermWithTagalongData( $term_id );
		$category['category_url'] = '';
		return $power->createstring_CategoryImageHTML( $category, 'medium-icon' );
	}

	/**
	 * Walk the walk...
	 *
	 * @param array $elements
	 * @param int $max_depth
	 *
	 * @return string
	 */
	public function walk( $elements, $max_depth ) {
		global $slplus;
		if ( $slplus->SmartOptions->hide_empty->is_true) {
			foreach ( $elements as $k => $wp_term ) {
				if ( ! property_exists( $wp_term , 'location_count' ) ){
					return;
				}
				if ( empty( $wp_term->location_count ) ) {
					unset( $elements[ $k ] );
				}
			}
		}
		$the_real_args = func_get_args();
		return parent::walk( $elements, $max_depth , $the_real_args[2]  );
	}
}