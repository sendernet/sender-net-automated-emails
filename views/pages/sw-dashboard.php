<?php

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }


?>

<div class="pure-g sender-woocommerce-card">
    <div class="pure-u-1-1 sender-woocommerce-header">
        <div class="pure-g">
            <div class="pure-u-1-1 pure-u-sm-1-2 sw-text-left">
                <?php
                echo '<img src="' . plugins_url( '/assets/images/logo.png', dirname(dirname(__FILE__)) ) . '" alt="Sender logo"> ';
                ?>
                <span>
                    <small>v<?php echo SENDERWOO_CURRENT_VERSION; ?></small>
                </span>
            </div>
            <?php if(get_option('sender_woocommerce_has_woocommerce')): ?>
            <div class="pure-u-1-2 pure-u-sm-1-2 sw-text-right">
                <?php
                echo '<img src="' . plugins_url( '/assets/images/woo.png', dirname(dirname(__FILE__)) ) . '" height="42" alt="Woocommerce logo"> ';
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="pure-u-1-1 pure-u-lg-3-24 sender-woocommerce-hide-small sender-woocommerce-menu">
        <ul class="sw-tabs sw-main-menu">
            <?php if(get_option('sender_woocommerce_has_woocommerce') && false): ?>
            <li class="tab-link sw-current sw-active" data-tab="dashboard" disabled>
                <a href="#!dashboard"><i class="zmdi zmdi-shopping-cart"></i> Active Carts</a>
            </li>
            <li class="tab-link" data-tab="converted" disabled>
                <a href="#!converted"><i class="zmdi zmdi-money"></i> Converted Carts</a>
            </li>
            <?php endif; ?>
            <li class="tab-link" data-tab="sw-push" disabled>
                <a href="#!sw-push"><i class="zmdi zmdi-notifications-active"></i> Push notifications</a>
            </li>
            <li data-tab="forms" class="tab-link">
                <a href="#!forms"><i class="zmdi zmdi-format-list-bulleted"></i> Forms</a>
            </li>

            <li data-tab="settings" id="workflows" class="tab-link sw-current sw-active">
                <a href="#!settings"><i class="zmdi zmdi-settings"></i> Settings</a>
            </li>
            
        </ul>
    </div>
    <div class="pure-u-1-1 pure-u-lg-18-24 sender-woocommerce-content">
       <?php if(get_option('sender_woocommerce_has_woocommerce') && false): ?>
        <div id="dashboard" class="sw-tab-content sw-current">
            <?php include_once 'tabs/sw-tab-dashboard.php'; ?>
        </div>
        <div id="converted" class="sw-tab-content">
            <?php include_once 'tabs/sw-tab-converted.php'; ?>
        </div>
        <?php endif; ?>
        <div id="forms" class="sw-tab-content <?php echo !get_option('sender_woocommerce_has_woocommerce') ? 'sw-current' : '';?>">
            <?php include_once 'tabs/sw-tab-forms.php'; ?>
        </div>
        <div id="sw-push" class="sw-tab-content">
            <?php include_once 'tabs/sw-tab-push.php'; ?>
        </div>
        <div id="settings" class="sw-tab-content sw-current">
            <?php include_once 'tabs/sw-tab-settings.php'; ?>
        </div>
      
    </div>
</div>



