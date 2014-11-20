<style type="text/css">
    #free_trial {
        background: url(<?php echo CTCT_FILE_URL; ?>images/admin/btn_free_trial_green.png) no-repeat 0px 0px;
        margin: 0px 5px 0px 0px;
        width: 246px;
        height: 80px;
    }
    a#free_trial,
    a#see_how {
        display:block;
        text-indent:-9999px;
        overflow:hidden;
        float:left;
    }
    a#free_trial:hover,
    a#see_how:hover {
        background-position: 0px -102px;
    }
    #see_how {
        background: url(<?php echo CTCT_FILE_URL; ?>images/admin/btn_see_how_blue.png) no-repeat 0px 0px;
        margin: 0px 10px 0px 0px;
        width: 216px;
        height: 80px;
    }
     {
        text-indent: -9999px;
        overflow: hidden;
        text-align: left;
    }
    #cc-message-wrap {
        padding: 10px;
        margin-bottom: 20px!important;
        clear: both;
        float: left;
    }
    #cc-message-wrap h2 {
        margin-bottom: .5em;
    }
    .learn-more {
        margin-left: 15px!important;
    }
</style>

<div class="wrap" id="cc-message-wrap">
    <h2 class="clear"><?php echo sprintf(__('Did you know that Constant Contact offers <a href="%s" title="Learn more about Constant Contact Event Marketing" rel="external">Event&nbsp;Marketing</a>?', 'ctct'), 'http://katz.si/4o'); ?></h2>
    <a id="see_how" href="http://katz.si/4p" rel="external"><?php _e('See How it Works!', 'ctct'); ?></a>
    <a id="free_trial" href="http://katz.si/4k" rel="external"><?php _e('Start Your Free Trial', 'ctct'); ?></a>
    <ul class="ul-disc clear">
        <li><?php echo sprintf(__('Affordable, priced for small business, discount for nonprofits. <a href="%s">Start for FREE!</a>', 'ctct'), 'http://katz.si/4k'); ?></li>
        <li><?php _e('Easy-to-use tools and templates for online event registration and promotion', 'ctct'); ?></li>
        <li><?php _e('Professional &#8212; you, and your events, look professional', 'ctct'); ?></li>
        <li><?php _e('Secure credit card processing &#8212; collect event fees securely with PayPal processing', 'ctct'); ?></li>
        <li><?php _e('Facebook, Twitter links make it easy to promote your events online', 'ctct'); ?></li>
        <li><?php echo sprintf(__('Track and see results with detailed reports on invitations, payments, RSVP\'s, <a href="%s">and more</a>', 'ctct'), 'http://katz.si/4n'); ?></li>
    </ul>
</div>