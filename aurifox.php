<?php
/**
    * Plugin Name: AuriFox 
    * Plugin URI: https://aurifox.com/
    * Description: Now you can show any aurifox page as your homepage or 404 error page or choose any other page and make simple URLs to your aurifix page by connecting to our account with simple authorization key
    * Version: 1.0.2
    * Author: Sun Soft, Inc
    * Author URI: http://sunsoftny.com/
*/
$siteurl = get_option( 'AURIFOX_API_URL' );
$url = $siteurl.'api/';
define( "AURIFOX_API_URL", $url );
class aurifox {
    public function __construct() {
        wp_enqueue_style( 'myplugin-style', plugin_dir_url(__FILE__) . 'css/admin.min.css' );
        wp_enqueue_style( 'myplugin-font-style', plugin_dir_url(__FILE__) . 'css/font-awesome.css' );
        wp_enqueue_script( 'update-meta', plugin_dir_url(__FILE__) . 'js/tag.min.js' );
        add_action( "init", array( $this, "aurifox_create_custom_post_type" ) );
        add_action( 'plugins_loaded', 'aurifox_upgrade_existing_posts' );
        add_action( 'add_meta_boxes', array( $this, 'aurifox_add_meta_box' ) );
        add_filter( 'manage_edit-aurifox_columns', array( $this, 'aurifox_add_columns' ) );
        add_action( 'save_post', array( $this, 'aurifox_save_meta' ), 10, 1 );
        add_action( 'manage_posts_custom_column', array( $this, 'aurifox_fill_columns' ) );
        add_action( "template_redirect", array( $this, "aurifox_process_page_request" ), 1, 2 );
        add_action( 'trashed_post', array( $this, 'aurifox_post_trash' ), 10 );
        add_filter( 'post_updated_messages', array( $this, 'aurifox_updated_message' ) );
        if ( get_option( 'permalink_structure' ) == '' ) {
            $message = '<div id="message" class="badAPI error notice" style="width: 733px;padding: 10px 12px;font-weight: bold"><i class="fa fa-thumbs-o-down" style="margin-right: 5px;"></i> Error in aurifox plugn, please check <a href="edit.php?post_type=aurifox&page=sd_api&error=compatibility">Settings > Requirements</a> for details.</div>';
            add_action( "admin_notices", array( $this, $message ) );
        }
    }

    public function aurifox_process_page_request() {
        if (is_front_page()) {
            if ($this->aurifox_get_home()) {
                status_header(200);
                $this->aurifox_show_post( $this->aurifox_get_home() );
                exit();
            } else {
                return; 
            }
        }

        $full_request_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $request_url_parts = explode( "?", $full_request_url );
        $request_url = $request_url_parts[0];
        $base_url = get_home_url()."/";
        $slug = str_replace( $base_url, "", $request_url );
        $slug = rtrim( $slug, '/' );

        if ($slug != '') {
            $query_args = array(
                'meta_key' => 'sd_slug',
                'meta_value' => $slug,
                'post_type' => 'aurifox',
                'compare' => '='
            );

            $the_posts = get_posts($query_args);
            $sd_page = current($the_posts);

            if ($sd_page) {
                status_header(200);
                $this->aurifox_show_post( $sd_page->ID );
                exit();
            }
        }

        if (is_404()) {
            if ($this->aurifox_get_404()) {
                $this->aurifox_show_post( $this->aurifox_get_404() );
                exit();
            } else {
                return;
            }
        }
        return; 
    }

    public function aurifox_show_post( $post_id ) {
        $url = get_post_meta( $post_id, "sd_step_url", true );
        $method = get_option('aurifox_display_method');

        if ($method == 'download') {
            echo $this->aurifox_get_page_content($url);
        } else if ($method == 'iframe') {
            echo $this->aurifox_get_page_iframe($url);
        } else if ($method == 'redirect') {
            wp_redirect($url, 301);
        }

        exit();
    }

    

    public function aurifox_get_page_content( $url ) 
    {
        $response11 = wp_remote_post($url);
        $body = $response11['body'];
        return $body;
    }

