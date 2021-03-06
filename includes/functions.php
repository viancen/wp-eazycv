<?php
if ( ! function_exists( 'dd' ) ) {
	function dd( $anything ) {
		add_action( 'shutdown', function () use ( $anything ) {
			//echo "<div style='font-family:courier; position: fixed; z-index: 100; left: 30px; bottom: 30px; right: 30px; background-color: #ff9db5; color:#5e0011;border:solid 2px darkred;padding:10px;'>";
			echo "<pre>";
			print_r( $anything );
			echo "</pre>";
			//echo "</div>";
			die();
		} );
	}

}

if ( ! function_exists( 'dump' ) ) {
	function dump( $anything ) {
		echo "<pre>";
		print_r( $anything );
		echo '</pre>';
	}

}
if ( ! function_exists( 'eazy_first_words' ) ) {
	function eazy_first_words( $string, $amountofWords = 10 ) {
		$pullString = implode( ' ', array_slice( explode( ' ', strip_tags( str_replace( PHP_EOL, ' ', $string ) ) ), 0, $amountofWords ) );
		if ( strlen( $pullString ) != strlen( strip_tags( str_replace( PHP_EOL, ' ', $string ) ) ) ) {
			$pullString .= '...';
		}

		return $pullString;
	}

}
if ( ! function_exists( 'current_location' ) ) {

	function current_location() {
		if ( isset( $_SERVER['HTTPS'] ) &&
		     ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ) ||
		     isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
		     $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}

		return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
}