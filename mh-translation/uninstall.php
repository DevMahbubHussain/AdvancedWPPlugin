<?php 
delete_option( 'mh_translation_db_version' );

    global $wpdb;

    $wpdb->query(
        "DELETE FROM $wpdb->posts
        WHERE post_type = 'mh-trnaslation'"
    );

    $wpdb->query(
        "DELETE FROM $wpdb->posts
        WHERE post_type = 'page'
        AND post_name IN( 'submit-translation', 'edit-translation' )"
    );

    $wpdb->query( $wpdb->prepare(
        "DROP TABLE IF EXISTS %s",
        $wpdb->prefix . 'translationmetas'
    ));

// drop a custom database table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}translationmetas" );