    public function aurifox_get_page_iframe( $sd_step_url ) {
        if (has_site_icon() && (get_option('aurifox_favicon_method') == 'wordpress')) {
            $favicon = '<link class="wp_favicon" href="'.get_site_icon_url().'" rel="shortcut icon"/>';
        } 
        else 
        {
            $url =  AURIFOX_API_URL.'getsiteicon';
            $args = array(
                            'step_url' => $sd_step_url,
                        );
            $response = wp_remote_post($url, $args);
            $favicon = '<link class="wp_favicon" href="'.esc_url($response).'" rel="shortcut icon"/>';
        }

        $additional_snippet = html_entity_decode(stripslashes(get_option('aurifox_additional_snippet')));

        return '<!DOCTYPE html>
            <head>
                '.esc_html($favicon).'
                <style>
                    body {
                        margin: 0;            /* Reset default margin */
                    }
                    iframe {
                        display: block;       /* iframes are inline by default */
                        border: none;         /* Reset default border */
                        height: 100vh;        /* Viewport-relative units */
                        width: 100vw;
                    }
                </style>
                <meta name="viewport" content="width=device-width, initial-scale=1">
            </head>
            <body>
                '.esc_html($additional_snippet).'
                
                <iframe width="100%" height="100%" src="'.esc_url($sd_step_url).'" frameborder="0" allowfullscreen></iframe>
            </body>
        </html>';
    }

    public function aurifox_updated_message( $messages ) {
        $post_id = get_the_ID();
        if ( get_post_meta( $post_id, "sd_step_id", true ) == "" )
            return $messages;

        $our_message = '<strong><i class="fa fa-thumbs-o-up" style="margin-right: 5px;"></i> Successfully saved and updated your aurifox page.</strong>';

        $messages['post'][1] = $our_message;
        $messages['post'][4] = $our_message;
        $messages['post'][6] = $our_message;
        $messages['post'][10] = $our_message;

        return $messages;
    }

    public function aurifox_post_trash( $post_id ) {
        if ( $this->is_404( $post_id ) ) {
            $this->aurifox_set_404(NULL);
        }
        if ( $this->aurifox_is_home( $post_id ) ) {
            $this->aurifox_set_home(NULL);
        }
    }

    public function aurifox_save_meta( $post_id ) {
        global $_POST;

        if (sanitize_key($_POST['post_type']) != 'aurifox') {
            return;
        }

        $sd_slug = sanitize_key($_POST['sd_slug']);
        $sd_page_type = sanitize_key($_POST['sd_page_type']);
        $sd_step_id = sanitize_key($_POST['sd_step_id']);
        $sd_step_name = sanitize_key($_POST['sd_step_name']);
        $sd_aurifox_id = sanitize_key($_POST['sd_aurifox_id']);
        $sd_aurifox_name = sanitize_key($_POST['sd_aurifox_name']);
        $sd_step_url = sanitize_text_field($_POST['sd_step_url']);

        if (isset($sd_slug)) {
            update_post_meta( $post_id, "sd_slug", $sd_slug );
        }
        if (isset($sd_page_type)) {
            update_post_meta( $post_id, "sd_page_type", $sd_page_type );
        }
        if (isset($sd_step_id)) {
            update_post_meta( $post_id, "sd_step_id", $sd_step_id );
        }
        if (isset($sd_step_name)) {
            update_post_meta( $post_id, "sd_step_name", $sd_step_name );
        }
        if (isset($sd_aurifox_id)) {
            update_post_meta( $post_id, "sd_aurifox_id", $sd_aurifox_id );
        }
        if (isset($sd_aurifox_name)) {
            update_post_meta( $post_id, "sd_aurifox_name", $sd_aurifox_name );
        }
        if (isset($sd_step_url)) {
            update_post_meta( $post_id, "sd_step_url", $sd_step_url );
        }

        if ($this->is_404($post_id)) {
            $this->aurifox_set_404(NULL);
        } else if ($this->aurifox_is_home($post_id)) {
            $this->aurifox_set_home(NULL);
        }

        if ($sd_page_type == "homepage") {
            $this->aurifox_set_home( $post_id );
        } else if ($sd_page_type == "404") {
            $this->aurifox_set_404( $post_id );
        }
    }

    public function aurifox_set_home( $post_id ) {
        update_option( 'aurifox_homepage_post_id', $post_id);
    }

    public function aurifox_get_home() {
        return get_option( "aurifox_homepage_post_id" );
    }

    public function aurifox_is_home( $post_id ) {
        return $post_id == get_option( "aurifox_homepage_post_id" );
    }

    public function aurifox_set_404( $post_id ) {
        update_option( 'aurifox_404_post_id', $post_id);
    }

    public function aurifox_get_404() {
        return get_option( "aurifox_404_post_id" );
    }

