<style>.hndle {display: none !important}</style>
<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (empty(sanitize_email($_POST['aurifox_api_email']))) {
			echo "<div id='message' class='error notice is-dismissible' style='width: 733px;padding: 10px 12px;font-weight: bold'><i class='fa fa-thumbs-o-down' style='margin-right: 5px;'></i> Please add an email address. <button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
		}
		else if (empty(sanitize_text_field($_POST['aurifox_api_auth']))) {
			echo "<div id='message' class='updated notice is-dismissible' style='width: 733px;padding: 10px 12px;font-weight: bold'><i class='fa fa-thumbs-o-down' style='margin-right: 5px;'></i> Please add Authorization Key. <button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
		}
		else {
			echo "<div id='message' class='updated notice is-dismissible' style='width: 733px;padding: 10px 12px;font-weight: bold'><i class='fa fa-thumbs-o-up' style='margin-right: 5px;'></i> Successfully updated aurifox plugin settings. <button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
			update_option( 'aurifox_api_email', trim(sanitize_email($_POST['aurifox_api_email']) ));
			update_option( 'aurifox_api_auth', trim(sanitize_text_field($_POST['aurifox_api_auth']) ));
			update_option( 'aurifox_display_method', sanitize_text_field($_POST['aurifox_display_method'] ));
			update_option( 'aurifox_favicon_method', sanitize_text_field($_POST['aurifox_favicon_method'] ));
			update_option( 'aurifox_additional_snippet', sanitize_key(htmlentities($_POST['aurifox_additional_snippet']) ));

			update_option( 'AURIFOX_API_URL', htmlentities($_POST['AURIFOX_API_URL']) );

			
		}
	}


?>

<script>
	
	jQuery(document).ready(function() {
		jQuery('.draft').hide();
		console.log("%caurifox WordPress Plugin", "background: #0166AE; color: white;");
		console.log("%cEditing anything inside the console is for developers only. Do not paste in any code given to you by anyone. Use with caution. Visit for support: https://support.aurifox.com/", "color: #888;");
		jQuery('.sdtablink').click(function() {
      jQuery('.sdtabs').hide();
      jQuery('.sdtablink').removeClass('active');
      jQuery(this).addClass('active');
      var tab = jQuery(this).attr('data-tab');
      jQuery('#'+tab).show();
		});

		var aurifoxURL = '<?php echo AURIFOX_API_URL ?>getapidetails?email=<?php echo get_option( "aurifox_api_email" ); ?>&auth_token=<?php echo get_option( "aurifox_api_auth" ); ?>';
		
		  jQuery.getJSON(aurifoxURL, function(data) {
			  jQuery('.checkSuccess').html('<i class="fa fa-thumbs-o-up successGreen"></i>');
			  jQuery('.checkSuccessDev').html('<i class="fa fa-thumbs-o-up"> Connected</i>');
			  jQuery('#api_check').addClass('compatenabled');
		  }).fail(function(jqXHR) {
		  	jQuery('#api_check').removeClass('compatenabled');
		  	jQuery('#api_check').addClass('compatdisabled');
	     	jQuery('.checkSuccess').html('<i class="fa fa-thumbs-o-down"></i>');
	     	jQuery('.checkSuccessDev').html('<i class="fa fa-thumbs-o-down"> Not Connected</i>');
	     	jQuery('.badAPI').show();
		  });
	});




</script>
<div id="message" class="badAPI error notice" style="display: none; width: 733px;padding: 10px 12px;font-weight: bold"><i class="fa fa-thumbs-o-down" style="margin-right: 5px;"></i> Failed API Connection with aurifox. Check <a href="edit.php?post_type=aurifox&page=sd_api&error=compatibility">Settings > Compatibility Check</a> for details.</div>
<div class="api postbox" style="width: 98%;margin-top: 20px;">
	<div class="head-top">

		<?php

		$i_url = plugins_url( '../images/logo.png', __FILE__ );
		?>
	<div class="logo_img"><img src="<?php echo esc_url($i_url); ?>" alt=""></div>
	<div class="apiSubHeader" style="padding: 18px 16px;">
		<h2 style="font-size: 1.5em"> Plugin Settings</h2>
	</div>
