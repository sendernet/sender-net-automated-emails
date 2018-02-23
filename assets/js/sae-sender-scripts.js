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
        
        jQuery('#saeAllowImportButton').on('click', function (event) {
            
            jQuery('#saeAllowImportButton').text('Saving...');
            jQuery('#saeAllowImportButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_import' }, function(response) {

                if(response == 0) {
                    jQuery('#saeAllowImportTitle').text('disabled');
                    jQuery('#saeAllowImportTitle').css('color', 'red');
                    jQuery('#saeAllowImportButton').text('Enable');
                    jQuery('#saeAllowImportButton').css('background-color', 'green');
                } else {
                    jQuery('#saeAllowImportTitle').text('enabled');
                    jQuery('#saeAllowImportTitle').css('color', 'green');
                    jQuery('#saeAllowImportButton').text('Disable');
                    jQuery('#saeAllowImportButton').css('background-color', 'red');
                }

                jQuery('#saeAllowImportButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#saeAllowHighAccButton').on('click', function (event) {
            
            jQuery('#saeAllowHighAccButton').text('Saving...');
            jQuery('#saeAllowHighAccButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_high_acc' }, function(response) {

                if(response == 0) {
                    jQuery('#saeAllowHighAccTitle').text('disabled');
                    jQuery('#saeAllowHighAccTitle').css('color', 'red');
                    jQuery('#saeAllowHighAccButton').text('Enable');
                    jQuery('#saeAllowHighAccButton').css('background-color', 'green');
                } else {
                    jQuery('#saeAllowHighAccTitle').text('enabled');
                    jQuery('#saeAllowHighAccTitle').css('color', 'green');
                    jQuery('#saeAllowHighAccButton').text('Disable');
                    jQuery('#saeAllowHighAccButton').css('background-color', 'red');
                }

                jQuery('#saeAllowHighAccButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#saeSubscribeButton').on('click', function (event) {
            
            jQuery('#saeSubscribeButton').text('Saving...');
            jQuery('#saeSubscribeButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_registration_track' }, function(response) {

                if(response == 0) {
                    jQuery('#saeSubscribeStatus').text('disabled');
                    jQuery('#saeSubscribeStatus').css('color', 'red');
                    jQuery('#saeSubscribeButton').text('Enable');
                    jQuery('#saeSubscribeButton').css('background-color', 'green');
                } else {
                    jQuery('#saeSubscribeStatus').text('enabled');
                    jQuery('#saeSubscribeStatus').css('color', 'green');
                    jQuery('#saeSubscribeButton').text('Disable');
                    jQuery('#saeSubscribeButton').css('background-color', 'red');
                }

                jQuery('#saeSubscribeButton').removeAttr('disabled');
                
            });
            
        });

        jQuery('#saeAllowPushButton').on('click', function (event) {
            
            jQuery('#saeAllowPushButton').text('Saving...');
            jQuery('#saeAllowPushButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_allow_push' }, function(response) {

                if(response == 0) {
                    jQuery('#saeAllowPushTitle').text('disabled');
                    jQuery('#saeAllowPushTitle').css('color', 'red');
                    jQuery('#saeAllowPushButton').text('Enable');
                    jQuery('#saeAllowPushButton').css('background-color', 'green');
                } else {
                    jQuery('#saeAllowPushTitle').text('enabled');
                    jQuery('#saeAllowPushTitle').css('color', 'green');
                    jQuery('#saeAllowPushButton').text('Disable');
                    jQuery('#saeAllowPushButton').css('background-color', 'red');
                }

                jQuery('#saeAllowPushButton').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#saeCartTrackButton').on('click', function (event) {
            
            jQuery('#saeCartTrackButton').text('Saving...');
            jQuery('#saeCartTrackButton').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_cart_track' }, function(response) {

                if(response == 0) {
                    jQuery('#saeTrackStatus').text('disabled');
                    jQuery('#saeTrackStatus').css('color', 'red');
                    jQuery('#saeCartTrackButton').text('Enable');
                    jQuery('#saeCartTrackButton').css('background-color', 'green');
                } else {
                    jQuery('#saeTrackStatus').text('enabled');
                    jQuery('#saeTrackStatus').css('color', 'green');
                    jQuery('#saeCartTrackButton').text('Disable');
                    jQuery('#saeCartTrackButton').css('background-color', 'red');
                }

                jQuery('#saeCartTrackButton').removeAttr('disabled');
                
            });
            
        });

         jQuery('#saeToggleWidget').on('click', function (event) {
            
            jQuery('#saeToggleWidget').text('Saving...');
            jQuery('#saeToggleWidget').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'toggle_form_widget' }, function(response) {

                if(response == 0) {
                    jQuery('#saeToggleWidgetTitle').text('disabled');
                    jQuery('#saeToggleWidgetTitle').css('color', 'red');
                    jQuery('#saeToggleWidget').text('Enable');
                    jQuery('#saeToggleWidget').css('background-color', 'green');
                } else {
                    jQuery('#saeToggleWidgetTitle').text('enabled');
                    jQuery('#saeToggleWidgetTitle').css('color', 'green');
                    jQuery('#saeToggleWidget').text('Disable');
                    jQuery('#saeToggleWidget').css('background-color', 'red');
                }

                jQuery('#saeToggleWidget').removeAttr('disabled');
                
            });
            
        });
        
    
        /**
         * Tab menu change handler
         */
        jQuery('ul.sae-tabs li').click(function(){
            var tab_id = jQuery(this).data().tab;
            jQuery('ul.sae-tabs li').removeClass('sae-current').removeClass('sae-active');
            jQuery('.sae-tab-content').removeClass('sae-current');
            jQuery("#"+tab_id).addClass('sae-current');
            jQuery(this).addClass('sae-current').addClass('sae-active');
        })
        
        if(window.location.hash) {
            var hash = window.location.hash.substring(2);
            jQuery('[data-tab="'+hash+'"]').trigger('click');
            
        } else {
        }

    })

})( jQuery );
