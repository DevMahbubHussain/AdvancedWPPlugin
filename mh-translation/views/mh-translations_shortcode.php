<?php
if( ! is_user_logged_in() ){
    mht_register_user();
    return;
}

if( isset( $_POST['mh_translations_nonce'] ) ){
    if( ! wp_verify_nonce( $_POST['mh_translations_nonce'], 'mh_translations_nonce' ) ){
        return;
    }
}

$errors = array();
$hasError = false;
if( isset( $_POST['submitted'])){
    $title              = $_POST['mh_translations_title'];
    $content            = $_POST['mh_translations_content'];
    $singer             = $_POST['mh_translations_singer'];
    //var_dump($singer);
    $transliteration    = $_POST['mh_translations_transliteration'];
    $video              = $_POST['mh_translations_video_url'];

    if( trim( $title ) === '' ){
        $errors[] = esc_html__( 'Please, enter a title', 'mh-translations' );
        $hasError = true;
    }

    if( trim( $content ) === '' ){
        $errors[] = esc_html__( 'Please, enter some content', 'mh-translations' );
        $hasError = true;
    }

    if( trim( $singer ) === '' ){
        $errors[] = esc_html__( 'Please, enter some singer', 'mh-translations' );
        $hasError = true;
    }

    if( $hasError === false ){
        $post_info = array(
            'post_type' => 'mh-trnaslation',
            'post_title'    => sanitize_text_field( $title ),
            'post_content'  => wp_kses_post( $content ),
            'tax_input' => array(
                'singers'   => sanitize_text_field( $singer )
            ),
            'post_status'   => 'pending'
        );

        $post_id = wp_insert_post( $post_info );

        global $post;
        MH_Translation_CPT::save_post( $post_id, $post );        
    }

}

?>


<div class="mh-translations">
    <form action="" method="POST" id="translations-form">
        <h2><?php esc_html_e( 'Submit new translation' , 'mh-translations' ); ?></h2>

        <?php 
            if( $errors != '' ){
                foreach( $errors as $error ){
                    ?>
                        <span class="error">
                            <?php echo $error; ?>
                        </span>
                    <?php
                }
            }
        ?>
        
        <label for="mv_translations_title"><?php esc_html_e( 'Title', 'mh-translations' ); ?> *</label>
        <input type="text" name="mh_translations_title" id="mh_translations_title" value="<?php if( isset( $title ) ) echo $title; ?>" required />
        <br />
        <label for="mh_translations_singer"><?php esc_html_e( 'Singer', 'mh-translations' ); ?> *</label>
        <input type="text" name="mh_translations_singer" id="mh_translations_singer" value="<?php if( isset( $singer ) ) echo $singer; ?>" required />

        <br />
        <?php 
        if( isset( $content )){
            wp_editor( $content, 'mh_translations_content', array( 'wpautop' => true, 'media_buttons' => true ) );
        }else{
            wp_editor( '', 'mh_translations_content', array( 'wpautop' => true, 'media_buttons' => true ) );
        }
        ?>
        </br />
        
        <fieldset id="additional-fields">
            <label for="mh_translations_transliteration"><?php esc_html_e( 'Has transliteration?', 'mv-translations' ); ?></label>
            <select name="mh_translations_transliteration" id="mh_translations_transliteration">
                <option value="Yes" <?php if( isset( $transliteration ) ) selected( $transliteration, "Yes" ); ?>><?php esc_html_e( 'Yes', 'mv-translations' ); ?></option>
                <option value="No" <?php if( isset( $transliteration ) ) selected( $transliteration, "No" ); ?>><?php esc_html_e( 'No', 'mv-translations' ); ?></option>
            </select>
            <label for="mh_translations_video_url"><?php esc_html_e( 'Video URL', 'mh-translations' ); ?></label>
            <input type="url" name="mh_translations_video_url" id="mh_translations_video_url" value="<?php if( isset( $video ) ) echo $video; ?>" />
        </fieldset>
        <br />
        <input type="hidden" name="mh_translations_action" value="save">
        <input type="hidden" name="action" value="editpost">
        <input type="hidden" name="mh_translations_nonce" value="<?php echo wp_create_nonce( 'mh_translations_nonce' ); ?>">
        <input type="hidden" name="submitted" id="submitted" value="true" />
        <input type="submit" name="submit_form" value="<?php esc_attr_e( 'Submit', 'mh-translations' ); ?>" />
    </form>
</div>
<div class="translations-list">
<?php 

global $current_user;
global $wpdb; 
$q = $wpdb->prepare(
    "SELECT ID, post_author, post_date, post_title, post_status, meta_key, meta_value
    FROM $wpdb->posts AS p
    INNER JOIN $wpdb->translationmetas AS tm
    ON p.ID = tm.translation_id
    WHERE p.post_author = %d
    AND tm.meta_key = 'mh_translations_transliteration'
    AND p.post_status IN ( 'publish', 'pending' )
    ORDER BY p.post_date DESC",
    $current_user->ID
);
$results = $wpdb->get_results( $q );
//var_dump( $results );
if( $wpdb->num_rows ):
?>
            <table>
                <caption><?php esc_html_e( 'Your Translations', 'mv-translations' ); ?></caption>
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'mh-translations' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'mh-translations' ); ?></th>
                        <th><?php esc_html_e( 'Transliteration', 'mh-translations' ); ?></th>
                        <th><?php esc_html_e( 'Edit?', 'mh-translations' ); ?></th>
                        <th><?php esc_html_e( 'Delete?', 'mh-translations' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'mh-translations' ); ?></th>
                    </tr>
                </thead>  
                <tbody>
                <?php foreach( $results as $result ): ?>  
                    <tr>
                        <td><?php echo esc_html( date( 'M/d/Y', strtotime( $result->post_date ) ) ); ?></td>
                        <td><?php echo esc_html( $result->post_title ); ?></td>
                        <td><?php echo $result->meta_value == 'Yes' ? esc_html__( 'Yes', 'mh-translations' ) : esc_html__( 'No', 'mh-translations' ); ?></td>
                        <?php $edit_post = add_query_arg( 'post', $result->ID, home_url( '/edit-translation' ) ); ?>
                        <td><a href="<?php echo esc_url( $edit_post );  ?>"><?php esc_html_e( 'Edit', 'mh-translations' ); ?></a></td>
                        <td><a onclick="return confirm( 'Are you sure you want to delete post: <?php echo $result->post_title ?>?' )" href="<?php echo get_delete_post_link( $result->ID, "", true ); ?>"><?php esc_html_e( 'Delete', 'mh-translations' ); ?></a></td>
                        <td><?php echo $result->post_status == 'publish' ? esc_html__( 'Published', 'mh-translations' ) : esc_html__( 'Pending', 'mh-translations' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php endif; ?>
</div>