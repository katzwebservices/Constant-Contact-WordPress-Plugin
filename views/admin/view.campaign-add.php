<script>

jQuery(document).ready(function($) {
    $('.step1 select').change(function() {
        if($(this).val() !== '') {
            $('.step2').slideDown();
        } else {
            $('.step2').slideUp();
        }
    });
});

</script>
<?php


$output = '<select id="allposts">
    <option value="">Select a Post</option>';
foreach($allposts as $post) {
    setup_postdata($post);

    $output .= '<option value="'.$post->ID.'">'.$post->post_title.' (ID #'.$post->ID.')</option>';

}
$output .= '</select>';


echo '<div class="step1 step">'.$output.'</div>';

?>

<div class="step2 step" style="display:none;">
    Template options
</div>

<?php

$template = get_post(742);

#die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 #   <html><body>'.str_replace(array("\n","\r", "\t"), ' ', force_balance_tags($template->post_content)).'</body></html>');

$campaign_details = array(
    'email_content_format' => 'XHTML',
    "name" => "Test Campaign 1351175725",
    "subject" => "Subject Test",
    "from_name" => "My Organization",
    "from_email" => "from-email@example.com",
    "reply_to_email" => "replyto-email@example.com",
    "is_permission_reminder_enabled" => true,
    "permission_reminder_text" => "As a reminder, you're receiving this email because you have expressed an interest in MyCompany. Don't forget to add from_email@example.com to your address book so we'll be sure to land in your inbox! You may unsubscribe if you no longer wish to receive our emails.",
    "is_view_as_webpage_enabled" => true,
    "view_as_web_page_text" => "View this message as a web page",
    "view_as_web_page_link_text" => "Click here",
    "greeting_salutations" => "Hello",
    "greeting_name" => "FIRST_NAME",
    "greeting_string" => "Dear ",
    "email_content" => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><title></title></head><body>'.str_replace(array("\n","\r", "\t"), ' ', force_balance_tags($template->post_content)).'</body></html>',
    "text_content" => strip_tags($template->post_content),
    "email_content_format" => "XHTML",
    "style_sheet" => $css,
    "message_footer" => array(
        "organization_name" => "My Organization",
        "address_line_1" => "123 Maple Street",
        "address_line_2" => "Suite 1",
        "address_line_3" => "",
        "city" => "Anytown",
        "state" => "MA",
        "international_state" => "",
        "postal_code" => "01444",
        "country" => "US",
        "include_forward_email" => true,
        "forward_email_link_text" => "Click here to forward this message",
        "include_subscribe_link" => true,
        "subscribe_link_text" => "Subscribe to Our Newsletter!"
    )
);
r(json_encode($campaign_details), true);
$Campaign = new KWSCampaign($campaign_details);

$Campaign->set('email_content_format', 'XHTML');

#$Campaign
/*{
    "name": "Test Campaign 1351175725",
    "subject": "Subject Test",
    "from_name": "My Organization",
    "from_email": "from-email@example.com",
    "reply_to_email": "replyto-email@example.com",
    "is_permission_reminder_enabled": true,
    "permission_reminder_text": "As a reminder, you're receiving this email because you have expressed an interest in MyCompany. Don't forget to add from_email@example.com to your address book so we'll be sure to land in your inbox! You may unsubscribe if you no longer wish to receive our emails.",
    "is_view_as_webpage_enabled": true,
    "view_as_web_page_text": "View this message as a web page",
    "view_as_web_page_link_text": "Click here",
    "greeting_salutations": "Hello",
    "greeting_name": "FIRST_NAME",
    "greeting_string": "Dear ",
    "email_content": "<html><body><p>This is text of the email message.</p></body></html>",
    "text_content": "This is the text of the email message.",
    "email_content_format": "HTML",
    "style_sheet": "",
    "message_footer": {
        "organization_name": "My Organization",
        "address_line_1": "123 Maple Street",
        "address_line_2": "Suite 1",
        "address_line_3": "",
        "city": "Anytown",
        "state": "MA",
        "international_state": "",
        "postal_code": "01444",
        "country": "US",
        "include_forward_email": true,
        "forward_email_link_text": "Click here to forward this message",
        "include_subscribe_link": true,
        "subscribe_link_text": "Subscribe to Our Newsletter!"
    }
   }*/