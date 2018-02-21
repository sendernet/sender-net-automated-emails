(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
    
     jQuery(document).ready(function(){
        
        jQuery('#swAllowImportButton').on('click', function (event) {
            
            jQuery('#swAllowImportButton').text('Saving...');
            jQuery('#swAllowImportButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_import' }, function(response) {

                if(response == 0) {
                    jQuery('#swAllowImportTitle').text('disabled');
                    jQuery('#swAllowImportTitle').css('color', 'red');
                    jQuery('#swAllowImportButton').text('Enable');
                    jQuery('#swAllowImportButton').css('background-color', 'green');
                } else {
                    jQuery('#swAllowImportTitle').text('enabled');
                    jQuery('#swAllowImportTitle').css('color', 'green');
                    jQuery('#swAllowImportButton').text('Disable');
                    jQuery('#swAllowImportButton').css('background-color', 'red');
                }

                jQuery('#swAllowImportButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#swAllowHighAccButton').on('click', function (event) {
            
            jQuery('#swAllowHighAccButton').text('Saving...');
            jQuery('#swAllowHighAccButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_high_acc' }, function(response) {

                if(response == 0) {
                    jQuery('#swAllowHighAccTitle').text('disabled');
                    jQuery('#swAllowHighAccTitle').css('color', 'red');
                    jQuery('#swAllowHighAccButton').text('Enable');
                    jQuery('#swAllowHighAccButton').css('background-color', 'green');
                } else {
                    jQuery('#swAllowHighAccTitle').text('enabled');
                    jQuery('#swAllowHighAccTitle').css('color', 'green');
                    jQuery('#swAllowHighAccButton').text('Disable');
                    jQuery('#swAllowHighAccButton').css('background-color', 'red');
                }

                jQuery('#swAllowHighAccButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#swSubscribeButton').on('click', function (event) {
            
            jQuery('#swSubscribeButton').text('Saving...');
            jQuery('#swSubscribeButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_registration_track' }, function(response) {

                if(response == 0) {
                    jQuery('#swSubscribeStatus').text('disabled');
                    jQuery('#swSubscribeStatus').css('color', 'red');
                    jQuery('#swSubscribeButton').text('Enable');
                    jQuery('#swSubscribeButton').css('background-color', 'green');
                } else {
                    jQuery('#swSubscribeStatus').text('enabled');
                    jQuery('#swSubscribeStatus').css('color', 'green');
                    jQuery('#swSubscribeButton').text('Disable');
                    jQuery('#swSubscribeButton').css('background-color', 'red');
                }

                jQuery('#swSubscribeButton').removeAttr('disabled');
                
            });
            
        });

        jQuery('#swAllowPushButton').on('click', function (event) {
            
            jQuery('#swAllowPushButton').text('Saving...');
            jQuery('#swAllowPushButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_push' }, function(response) {

                if(response == 0) {
                    jQuery('#swAllowPushTitle').text('disabled');
                    jQuery('#swAllowPushTitle').css('color', 'red');
                    jQuery('#swAllowPushButton').text('Enable');
                    jQuery('#swAllowPushButton').css('background-color', 'green');
                } else {
                    jQuery('#swAllowPushTitle').text('enabled');
                    jQuery('#swAllowPushTitle').css('color', 'green');
                    jQuery('#swAllowPushButton').text('Disable');
                    jQuery('#swAllowPushButton').css('background-color', 'red');
                }

                jQuery('#swAllowPushButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#swCartTrackButton').on('click', function (event) {
            
            jQuery('#swCartTrackButton').text('Saving...');
            jQuery('#swCartTrackButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_cart_track' }, function(response) {

                if(response == 0) {
                    jQuery('#swTrackStatus').text('disabled');
                    jQuery('#swTrackStatus').css('color', 'red');
                    jQuery('#swCartTrackButton').text('Enable');
                    jQuery('#swCartTrackButton').css('background-color', 'green');
                } else {
                    jQuery('#swTrackStatus').text('enabled');
                    jQuery('#swTrackStatus').css('color', 'green');
                    jQuery('#swCartTrackButton').text('Disable');
                    jQuery('#swCartTrackButton').css('background-color', 'red');
                }

                jQuery('#swCartTrackButton').removeAttr('disabled');
                
            });
            
        });

         jQuery('#swToggleWidget').on('click', function (event) {
            
            jQuery('#swToggleWidget').text('Saving...');
            jQuery('#swToggleWidget').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_form_widget' }, function(response) {

                if(response == 0) {
                    jQuery('#swToggleWidgetTitle').text('disabled');
                    jQuery('#swToggleWidgetTitle').css('color', 'red');
                    jQuery('#swToggleWidget').text('Enable');
                    jQuery('#swToggleWidget').css('background-color', 'green');
                } else {
                    jQuery('#swToggleWidgetTitle').text('enabled');
                    jQuery('#swToggleWidgetTitle').css('color', 'green');
                    jQuery('#swToggleWidget').text('Disable');
                    jQuery('#swToggleWidget').css('background-color', 'red');
                }

                jQuery('#swToggleWidget').removeAttr('disabled');
                
            });
            
        });
        
    
        /**
         * Tab menu change handler
         */
        jQuery('ul.sw-tabs li').click(function(){
            var tab_id = jQuery(this).data().tab;
            jQuery('ul.sw-tabs li').removeClass('sw-current').removeClass('sw-active');
            jQuery('.sw-tab-content').removeClass('sw-current');
            jQuery("#"+tab_id).addClass('sw-current');
            jQuery(this).addClass('sw-current').addClass('sw-active');
        })
        
        if(window.location.hash) {
            var hash = window.location.hash.substring(2);
            jQuery('[data-tab="'+hash+'"]').trigger('click');
            
        } else {
        }

    })

})( jQuery );
