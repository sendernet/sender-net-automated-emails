<?php

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

?>

<div class="pure-g sender-woocommerce-connect">
    <div class="pure-u-1-1 sender-woocommerce-header">
        <div class="pure-g">
            <div class="pure-u-1-1 pure-u-sm-1-2 sw-text-left">
                <img src="https://app.sender.net/logo_emails.png?1479651832" alt="">

                <span>
                    <small>v<?php echo SENDERWOO_CURRENT_VERSION; ?></small>
                </span>
            </div>
            <?php if(get_option('sender_woocommerce_has_woocommerce')): ?>
            <div class="pure-u-1-2 pure-u-sm-1-2 sw-text-right">
                <img src="https://help.sender.net/wp-content/uploads/2017/11/wooo.png" height="42" alt="">
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="pure-u-1-3 sender-woocommerce-content">
        <div id="sw-connect" class="sw-tab-content sw-current" style="text-align: left !important;">
            <h1>Connect your plugin</h1>
            <p>We need to connect this plugin to your Seder.net account. </p>
            <br>
            <div class="sw-text-left">
                <h3>Data tracking & scripts</h3>
                <h4><i class="zmdi zmdi-info"></i> All of these options can be disabled!</h4>
                <ul>
                    <?php if(get_option('sender_woocommerce_has_woocommerce')): ?>
                    <li>
                        <i class="zmdi zmdi-shopping-cart"></i>
                        If enabled the plugin will sync customer email and cart with Sender.net
                    </li>
                    <?php endif; ?>
                    
                    <li>
                        <i class="zmdi zmdi-format-list-bulleted"></i>
                        Form widget will include your Sender.net subscription form to your page
                    </li>

                    <li>
                        <i class="zmdi zmdi-notifications-active"></i>
                        Push notifications will add an icon to your shop for users to subscribe
                    </li>

                    <li>
                        <i class="zmdi zmdi-help"></i>
                        It will provide external links to the Sender.net's Help Center documentation
                    </li>
                    
                </ul>
            </div>
            <br>

            <p>To activate this plugin you need to authenticate yourself with Sender.net, please click <strong>authenticate</strong> to enter your credentials</p>

            <a href="<?php echo $sender_helper->getAuthUrl(); ?>" class="sender-woocommerce-button">Authenticate</a>
        </div>
    </div>
</div>