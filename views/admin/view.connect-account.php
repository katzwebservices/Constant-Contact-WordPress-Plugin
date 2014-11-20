<style type="text/css">

    /**
     * Hide the "Settings" page name
     */
    .ctct-page-name {
        display: none;
    }

    .wp-filter {
        text-align: center;
        padding-top: .75em;
        padding-bottom: .75em;
    }

    .wp-filter h1,
    .wp-filter h2 {
        margin-bottom: .5em;
    }

    .wp-filter p {
        font-size: 1.2em;
    }

</style>

<div class="wrap">

    <div class="wp-filter">

        <h1><?php _e('Already have a Constant Contact account?', 'ctct'); ?></h1>

        <?php

        echo '<h2>'.esc_html__('To start, sign in to Constant Contact.').'</h2>';

        printf( '<a href="%s" class="button button-primary button-hero">%s</a>', $CTCT->oauth->getAuthorizationUrl(), __('Grant Authorization to this plugin', 'ctct') );

        echo wpautop( esc_html__('Sign in to the Constant Contact account you would like to use.

        Once you sign in, you will be redirected back to this page, and plugin features will be available.', 'ctct') ); ?>
    </div>

    <div class="wp-filter">

        <h1><?php esc_html_e('Don&rsquo;t have a Constant Contact account?', 'ctct'); ?></h1>

        <h2><?php esc_html_e('It&rsquo;s easy to get started.', 'ctct'); ?></h2>

        <?php echo wpautop(esc_html__('Your customers check their inbox all day, every day. You&rsquo;re sure to reach them when you work with Constant Contact. Build relationships, drive revenue, and deliver real results for your business.
', 'ctct')); ?>

        <p>
            <a class="button button-hero button-primary" href="http://katz.si/4l"><?php esc_html_e('Start Your 60-day Free Trial', 'ctct'); ?></a>
            <?php printf(_x('or %sLearn More%s', 'HTML link tags', 'ctct'), '<a href="http://katz.si/4h" class="button button-large button-secondary">', '</a>'); ?>
        </p>

    </div>
</div>