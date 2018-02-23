<?php 
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    $formWidgetEnabled = get_option('sender_automated_emails_allow_forms');
    $forms = $sender_api->getAllForms();
?>
 
<h1>Forms</h1>
<?php if(isset($forms->error)): ?>
<div class="pure-g">
    <div class="pure-u-3-24 sae-details-settings">
<?php
update_option( 'sender_automated_emails_allow_forms', false );
echo $sender_helper->showNotice($forms->error->message, 'warning'); 
?>
    </div>
    <div class="pure-u-12-24">
        <p>
            In order to use this widget you need to have form created at Sender.net
        </p>
        <p>
            <a target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/forms/add">Create form</a>
        </p>
    </div>
</div>
<?php else: ?>
<div class="pure-g">

    <div class="pure-u-1-1">
        <h3><i class="zmdi zmdi-format-list-bulleted"></i> Widget is <?php echo !$formWidgetEnabled ? '<span id="saeToggleWidgetTitle" style="color:red;">disabled</span>' : '<span id="saeToggleWidgetTitle" style="color:green;">enabled</span>'; ?> </h3>  
    </div>

    <div class="pure-u-1-1 pure-u-sm-3-24 sae-details-settings">
            <button id="saeToggleWidget" style="width: 90%; background-color:<?php echo !$formWidgetEnabled ? 'green' : 'red'; ?>" class="sender-net-automated-emails-button"><?php echo !$formWidgetEnabled ? 'Enable' : 'Disable'; ?></button>
    </div>

    <div class="pure-u-1-1 pure-u-sm-12-24">
        <p>
            When enabled, a Sender.net form widget will appear in the customization menu. It allows you to insert your Sender.net form into anywhere on your web page.
        </p>
        <p>
            <a href="<?php echo admin_url() . 'widgets.php'; ?>">Manage widgets</a>
        </p>
    </div>

</div>
<h3>Your form list</h3>
<a target="_BLANK" href="<?php echo $sender_helper->getBaseurl(); ?>/forms/add" class="sender-net-automated-emails-button">Create form</a>
<div class="table-responsive-vertical shadow-z-1">
    <table id="table"  class="table table-hover tablesorter">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Version</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php

                $i = 0;
                foreach($forms as $form) {
                    $forma = $sender_api->getFormById($form->id);
                    $i++;
                    printf('
                        <tr>
                            <td>%d</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td><a target="_BLANK" href="%s/forms/subscription_edit/%d" >Edit</a> | <a target="_BLANK" href="%s/forms/subscription/%d" >Preview</a></td>
                        </tr>',(int) $i, esc_html($form->title), esc_html($form->version), esc_url($sender_api->getBaseUrl()), (int) $form->id, esc_url($sender_api->getBaseUrl()), (int) $form->id );
                }
            ?>
        </tbody>
    </table>
         
    <script> jQuery("table").tablesorter( {sortList: [[6,1]]});</script>
</div>
<?php endif; ?>
