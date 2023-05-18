<?php 

if(!class_exists('MH_Translation_CPT'))
{

    class MH_Translation_CPT{

        public function __construct()
        {
           add_action('init', array($this, 'mh_translation_cpt'));
           add_action( 'init', array($this,'mh_translation_type_taxonomy'), 0 );
           add_action('init', array($this, 'translationmeta'));
           add_action(  'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
           add_action( 'wp_insert_post', array( $this, 'save_post' ), 10, 2 );
           add_action( 'delete_post', array( $this, 'delete_post' ),10,2 );
           add_action( 'pre_get_posts', array( $this, 'add_cpt_author' ) );
           add_action( 'pre_get_posts', array( $this,'get_taxonomies_pre_get_posts' ) );
        }

        public function mh_translation_cpt()
        {
            $labels = array(
                'name'                => _x( 'Translations', 'Post Type General Name', 'mh-translations' ),
                'singular_name'       => _x( 'Translation', 'Post Type Singular Name', 'mh-translations' ),
                'menu_name'           => __( 'Translations', 'mh-translations' ),
                'parent_item_colon'   => __( 'Parent Translation', 'mh-translations' ),
                'all_items'           => __( 'All Translations', 'mh-translations' ),
                'view_item'           => __( 'View Translation', 'mh-translations' ),
                'add_new_item'        => __( 'Add New Translation', 'mh-translations' ),
                'add_new'             => __( 'Add New Translation', 'mh-translations' ),
                'edit_item'           => __( 'Edit Translation', 'mh-translations' ),
                'update_item'         => __( 'Update Translation', 'mh-translations' ),
                'search_items'        => __( 'Search Translation', 'mh-translations' ),
                'not_found'           => __( 'No Translation Found', 'mh-translations' ),
                'not_found_in_trash'  => __( 'Not found in trash', 'mh-translations' ),
            );

             $args = array(
                'label'               => __( 'Translation', 'mh-translations' ),
                'description'         => __( 'Translation', 'mh-translations' ),
                'labels'              => $labels,
                // Adding Support for the Post type
                'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
                /*
                * Hierarchical: Like Pages, can have parents or children.
                * Non Hierachical: Like Posts, can't have parents or childs.
                */
                'hierarchical'        => false,
                'public'              => true, //how the type is visible to authors
                'show_ui'             => true, //Generate a default UI in the admin
                'show_in_menu'        => true, //Show or not in the admin menu
                'show_in_nav_menus'   => true, //Is available to select on Nav Menus
                'show_in_admin_bar'   => true, // show or not in the admin bar
                'menu_position'       => 5, // The position order of the post type in the admin menu
                'menu_icon'           => 'dashicons-admin-page', // the icon for the admin menu
                'can_export'          => true, // can this post type be exported
                'has_archive'         => true, //enables archives to this post type
                'exclude_from_search' => false, // exclude from search results
                'capability_type'     => 'page', // the name of the capability that will be generated or used
                'show_in_rest'        => true,
                'rewrite'       => array('slug' =>'mh-trnaslations'),
                );

            register_post_type( 'mh-trnaslation', $args );
        }


        public function mh_translation_type_taxonomy()
        {
            $labels = array(
                'name'              => _x( 'Singer', 'mh-translations'),
                'singular_name'     => _x( 'Singer', 'mh-translations'),
                'search_items'      => __( 'Search Singer','mh-translations'),
                'all_items'         => __( 'All Singers','mh-translations'),
                'parent_item'       => __( 'Parent Singer','mh-translations'),
                'parent_item_colon' => __( 'Parent Singer','mh-translations'),
                'edit_item'         => __( 'Edit Singer','mh-translations'),
                'update_item'       => __( 'Update Singer','mh-translations'),
                'add_new_item'      => __( 'Add Singer','mh-translations'),
                'new_item_name'     => __( 'New Singer','mh-translations'),
                'menu_name'         => __( 'Singer','mh-translations'),
            );

            $args = array(
                'hierarchical'  => true, //like categories or tags
                'labels'        => $labels,
                'show_ui'       => true, //add the default UI to this taxonomy
                'show_admin_column' => true, //add the taxonomies to the wordpress admin
                'query_var'         => true,
                // 'rewrite'       => array('slug' =>'singers'),
            );

            register_taxonomy( 'singers', 'mh-trnaslation', $args );
        }


        public function translationmeta()
        {
            global $wpdb;
            $wpdb->translationmetas = $wpdb->prefix . "translationmetas";
        }

        public function add_meta_boxes(){
            add_meta_box(
                'mh_translations_meta_box',
                esc_html__( 'Translations Options', 'mh-translations' ),
                array( $this, 'add_inner_meta_boxes' ),
                'mh-trnaslation',
                'normal',
                'high'
            );
        }

        public function add_cpt_author($query)
        {
            if ( !is_admin() && $query->is_author() && $query->is_main_query() ) {
                $query->set( 'post_type', array( 'mh-trnaslation', 'post' ) );
            }
        }

        public function get_taxonomies_pre_get_posts($query)
        {
            if( !is_admin() && $query->is_main_query() && $query->is_tax('singers')) {

                 $taxonomies = array();
                 $tax_order = (get_query_var('po') == 'DESC' ?  'DESC' : 'ASC' );

                 foreach (get_terms('singers', array('order' => $tax_order)) as $tax ) {
                   $taxonomies[] = $tax->name;
                 }

                $taxquery = array(
                    array(
                        'taxonomy' => 'singers',
                        'field' => 'slug',
                        'terms' => $taxonomies,
                    )
                );

                $query->set( 'tax_query', $taxquery );
                $query->set( 'orderby', 'how_to_order_by_taxonomy_name' );

            }

        }

        public function add_inner_meta_boxes( $post ){
            require_once( MH_TRANSLATIONS_PATH . 'views/mh-translations_metabox.php' );
        }

        public static function save_post( $post_id, $post )
        {
            if( isset( $_POST['mh_translations_nonce'] ) )
            {
                if( ! wp_verify_nonce( $_POST['mh_translations_nonce'], 'mh_translations_nonce' ) ){
                    return;
                }
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            {
                return;
            }

            if( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'mh-trnaslation' )
            {
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }
            }


             if (isset($_POST['action']) && $_POST['action'] == 'editpost') 
             {

                $transliteration = sanitize_text_field( $_POST['mh_translations_transliteration'] );
                $video = esc_url_raw( $_POST['mh_translations_video_url'] );

                 global $wpdb;

                  if( $_POST['mh_translations_action'] == 'save' )
                  {
                     if(get_post_type($post)=='mh-trnaslation'  &&
                        $post->post_status != 'trash' &&
                        $post->post_status != 'auto-draft' &&
                        $post->post_status != 'draft' &&
                            $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT translation_id
                                    FROM $wpdb->translationmetas
                                    WHERE translation_id = %d",
                                    $post_id
                                ))== null )

                        {
                        $wpdb->insert(
                                $wpdb->translationmetas,
                                    array(
                                        'translation_id'    => $post_id,
                                        'meta_key'  => 'mh_translations_transliteration',
                                        'meta_value'    => $transliteration
                                    ),
                                    array(
                                        '%d', '%s', '%s'
                                )
                            );

                        $wpdb->insert(
                                $wpdb->translationmetas,
                                array(
                                    'translation_id'    => $post_id,
                                    'meta_key'  => 'mh_translations_video_url',
                                    'meta_value'    => $video
                                ),
                                array(
                                    '%d', '%s', '%s'
                                )
                            );



                        }
                  }
                  else{
                    if( get_post_type( $post ) == 'mh-trnaslation' ){

                        $wpdb->update(
                            $wpdb->translationmetas,
                            array(
                                'meta_value'    => $transliteration
                            ),
                            array(
                                'translation_id'    => $post_id,
                                'meta_key'  => 'mh_translations_transliteration',   
                            ),
                            array( '%s' ),
                            array( '%d', '%s' )
                        );
                        $wpdb->update(
                            $wpdb->translationmetas,
                            array(
                                'meta_value'    => $video
                            ),
                            array(
                                'translation_id'    => $post_id,
                                'meta_key'  => 'mh_translations_video_url',   
                            ),
                            array( '%s' ),
                            array( '%d', '%s' )
                        );
                    }
                  }


             }



        }

        public function delete_post($post_id,$post)
        {
           if( ! current_user_can( 'delete_posts' ) )return;

            if( get_post_type($post ) == 'mh-trnaslation' ){
                global $wpdb;
                $wpdb->delete(
                    $wpdb->translationmetas,
                    array( 'translation_id' => $post_id ),
                    array( '%d' )
                );
            }
            
        }





    }
}