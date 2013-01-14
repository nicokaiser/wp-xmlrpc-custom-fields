<?php

/*
Plugin Name: XML-RPC custom_fields for Comments
Plugin URI: https://github.com/godmodelabs/wp-xmlrpc-custom-fields
Description: Add support for custom_fields for Comment functions in XML-RPC
Version: 1.0.0
Author: Nico Kaiser
Author URI: http://siriux.net
*/

add_action('xmlrpc_call_success_wp_newComment', 'xmlrpc_custom_fields_newComment', 10, 2);
add_action('xmlrpc_call_success_wp_editComment', 'xmlrpc_custom_fields_editComment', 10, 2);

add_filter('xmlrpc_prepare_comment', 'xmlrpc_custom_fields_prepare_comment', 10, 2);

function xmlrpc_custom_fields_prepare_comment($_comment, $comment)
{
    global $wpdb;

    $_comment['custom_fields'] = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, comment_id
        FROM $wpdb->commentmeta WHERE comment_id = %d
        ORDER BY meta_key,meta_id", $_comment['comment_id'] ), ARRAY_A );

    return $_comment;
}

function xmlrpc_custom_fields_newComment($comment_id, $args)
{
    $content_struct = $args[3];
    if ( isset( $content_struct['custom_fields'] ) )
        xmlrpc_custom_fields_set_custom_fields( $comment_id, $content_struct['custom_fields'] );
}

function xmlrpc_custom_fields_editComment($comment_id, $args)
{
    $content_struct = $args[4];
    if ( isset( $content_struct['custom_fields'] ) )
        xmlrpc_custom_fields_set_custom_fields( $comment_id, $content_struct['custom_fields'] );
}

/**
 * Set custom fields for comment.
 *
 * @param int $comment_id Comment ID.
 * @param array $fields Custom fields.
 */
function xmlrpc_custom_fields_set_custom_fields($comment_id, $fields)
{
    $comment_id = (int) $comment_id;

    foreach ( (array) $fields as $meta ) {
        if ( isset($meta['id']) ) {
            $meta['id'] = (int) $meta['id'];
            $pmeta = get_metadata_by_mid( 'comment', $meta['id'] );
            if ( isset($meta['key']) ) {
                $meta['key'] = stripslashes( $meta['key'] );
                if ( $meta['key'] != $pmeta->meta_key )
                    continue;
                $meta['value'] = stripslashes_deep( $meta['value'] );
                if ( current_user_can( 'edit_comment', $comment_id ) )
                    update_metadata_by_mid( 'comment', $meta['id'], $meta['value'] );
            } elseif ( current_user_can( 'edit_comment', $comment_id ) ) {
                delete_metadata_by_mid( 'comment', $meta['id'] );
            }
        } elseif ( current_user_can( 'edit_comment', $comment_id ) ) {
            add_comment_meta( $comment_id, $meta['key'], $meta['value'] );
        }
    }
}

/**
 * Retrieve custom fields for comment.
 *
 * @param int $post_id Post ID.
 * @return array Custom fields, if exist.
 */
function xmlrpc_custom_fields_get_custom_fields($comment_id) {
    $comment_id = (int) $comment_id;

    $custom_fields = array();
    if ( current_user_can( 'edit_comment', $comment_id ) ) {
        foreach ( (array) xmlrpc_custom_fields_has_comment_meta($comment_id) as $meta ) {
            $custom_fields[] = array(
                "id"    => $meta['meta_id'],
                "key"   => $meta['meta_key'],
                "value" => $meta['meta_value']
            );
        }
    }

    return $custom_fields;
}
