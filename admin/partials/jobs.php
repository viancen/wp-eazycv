<div id="normal-sortables" class="meta-box-sortables ui-sortable">
	<div id="itsec_sss" class="postbox ">
		<h3 class="hndle"><span>Welcome!</span></h3>
		<div class="inside">
			<p>Welkom, stel hier uw Jobs shit gegevens in.</p>
		</div>
	</div>
</div>
<form method="post" action="options.php">
	<div id="normal-sortables" class="meta-box-sortables ui-sortable">
		<div id="itsec_get_started" class="postbox ">
			<h3 class="hndle"><span>Settings</span></h3>
			<div class="inside">
				<?php
				//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
				settings_fields($this->plugin_name."-job_section");
				//settings_fields("advertising_section");

				// all the add_settings_field callbacks is displayed here
				do_settings_sections($this->plugin_name."-job-options");

				submit_button( 'Save Settings' );
				?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</form>