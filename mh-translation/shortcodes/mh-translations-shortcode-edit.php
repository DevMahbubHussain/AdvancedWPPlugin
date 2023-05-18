<?php 

if( ! class_exists('MH_Translations_Edit_Shortcode')){
    class MH_Translations_Edit_Shortcode{
        public function __construct(){
            add_shortcode( 'mh_translations_edit', array( $this, 'add_shortcode' ) );
        }

        public function add_shortcode(){
            
            ob_start();
            require( MH_TRANSLATIONS_PATH . 'views/mh-translations_edit_shortcode.php' );
            wp_enqueue_script( 'custom_js' );
            wp_enqueue_style('custom_css');
            wp_enqueue_script( 'validate_js' );
            return ob_get_clean();
        }
    }
}
