<?php
/**
 * Plugin Name:       Post to QRCode
 * Plugin URI:        http://saberhr.com/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Saber Hossen Rabbani
 * Author URI:        http://saberhr.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pqrc
 * Domain Path:       /languages
 */

/*
function wordcount_activation_hook () {}
register_activation_hook(__FILE__ , 'wordcount_activation_hook');
function wordcount_deactivation_hook () {}
register_deactivation_hook(__FILE__ , 'wordcount_deactivation_hook');
*/


function pqrc_load_textdomain () {
    load_plugin_textdomain('pqrc', false, dirname(__FILE__) . '/languages');
}
add_action('plugins_loaded', 'pqrc_load_textdomain');

function pqrc_display_qrcode ($content) {
    $current_post_id = get_the_ID();
    $current_post_url = urlencode(get_permalink($current_post_id));
    $current_post_title = get_the_title($current_post_id);
    $current_post_type = get_post_type($current_post_id);
    // post type
    $excluded_post_types = apply_filters('pqrc_excluded_post_types', array());
    if (in_array($current_post_type, $excluded_post_types)) {
        return $content;
    }
    // Dimension

    $height = get_option('pqrc_height');
    $height = $height ? $height : 150;

    $width = get_option('pqrc_width');
    $width = $width ? $width : 150;
    $dimension  = apply_filters("pqrc_dimension", "{$height}x{$width}");
    // image attributes
    $image_attr = get_option('pqrc_image_attributes');
    $image_attributes = apply_filters("pqrc_image_attributes", $image_attr);
    $img_src = sprintf("https://api.qrserver.com/v1/create-qr-code/?size=%s&data=%s", $dimension, $current_post_url);
    $content .= sprintf("<div class='qrcode'><img %s src='%s' alt='%s'></div>", $image_attributes, $img_src, $current_post_title);

    return $content;
}

add_filter('the_content', 'pqrc_display_qrcode');



function pqrc_settings_init () {
    // add section
    add_settings_section('pqrc_section', __('Post to QR Code', 'pqrc'), 'pqrc_section_callback', 'general');
    // add settings
    add_settings_field('pqrc_height', __('QR Code Height', 'pqrc'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_height'));
    add_settings_field('pqrc_width', __('QR Code Width', 'pqrc'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_width'));
    add_settings_field('pqrc_image_attributes', __('QR Code Image Attributes', 'pqrc'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_image_attributes'));
    add_settings_field('pqrc_select', __('Countries', 'pqrc'), 'pqrc_display_select_field', 'general', 'pqrc_section');
    // register settings
    register_setting('general', 'pqrc_height', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_width', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_image_attributes', array('sanitize_callback' => 'esc_attr'));
}

function pqrc_display_field ($args) {
    $option = get_option($args[0]);
    printf("<input type='text' id='%s' name='%s' value='%s' />", $args[0], $args[0], $option);
}

function pqrc_display_select_field () {
    $option = get_option('pqrc_select');
    $countries = array(
        "None",
        "Afganistan",
        "Bangladesh",
        "Bhutan",
        "India",
        "Maldives",
        "Nepal",
        "Pakistan",
        "Sri Lanka"
    );
    printf('<select name="%s" id="%s">', "pqrc_select", "pqrc_select");
    foreach ($countries as $country) {
        $selected =  ($country == $option) ? "selected=''" : "";
        printf('<option value="%s" %s>%s</option>', $country, $selected, $country);
    }
    echo "</select>";
}


function pqrc_section_callback () {
    echo "<p>".__('Settings for Post to QR Code Plugin', 'pqrc')."</p>";
}



add_action('admin_init', 'pqrc_settings_init');




