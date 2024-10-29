<style>.hndle {display: none !important}</style>
<?php
  $post_id = get_the_ID();
  $sd_page_type = get_post_meta( sanitize_key($_GET['post']), "sd_page_type", true );
  $sd_aurifox_id = get_post_meta( sanitize_key($_GET['post']), "sd_aurifox_id", true );
  $sd_aurifox_name = get_post_meta( sanitize_key($_GET['post']), "sd_aurifox_name", true );
  $sd_step_id = get_post_meta( sanitize_key($_GET['post']), "sd_step_id", true );
  $sd_step_name = get_post_meta( sanitize_key($_GET['post']), "sd_step_name", true );
  $sd_step_url = get_post_meta( sanitize_key($_GET['post']), "sd_step_url", true );
  $sd_slug = get_post_meta( sanitize_key($_GET['post']), 'sd_slug', true );
  $sd_authorization_email = sanitize_email(get_option( 'aurifox_api_email' ));
  $sd_authorization_token = sanitize_text_field(get_option( 'aurifox_api_auth' ));
  $sd_homepage = get_option( "aurifox_homepage_post_id" );
  $sd_404 = get_option( "aurifox_404_post_id" );
?>

<script type="text/javascript">
  function string_to_slug(str) {
    str = str.replace(/^\s+|\s+$/g, ''); 
    str = str.toLowerCase();

    var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
    var to   = "aaaaeeeeiiiioooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
      str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9 -]/g, '') 
      .replace(/\s+/g, '-') 
      .replace(/-+/g, '-'); 

    return str;
  }

  function aurifox_get_aurifox_url(id) {
    var js_api_url = '<?php echo AURIFOX_API_URL ?>';
    var js_api_email = '<?php echo $sd_authorization_email ?>';
    var js_api_token = '<?php echo $sd_authorization_token ?>';
    var the_resource;

    if (id) {
      the_resource = 'funneldetails/' + id;
    } else {
      the_resource = 'funnellist';
    }

    return js_api_url + the_resource + '?email=' + js_api_email + '&auth_token=' + js_api_token;
  }

  jQuery(document).ready(function(){
    var $ = jQuery;
    $('.draft').hide();
    console.log("%caurifox WordPress Plugin", "background: #0166AE; color: white;");
    console.log("%cEditing anything inside the console is for developers only. Do not paste in any code given to you by anyone. Use with caution. Visit for support: https://support.aurifox.com/", "color: #888;");

    var selected_aurifox = '<?php echo $sd_aurifox_id ?>';
    var selected_step = '<?php echo $sd_step_id ?>';

    $('#sd_page_type').change(function() {
      if ($(this).val() == 'homepage') {
        $('.sd_url').hide();
        $('#publish').removeClass('disabledLink');
      } else if ($(this).val() == '404') {
        $('.sd_url').hide();
        $('#publish').removeClass('disabledLink');
      } else {
        $('.sd_url').show();
        $('#sd_slug').change();
      }
    }).change();

    $('#loading-aurifoxs').fadeIn();
    $.getJSON(aurifox_get_aurifox_url(), function(data) {
      $.each(data, function() {
        $('#sd_aurifox_id').append('<option value="' + this.id + '">' + this.name + '</option>');
      });
      if (selected_aurifox) {
        $("#sd_aurifox_id option[value='"+ selected_aurifox +"']").prop('selected', true);
      }
      $('#sd_aurifox_id').change();
    }).fail(function() {
      $('.badAPI').show();
    }).always(function() {
      $('#loading-aurifoxs').fadeOut();
    });

    $('#sd_aurifox_id').change(function() {
      $('#loading-steps').fadeIn();
      var aurifox_name = $(this.selectedOptions[0]).text();
      $('.apiSubHeader h2').text(aurifox_name)
      $('#sd_aurifox_name').val(aurifox_name);

      selected_aurifox = this.value;
      $.getJSON(aurifox_get_aurifox_url(selected_aurifox), function(data) {
        $('#sd_step_id').html(''); 

        $.each(data.aurifox_steps, function() {
          $('#sd_step_id').append('<option data-url=' + this.published_url + ' value="' + this.id + '">' + this.name + '</option>');
        });

        if (data.aurifox_steps.length == 0) {
          $('#sd_step_id').fadeOut();
          $('#noPageWarning').fadeIn();
        } else {
          $('#sd_step_id').fadeIn();
          $('#noPageWarning').fadeOut();
        }

        if (selected_step) {
          $("#sd_step_id option[value='"+ selected_step +"']").prop('selected', true);
        }

        $('#sd_step_id').trigger('change');
      }).fail(function() {
        $('.badAPI').show();
      }).always(function() {
        $('#loading-steps').fadeOut();
      });
    });

    $('#sd_step_id').change(function() {
      var published_url = $(this.selectedOptions[0]).data('url');
      $('#sd_step_url').val(published_url);

      var page_name = $(this.selectedOptions[0]).text();
      $('#sd_step_name').val(page_name);
    });

    $('#sd_slug').bind('keyup keypress blur change', function() {
      var myStr = $(this).val().toLowerCase().replace(/\s/g , "-");
      $('#sd_slug').val(myStr);
      slug = $(this).val();
      customSlug = slug;
      customSlug = string_to_slug(customSlug);
      $(this).val(customSlug);

      $('.customSlugText').text(customSlug);
      newurl = $('#sdslugurl').text();
      $('#sdslugurl').attr('href', newurl);
      $('#customurlError').hide();
      $('#customurlError_duplicate').hide();
      $('#publish').removeClass('disabledLink');

      $('.used_slug').each(function () {
        if ($(this).html() == customSlug) {
         $('#customurlError_duplicate').fadeIn();
         $('#publish').addClass('disabledLink');
        }
      });

      if ('' == customSlug) {
       $('#customurlError').fadeIn();
       $('#publish').addClass('disabledLink');
      }
    });

    $('.sdtablink').click(function() {
      if ($(this).hasClass('disabledLink') === false) {
        $('.sdtabs').hide();
        $('.sdtablink').removeClass('active');
        $(this).addClass('active');
        var tab = $(this).attr('data-tab');
        $('#'+tab).show();
      }
    });
  });
