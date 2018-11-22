<?php
/**
 *
 * @package AristechDiavgeia
 */


if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$array = array( 'aristech_select','aristech_text','aristech_datepick1','aristech_datepick2','aristech_datepick3','aristech_datepick4','aristech_textarea', 'aristech_extra');

foreach ($array as $item) {
    $item = esc_sql( $item );
    $wpdb->query( "DELETE FROM wp_options WHERE option_name = '$item'" );
}


