<style>.hndle {display: none !important}</style>
<?php
    $sd_authorization_email = sanitize_email(get_option( 'aurifox_api_email' ));
    $sd_authorization_token = sanitize_text_field(get_option( 'aurifox_api_auth' ));
?>



<script type="text/javascript">
    function aurifox_get_aurifox_url(id) {
      var js_api_url = '<?php echo AURIFOX_API_URL ?>';
      var js_api_email = '<?php echo $sd_authorization_email; ?>';
      var js_api_token = '<?php echo $sd_authorization_token ?>';
      var the_resource;

      if (id) {
        the_resource = 'funneldetails/' + id;
      } else {
        the_resource = 'funnellist';
      }

      return js_api_url + the_resource + '?email=' + js_api_email+ '&auth_token=' + js_api_token;
    }

    jQuery(document).ready(function(){
        jQuery('.draft').hide();
        console.log("%caurifox WordPress Plugin", "background: #0166AE; color: white;");
        console.log("%cEditing anything inside the console is for developers only. Do not paste in any code given to you by anyone. Use with caution. Visit for support: https://support.aurifox.com/", "color: #888;");
        var allaurifoxs = aurifox_get_aurifox_url();
         jQuery.getJSON(allaurifoxs, function(data) {
            jQuery.each(data, function() {
            	jQuery('#sd_theaurifox').append('<option value="' + this.id + '">' + this.name + '</option>');
            	jQuery('#sd_theaurifox_clickpop').append('<option value="' + this.id + '">' + this.name + '</option>');
            	jQuery('#sd_theaurifox_clickoptin').append('<option value="' + this.id + '">' + this.name + '</option>');
            });
          }).fail(function() {
          	jQuery('.badAPI').show();
          });
        

         jQuery( '#sd_theaurifox' ).change(function() {
           jQuery('.choosePageBox').fadeIn();
            var theaurifox = jQuery(this).val();
            var totalPages = 0;
            var specificFunnel = aurifox_get_aurifox_url(theaurifox);
            jQuery('#sd_thepage').find('option').remove().end();
            jQuery.getJSON(specificFunnel, function(data) {
                jQuery.each(data.aurifox_steps, function() {
                  if( this.pages.length ) {
                    jQuery('#sd_thepage').append('<option value="' + this.pages[0].published_url+'">'+ this.name +'</option>');
                    jQuery('#sd_shortcode').val('[aurifox_embed height="650" url="'+this.pages[0].published_url+'"]');
                    jQuery('#sd_shortcode').select();
                    totalPages += 1;
                  }
                });
            }).done(function() {
                jQuery('#loading').fadeOut();
            	jQuery('#sd_thepage').trigger('change');
                if (totalPages == 0) {
                    jQuery('#sd_thepage').hide();
                    jQuery('#noPageWarning').fadeIn();
                }
                else {
                    jQuery('#noPageWarning').hide();
                    jQuery('#sd_thepage').fadeIn();
                }
              })
              .fail(function() {
                jQuery('#loading').fadeOut();
              })
              .always(function() {
                jQuery('#loading').fadeOut();
              });
        });
        jQuery( '#sd_thepage' ).change(function() {
            jQuery('#loading').fadeOut();
             height = jQuery('#sd_height').val();
            theURL = jQuery('#sd_thepage').val();
            scrollCheck = jQuery('#sd_scrolling').val();
            jQuery('#sd_shortcode').val('[aurifox_embed height="'+height+'" url="'+theURL+'" scroll="'+scrollCheck+'"]');
            jQuery('#sd_shortcode').select();
        });
        jQuery( '.sd_embedchange' ).change(function() {
            jQuery('#loading').fadeOut();
            height = jQuery('#sd_height').val();
            theURL = jQuery('#sd_thepage').val();
            scrollCheck = jQuery('#sd_scrolling').val();
            jQuery('#sd_shortcode').val('[aurifox_embed height="'+height+'" url="'+theURL+'" scroll="'+scrollCheck+'"]');
             jQuery('#sd_shortcode').select();
        });
        





        jQuery( '#sd_theaurifox_clickoptin' ).change(function() {
           jQuery('.choosePageBox_clickoptin').fadeIn();
            var theaurifox = jQuery(this).val();
            var totalPages = 0;
            var specificFunnel = aurifox_get_aurifox_url(theaurifox);
            jQuery('#sd_thepage_clickoptin').find('option').remove().end();
                jQuery.getJSON(specificFunnel, function(data) {
                jQuery.each(data.aurifox_steps, function() {
                  if( this.pages.length ) {
                  			var parts = this.pages[0].published_url.split('.');
                        if (this.pages[0].published_url.indexOf('aurifox.com') > -1) {
                          subdomain = parts.shift().replace("https://", "");
                        }
                        else {
                          subdomain = parts[0] + '.' + parts[1];
                          subdomain = subdomain.replace("https://", "")
                        }
                        jQuery('#sd_thepage_clickoptin').append('<option value="' + this.pages[0].key+'{#}'+subdomain+'">'+ this.name +'</option>');
                    }
                    placeholder = jQuery('#sd_placeholder').val();
                    button_text = jQuery('#sd_button_text').val();
                    button_color = jQuery('#sd_button_color').val();
                    redirect = jQuery('#sd_redirect').val();
                    input_icon = jQuery('#sd_input_icon').val();
                    jQuery('#sd_shortcode_clickoptin').val('[aurifox_clickoptin id="'+this.pages[0].key+'" subdomain="'+subdomain+'" placeholder="'+placeholder+'" button_text="'+button_text+'" button_color="'+button_color+'" redirect="'+redirect+'" input_icon="'+input_icon+'"]');
                        jQuery('#sd_shortcode_clickoptin').select();
                         totalPages += 1;
                });
            }).done(function() {
                jQuery('#loading').fadeOut();
            	jQuery('#sd_thepage_clickoptin').trigger('change');
                if (totalPages == 0) {
                    jQuery('#sd_thepage_clickoptin').hide();
                    jQuery('#noPageWarning_clickoptin').fadeIn();
                }
                else {
                    jQuery('#noPageWarning_clickoptin').hide();
                    jQuery('#sd_thepage_clickoptin').fadeIn();
                }
              })
              .fail(function() {
                jQuery('#loading').fadeOut();
              })
              .always(function() {
                jQuery('#loading').fadeOut();
              });
        });
        jQuery( '#sd_thepage_clickoptin' ).change(function() {
            jQuery('#loading').fadeOut();
            data = jQuery(this).val().split('{#}');
            var parts = data[1].split('.');
						var subdomain = data[1].split('/');
						placeholder = jQuery('#sd_placeholder').val();
            button_text = jQuery('#sd_button_text').val();
            button_color = jQuery('#sd_button_color').val();
            redirect = jQuery('#sd_redirect').val();
            input_icon = jQuery('#sd_input_icon').val();
            jQuery('#sd_shortcode_clickoptin').val('[aurifox_clickoptin id="'+data[0]+'" subdomain="'+subdomain[0]+'" placeholder="'+placeholder+'" button_text="'+button_text+'" button_color="'+button_color+'" redirect="'+redirect+'" input_icon="'+input_icon+'"]');
            jQuery('#sd_shortcode_clickoptin').select();
        });
        jQuery( '.sd_optinchange' ).change(function() {
            jQuery('#loading').fadeOut();
            data = jQuery('#sd_thepage_clickoptin').val().split('{#}');
            var parts = data[1].split('.');
						var subdomain = data[1].split('/');
						placeholder = jQuery('#sd_placeholder').val();
            button_text = jQuery('#sd_button_text').val();
            button_color = jQuery('#sd_button_color').val();
            redirect = jQuery('#sd_redirect').val();
            input_icon = jQuery('#sd_input_icon').val();
            jQuery('#sd_shortcode_clickoptin').val('[aurifox_clickoptin id="'+data[0]+'" subdomain="'+subdomain[0]+'" placeholder="'+placeholder+'" button_text="'+button_text+'" button_color="'+button_color+'" redirect="'+redirect+'" input_icon="'+input_icon+'"]');
        });
        



        jQuery( '#sd_theaurifox_clickpop' ).change(function() {
        	jQuery('#loading').fadeIn();
           jQuery('.choosePageBox_clickpop').fadeIn();
            var theaurifox = jQuery(this).val();
            var totalPages = 0;
            var specificFunnel = aurifox_get_aurifox_url(theaurifox);
            jQuery('#sd_thepage_clickpop').find('option').remove().end();
                jQuery.getJSON(specificFunnel, function(data) {
                jQuery.each(data.aurifox_steps, function() {
                  if( this.pages.length ) {
                  			var parts = this.pages[0].published_url.split('.');
                        if (this.pages[0].published_url.indexOf('aurifox.com') > -1) {
                          subdomain = parts.shift().replace("https://", "");
                        }
                        else {
                          subdomain = parts[0] + '.' + parts[1];
                          subdomain = subdomain.replace("https://", "")
                        }
                        jQuery('#sd_thepage_clickpop').append('<option value="' + this.pages[0].key+'{#}'+subdomain+'">'+ this.name +'</option>');
                    		jQuery('#sd_shortcode_clickpop').val('[aurifox_clickpop id="'+this.pages[0].key+'" subdomain="'+subdomain+'"]Your Content[/aurifox_clickpop]');
                        jQuery('#sd_shortcode_clickpop').select();
                        totalPages += 1;
                    }
                });
            }).done(function() {
              jQuery('#loading').fadeOut();
            	jQuery('#sd_thepage_clickpop').trigger('change');
                if (totalPages == 0) {
                    jQuery('#sd_thepage_clickpop').hide();
                    jQuery('#noPageWarning_clickpop').fadeIn();
                }
                else {
                    jQuery('#noPageWarning_clickpop').hide();
                    jQuery('#sd_thepage_clickpop').fadeIn();
                }
              })
              .fail(function() {
                jQuery('#loading').fadeOut();
              })
              .always(function() {
                jQuery('#loading').fadeOut();
              });
        });
        jQuery( '#sd_thepage_clickpop' ).change(function() {
            jQuery('#loading').fadeOut();
            showOnExit = '';
            if(jQuery('#sd_exit').val() == 'true') {
            	showOnExit = 'exit="true" ';
            }
            showDelay = '';
            if(jQuery('#sd_delay').val() != '') {
            	showDelay = 'delay="'+jQuery('#sd_delay').val()+'" ';
            }
            data = jQuery(this).val().split('{#}');
						var subdomain = data[1].split('/');
            jQuery('#sd_shortcode_clickpop').val('[aurifox_clickpop '+showOnExit+showDelay+'id="'+data[0]+'" subdomain="'+subdomain[0]+'"]Your Content[/aurifox_clickpop]');
            jQuery('#sd_shortcode_clickpop').select();
        });
        jQuery( '#sd_exit' ).change(function() {
            jQuery('#loading').fadeOut();
            showOnExit = '';
            if(jQuery(this).val() == 'true') {
            	showOnExit = 'exit="true" ';
            }
            showDelay = '';
            if(jQuery('#sd_delay').val() != '') {
            	showDelay = 'delay="'+jQuery('#sd_delay').val()+'" ';
            }
            data = jQuery('#sd_thepage_clickpop').val().split('{#}');
            var subdomain = data[1].split('/');
            jQuery('#sd_shortcode_clickpop').val('[aurifox_clickpop '+showOnExit+showDelay+'id="'+data[0]+'" subdomain="'+subdomain[0]+'"]Your Content[/aurifox_clickpop]');
            jQuery('#sd_shortcode_clickpop').select();
        });
        jQuery( '#sd_delay' ).change(function() {
            jQuery('#loading').fadeOut();
            showOnExit = '';
            if(jQuery('#sd_exit').val() == 'true') {
            	showOnExit = 'exit="true" ';
            }
            showDelay = '';
            if(jQuery(this).val() != '') {
            	showDelay = 'delay="'+jQuery('#sd_delay').val()+'" ';
            }
            data = jQuery('#sd_thepage_clickpop').val().split('{#}');
            var subdomain = data[1].split('/');
            jQuery('#sd_shortcode_clickpop').val('[aurifox_clickpop '+showOnExit+showDelay+'id="'+data[0]+'" subdomain="'+subdomain[0]+'"]Your Content[/aurifox_clickpop]');
            jQuery('#sd_shortcode_clickpop').select();
        });




				jQuery('.sdtablink').click(function() {
				    if (jQuery(this).hasClass('disabledLink')=== true) {
				    }
				    else {
				        jQuery('.sdtabs').hide();
				        jQuery('.sdtablink').removeClass('active');
				        jQuery(this).addClass('active');
				        var tab = jQuery(this).attr('data-tab');
				        jQuery('#'+tab).show();
				    }
				});
	});
