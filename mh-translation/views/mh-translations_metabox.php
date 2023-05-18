<?php
global $wpdb;
$query = $wpdb->prepare( 
    "SELECT * FROM $wpdb->translationmetas
    WHERE translation_id = %d",
    $post->ID
);
$results = $wpdb->get_results( $query, ARRAY_A );

//var_dump($results);
 ?>
<table class="form-table mh-translations-metabox"> 
    <!-- Nonce -->
    <input type="hidden" name="mh_translations_nonce" value="<?php echo wp_create_nonce( 'mh_translations_nonce' ); ?>">

    <input 
    type="hidden" 
    name="mh_translations_action" 
    value="<?php echo ( empty ( $results[0]['meta_value'] ) || empty ( $results[1]['meta_value'] ) ? 'save' : 'update' ); ?>">
    <tr>
        <th>
            <label for="mh_translations_transliteration"><?php esc_html_e( 'Has transliteration?', 'mh-translations' ); ?></label>
        </th>
        <td>
            <select name="mh_translations_transliteration" id="mh_translations_transliteration">
                <option value="Yes" <?php if( isset( $results[0]['meta_value'] ) ) selected( $results[0]['meta_value'], 'Yes' ); ?>><?php esc_html_e( 'Yes', 'mh-translations' )?></option>';
                <option value="No" <?php if( isset( $results[0]['meta_value'] ) ) selected( $results[0]['meta_value'], 'No' ); ?>><?php esc_html_e( 'No', 'mh-translations' )?></option>';
            </select>            
        </td>
    </tr>
    <tr>
        <th>
            <label for="mh_translations_video_url"><?php esc_html_e( 'Video URL', 'mh-translations' ); ?></label>
        </th>
        <td>
            <input 
                type="url" 
                name="mh_translations_video_url" 
                id="mh_translations_video_url" 
                class="regular-text video-url"
                value="<?php echo ( isset( $results[1]['meta_value'] ) ) ? esc_url( $results[1]['meta_value'] ) : ""; ?>"
            >
        </td>
    </tr> 
</table>