    public function is_404( $post_id ) {
        return $post_id == get_option( "aurifox_404_post_id" );
    }

    public function aurifox_add_columns( $columns ) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['sd_post_name'] = "Page";
        $new_columns['sd_post_aurifox'] = "Funnel";
        $new_columns['sd_path'] = 'View';
        $new_columns['sd_open_in_editor'] = 'Edit Funnel';
        $new_columns['sd_page_type'] = 'Type';
        return $new_columns;
    }

    public function aurifox_fill_columns( $column ) {
        $id = get_the_ID();
        $sd_page_type = get_post_meta( $id, 'sd_page_type', true );
        $sd_slug = get_post_meta( $id, 'sd_slug', true );
        $sd_step_id = get_post_meta( $id, 'sd_step_id', true );
        $sd_step_name = get_post_meta( $id, 'sd_step_name', true );
        $sd_aurifox_id = get_post_meta( $id, 'sd_aurifox_id', true );
        $sd_aurifox_name = get_post_meta( $id, 'sd_aurifox_name', true );

        if ( 'sd_post_name' == $column ) {
            $url = get_edit_post_link( get_the_ID() );
            echo '<strong><a href="' . esc_url($url) .'">'. esc_html($sd_step_name) .'</a></strong>';
        }
        if ( 'sd_post_aurifox' == $column ) {
            echo '<strong>'.esc_html($sd_aurifox_name).'</strong>';
        }
        if ( 'sd_open_in_editor' == $column ) {
            echo "<strong><a href='" . AURIFOX_API_URL . "aurifox/" . $sd_aurifox_id . "/steps/". $sd_step_id ."' target='_blank'>Open in aurifox</a></strong>";
        }

        switch ( $sd_page_type ) {
        case "page":
            $post_type = "Page";
            $url = get_home_url()."/".$sd_slug;
            break;
        case "homepage":
            $post_type = "<img src='".plugins_url( 'images/home.png', __FILE__ )."' style='margin-right: 2px;margin-top: 3px;opacity: .7;width: 16px;height: 16px;' />Home Page";
            $url = get_home_url().'/';
            break;
        case "404":
            $post_type = "<img src='".plugins_url( 'images/attention.png', __FILE__ )."' style='margin-right: 2px;margin-top: 3px;opacity: .7;width: 16px;height: 16px;' />404 Page";
            $url = get_home_url().'/test-url-404-page';
            break;
        default:
            $post_type = $sd_page_type;
            $url = get_edit_post_link( get_the_ID() );
        }

        if ( 'sd_page_type' == $column ) {
           echo "<strong>$post_type</strong>";
        }
        if ( 'sd_path' == $column ) {
            echo "<strong><a href='$url' target='_blank'>View Page</a></strong>";
        }
    }

    public function aurifox_add_meta_box() {
        add_meta_box(
            'aurifox_meta_box', 
            'Setup Your aurifox Page',
            array( $this, "aurifox_show_meta_box" ),
            'aurifox', 
            'normal', 
            'high' 
        );
    }

    public function aurifox_show_meta_box( $post ) {
        include 'pages/edit.php';
    }

    public function aurifox_remove_save_box() {
        global $wp_meta_boxes;
        foreach ( $wp_meta_boxes['aurifox'] as $k=>$v )
            foreach ( $v as $l=>$m )
                foreach ( $m as $o=>$p )
                    if ( $o !="aurifox_meta_box" )
                        unset( $wp_meta_boxes['aurifox'][$k][$l][$o] );
    }

    public function aurifox_create_custom_post_type() {
        $labels = array(
            'name' => _x( 'AuriFox', 'post type general name' ),
            'singular_name' => _x( 'Pages', 'post type singular name' ),
            'add_new' => _x( 'Add Funnels', 'AuriFox' ),
            'add_new_item' => __( 'Add New aurifox Page' ),
            'edit_item' => __( 'Edit Funnel' ),
            'new_item' => __( 'Add Funnels' ),
            'all_items' => __( 'Funnels' ),
            'view_item' => __( 'View AuriFox Pages' ),
            'search_items' => __( 'Search AuriFox Funnels' ),
            'not_found' => __( 'No Funnels Yet <br>
                              <a href="'.get_admin_url().'post-new.php?post_type=aurifox">add a new page</a> or <a href="'.get_admin_url().'edit.php?post_type=aurifox&page=sd_api/">finish plugin set-up</a>' ),
            'parent_item_colon' => '',
            'hide_post_row_actions' => array('trash', 'edit' ,'quick-edit')
        );

        register_post_type( 'aurifox',
            array(
                'labels' =>  $labels,
                'public' => true,
                'menu_icon' => plugins_url( 'images/icon.png', __FILE__ ),
                'has_archive' => true,
                'supports' => array( '' ),
                'rewrite' => array( 'slug' => 'aurifox' ),
                'register_meta_box_cb' => array( $this, "aurifox_remove_save_box" ),
                'hide_post_row_actions' => array( 'trash' )
            )
        );
    }
}

