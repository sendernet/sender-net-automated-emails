<?php

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }


?>

<div class="pure-g sender-net-automated_emails-card">
    <div class="pure-u-1-1 sender-net-automated_emails-header">
        <div class="pure-g">
            <div class="pure-u-1-1 pure-u-sm-1-2 sae-text-left">
                <?php
                echo '<img src="' . plugins_url( '/assets/images/logo.png', dirname(dirname(__FILE__)) ) . '" alt="Sender logo"> ';
                ?>
                <span>
                    <small>v<?php echo SENDERAUTOMATEDEMAILS_CURRENT_VERSION; ?></small>
                </span>
            </div>
            <?php if(get_option('sender_automated_emails_has_woocommerce')): ?>
            <div class="pure-u-1-2 pure-u-sm-1-2 sae-text-right">
                <?php
                echo '<img src="' . plugins_url( '/assets/images/woo.png', dirname(dirname(__FILE__)) ) . '" height="42" alt="Woocommerce logo"> ';
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="pure-u-1-1 pure-u-lg-3-24 sender-net-automated_emails-hide-small sender-net-automated_emails-menu">
        <ul class="sae-tabs sae-main-menu">
            <?php if(get_option('sender_automated_emails_has_woocommerce') && false): ?>
            <li class="tab-link sae-current sae-active" data-tab="dashboard" disabled>
                <a href="#!dashboard"><i class="zmdi zmdi-shopping-cart"></i> Active Carts</a>
            </li>
            <li class="tab-link" data-tab="converted" disabled>
                <a href="#!converted"><i class="zmdi zmdi-money"></i> Converted Carts</a>
            </li>
            <?php endif; ?>
            <li class="tab-link" data-tab="sae-push" disabled>
                <a href="#!sae-push"><i class="zmdi zmdi-notifications-active"></i> Push notifications</a>
            </li>
            <li data-tab="forms" class="tab-link">
                <a href="#!forms"><i class="zmdi zmdi-format-list-bulleted"></i> Forms</a>
            </li>

            <li data-tab="settings" id="workflows" class="tab-link sae-current sae-active">
                <a href="#!settings"><i class="zmdi zmdi-settings"></i> Settings</a>
            </li>
            
        </ul>
    </div>
    <div class="pure-u-1-1 pure-u-lg-18-24 sender-net-automated_emails-content">
       <?php if(get_option('sender_automated_emails_has_woocommerce') && false): ?>
        <div id="dashboard" class="sae-tab-content sae-current">
            <?php include_once 'tabs/sae-tab-dashboard.php'; ?>
        </div>
        <div id="converted" class="sae-tab-content">
            <?php include_once 'tabs/sae-tab-converted.php'; ?>
        </div>
        <?php endif; ?>
        <div id="forms" class="sae-tab-content <?php echo !get_option('sender_automated_emails_has_woocommerce') ? 'sae-current' : '';?>">
            <?php include_once 'tabs/sae-tab-forms.php'; ?>
        </div>
        <div id="sae-push" class="sae-tab-content">
            <?php include_once 'tabs/sae-tab-push.php'; ?>
        </div>
        <div id="settings" class="sae-tab-content sae-current">
            <?php include_once 'tabs/sae-tab-settings.php'; ?>
        </div>
      
    </div>
</div>



