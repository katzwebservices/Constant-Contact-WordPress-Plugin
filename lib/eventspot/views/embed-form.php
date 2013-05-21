<script>

    jQuery(document).ready(function() {
        jQuery('#insert-eventspot').on('click', function() {
            var event_id = jQuery("#add_event_id").val();
            if(event_id == ""){
                alert("<?php _e("Please select an event", "constant-contact-api") ?>");
                jQuery("#add_event_id").focus();
                return;
            }

            // Generate the shortcode code
            var settings = [];
            settings[0] = 'id="'+event_id+'"';
            settings[2] = (jQuery("#eventspot_display_description").is(":checked") ? 'showdescription=1' : 'showdescription=0');
            settings[3] = (jQuery("#eventspot_display_datetime").is(":checked") ? ' datetime=1' : '');
            settings[4] = (jQuery("#eventspot_display_location").is(":checked") ? ' location=1' : '');
            settings[5] = (jQuery("#eventspot_display_map").is(":checked") ? ' map=1' : '');
            settings[6] = (jQuery("#eventspot_display_calendar").is(":checked") ? ' calendar=1' : '');
            settings[7] = (jQuery("#eventspot_display_directtoregistration").is(":checked") ? ' directtoregistration=1' : '');
            settings[8] = (jQuery("#eventspot_display_newwindow").is(":checked") ? ' newwindow=1' : '');
            settings[9] = (jQuery("#eventspot_display_style").is(":checked") ? ' style=1' : '');

            window.send_to_editor('[eventspot ' + settings.join(' ') + ' /]');

        });
    });

</script>
<div id="select_eventspot_event" style="display:none;">
    <div class="wrap">
        <div>
            <div style="padding:15px 15px 0 15px;">
                <a href="http://katz.si/4o" rel="external"><img src="<?php echo plugins_url('images/eventspot-logo.png', EVENTSPOT_FILE); ?>" alt="EventSpot from Constant Contact" /></a>
            <?php
            $events = CTCT_EventSpot::getInstance()->old_api->getEvents();
            $events = null;
            if(empty($events)) {
                include(EVENTSPOT_FILE_PATH.'/views/promo.php');
            } else {
            ?>
                <h2><?php _e("Insert An Event", "constant-contact-api"); ?></h2>
                <span>
                    <?php _e("Select an event below to add it to your post or page.", "constant-contact-api"); ?>
                </span>
            </div>
            <div style="padding:15px 15px 0 15px;">
                <select id="add_event_id">
                    <option value="">  <?php _e("Select an Event", "constant-contact-api"); ?>  </option>
                    <?php

                        foreach($events['events'] as $event){
                            ?>
                            <option value="<?php echo constant_contact_get_id_from_object($event); ?>"><?php echo esc_html($event->title) ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div style="padding:15px 15px 0 15px;">
                <label for="eventspot_display_description" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_description" checked='checked' />
                    <?php _e("Display event description", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_datetime" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_datetime" />
                    <?php _e("Display event date &amp; time", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_location" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_location" />
                    <?php _e("Display event location", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_map" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_map" />
                    <?php _e("Display map link for location (if location is shown)", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_calendar" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_calendar" />
                    <?php _e("Display \"Add to Calendar\" link", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_directtoregistration" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_directtoregistration" />
                    <?php _e("Link directly to registration page, rather than event homepage", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_newwindow" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_newwindow" />
                    <?php _e("Open event links in a new window", "constant-contact-api"); ?>
                </label>
                <label for="eventspot_display_usestyles" style="padding-right:.7em; display:block;">
                    <input type="checkbox" id="eventspot_display_usestyles" checked="checked" />
                    <?php _e("Use plugin styles. Disable if you want to use your own styles (CSS)", "constant-contact-api"); ?>
                </label>
            </div>
            <div style="padding:15px;" class="submit">
                <input type="button" class="button-primary" id="insert-eventspot" value="<?php _e('Insert Event', 'constant-contact-api'); ?>" />
                <a class="button alignright button-secondary" style="color:#ccc;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "constant-contact-api		"); ?></a>
            </div>
        </div>
    <?php } // End empty events ?>
    </div>
</div>