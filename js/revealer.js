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

"use strict";

/** Document Ready. */
document.addEventListener( 'DOMContentLoaded', function() {
   
    /** Fastest way to detect external URLs. */
    var isExternal = ( function () {
        var domainRe = /https?:\/\/((?:[\w\d-]+\.)+[\w\d]{2,})/i;

        return function ( url ) {
            function domain( url ) {
                return domainRe.exec( url )[1];
            }

            return domain( location.href ) !== domain( url );
        };
    } ) ();
    
    /** Popup Settings. */
    var settings = {
        target: mdp_revealer.selector,
        animation: mdp_revealer.animation,
        maxWidth: mdp_revealer.maxWidth,
        duration: [275, 250],
        allowHTML: true,
        delay: [300, 200],
        interactive: true,
        interactiveBorder: 15,
        theme: mdp_revealer.theme,
        flipOnUpdate: true
    };
    
    /** Arrow. */
    if ( mdp_revealer.arrow ) {
        settings['arrow'] = true;
        settings['arrowType'] = mdp_revealer.arrowType;
    }
    
    /** 
     * Invoked when the tippy begins to transition in. 
     * You can cancel showing by returning false from this lifecycle. 
     * Receives the instance as an argument. 
     **/
    settings['onShow'] = function ( instance ) {
        
        /** Target URL. */
        var URL = encodeURI( instance.reference.getAttribute( 'href' ) );

        /** Process only internal URLs. */
        if ( isExternal( URL ) ) { return false; }
        
        /** Mark element to process only once. */
        if ( instance.reference.getAttribute( 'data-revealer' ) ) { return true; }
        instance.reference.setAttribute( 'data-revealer', '1' );
        
        /** Set up our HTTP request. */
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', mdp_revealer.ajaxurl, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded;' );
        xhr.responseType = 'json';

        /** Setup our listener to process completed requests. */
        xhr.onload = function () {

            /** Process our return data. */
            if ( xhr.status >= 200 && xhr.status < 300 ) {
                if ( xhr.response.status ) {
                    instance.setContent( xhr.response.html );
                    instance.show();
                }
            } else {
                /** Request fails. */
                console.log('The request failed!');
            }
            
        };
        
        /** Connection error. */
        xhr.onerror = function() {
            console.log( 'Connection error.' );                    
        };

        /** Create and send a POST request. */
        xhr.send( 'action=get_page_by_url&url=' + URL );
        
        return false;
    };
    
    /** 
     * Lifecycle function invoked when the tippy has fully transitioned out and is unmounted from the DOM. 
     * Receives the instance as an argument.
     **/
    settings['onHidden'] = function ( instance ) {
        /** Remove Attribute to allow re-shown tooltip. */
        instance.reference.removeAttribute( 'data-revealer' );
    };

    tippy( 'html', settings );
   
} );
