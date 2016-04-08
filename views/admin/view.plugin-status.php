<?php $version_info = get_plugin_data(CTCT_FILE, false); ?>
<h3><?php _e("Constant Contact Plugin Status", 'constant-contact-api'); ?></h3>
<table class="form-table" id="ctct-plugin-status">
    <tr valign="top">
       <th scope="row"><?php _e("PHP Version", 'constant-contact-api'); ?></th>
        <td class="installation_item_cell">
            <strong><?php echo phpversion(); ?></strong>
        </td>
        <td>
            <?php
                if(version_compare(phpversion(), WP_CTCT::min_php_version, '>')){
                    ?>
                    <i title="<?php printf(__('You are running a compatible PHP version (%s). This plugin requires version %s or higher.', 'constant-contact-api'), phpversion(), WP_CTCT::min_php_version ); ?>"  class="dashicons dashicons-yes"></i>
                    <?php
                }
                else{
                    ?>
                    <i class="dashicons dashicons-no"></i>
                    <span class="installation_item_message"><?php echo sprintf(__("The %s requires PHP %s or above.", 'constant-contact-api'), $version_info['Name'], WP_CTCT::min_php_version ); ?></span>
                    <?php
                }
            ?>
        </td>
    </tr>
    <tr valign="top">
       <th scope="row"><?php _e("WordPress Version", 'constant-contact-api'); ?></th>
        <td class="installation_item_cell">
            <strong><?php echo get_bloginfo("version"); ?></strong>
        </td>
        <td>
            <?php
                if(version_compare(get_bloginfo("version"), '3.2', '>')){
                    ?>
                    <i title="<?php printf(__('Your WordPress version is compatible with this plugin. The plugin requires WordPress version 3.2 or higher, and you are running version %s.', 'constant-contact-api'), get_bloginfo("version")); ?>" class="dashicons dashicons-yes"></i>
                    <?php
                }
                else{
                    ?>
                    <i class="dashicons dashicons-no"></i>
                    <span class="installation_item_message"><?php printf(__("The %s requires WordPress v%s or greater. You must upgrade WordPress in order to use this version of Gravity Forms.", 'constant-contact-api'), $version_info['Name'], '3.2'); ?></span>
                    <?php
                }
            ?>
        </td>
    </tr>
    <tr valign="top">
       <th scope="row"><?php _e("Plugin Version", 'constant-contact-api'); ?></th>
        <td class="installation_item_cell">
            <strong><?php echo WP_CTCT::version; ?></strong>
        </td>
        <td>
            <?php
                if(version_compare(floatval( WP_CTCT::version ), floatval($version_info["Version"]), '>=')){
                    ?>
                    <i title="<?php printf(__('You are running %s, the latest version of the plugin.', 'constant-contact-api'), $version_info['Version']); ?>" class="dashicons dashicons-yes"></i>
                    <?php
                }
                else{
                    echo sprintf(__("New version %s available. Automatic upgrade available on the %splugins page%s", 'constant-contact-api'), $version_info["Version"], '<a href="plugins.php">', '</a>');
                }
            ?>
        </td>
    </tr>
     <tr valign="top">
       <th scope="row"><?php _e("Constant Contact Status", 'constant-contact-api'); ?></th>
        <td colspan="2">
            <?php _e(sprintf('<a href="%s" rel="external">Check Constant Contact service status</a>', 'http://status.constantcontact.com'), 'constant-contact-api'); ?>
        </td>
    </tr>
</table>