function aurifox_plugin_activated() {
    if (!get_option('aurifox_display_method')) {
        update_option('aurifox_display_method', 'download');
    }
    aurifox_upgrade_existing_posts();
}

function aurifox_upgrading_aurifox_posts() {
    ?>
    <div class="error notice">
        <p>Your aurifoxs posts have been upgraded to a new version.</p>
        <p>In order to confirm to the new format, you may need to recreate your homepage and 404 page manually.</p>
    </div>
    <?php
}

function aurifox_upgrade_existing_posts() {
    if (get_option('aurifox_posts_schema_version') == 1) {
        return;
    }
    add_action( 'admin_notices', 'aurifox_upgrading_aurifox_posts' );

    $sd_options = get_option( "sd_options" );
    $args = array(
        'posts_per_page' => -1,
        'post_type' =>'aurifox',
        'post_status' => 'any',
        'fields' => 'id'
    );
    $the_posts = get_posts( $args );
    if (is_array($the_posts)) {
        foreach ($the_posts as $the_post) {
            $id = $the_post->ID;
            $sep = '{#}';

            $url = get_post_meta($id, 'sd_iframe_url', true);
            $aurifox = get_post_meta($id, 'sd_theaurifox', true);
            $thepage = get_post_meta($id, 'sd_thepage', true);
            $slug = get_post_meta($id, 'sd_slug', true);

            if ($url && $aurifox && $thepage && $slug) {
                $aurifox_parts = explode($sep, $aurifox);
                $aurifox_id = $aurifox_parts[0];
                $aurifox_name = $aurifox_parts[11];
                $page_parts = explode($sep, $thepage);
                $page_name = $page_parts[5];
            } else {
                if (isset($sd_options)) {
                    foreach ($sd_options['pages'] as $key => $value) {
                        $parts = explode($sep, $value);
                        if ($parts[5] == $id) {
                            $url = $parts[7];
                            $aurifox_id = $parts[0];
                            $aurifox_name = $parts[11];
                            $page_name = $parts[6];
                            $slug = $key;
                            break;
                        }
                    }
                }
            }


            if ($url && $aurifox_id && $slug) {
                if (!get_post_meta($id, 'sd_slug', true)){
                   update_post_meta($id, 'sd_slug', $slug);
                }
                if (!get_post_meta($id, 'sd_step_url', true)){
                   update_post_meta($id, 'sd_step_url', $url);
                }
                if (!get_post_meta($id, 'sd_aurifox_id', true)){
                   update_post_meta($id, 'sd_aurifox_id', $aurifox_id);
                }
                if (!get_post_meta($id, 'sd_aurifox_name', true)){
                   update_post_meta($id, 'sd_aurifox_name', $aurifox_name);
                }
                if (!get_post_meta($id, 'sd_step_name', true)){
                    update_post_meta($id, 'sd_step_name', $page_name);
                }

                $page_type = get_post_meta($id, 'sd_type', true);

                if ($page_type == 'hp' || $page_type == 'homepage') {
                    if (!get_option('aurifox_homepage_post_id')) {
                        update_option( 'aurifox_homepage_post_id', $id);
                    }
                    update_post_meta($id, 'sd_page_type', 'homepage');
                } else if ($page_type == 'np' || $page_type == '404') {
                    if (!get_option('aurifox_404_post_id')) {
                        update_option( 'aurifox_404_post_id', $id);
                    }
                    update_post_meta($id, 'sd_page_type', '404');
                } else {
                    update_post_meta($id, 'sd_page_type', 'page');
                }
            }
        }
    }
    update_option('aurifox_posts_schema_version', 1);
}

register_activation_hook( __FILE__, 'aurifox_plugin_activated' );

