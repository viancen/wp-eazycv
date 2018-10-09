<?php
/**
 * Link shortcode
 *
 * Write [eazycv_job] in your post editor to render this shortcode.
 *
 * @package     ABS
 * @since    1.0.0
 */
if ( ! function_exists( 'eazycv_job' ) ) {
	// Add the action.
	add_action( 'plugins_loaded', function () {
		// Add the shortcode.
		add_shortcode( 'eazycv_job', 'eazycv_job' );
	} );

	/**
	 * Shortcode Function
	 *
	 * @param  Attributes $atts l|t URL TEXT.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	function eazycv_job( $atts ) {
		$pagename = get_query_var('JobID');
		if(empty($pagename)){
			//
			return 'Not available anymeer';
		} else {
			$jobId = (explode('-',$pagename));

			return array_pop($jobId);
		}
		if ( !$pagename  ) {
			// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
			$post = $wp_query->get_queried_object();
			var_dump($post);
			$pagename = $post->post_name;
		}
		// Text Default.
		$text_default = __( 'Vacatuos', 'ABS' );
		// Save $atts.
		$_atts = shortcode_atts( array(
			'u' => '/',           // URL.
			't' => $text_default, // Text.
		), $atts );
		// URL.
		$_url = $_atts['u'];
		// Text.
		$_text = $_atts['t'];
		// Return it, Safe in PHP 7.0.
		$_return = '<a href="' . $_url . '"> ' . $_text . ' </a>';

		// Return the data.
		return $_return;
	}
} // End if().