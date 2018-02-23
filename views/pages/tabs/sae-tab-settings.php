<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
// Only if woo is found
  if(get_option('sender_automated_emails_has_woocommerce')) {
    $allowImport = get_option('sender_automated_emails_allow_import');
    $allowTrack = get_option('sender_automated_emails_allow_guest_track');
    $allowSubscribe = get_option('sender_automated_emails_registration_track');
    $customersList = get_option('sender_automated_emails_customers_list');
    $saeNewUsersList = get_option('sender_automated_emails_registration_list');
    $lists = $sender_api->getAllLists();
    $allowHighAcc = get_option('sender_automated_emails_high_acc');
  }
?>
<h1>Settings</h1>

<div class="content">
    
    <div class="pure-g sender-net-automated-emails-section">

        <div class="pure-u-1-1">
            <h3><i class="zmdi zmdi-account"></i> Plugin is connected to Sender.net</h3>
        </div>

        <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">
            <a style="width: 80%; background-color:red" id="tye" href="options-general.php?page=sender-net-automated-emails&action=disconnect" class="sender-net-automated-emails-button">Disconnect</a>
            
        </div>


        <div class="pure-u-1-1 pure-u-sm-12-24">
            <table>
                <tr>
                    <td>Account email:</td>
                    <td><strong><?php echo $sender_api->ping()->email; ?></strong></td>
                </tr>
                <tr>
                    <td>API key:</td>
                    <td><strong><?php echo $sender_api->getApiKey(); ?></strong></td>
                </tr>
            </table>
           
           
        </div>

    </div>
    
 
    <?php if(isset($lists[0]->id)): ?>
    <div class="pure-g sender-net-automated-emails-section">

        <div class="pure-u-1-1">
            <h3 style="margin-bottom:0px;"><i class="zmdi zmdi-accounts-list-alt"></i> Auto subscribe new users is <?php echo !$allowSubscribe ? '<span id="saeSubscribeStatus" style="color:red;">disabled</span>' : '<span id="saeSubscribeStatus" style="color:green;">enabled</span>'; ?></h3>
            <h3 style="display: inline-block"><i class="zmdi zmdi-account"></i> Save new users emails to: </h3>
            <?php if(isset($lists[0]->id)): ?>
            <select id="saeNewUsersList" style="margin-bottom: 10px;">
                <option value="#" disabled selected>Select list</option>
                <?php
                    
                    foreach($lists as $list) {
                        echo '<option value="' . $list->id . '" ' . ($list->id == $saeNewUsersList['id'] ? 'selected' : '') . '>' . $list->title . '</option>';
                    }
                ?>
            </select>
            <?php else: ?>
            <?php update_option('sender_automated_emails_registration_list', array('id' => false, 'title' => '')); ?>
            <?php echo $sender_helper->showNotice('Please create a list in order to track new users', 'error'); ?>
            <?php endif; ?>
        </div>
        
        <script>
            jQuery('#saeNewUsersList').on('change', function (event) {
                var data = {
                    listId: jQuery('#saeNewUsersList option:selected').val(),
                    title: jQuery('#saeNewUsersList option:selected').text(),
                    action: 'change_register_list'
                }
                jQuery('#saeNewUsersList').attr('disabled', true);
                jQuery.post( "<?php echo get_admin_url();?>admin-ajax.php", data, function(response) {
                      jQuery('#saeNewUsersList').removeAttr('disabled');
                });

            });
        </script>

        <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">
            <button id="saeSubscribeButton" style="width: 90%; background-color:<?php echo !$allowSubscribe ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$allowSubscribe ? 'Enable' : 'Disable'; ?></button>
        </div>


        <div class="pure-u-1-1 pure-u-sm-12-24">
            <p>
            This feature allows you to capture new users automatically and add them to the selected subscriber list. It is especially useful for automated welcome emails.
            </p>
            <p>
                <a target="_BLANK" href="https://help.sender.net/knowledgebase/how-to-create-an-automated-welcome-email/">Create an automated welcome email</a> | <a target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/mailinglists">Manage subscriber lists</a>
            </p>
        </div>

    </div>
    <?php else: ?>
    <?php update_option('sender_automated_emails_registration_track', 0); ?>
    <?php endif; ?>
    
    
    <?php if(get_option('sender_automated_emails_has_woocommerce')): ?>
        <?php if(isset($lists[0]->id)): ?>
        <div class="pure-g sender-net-automated-emails-section">

            <div class="pure-u-1-1">
                <h3 style="margin-bottom:0px;"><i class="zmdi zmdi-accounts-list-alt"></i> Guest cart tracking is <?php echo !$allowTrack ? '<span id="saeTrackStatus" style="color:red;">disabled</span>' : '<span id="saeTrackStatus" style="color:green;">enabled</span>'; ?></h3>
                <h3 style="display: inline-block;"><i class="zmdi zmdi-email"></i> Save guest customers emails to:</h3>
                <?php  if(isset($lists[0]->id)): ?>
                <select id="customersList" style="margin-bottom: 10px;">
                    <option value="#" disabled selected>Select list</option>
                    <?php

                        foreach($lists as $list) {
                            echo '<option value="' . $list->id . '" ' . ($list->id == $customersList['id'] ? 'selected' : '') . '>' . $list->title . '</option>';
                        }
                    ?>
                </select>
                <?php else: ?>
                <?php update_option('sender_automated_emails_customers_list', array('id' => false, 'title' => '')); ?>
                <?php echo $sender_helper->showNotice('Please create a list in order to track guest customers', 'error'); ?>
                <?php endif; ?>
            </div>
            
            <script>
                jQuery('#customersList').on('change', function (event) {
                    var data = {
                        listId: jQuery('#customersList option:selected').val(),
                        title: jQuery('#customersList option:selected').text(),
                        action: 'change_customer_list'
                    }
                    jQuery('#customersList').attr('disabled', true);
                    jQuery.post( "<?php echo get_admin_url();?>admin-ajax.php", data, function(response) {
                          jQuery('#customersList').removeAttr('disabled');
                    });
                });
            </script>

            <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">
                <button id="saeCartTrackButton" style="width: 90%; background-color:<?php echo !$allowTrack ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$allowTrack ? 'Enable' : 'Disable'; ?></button>
            </div>


            <div class="pure-u-1-1 pure-u-sm-12-24">
                <p>
                Once the guest tracking is activated - all guest carts will be tracked and will show up in the dashboard. If the guest user enters his email in the checkout form, the email will automatically be captured even if the guest did not click ‘Submit information’ button. This is useful when using the one-step checkout.
                </p>
                <p>
                    <a target="_BLANK" href="https://help.sender.net/knowledgebase/how-to-create-automated-abandoned-cart-email/">How to create an adandoned cart workflow?</a>
                </p>
            </div>
            
        </div>
        <?php else: ?>
        <?php update_option('sender_automated_emails_allow_guest_track', 0); ?>
        <?php endif; ?>
    
    
    <div class="pure-g sender-net-automated-emails-section">
        
        <div class="pure-u-1-1">
            <h3><i class="zmdi zmdi-wallpaper"></i> Product block import is <?php echo !$allowImport ? '<span id="saeAllowImportTitle" style="color:red;">disabled</span>' : '<span id="saeAllowImportTitle" style="color:green;">enabled</span>'; ?> </h3>  
        </div>
        
        <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">

        <button id="saeAllowImportButton" style="width: 90%; background-color:<?php echo !$allowImport ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$allowImport ? 'Enable' : 'Disable'; ?></button>
        </div>

        <div class="pure-u-1-1 pure-u-sm-12-24">
            <p>
                Here you can enable the product import block in the Sender.net’s email creator. When enabled, it allows you to just copy the product's link and the system will automatically import the product to your email.
            </p>
            <p>
                <a target="_BLANK" href="https://help.sender.net/section/sending-a-campaign/">Campaign tutorials </a>
            </p>
        </div>
        
    </div>
    
    <div class="pure-g sender-net-automated-emails-section">
        
        <div class="pure-u-1-1">
            <h3><i class="zmdi zmdi-alert-circle-o"></i> High accuracy mode is <?php echo !$allowHighAcc ? '<span id="saeAllowHighAccTitle" style="color:red;">disabled</span>' : '<span id="saeAllowHighAccTitle" style="color:green;">enabled</span>'; ?> </h3>  
        </div>
        
        <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">

        <button id="saeAllowHighAccButton" style="width: 90%; background-color:<?php echo !$allowHighAcc ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$allowHighAcc ? 'Enable' : 'Disable'; ?></button>
        </div>

        <div class="pure-u-1-1 pure-u-sm-12-24">
            <p>
                This setting enables high accuracy cart tracking, which checks for the cart data more often. If you are experiencing any slow downs, please disable this option.
            </p>
            
        </div>
        
    </div>
    
    <?php endif; ?>
</div>