</script>


<div id="no-aurifoxs-error" class="badAPI error notice" style="display: none; width: 733px;padding: 10px 12px;font-weight: bold"><i class="fa fa-thumbs-o-down" style="margin-right: 5px;"></i>There are no Funnels in your aurifox account! Click here <a href="https://app.aurifox.com/" target="_blank">aurifox</a> to get add new funnels!</div>

<div id="failed-connection-error" class="badAPI error notice" style="display: none; width: 733px;padding: 10px 12px;font-weight: bold"><i class="fa fa-thumbs-o-down" style="margin-right: 5px;"></i> Failed API Connection with aurifox. Please check <a href="edit.php?post_type=aurifox&page=sd_api&error=compatibility">Settings > Requirements</a> for details.</div>

<div class="logo_img"><img src="<?php echo plugins_url( '../images/logo.png', __FILE__ ); ?>" alt=""></div>
<div class="apiSubHeader">
  <h2>Lead Funnel</h2>
  <?php if ($sd_step_id) {  ?>
    <a style="margin-right: 0;margin-top: -27px;" href="<?php echo AURIFOX_API_URL ?>aurifox/<?php echo $sd_aurifox_id; ?>/steps/<?php echo $sd_step_id; ?>" target="_blank" class="editThisPage"><i class="fa fa-edit"></i>EDIT IN Lead Funnel</a>
    <a style="margin-right: 10px;margin-top: -27px;" href="<?php echo get_home_url() ; ?>/<?php echo $sd_slug; ?>" title="View Page" target="_blank" class="editThisPage"><i class="fa fa-search"></i> PREVIEW</a>
    <?php if ( $sd_page_type=='page' ) { ?><?php }?>
    <?php if ( $sd_page_type=='homepage' ) {?>
       <span style="margin-right: 10px;margin-top: -27px;" class="editThisPage2"><i class="fa fa-home"></i> Home Page</span>
    <?php }?>
    <?php if ( $sd_page_type=='404' ) {?>
        <span style="margin-right: 10px;margin-top: -27px;" class="editThisPage2"><i class="fa fa-exclamation-triangle"></i> 404 Page</span>
    <?php }?>
  <?php }?>
</div>