function aurifox_sd_plugin_submenu() {
    add_submenu_page(
        'edit.php?post_type=aurifox',
        __( 'aurifox Shortcodes', 'aurifox-menu' ),
        __( 'Shortcodes', 'aurifox-menu' ),
        'manage_options',
        'aurifox_shortcodes',
        'aurifox_shortcodes'
    );
    add_submenu_page(
        'edit.php?post_type=aurifox',
        __( 'Settings', 'aurifox-menu' ),
        __( 'Settings', 'aurifox-menu' ),
        'manage_options',
        'sd_api',
        'aurifox_sd_api_settings_page'
    );
    add_submenu_page(
        null,
        __( 'Reset Data', 'aurifox-menu' ),
        __( 'Reset Data', 'aurifox-menu' ),
        'manage_options',
        'reset_data',
        'aurifox_reset_data_show_page'
    );
}
add_action( 'admin_menu', 'aurifox_sd_plugin_submenu' );

function aurifox_reset_data_show_page(){
    include 'pages/reset_data.php';
}

function aurifox_sd_api_settings_page() {
    include 'pages/settings.php';
}

function aurifox_shortcodes() {
    include 'pages/shortcodes.php';
}

function aurifox_loadjquery($hook) {
    if( $hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php' ) {
        return;
    }
    wp_enqueue_script( 'jquery' );
}
add_action('admin_enqueue_scripts', 'aurifox_loadjquery');



function aurifox_embed( $atts ) {
    $a = shortcode_atts( array(
        'height' => '650',
        'scroll' => 'on',
        'url' => '<?php echo AURIFOX_API_URL ?>',
    ), $atts );

    return "<iframe src='{$a['url']}' width='100%' height='{$a['height']}' frameborder='0' scrolling='{$a['scroll']}'></iframe>";
}
add_shortcode( 'aurifox_embed', 'aurifox_embed' );


function aurifox_clickpop_script() {
    wp_enqueue_script( 'sd_clickpop' );
}
add_action( 'wp_enqueue_scripts', 'aurifox_clickpop_script' );
function aurifox_clickpop( $atts, $content = null ) {
    $a = shortcode_atts( array(
        'exit' => 'false',
        'delay' => '',
        'id' => '',
        'subdomain' => '',
    ), $atts );
    if ($a['delay'] != '') {
        $delayTime = "{$a['delay']}000";
        $delay_js = "<script>window.onload=function(){setTimeout(clickpop_timed_click, $delayTime);}; function clickpop_timed_click(){for (links=document.getElementsByTagName('a'), i=0; i < links.length; ++i) link=links[i], null !=link.getAttribute('href') && link.getAttribute('href').match(/\/optin_box\/(([a-zA-Z]|\d){16})/i) && (sd_showpopup(link.getAttribute('href'))); function openPopup(e){if (ID=e.hashCode(), currentPopup=ID, sd_iframe=document.getElementById(ID), null==document.getElementById(ID)){var t=document.getElementsByTagName(\"body\"), n=e; document.body.innerHTML +='<iframe src=\"' + n + '?iframe=true\" id=\"' + ID + '\" style=\"position: fixed !important; left: 0px; top: 0px !important; width: 100%; border: none; z-index: 999999999999999 !important; visibility: hidden; \"></iframe>'}document.getElementById(ID).style.width=viewWidth + \"px\", document.getElementById(ID).style.height=viewHeight + \"px\", document.getElementById(ID).style.visibility=\"visible\", makeWindowModal(); var i=document.documentElement, t=document.body, o=i && i.scrollLeft || t && t.scrollLeft || 0, d=i && i.scrollTop || t && t.scrollTop || 0; document.getElementById(ID).style.top=0 + \"px\", document.getElementById(ID).style.left=o + \"px\"; var l=0; return reanimateMessageIntervalID=setInterval(function(){iframe=document.getElementById(ID), void 0 !=iframe && iframe.contentWindow.postMessage(\"reanimate\", \"*\"), ++l >=15 && clearInterval(reanimateMessageIntervalID)}, 1e3), !1}function sd_showpopup(url){openPopup(url);}}</script>";
    } else {
        $delayTime = '';
        $delay_js = "";
    }
    if (strpos($a['subdomain'], '.') !== false) {
        return "<a href='https://{$a['subdomain']}/optin_box/{$a['id']}' data-exit='{$a['exit']}'>$content</a>$delay_js";
    }
    else {
      return "<a href='https://{$a['subdomain']}.aurifox.com/optin_box/{$a['id']}' data-exit='{$a['exit']}'>$content</a>$delay_js";
    }

}
add_shortcode( 'aurifox_clickpop', 'aurifox_clickpop' );



