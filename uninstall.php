<?php
/** 
 * Revealer add stylish hover pop-up with five animation effects for internal WordPress website links.
 * Exclusively on Envato Market: https://1.envato.market/revealer
 * 
 * @encoding     UTF-8
 * @version      1.0.2
 * @copyright    Copyright (C) 2019 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license      Envato Standard License https://1.envato.market/KYbje
 * @author       Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 * @support      dmitry@merkulov.design
 **/

/** 
 * Runs on Uninstall of Revealer plugin. 
 **/

/** Check that we should be doing this. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/** Delete Options. */
$settings = array(
    'mdp_revealer_settings',
    'mdp_revealer_assignments_settings'
);

foreach ( $settings as $key ) {
    
    if ( is_multisite() ) { // For Multisite.
        if ( get_site_option( $key ) ) {
            delete_site_option( $key );
        }
    } else { 
        if ( get_option( $key ) ) {
            delete_option( $key );
        }    
    }

}
