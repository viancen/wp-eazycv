<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="itsec_sss" class="postbox ">
        <h3 class="hndle"><span><?php echo __( 'Job and applications' ) ?></span></h3>
        <div class="inside">
            <p><?php echo __( 'Configure your default job and apply options, make sure the selected pages have the following shortcodes in them:' ) ?></p>
            <p>
            <table>
                <tr>
                    <td><strong>Page</strong></td>
                    <td></td>
                    <td><strong>Shortcode</strong></td>
                    <td><em>Help</em></td>
                </tr>
                <tr>
                    <td>Jobs overview-page</td>
                    <td>:</td>
                    <td>[eazycv_job_search]</td>
                    <td class="eazy-text-muted">Optionally you can pass a different portalId or type: [eazycv_job_search portal_id="312" job_type="project"]</td>
                </tr>
                <tr>
                    <td>Job-detailspage</td>
                    <td>:</td>
                    <td>[eazycv_job]</td>
                    <td class="eazy-text-muted">Template for pagetitle example: "Vacature *|original_functiontitle|*"</td>
                </tr>
                <tr>
                    <td>Job apply-page</td>
                    <td>:</td>
                    <td>[eazycv_apply]</td>
                    <td class="eazy-text-muted">Optionally you can pass a different portalId : [eazycv_apply portal_id="312"]</td>
                </tr>
            </table>
            </p>
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
				settings_fields( $this->plugin_name . "-job_section" );
				//settings_fields("advertising_section");

				// all the add_settings_field callbacks is displayed here
				do_settings_sections( $this->plugin_name . "-job-options" );

				submit_button( 'Save Settings' );
				?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</form>