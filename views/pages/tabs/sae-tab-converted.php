<?php 
    $cartPeriod = get_option('sender_automated_emails_cart_period');
?>
<h1>Converted Carts</h1>
<div class="pure-g">
    <div class="pure-u-1-1">
        <div class="pure-u-1-1">
        <label for="saeCartsPeriod" style="font-size:15px; font-weight: bold;"><i class="zmdi zmdi-shopping-cart"></i> Show carts carts for:</label>
        <select id="saeCartsPeriod" style="margin-right: 15px;">
            <option value="#" disabled selected>Select period</option>
            <option value="today" <?php echo 'today' === $cartPeriod ? 'selected' : ''; ?>>Today</option>
            <option value="week" <?php echo 'week' === $cartPeriod ? 'selected' : ''; ?>>Last week</option>
            <option value="month" <?php echo 'month' === $cartPeriod ? 'selected' : ''; ?>>Last month</option>
            <option value="alltime" <?php echo 'alltime' === $cartPeriod ? 'selected' : ''; ?>>All time</option>
        </select>
        <script>
            jQuery('#saeCartsPeriod').on('change', function (event) {
                var period = jQuery('#saeCartsPeriod option:selected').val();
                window.location = "<?php echo get_admin_url();?>options-general.php?page=sender-net-automated-emails&action=change_period&tp=" + period;
            });
        </script>
    </div>
    <div class="table-responsive-vertical shadow-z-1">
        <?php echo $sender_helper->getSenderConvertedCarts(); ?>
         
        <script> jQuery("table").tablesorter( {sortList: [[6,1]]});</script>
    </div>
</div>
</div>

