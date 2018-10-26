<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://eazycv.nl
 * @since      1.0.0
 *
 * @package    Filter_WP_Api
 * @subpackage Filter_WP_Api/admin/partials
 */

//flush rewrite rules always
flush_rewrite_rules();

?>

<div class="wrap">
	<?php
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
	$this->filter_wp_api_render_tabs();
	?>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-2" class="postbox-container">
				<?php
				switch ($tab) {
					case 'jobs':
					    include(dirname(__FILE__).'/jobs.php');
						break;
					// If no tab or general
					default:
						include(dirname(__FILE__).'/general.php');
						break;
				} ?>
			</div>
		</div>
	</div>
</div>