<?php if ( $sd_authorization_email == "" || $sd_authorization_token == "" ) { ?>
  <div class="noAPI">
      <h4>You haven't setup your Interface settings. <a href="<?php echo get_admin_url() ?>edit.php?post_type=aurifox&page=sd_api">Config</a></h4>
  </div>
<?php } else { ?>

<form method="post">
    <div class="bootstrap-wp"><?php wp_nonce_field( "save_aurifox", "aurifox_nonce" ); ?>
<div id="app_main" class="col-sm-7 row-fluid form-horizontal">
            <div id="tab1" class="sdtabs">
                <h2>Page Settings</h2>
                <div class="innerTab">
                    <div class="control-group ">
                        <label class="control-label" for="sd_page_type"> Choose Page Type</label>
                        <select name="sd_page_type"  id="sd_page_type" class="sd_header" style="width: 100% !important">
                            <option value="page" <?php if($sd_page_type == 'page'){ echo 'selected'; } ?>>Regular Page</option>
                            <option value="homepage"
                              <?php  if($sd_homepage == $post_id) {
                                echo 'selected';
                              } ?>
                            >Home Page</option>
                            <option value="404"
                              <?php  if($sd_404 == $post_id) {
                                echo 'selected';
                              } ?>
                            >404 Page</option>
                        </select>
                    </div>
                </div>

                <div class="control-group sd_uses_api clearfix" style="">
                    <label class="control-label" for="sd_aurifox_id">
                      Choose Funnel  <span id="loading-aurifoxs"><i class="fa fa-spinner"></i> <em style="margin-left: 5px;font-size: 11px;">Loading Funnels...</em></span>
                    </label>
                    <div class="controls">
                        <select class="input-xlarge" id="sd_aurifox_id" name="sd_aurifox_id">
                        </select>
                    </div>
                </div>

                <div class="control-group choosePageBox clearfix">
                    <label class="control-label" for="sd_step_id">
                        Choose Step  <span id="loading-steps"><i class="fa fa-spinner"></i> <em style="margin-left: 5px;font-size: 11px;">Loading Pages...</em></span>
                    </label>
                    <div class="controls">
                        <select class="input-xlarge" id="sd_step_id" name="sd_step_id" style="float: left;">
                        </select>
                    </div>
                    <div id="noPageWarning" style="font-size: 11px; margin-left: 28px; margin-top: -13px;float: left;padding-top: 14px;display: none;width: 100%; clear: both">
                        <strong style="font-size: 13px;display: block;">No compatible pages found. </strong>
                        <em style="display: block">Membership pages and order pages are not available through plugin.</em>
                    </div>
                    <br clear="all">
                </div>

                <div class="control-group" style="display: block">
                    <label class="control-label" for="sd_step_url"> aurifox URL <small>(reference only)</small></label>
                    <div class="controls">
                        <input type="text" class="input-xlarge" name="sd_step_url" id="sd_step_url" readonly="readonly" style="height: 30px;" value="<?php echo $sd_step_url; ?>" />
                    </div>
                </div>

                <div class="sd_url control-group clearfix">
                    <label class="control-label" for="sd_slug">Custom Slug</label>
                    <div id="sd-wp-path" class="controls ">
                       <input  style="padding:10px;"type="text" value="<?php if ( isset( $sd_slug ) ) echo $sd_slug;?>" placeholder="your-path-here" name="sd_slug" id="sd_slug" class="input-xlarge">
                       <div id="customurlError" style="display: none;> color: #E54F3F; font-weight: bold;margin-top: 4px;">
                           Add a path before saving.
                       </div>
                       <div id="customurlError_duplicate" style="display: none;> color: #E54F3F; font-weight: bold;margin-top: 4px;">
                           Slug already taken
                       </div>
                    </div>
                    <p class="infoHelp">
                      <span style="font-weight: bold;text-decoration: none; padding-bottom: 3px;"> <?php echo get_home_url() ; ?>/<span class="customSlugText"><?php echo esc_html($sd_slug); ?></span></span>
                    </p>
                </div>
            </div>

            <div style="display: none">
              <input type="hidden" name="sd_aurifox_name" id="sd_aurifox_name" value="<?php echo esc_attr($sd_aurifox_name); ?>"  />
              <input type="hidden" name="sd_step_name" id="sd_step_name" value="<?php echo esc_attr($sd_step_name); ?>"  />
            </div>

            <div id="savePage">
                <div style="width: 100%">
                    <input type="submit" name="publish" id="publish" value="Save Page" class="action-button shadow animate green" style="float: right; ">
                    <div id="saving" style="float: right;display: none; padding-right: 10px;opacity: .6;padding-top: 9px;margin-right: 4px;font-size: 15px;">
                         <i class="fa fa-spinner fa-spin"></i>
                         <span>Saving...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php } ?>
