<?php
class MDC_TinyMCE_editor{
    public function __construct(){
        add_action('init', array($this, 'mdc_shortcode_button_init'));
        add_action( 'admin_enqueue_scripts', array($this, 'mdc_admin_enqueue_scripts' ));
    }
    // init process for registering our button
    public function mdc_shortcode_button_init() {

        // Abort early if the user will never see TinyMCE
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
        return;

        // Add a callback to regiser our tinymce plugin   
        add_filter("mce_external_plugins", array($this, "mdc_register_tinymce_plugin")); 

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', array($this, 'mdc_add_tinymce_button'));
    }

    // This callback registers our plug-in
    public function mdc_register_tinymce_plugin($plugin_array) {
        $plugin_array['mdc_button'] = plugins_url('../js/custom.js', __FILE__);
        return $plugin_array;
    }

    // This callback adds our button to the toolbar
    public function mdc_add_tinymce_button($buttons) {
        // Add the button ID to the $button array
        $buttons[] = "mdc_button";
        return $buttons;
    }

    public function mdc_admin_enqueue_scripts() {
        // wp_enqueue_style( 'mdc-adfly-style', plugins_url('/css/admin.css',__FILE__));
        wp_enqueue_script( 'mdc-yt-scipt', plugins_url('../js/admin.js',__FILE__));

        // localize script
        $wp_ulrs = array( 'yt_icon' => plugins_url('../images/icon.png',__FILE__) );
        wp_localize_script( 'mdc-yt-scipt', 'wp_ulrs', $wp_ulrs );
    }
}
$tiny = new MDC_TinyMCE_editor;