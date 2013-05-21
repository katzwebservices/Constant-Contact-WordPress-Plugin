<div class="wrap constant_contact_plugin_page_list cc_hidden" style="padding-bottom:10px; background:white; display:none;">
    <h2>Plugin Pages</h2>
    <h3>Plugin Configuration</h3>
    <ul class="ul-disc">
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-api'); ?>">Plugin Settings</a> - Configure plugin settings for adding newletter signup capabilities to the WordPress registration form.</li>
        <li><a href="<?php echo admin_url('admin.php?page=constant-analytics'); ?>">Constant Analytics Settings</a> - Configure Google Analytics reports.</li>
        <?php if(defined('CC_FORM_GEN_PATH')) { ?>
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-forms'); ?>">Form Designer</a> - Design a signup form from the ground up.</li>
        <?php } ?>
    </ul>
    <h3>Account Actions</h3>
    <ul class="ul-disc">
        <li><a href="<?php echo admin_url('index.php?page=constant-analytics.php'); ?>">Constant Analytics</a> - View Google Analytics and Constant Contact data directly in your dashboard.</li>
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-contacts'); ?>">Contacts</a> - View, add, edit and delete your contacts.</li>
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-lists'); ?>">Lists</a> - Add, remove, and edit your contact lists.</li>
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-events'); ?>">Events</a> - View your Constant Contact Event Marketing data: events and registrant information.</li>
        <li><a href="<?php echo admin_url('admin.php?page=constant-contact-campaigns'); ?>">Campaigns</a> - View details of your sent &amp; draft email campaigns, <strong>including email campaign stats</strong> such as # Sent, Opens, Clicks, Bounces, OptOuts, and Spam Reports.</li>
    </ul>
</div>