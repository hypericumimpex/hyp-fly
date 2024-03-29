/**
 * Revealer - Adds a stylish hover pop-up with five animation effects for internal WordPress website links.
 *
 * @encoding UTF-8
 * @version 1.0.2
 * @copyright Copyright (C) 2019 merkulove ( https://1.envato.market/cc-merkulove ). All rights reserved.
 * @license Envato License https://1.envato.market/KYbje
 * @author merkulove
 * @url https://revealer.merkulov.design/
 **/

( function ( $ ) {
    
    "use strict";
    
    jQuery( document ).ready( function () {
        
        jQuery( '.mdp-revealer-rating-stars' ).find( 'a' ).hover(
            function() {
                jQuery( this ).nextAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-filled' ).addClass( 'dashicons-star-empty' );
                jQuery( this ).prevAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
                jQuery( this ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
            }
        );
        
    } );

} ( jQuery ) );