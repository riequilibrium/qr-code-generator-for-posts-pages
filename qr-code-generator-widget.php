<?php
/*
Plugin Name: QR Code Generator for Posts/Pages
Plugin URI: https://github.com/riequilibrium/qr-code-generator-for-posts-pages
Description: This plugin generates QR Codes automatically after you publish a Post/Page and it is visible in the Admin area. This QR Code links directly to the Post/Page you have just created.
Version: 1.0.0
Text Domain: qr-code-generator-riequilibrium
Author: Riequilibrium Web Agency
Author URI: https://riequilibrium.com
License: GPLv3
*/

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2020-12-07
 * Description: Loads existing translations based on installation's language
 */
function qr_code_generator_init(){
    $current_user = wp_get_current_user();
    if(!($current_user instanceof WP_User))
        return;
    if(function_exists('get_user_locale'))
        $language = get_user_locale($current_user);
    else
        $language = get_locale();
    load_textdomain("qr-code-generator-riequilibrium", plugin_dir_path(__FILE__) . "/languages/" . $language . ".mo");
}
add_action("plugins_loaded", "qr_code_generator_init");

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2020-11-03
 * Description: Creates a meta box on the side as first element, shown in posts and pages
 */
function qr_code_riequilibrium_box(){
	$screens = ["post", "page"]; // Select types of screens
	foreach($screens as $screen){
		add_meta_box(
			"qr_code_riequilibrium", // Unique ID
			__("QR Code for this page", "qr-code-generator-riequilibrium"), // Box title
			"qr_code_riequilibrium_html", // Content callback, must be of type callable
			$screen, // Post type
			"side", // Context
			"high" // Priority
		);
	}
}
add_action("add_meta_boxes", "qr_code_riequilibrium_box", 1);

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2020-11-03
 * Description: HTML callback for the creation of the meta box
 */
function qr_code_riequilibrium_html($post){
    if(get_post_status() == "publish" || get_post_status() == "future" || get_post_status() == "private"){ // If the post status is set to Publish, Future or Private
        ?>
        <script>
            /**
             * Author: Simone Di Paolo
             * Company: Riequilibrium Web Agency
             * Contact: it@riequilibrium.com
             * Date: 2020-11-04
             * Description: When the button is clicked, calls handler through AJAX to download locally the QR Code
             */
            function download_qr(){
                var download_btn = document.getElementById("download_qr");
                jQuery.ajax({
                    url: "<?php echo plugin_dir_url(__FILE__) . 'handler.php'; ?>",
                    data: { type: "download", permalink: "<?php echo get_permalink(); ?>", postID: "<?php echo $post->ID; ?>" },
                    type: "POST",
                    success: function(){
                        download_btn.setAttribute("href", "<?php echo plugin_dir_url( __FILE__ ) . 'tmp/qr-post-' . $post->ID . '.png'; ?>"); // Adds href attribute to hidden element linked to the temporary image
                        download_btn.setAttribute("download", "qr-<?php echo $post->post_name; ?>.png"); // Adds download attribute to hidden element renaming it with the slug
                        download_btn.click(); // Clicks the hidden element to automatically download locally the QR Code
                        download_btn.removeAttribute("href"); // Removes attribute href to hidden element
                        download_btn.removeAttribute("download"); // Removes attribute download to hidden element
                        delete_qr(); // Calls delete function
                    },
                    error: function(e){
                        console.log(e);
                    }
                });
            }
            /**
             * Author: Simone Di Paolo
             * Company: Riequilibrium Web Agency
             * Contact: it@riequilibrium.com
             * Date: 2020-11-04
             * Description: Calls handler through AJAX to delete the QR Code saved temporarily on the server
             */
            function delete_qr(){
                jQuery.ajax({
                    url: "<?php echo plugin_dir_url(__FILE__) . 'handler.php'; ?>",
                    data: { type: "delete", permalink: "<?php echo get_permalink(); ?>", postID: "<?php echo $post->ID; ?>" },
                    type: "POST",
                    error: function(e){
                        console.log(e);
                    }
                });
            }
        </script>
        <!-- HTML -->
        <img style="width: 100%;" src="https://chart.apis.google.com/chart?cht=qr&chs=500x500&chl=<?php echo get_permalink(); ?>" />
        <div style="text-align: center; padding-bottom: 25px;">
            <a onclick="download_qr();" class="button button-primary button-large"><?php echo __("Download QR Code", "qr-code-generator-riequilibrium"); ?></a>
            <a id="download_qr"></a>
        </div>
        <?php
    }else{ // If the post status is set to Draft, Pending, Trash or Auto-Draft
        /**
         * Author: Simone Di Paolo
         * Company: Riequilibrium Web Agency
         * Contact: it@riequilibrium.com
         * Date: 2020-11-04
         * Description: Shows information message, distinguished by post or page, that says that you have to publish to see the QR Code
         */
        ?>
        <h4><?php if($post->post_type == "post") echo __("Publish the post to show the QR Code", "qr-code-generator-riequilibrium"); else if($post->post_type == "page") echo __("Publish the page to show the QR Code", "qr-code-generator-riequilibrium"); ?></h4>
        <?php
    }
}
