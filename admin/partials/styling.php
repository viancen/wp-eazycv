<form method="post" action="options.php">
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="itsec_sss" class="postbox ">
            <h3 class="hndle"><span><?php echo __( 'Extra CSS ' ) ?></span></h3>
            <div class="inside">
                <p><?php echo __( 'Add extra styling for eazycv elements:' ) ?></p>
                <p>
					<?php
					settings_fields( $this->plugin_name . "-styling" );
					do_settings_sections( $this->plugin_name . "-styling-options" );

					submit_button( 'Save Settings' );
					?>
                </p>
            </div>
        </div>
    </div>
</form>