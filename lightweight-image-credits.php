<?php
/*
Plugin Name: Lightweight Image Credits Plugin
Plugin URI: http://www.meinstoffwechsel.com
Description: Will add a box to your backend edit pages to insert image credits. A shortcode will show all image credits, sorted by their origin site.
Version: 1.0
Author: Matthias Burgdorf
Author URI: http://www.meinstoffwechsel.com
License: GPL2

------------------------------------------------------------------------
Copyright Matthias Burgdorf

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

*/


/**
 * Output image credit list if shortcode is inserted somewhere
 */

function lightweight_ic_show_image_credits()
{
    global $wpdb;

    $metakey = 'image_credits';
    $post_status = 'publish';

    $list_of_image_credits = $wpdb->get_results("SELECT $wpdb->posts.post_name, $wpdb->postmeta.meta_value FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE $wpdb->postmeta.meta_key = 'image_credits' AND $wpdb->posts.post_status = 'publish' ORDER BY meta_value ASC");
    
    foreach ($list_of_image_credits as $image_credit_list) {
        if (!empty($image_credit_list->meta_value)) {
            echo "<strong>" . get_site_url() . "/" . esc_attr($image_credit_list->post_name) . "</strong><br>";
            echo "© " . esc_attr( $image_credit_list->meta_value);
            echo "<br><br>";
        }
    }
}

/**
 * add shortcode
 */

add_shortcode( 'image_credits' , 'lightweight_ic_show_image_credits' );

/**
 * Add custom meta box for postmeta field "image_credits" to admin backend pages.
 * 
 */

function lightweight_ic_custom_meta_box_markup($post)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    $value = get_post_meta( $post->ID, 'image_credits', true );
    ?>
        <div>
            <label for="meta-box-text"><?php __('Please insert all image credit notices here and separate them with a comma:','lightweight-image-credits'); ?></label><br>
            <input name="meta-box-text" type="text" style="width:100%" value="<?php print(esc_attr( $value )); ?>">

            <br>

        </div>
    <?php  
}

function lightweight_ic_save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $meta_box_text_value = "";
    $meta_box_dropdown_value = "";
    $meta_box_checkbox_value = "";

    if(isset($_POST["meta-box-text"]))
    {
        $meta_box_text_value = sanitize_text_field($_POST["meta-box-text"]);
    }   
    update_post_meta($post_id, "image_credits", $meta_box_text_value);
}

add_action("save_post", "lightweight_ic_save_custom_meta_box", 10, 3);

/*
* adds a custom meta box to the admin section for page and post edit pages
*/

function lightweight_ic_add_custom_meta_box()
{
    $screens = array( 'post', 'page' );
    add_meta_box("image-credits-box", "Image Credits", "lightweight_ic_custom_meta_box_markup", $screens, "normal", "high", null);
}

add_action("add_meta_boxes", "lightweight_ic_add_custom_meta_box");
