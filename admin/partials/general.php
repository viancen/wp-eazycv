<div id="normal-sortables" class="meta-box-sortables ui-sortable">
	<div id="itsec_sss" class="postbox ">
		<h3 class="hndle"><span>Welcome!</span></h3>
		<div class="inside">
			<p>
                Welkom, stel hier uw API credentials in.

            </p>
		</div>
	</div>
</div>
<?php if(!empty($this->eazyCvException)){
	?>
    <p class="notice notice-error is-dismissible"><?php echo $this->eazyCvException;?></p>
	<?php
} else {
	?>
    <p class="notice notice-success is-dismissible"><Br />
       <strong><?php echo __('Your site is now connected to EazyCV!') ?></strong><br /><br /></p>
	<?php
} ?>
<form method="post" action="options.php" class="">
	<div id="normal-sortables" class="meta-box-sortables ui-sortable">
		<div id="itsec_get_started" class="postbox ">
			<h3 class="hndle"><span>Settings</span></h3>
			<div class="inside" >

				<?php
				//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
				settings_fields($this->plugin_name."-general_section");
				//settings_fields("advertising_section");

				// all the add_settings_field callbacks is displayed here
				do_settings_sections($this->plugin_name."-general-options");

				submit_button( 'Save Settings' );
				?>
               <!-- <button class="button button-secondary eazycv-custom-btn" id="check-api-connection"><?php echo  __('Check Api Connection')?></button>-->
				<div class="clear"></div>
			</div>
		</div>
	</div>
</form>