function aurifox_clickoptin( $atts ) {
    $a = shortcode_atts( array(
        'button_text' => 'Subscribe To Our Mailing List',
        'button_color' => 'blue',
        'placeholder' => 'Enter Your Email Address Here',
        'id' => '#',
        'subdomain' => '#',
        'input_icon' => 'show',
        'redirect' => '',
    ), $atts );
    if ($a['button_text'] == '') {
        $button_text = 'Subscribe To Our Mailing List';
    } else {
        $button_text = $a['button_text'];
    }

    if ($a['placeholder'] == '') {
        $placeholder = 'Enter Your Email Address Here';
    } else {
        $placeholder = $a['placeholder'];
    }

    if (strpos($a['subdomain'], '.') !== false) {
        $subdomain = $a['subdomain'];
    } else {
      $subdomain = $a['subdomain'] . '.aurifox.com';
    }


    return "<div id='clickoptin_sd_wrapper_".$a['id']."' class='clickoptin_".$a['theme_style']."'>
    <input type='text' id='clickoptin_sd_email_".$a['id']."' placeholder='".$placeholder."' class='clickoptin_".$a['input_icon']."' />
    <span class='clickoptin_".$a['button_color']."' id='clickoptin_sd_button_".$a['id']."'>".esc_html($button_text)."</span>
</div>
<script>
    if (!window.jQuery) {
      var jq = document.createElement('script'); jq.type = 'text/javascript';
      document.getElementsByTagName('head')[0].appendChild(jq);
      var jQueries = jQuery.noConflict();
        jQueries(document).ready(function($) {
            jQueries( '#clickoptin_sd_button_".$a['id']."' ).click(function() {
                var check_email = jQueries( '#clickoptin_sd_email_".$a['id']."' ).val();
                if (check_email != '' && /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(check_email)) {
                    jQueries( '#clickoptin_sd_email_".$a['id']."' ).addClass('clickoptin_sd_email_green');
                    if('".$a['redirect']."' == 'newtab') {
                        window.open('https://".$subdomain."/instant_optin/".$a['id']."/'+jQueries( '#clickoptin_sd_email_".$a['id']."' ).val(), '_blank');
                    }
                    else {
                        window.location.href = 'https://".$subdomain."/instant_optin/".$a['id']."/'+jQueries( '#clickoptin_sd_email_".$a['id']."' ).val();
                    }
                }
                else {
                   jQueries( '#clickoptin_sd_email_".$a['id']."' ).addClass('clickoptin_sd_email_red');
                }
            });
        });
    }
    else {
      var jq = document.createElement('script'); jq.type = 'text/javascript';
      document.getElementsByTagName('head')[0].appendChild(jq);
      var $ = jQuery.noConflict();
        $(document).ready(function($) {
            $( '#clickoptin_sd_button_".$a['id']."' ).click(function() {
                var check_email = $( '#clickoptin_sd_email_".$a['id']."' ).val();
                if (check_email != '' && /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(check_email)) {
                    $( '#clickoptin_sd_email_".$a['id']."' ).addClass('clickoptin_sd_email_green');
                    if('".$a['redirect']."' == 'newtab') {
                        window.open('https://".$subdomain."/instant_optin/".$a['id']."/'+$( '#clickoptin_sd_email_".$a['id']."' ).val(), '_blank');
                    }
                    else {
                        window.location.href = 'https://".$subdomain."/instant_optin/".$a['id']."/'+$( '#clickoptin_sd_email_".$a['id']."' ).val();
                    }
                }
                else {
                   $( '#clickoptin_sd_email_".$a['id']."' ).addClass('clickoptin_sd_email_red');
                }
            });
        });
    }

</script>
<style>
    #clickoptin_sd_wrapper_".$a['id']." * {
        margin: 0;
        padding: 0;
        position: relative;
        font-family: Helvetica, sans-serif;
    }
    #clickoptin_sd_wrapper_".$a['id']." {
        padding: 5px 15px;
        border-radius: 4px;
        width: 100%;
        margin: 20px 0;
    }
    #clickoptin_sd_wrapper_".$a['id'].".clickoptin_dropshadow_off {
        box-shadow: none;
    }
    #clickoptin_sd_email_".$a['id']." {
        display: block;
        background: #fff;
        color: #444;
        border-radius: 5px;
        padding: 10px;
        width: 100%;
        font-size: 15px;
        border: 2px solid #eee;
        text-align: left;
    }
    #clickoptin_sd_email_".$a['id'].".clickoptin_show {
        background: #fff url(plugins_url( '../images/email.png', __FILE__ )) no-repeat right;
        background-position: 97% 50%;
    }
    #clickoptin_sd_email_".$a['id'].".clickoptin_sd_email_red {
        border: 2px solid #E54E3F;
    }
    #clickoptin_sd_email_".$a['id'].".clickoptin_sd_email_green {
        border: 2px solid #339933;
    }
    #clickoptin_sd_button_".$a['id']." {
        display: block;
        font-weight: bold;
        background: #0166AE;
        border: 1px solid #01528B;
        border-bottom: 3px solid #01528B;
        color: #fff;
        border-radius: 5px;
        padding: 8px;
        width: 100%;
        font-size: 16px;
        margin-top: 8px;
        cursor: pointer;
        text-align: center;
    }
    #clickoptin_sd_button_".$a['id'].".clickoptin_red {
        background: #F05A38;
        border: 1px solid #D85132;
        border-bottom: 3px solid #D85132;
    }
    #clickoptin_sd_button_".$a['id'].".clickoptin_green {
        background: #339933;
        border: 1px solid #2E8A2E;
        border-bottom: 3px solid #2E8A2E;
    }
    #clickoptin_sd_button_".$a['id'].".clickoptin_black {
        background: #23282D;
        border: 1px solid #111;
        border-bottom: 3px solid #111;
    }
    #clickoptin_sd_button_".$a['id'].".clickoptin_grey {
        background: #fff;
        color: #0166AE;
        border: 1px solid #eee;
        border-bottom: 3px solid #eee;
    }
