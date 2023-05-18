<?php

/**
* Plugin Name: MH Translations
* Plugin URI: https://www.wordpress.org/mh-translations
* Description: My plugin's description
* Version: 1.0
* Requires at least: 5.6
* Requires PHP: 7.0
* Author: Mahbub Hussain
* Author URI: https://www.codigowp.net
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: mh-translations
* Domain Path: /languages
*/
/*
mh Translations is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
mh Translations is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with mh Translations. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !class_exists( 'MH_Translations' )){

	class MH_Translations{

		public function __construct(){

			$this->define_constants(); 
             $this->load_textdomain();

            // add cpt 
            require_once(MH_TRANSLATIONS_PATH . 'post-types/mh-translation-cpt.php');
            $mh_translation_obj = new MH_Translation_CPT();

            //shortcode
            require_once(MH_TRANSLATIONS_PATH . 'shortcodes/mh-translations-shortcode.php');
            $mh_shortcode_obj = new MH_Translations_Shortcode();
            
            require_once(MH_TRANSLATIONS_PATH . 'shortcodes/mh-translations-shortcode-edit.php');
            $mh_shortcode2_obj = new MH_Translations_Edit_Shortcode();

           // enqueue 
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 999 );

            // functions 
            require_once(MH_TRANSLATIONS_PATH . 'functions/functions.php');

           //single page
            add_filter( 'single_template', array( $this, 'load_custom_single_template' ) );


            			
		}

        public function load_textdomain(){
            load_plugin_textdomain(
                'mh-translations',
                false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
            );
        }

		public function define_constants()
        {
            // Path/URL to root of this plugin, with trailing slash.
			define ( 'MH_TRANSLATIONS_PATH', plugin_dir_path( __FILE__ ) );
            define ( 'MH_TRANSLATIONS_URL', plugin_dir_url( __FILE__ ) );
            define ( 'MH_TRANSLATIONS_VERSION', '1.0.0' );
		}

        public function register_scripts(){
            wp_enqueue_style('custom_csss',MH_TRANSLATIONS_URL . 'assets/style.css', array(), MH_TRANSLATIONS_VERSION, true );
            wp_register_style( 'custom_css', MH_TRANSLATIONS_URL . 'assets/style.css', array(), MH_TRANSLATIONS_VERSION, true );
            wp_register_script( 'custom_js', MH_TRANSLATIONS_URL . 'assets/jquery.custom.js', array( 'jquery' ), MH_TRANSLATIONS_VERSION, true );
            wp_register_script( 'validate_js', MH_TRANSLATIONS_URL . 'assets/jquery.validate.min.js', array( 'jquery' ), MH_TRANSLATIONS_VERSION, true );
        }

        public function load_custom_single_template($tpl)
        {
              if(is_singular('mh-trnaslation'))
              {
                 $tpl = MH_TRANSLATIONS_PATH . 'views/templates/single-mh-translations.php';

              }
            return $tpl;
        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            update_option('rewrite_rules', '' );

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            //$table_name = $wpdb->base_prefix . "mh_translationmeta";
            $mh_db_version = get_option('mh_translation_db_version');
            if(empty($mh_db_version)){
                $query = "
                    CREATE TABLE `{$wpdb->base_prefix}translationmetas`(
                    meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    translation_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    meta_key varchar(255) DEFAULT NULL,
                    meta_value longtext,
                    PRIMARY KEY ( meta_id ),
                    KEY translation_id (translation_id),
                    KEY meta_key (meta_key)
                    ) $charset_collate;";


                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($query);
                $mh_db_version = '1.0';
                add_option('mh_translation_db_version', $mh_db_version);
                $success = empty($wpdb->last_error);
                return $success;

            }

            if( $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'submit-translation'" ) === null ){
                
                $current_user = wp_get_current_user();

                $page = array(
                    'post_title'    => __('Submit Translation', 'mv-translations' ),
                    'post_name' => 'submit-translation',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user->ID,
                    'post_type' => 'page',
                    'post_content'  => '<!-- wp:shortcode -->[mh_translations]<!-- /wp:shortcode -->'
                );
                wp_insert_post( $page );
            }

            if( $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'edit-translation'" ) === null ){
                
                $current_user = wp_get_current_user();

                $page = array(
                    'post_title'    => __('Edit Translation', 'mh-translations' ),
                    'post_name' => 'edit-translation',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user->ID,
                    'post_type' => 'page',
                    'post_content'  => '<!-- wp:shortcode -->[mh_translations_edit]<!-- /wp:shortcode -->'
                );
                wp_insert_post( $page );
            }

        }


        /**
         * Deactivate the plugin
         */
        public static function deactivate(){
            flush_rewrite_rules();
            unregister_post_type( 'mh-trnaslation' );
        }        

        /**
         * Uninstall the plugin
         */
        // public static function uninstall(){
        //     delete_option( 'mh_translation_db_version' );

        //     global $wpdb;

        //     $wpdb->query(
        //         "DELETE FROM $wpdb->posts
        //         WHERE post_type = 'mh-trnaslation'"
        //     );

        //     $wpdb->query(
        //         "DELETE FROM $wpdb->posts
        //         WHERE post_type = 'page'
        //         AND post_name IN( 'submit-translation', 'edit-translation' )"
        //     );

        //     $wpdb->query( $wpdb->prepare(
        //         "DROP TABLE IF EXISTS %s",
        //         $wpdb->prefix . 'translationmetas'
        //     ));
        // }       

	}
}

// Plugin Instantiation
if (class_exists( 'MH_Translations' )){

    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'MH_Translations', 'activate'));
    register_deactivation_hook( __FILE__, array( 'MH_Translations', 'deactivate'));
    register_uninstall_hook( __FILE__, array( 'MH_Translations', 'uninstall' ) );

    // Instatiate the plugin class
    $mh_translations = new MH_Translations(); 
}