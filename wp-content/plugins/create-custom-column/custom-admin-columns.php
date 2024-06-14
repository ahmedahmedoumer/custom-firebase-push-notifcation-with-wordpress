<?php
/**
 * Plugin Name: Is Notified Column
 * Description: Adds a custom column to the posts list table in the admin to display the "Is Notified" status or prompt to send notification.
 * Version: 1.0
 * Author: Your Name
 */

 function add_is_notified_column() {
    global $wpdb;
    $column = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->posts} LIKE 'is_notified'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE {$wpdb->posts} ADD COLUMN is_notified TINYINT(1) DEFAULT 0");
    }
}
register_activation_hook(__FILE__, 'add_is_notified_column');



function custom_add_column_to_post_view($columns) {
    $columns['is_notified'] = 'Is Notified';
    return $columns;
}
add_filter('manage_posts_columns', 'custom_add_column_to_post_view');



function custom_display_is_notified_column($column_name, $post_id) {
    if ($column_name === 'is_notified') {
        global $wpdb;
        $is_notified = $wpdb->get_var(
            $wpdb->prepare("SELECT is_notified FROM $wpdb->posts WHERE ID = %d", $post_id)
        );
        if ($is_notified == 1) {
            echo '<div id="sendNotificationContainer_' . $post_id . '"><span style="cursor: pointer; padding: 5px 10px; border-radius: 5px; background-color: #4CAF50; color: white;" data-post-id="' . $post_id . '" onclick="send_manual_fcm_notification_ajax(' . $post_id . ')"> notify again</span></div>';
        } else {
            echo '<div id="sendNotificationContainer_' . $post_id . '"><span class="send-notification-button" data-post-id="' . $post_id . '" onclick="send_manual_fcm_notification_ajax(' . $post_id . ')" style="cursor: pointer; padding: 5px 10px; border-radius: 5px; background-color: #f44336; color: white;">Send Notification</span></div>';
        }
        
        
    }
}
add_action('manage_posts_custom_column', 'custom_display_is_notified_column', 10, 2);
// In your theme's functions.php or a custom plugin
function enqueue_custom_admin_script() {
    ?>
    <script type="text/javascript">
        function send_manual_fcm_notification_ajax(post_id) {
            // console.log("Sending notification for post ID: " + post_id);
            
            // Making an AJAX call to the server-side function
            jQuery.ajax({
                url: ajaxurl, // This is a global variable in WordPress admin that points to admin-ajax.php
                type: 'POST',
                data: {
                    action: 'send_manual_fcm_notification',
                    post_id: post_id
                },
                success: function(response) {
                    console.log('Notification sent successfully', response);
                    // You can update the UI or provide feedback to the user here
                },
                error: function(error) {
                    console.error('Error sending notification', error);
                }
            });
        }
    </script>
    <?php
}
add_action('admin_footer', 'enqueue_custom_admin_script');

// Define the server-side function to handle the AJAX request
function handle_send_manual_fcm_notification() {
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        
        // Call your function to send the FCM notification
        send_manual_fcm_notification($post_id);
        
        // Respond with success
        wp_send_json_success("Notification sent for post ID: " . $post_id);
    } else {
        wp_send_json_error("Post ID not provided");
    }
}
add_action('wp_ajax_send_manual_fcm_notification', 'handle_send_manual_fcm_notification');


function send_manual_fcm_notification($post_id) {
    // Load the Google Auth library
    require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

    // Read the JSON file content
    $serviceAccountJson = file_get_contents(plugin_dir_path(__FILE__) . 'pvKey.json');
    $serviceAccount = json_decode($serviceAccountJson, true);

    if ($serviceAccount === null) {
        // Log error if JSON decoding fails
        error_log('Error decoding service account JSON: ' . json_last_error_msg());
        return;
    }

    // Instantiate ServiceAccountCredentials
    $credential = new Google\Auth\Credentials\ServiceAccountCredentials(
        "https://www.googleapis.com/auth/firebase.messaging",
        $serviceAccount
    );
    
    $token = $credential->fetchAuthToken(Google\Auth\HttpHandler\HttpHandlerFactory::build());

    // Retrieve device tokens from the wp_device_tokens table
    $device_tokens = get_manually_device_tokens();

    error_log('Retrieved device tokens: ' . print_r($device_tokens, true));

    if (empty($device_tokens)) {
        // Log error if no device tokens found
        error_log('No device tokens found.');
        return;
    }

    // Send FCM notification for each device token
    foreach ($device_tokens as $device_token) {
        send_manual_fcm_to_device($device_token, $token['access_token'], $post_id);
    }
}

function get_manually_device_tokens() {
    global $wpdb;
    return $wpdb->get_col("SELECT token FROM {$wpdb->prefix}device_tokens");
}

function send_manual_fcm_to_device($device_token, $access_token, $post_id) {
    $ch = curl_init("https://fcm.googleapis.com/v1/projects/project-blog-test-d67dc/messages:send");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);

    // Customize your notification payload here
    // $post_title = get_the_title($post_id);

    $notification_payload = [
        "message" => [
            "token" => $device_token,
            "notification" => [
                "title" => "post_title",
                "body" => "A new post has just been published. Click to read it now!",
                "image" => "https://cdn.shopify.com/s/files/1/1061/1924/files/Sunglasses_Emoji.png?2976903553660223024"
            ],
            "webpush" => [
                "fcm_options" => [
                    "link" => get_permalink($post_id) // Link to the newly created post
                ]
            ]
        ]
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification_payload));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        // Handle the error
        error_log('FCM Notification Error: ' . curl_error($ch));
    } else {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'posts',
            array('is_notified' => 1),
            array('ID' => $post_id),
            array('%d')
        );
        // Log the response for debugging
        error_log('FCM Notification Response: ' . $response);
    }

    curl_close($ch);
}