</div>
	
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>">
		<div class="bootstrap-wp">
			<div id="app_sidebar">
				<div class="col-md-4 bb"><li><a href="#" data-tab="tab2" class="sdtablink <?php if(!$_GET['error']) { echo 'active';} ?>">Requirements</a></li></div>
				<div class="col-md-4 bb"><li><a href="#" data-tab="tab3" class="sdtablink <?php if($_GET['error']) { echo 'active';} ?>">Settings</a></li></div>
				<div class="col-md-4 bb"><li><a href="#" data-tab="tab1" class="sdtablink <?php if($_GET['error']) { echo 'active';} ?>">Interface</a></li></div>
				<div class="col-md-4 bb"><li><a href="#" data-tab="tab5" class="sdtablink <?php if($_GET['error']) { echo 'active';} ?>">Reset</a></li></div>
			</div>
			<div id="app_main">
				<div id="tab3" class="sdtabs" style="display: none;">
					<h2>Settings</h2>

					<div class="control-group clearfix" >
						<label class="control-label" for="aurifox_display_method">Display:</span> </label>
						<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
							<select name="aurifox_display_method" id="aurifox_display_method" class="input-xlarge" style="height: 30px;">
								<option value="download" <?php if (esc_attr(get_option('aurifox_display_method')) == 'iframe') { echo "selected";}?>>Download &amp; Display</option>
								<option value="iframe" <?php if (esc_attr(get_option('aurifox_display_method')) == 'iframe') { echo "selected";}?>>Embed Full Page iFrame</option>
								<option value="redirect" <?php if (esc_attr(get_option('aurifox_display_method')) == 'redirect') { echo "selected";}?>>Redirect to aurifoxs</option>
							</select>
						</div>
					</div>
					<div class="control-group clearfix" >
						<label class="control-label" for="aurifox_favicon_method">Favicon:</span> </label>
						<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
							<select name="aurifox_favicon_method" id="aurifox_favicon_method" class="input-xlarge" style="height: 30px;">
								<option value="aurifox" <?php if (esc_attr(get_option('aurifox_favicon_method')) == 'aurifox') { echo "selected";}?>>Use Funnel Favicon</option>
								<option value="wordpress" <?php if (esc_attr(get_option('aurifox_favicon_method')) == 'wordpress') { echo "selected";}?>>Use Wordpress Favicon</option>
							</select>
						</div>
					</div>
					<div class="control-group clearfix">
						<label class="control-label" for="aurifox_additional_snippet">Tracking Snippet:</label>
						<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
							<textarea class="input-xlarge" name="aurifox_additional_snippet">
								<?php echo html_entity_decode(stripslashes(get_option( 'aurifox_additional_snippet' ))); 
								?></textarea>
						</div>
					</div>
					<button class="action-button shadow animate green" id="publish" style="float: right;margin-top: 10px;"><i class="fa fa-thumbs-o-up-circle"></i>Update</button>
				</div>
				<div id="tab5" class="sdtabs" style="display: none;">
					<h2>Reset</h2>
					<a href="edit.php?post_type=aurifox&page=reset_data" class="button" style="margin-left: 51px" onclick="return confirm('Are you sure?')">Delete All Pages and API Settings</a>
				</div>
				<div id="tab2" class="sdtabs">
					<h2>Requirements</h2>
					<span class="compatCheck" id="api_check">Interface : <strong class='checkSuccessDev'><i class="fa fa-spinner"></i> Connecting...</strong></span>
					<?php
						if (isset($_SERVER["HTTP_SD_CONNECTING_IP"])) {
						echo '<span class="compatCheck compatwarning">CloudFlare:  <strong><a target="_blank" href="https://support.aurifox.com/support/solutions/5000164139">If you have blank pages, turn off minify for JavaScript.</a></strong></span>';
						}
					?>
					<?php if ( empty(get_option( 'permalink_structure' ) ) ) {
							echo '<span class="compatCheck compatdisabled">Permalinks:  <strong>aurifox needs <a href="options-permalink.php">custom permalinks</a> enabled!</strong></span>';
					}
					else {
						echo '<span class="compatCheck compatenabled">Permalinks:  <strong><i class="fa fa-thumbs-o-up"> Enabled</i></strong></span>';
					} ?>
					<?php echo function_exists('curl_version') ? '<span class="compatCheck compatenabled">CURL:  <strong><i class="fa fa-thumbs-o-up"> Enabled</i></strong></span>' : '<span class="compatCheck"><i class="fa fa-thumbs-o-down">Disabled</i></strong></span>'  ?>
					<?php echo file_get_contents(__FILE__) ? '<span class="compatCheck compatenabled">File Get Contents:  <strong><i class="fa fa-thumbs-o-up"> Enabled</i></strong></span>' : '<span class="compatCheck">File Get Contents:  <strong><i class="fa fa-thumbs-o-down">Disabled</i></strong></span>' ; ?>
					<?php echo ini_get('allow_url_fopen') ? '<span class="compatCheck compatenabled">Allow URL fopen:  <strong><i class="fa fa-thumbs-o-up"> Enabled</i></strong></span>' : '<span class="compatCheck">Allow URL fopen:  <strong><i class="fa fa-thumbs-o-down">Disabled</i></strong></span>' ; ?>
					<?php
						if (version_compare(phpversion(), "5.3.0", ">=")) {
							echo '<span class="compatCheck compatenabled">PHP Version:  <strong>'.PHP_VERSION.'</strong></span>';
						} else {
							echo '<span class="compatCheck compatdisabled">PHP Version:  <strong><a href="https://support.aurifox.com/support/home" target="_blank">This plugin requires PHP 5.3.0 or above.</a></strong></span>';
						}
					?>
				</div>
				<div id="tab1" class="sdtabs" style="display: none;">
					<h2>Interface</h2>
					<div>
						<div class="control-group clearfix">
							<label class="control-label" for="AURIFOX_API_URL">Site URL:<span class="checkSuccess"></span> </label>
							<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
								<input type="text" class="input-xlarge" style="height: 30px;" value="<?php echo get_option( 'AURIFOX_API_URL' ); ?>" name="AURIFOX_API_URL" />
								<p>Note: Save your URL like www.example.com/
							</div>
						</div>

						<div class="control-group clearfix">
							<label class="control-label" for="aurifox_api_email">Account Email:<span class="checkSuccess"></span> </label>
							<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
								<input type="text" class="input-xlarge" style="height: 30px;" value="<?php echo esc_attr(get_option( 'aurifox_api_email' )); ?>" name="aurifox_api_email" />
							</div>
						</div>
						<div class="control-group clearfix">
							<label class="control-label" for="aurifox_api_auth">Authentication Token:<span class="checkSuccess"></span> </label>
							<div class="controls" style="padding-left: 24px;margin-bottom: 16px;">
								<input type="text" class="input-xlarge" style="height: 30px;" value="<?php echo esc_attr(get_option( 'aurifox_api_auth' )); ?>" name="aurifox_api_auth" />
							</div>
						</div>
						
					</div>
					<button class="action-button shadow animate green" id="publish" style="float: right;margin-top: 10px;"><i class="fa fa-thumbs-o-up-circle"></i>Update</button>
				</div>

				<br clear="both" />
			</div>
		</div>
	</form>
	
</div>