</style>";
}
add_shortcode( 'aurifox_clickoptin', 'aurifox_clickoptin' );


add_filter('widget_text', 'do_shortcode');
class aurifox_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'aurifox_widget',
            esc_html(__('aurifox Shortcode', 'aurifox_widget_domain')),
            array( 'description' => esc_html(__( 'Paste your aurifox shortcodes here to embed an iframe, a ClickPop link or show a ClickForm box in your sidebar or footer.', 'aurifox_widget_domain' )), )
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $shortcode = apply_filters( 'widget_title', $instance['shortcode'] );
        echo $args['before_widget'];
        if ( ! empty( $title ) ) echo '<h3 style="text-align: center;">'.$title.'</h3>';
        if ( ! empty( $shortcode ) ) echo do_shortcode(htmlspecialchars_decode(($shortcode)));
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = esc_html(__( '', 'aurifox_widget_domain' ));
        }
        if ( isset( $instance[ 'shortcode' ] ) ) {
            $shortcode = $instance[ 'shortcode' ];
        }
        else {
            $shortcode = esc_html(__( '', 'aurifox_widget_domain' ));
        }

        ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Headline:' ); ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'shortcode' ); ?>"><?php _e( 'Shortcode:' ); ?></label>
                <textarea style="height: 130px;font-size: 12px;color: #555;" class="widefat" id="<?php echo $this->get_field_id( 'shortcode' ); ?>" name="<?php echo $this->get_field_name( 'shortcode' ); ?>" ><?php echo esc_attr( $shortcode ); ?></textarea>
            </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ?  $new_instance['title']  : '';
        $instance['shortcode'] = ( ! empty( $new_instance['shortcode'] ) ) ? $new_instance['shortcode']  : '';
        return $instance;
    }
}

function aurifox_widget_load() {
    register_widget( 'aurifox_widget' );
}
add_action( 'widgets_init', 'aurifox_widget_load' );

add_action('all_admin_notices', 'aurifox_edit_page_settings');
function aurifox_edit_page_settings() {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    if (isset($_GET['post_type']) and $_GET['post_type'] == 'aurifox' && strpos($url,'edit.php') !== false && !isset($_GET['page'])) {
    ?>
        <script>
            jQuery(function() {
                jQuery('.wrap h1').attr('style', 'font-weight: bold;');
                jQuery('.wrap h1').first().prepend('<img src="<?php echo plugins_url( 'images/logo.png', __FILE__ ); ?>" style="margin-right: 5px;margin-bottom: -7px" />');
            });
        </script>
    <?php
    }
}

$sd = new aurifox();

?>