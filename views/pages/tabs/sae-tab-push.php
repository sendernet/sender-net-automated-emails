<?php 
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    $isPushEnabled = get_option('sender_automated_emails_allow_push');
    $pushProject = $sender_api->getPushProject();
?>
<h1>Push project</h1>
<div class="pure-g">

<?php if(!isset($pushProject->error)): ?>
        <div class="pure-u-1-1">
            <h3><i class="zmdi zmdi-notifications-active"></i> Push notifications are <?php echo !$isPushEnabled ? '<span id="saeAllowPushTitle" style="color:red;">disabled</span>' : '<span id="saeAllowPushTitle" style="color:green;">enabled</span>'; ?> </h3>  
        </div>
        
        <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">

        <button id="saeAllowPushButton" style="width: 90%; background-color:<?php echo !$isPushEnabled ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$isPushEnabled ? 'Enable' : 'Disable'; ?></button>
        </div>

        <div class="pure-u-1-1 pure-u-sm-12-24">
            <p>
                When enabled, this feature shows your push project's subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard. 
            </p>
            <p>
                <a target="_BLANK" href="https://help.sender.net/section/push-notifications/">Getting started with push notifications</a> | <a target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/push_campaigns">Manage your push campaigns</a> | <a target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/push_projects/view">Customize push project</a>
            </p>
        </div>
<?php else: ?>
    <div class="pure-u-1-1">
        <h3><i class="zmdi zmdi-alert-circle-o"></i> You don't have push project</h3>
        <a class="sender-net-automated-emails-button" target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/push_projects/create">Create a new push project</a>
        
    </div>
<?php endif; ?>
        
   
</div>