</script>

<div id="message" class="badAPI error notice" style="display: none; width: 733px;padding: 10px 12px;font-weight: bold"><i class="fa fa-thumbs-o-down" style="margin-right: 5px;"></i> Failed API Connection with aurifox. Check <a href="edit.php?post_type=aurifox&page=sd_api&error=compatibility">Settings > Requirements</a> for details.</div>
<div class="api postbox" style="width: 99%;margin-top: 20px;">

    <div class="logo_img"><img src="<?php echo esc_url(plugins_url( '../images/logo.png', __FILE__ )); ?>" alt=""></div>
	<div class="apiSubHeader" style="padding: 18px 16px;">
		<h2 style="font-size: 1.5em"><i class="fa fa-code" style="margin-right: 5px"></i> Shortcode Generator</h2>
	</div>
	<div class="bootstrap-wp">
		<div id="app_sidebar">
            <div class="col-md-4 bb"><li><a href="#" data-tab="tab1" class="sdtablink active">Embed Code</a></li></div>
            <div class="col-md-4 bb"><li><a href="#" data-tab="tab2" class="sdtablink">ClickPop</a></li></div>
            <div class="col-md-4 bb"><li><a href="#" data-tab="tab3" class="sdtablink">ClickForms</a></li></div>
        </div>
		<div id="app_main">
			<div id="tab4" class="sdtabs"  style="display: none">
				<h2>Shortcode Settings</h2>
			</div>
			<div id="tab3" class="sdtabs"  style="display: none">
				<h2>Collect Leads with ClickForm</h2>
				<div class="control-group sd_uses_api clearfix" style="">
					<label class="control-label" for="sd_theaurifox_clickoptin"> Choose Funnel  </label>
					<div class="controls">
						<select class="input-xlarge" id="sd_theaurifox_clickoptin" style="width: 450px !important;margin-left: 26px !important;" name="sd_theaurifox_backup">
							<option value="0">Select a Funnel</option>
						</select>
					</div>
				</div>
				<div class="control-group choosePageBox_clickoptin clearfix" style="display: none">
					<label class="control-label" for="sd_thepage_clickoptin"> Choose Page  <i class="fa fa-spinner fa-spin" id="loading"></i></label>
					<div class="controls">
						<select class="input-xlarge " id="sd_thepage_clickoptin"  style="width: 450px !important;margin-left: 26px !important;" name="sd_thepage">
							<option value="0">No Pages Found</option>
						</select>
					</div>
					<div id="noPageWarning_clickoptin" style="font-size: 11px; margin-left: 26px !important; margin-top: -13px;float: left;padding-top: 10px;display: none;width: 100%; clear: both">
						<strong style="font-size: 13px;display: block;">No compatible pages found. </strong>
					</div>
				</div>
				<div class="control-group choosePageBox_clickoptin sd_uses_api clearfix" style="display: none">
					<label class="control-label" for="" style="width: 450px !important;"> Placeholder Text</label>
					<div class="controls">
						<input type="text" class="input-xlarge sd_optinchange" id="sd_placeholder" style="width: 450px !important;margin-left: 26px" value="" placeholder="Text to display for placeholder on email input..." >
					</div>
				</div>
				<div class="control-group choosePageBox_clickoptin sd_uses_api clearfix" style="display: none">
					<label class="control-label" for="" style="width: 450px !important;"> Button Text</label>
					<div class="controls">
						<input type="text" class="input-xlarge sd_optinchange" id="sd_button_text" style="width: 450px !important;margin-left: 26px" value="" placeholder="Text to display on subscribe button..." >
					</div>
				</div>
				<div class="control-group choosePageBox_clickoptin sd_uses_api clearfix" style="display: none">
					<label class="control-label" for=""> Button Color</label>
					<select class="input-xlarge sd_optinchange" id="sd_button_color"  style="width: 450px !important;margin-left: 26px" name="">
						<option value="blue">Blue</option>
						<option value="red">Red</option>
						<option value="grey">Grey</option>
						<option value="green">Green</option>
						<option value="black">Black</option>
					</select>
				</div>
				<div class="control-group choosePageBox_clickoptin sd_uses_api clearfix" style="display: none">
					<label class="control-label" for=""> Redirect on Submit</label>
					<select class="input-xlarge sd_optinchange" id="sd_redirect"  style="width: 450px !important;margin-left: 26px" name="">
						<option value="">Submit Form in Same Page</option>
						<option value="newtab">Submit Form in New Tab</option>
					</select>
				</div>
				<div class="control-group choosePageBox_clickoptin sd_uses_api clearfix" style="display: none">
					<label class="control-label" for=""> Input Icon</label>
					<select class="input-xlarge sd_optinchange" id="sd_input_icon"  style="width: 450px !important;margin-left: 26px" name="">
						<option value="show">Show Envelope Icon</option>
						<option value="emailiimage">Hide Envelope Icon</option>
					</select>
				</div>
				<div class="control-group choosePageBox_clickoptin" style="display: none">
					<label class="control-label" for="sd_shortcode_clickoptin"> ClickOptin Shortcode </label>
					<div class="controls">
						<textarea  class="input-xlarge " id="sd_shortcode_clickoptin" style="width: 450px !important;height: 80px;margin-left: 26px"  placeholder="Shortcode embed code here..."></textarea>
					</div>
				</div>
			</div>
			<div id="tab2" class="sdtabs" style="display: none">
				<h2>Show ClickPop Link or Automated Popup</h2>
				<div class="control-group sd_uses_api clearfix" style="">
					<label class="control-label" for="sd_theaurifox_clickpop"> Choose Funnel  </label>
					<div class="controls">
						<select class="input-xlarge" id="sd_theaurifox_clickpop" style="width: 450px !important;margin-left: 26px !important;" name="sd_theaurifox_backup">
							<option value="0">Select a Funnel</option>
						</select>
					</div>
				</div>
				<div class="control-group choosePageBox_clickpop clearfix" style="display: none">
					<label class="control-label" for="sd_thepage_clickpop"> Choose Page  <i class="fa fa-spinner fa-spin" id="loading"></i></label>
					<div class="controls">
						<select class="input-xlarge" id="sd_thepage_clickpop"  style="width: 450px !important;margin-left: 26px !important;" name="sd_thepage">
							<option value="0">No Pages Found</option>
						</select>
					</div>
					<div id="noPageWarning_clickpop" style="font-size: 11px; margin-left: 26px !important; margin-top: -13px;float: left;padding-top: 10px;display: none;width: 100%; clear: both">
						<strong style="font-size: 13px;display: block;">No compatible pages found. </strong>
					</div>
				</div>
				<div class="control-group choosePageBox_clickpop sd_uses_api clearfix" style="display: none">
					<label class="control-label" for="sd_theaurifox"> Show on Mouse Exit  <small style="margin-right: 32px">(optional)</small></label>
					<select class="input-xlarge" id="sd_exit"  style="width: 450px !important;margin-left: 26px" name="">
						<option value="false">Disabled</option>
						<option value="true">Enable Popup on Mouse Leave</option>
					</select>
				</div>
				<div class="control-group choosePageBox_clickpop sd_uses_api clearfix" style="display: none">
					<label class="control-label" for="sd_theaurifox" style="width: 450px !important;"> Timed Popup Delay <small style="margin-right: 0;">(optional)</small> </label>
					<div class="controls">
						<input type="text" class="input-xlarge " id="sd_delay" style="width: 450px !important;margin-left: 26px" value="" placeholder="Number of seconds for automatic popup." >
					</div>
				</div>
				<div class="control-group choosePageBox_clickpop" style="display: none">
					<label class="control-label" for="sd_shortcode_clickpop"> ClickPop Shortcode </label>
					<div class="controls">
						<textarea  class="input-xlarge " id="sd_shortcode_clickpop" style="height: 80px;width: 450px !important;margin-left: 26px"  placeholder="Shortcode embed code here..."></textarea>
					</div>
				</div>
			</div>
			<div id="tab1" class="sdtabs">
				<h2>Embed aurifox Page on a Blog Post</h2>
				<div class="control-group sd_uses_api clearfix" style="">
					<label class="control-label" for="sd_theaurifox"> Choose Funnel  </label>
					<div class="controls">
						<select class="input-xlarge" id="sd_theaurifox" style="width: 450px !important;margin-left: 26px" name="sd_theaurifox_backup">
							<option value="0">Select a Funnel</option>
						</select>
					</div>
				</div>
				<div class="control-group choosePageBox clearfix" style="<?php if ( empty( $_GET['action'] ) ) {  echo "display: none"; } ?>">
					<label class="control-label" for="sd_thepage"> Choose Page  <i class="fa fa-spinner fa-spin" id="loading"></i></label>
					<div class="controls">
						<select class="input-xlarge" id="sd_thepage"  style="width: 450px !important;margin-left: 26px" name="sd_thepage">
							<option value="0">No Pages Found</option>
						</select>
					</div>
					<div id="noPageWarning" style="font-size: 11px; margin-left: 26px !important; margin-top: -13px;float: left;padding-top: 10px;display: none;width: 100%; clear: both">
						<strong style="font-size: 13px;display: block;">No compatible pages found. </strong>
					</div>
				</div>
                <div class="control-group choosePageBox  clearfix" style="display: none">
                    <label class="control-label" for="sd_height" style="width: 450px !important;"> Height of Iframe</label>
                    <div class="controls">
                        <input type="text" class="input-xlarge sd_embedchange" id="sd_height" style="width: 450px !important;margin-left: 26px" placeholder="Number for height in px." value="650" >
                    </div>
                </div>
                <div class="control-group choosePageBox clearfix" style="display: none">
                    <label class="control-label" for="sd_scrolling"> Allow Page Scrolling  <small style="margin-right: 32px">(optional)</small></label>
                    <select class="input-xlarge sd_embedchange" id="sd_scrolling"  style="width: 450px !important;margin-left: 26px" name="">
                        <option value="yes">Enable Scrolling</option>
                        <option value="no">Disable Scrolling</option>
                    </select>
                </div>
				<div class="control-group choosePageBox" style="display: none">
					<label class="control-label" for="sd_shortcode" > Blog Embed Shortcode </label>
					<div class="controls">
						<textarea  class="input-xlarge " id="sd_shortcode" style="width: 450px !important;height: 80px;margin-left: 26px"  placeholder="Shortcode embed code here..."></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
