<?php
/**
 * @global \Ctct\Components\EventSpot\Registrant\Registrant $Registrant
 * @global \Ctct\Components\EventSpot\EventSpot $event
 */


echo ctct_generate_component_table( $Registrant );

?>
<p class="submit"><a href="<?php echo remove_query_arg(array('registrant', 'refresh')); ?>" class="button-primary"><?php _e('Return to Event', 'constant-contact-api'); ?></a> <a href="<?php echo add_query_arg('refresh', 'registrant'); ?>" class="button-secondary alignright" title="<?php _e('Registrant data is stored for 1 hour. Refresh data now.', 'constant-contact-api'); ?>"><?php _e('Refresh Registrant', 'constant-contact-api'); ?></a></p>
