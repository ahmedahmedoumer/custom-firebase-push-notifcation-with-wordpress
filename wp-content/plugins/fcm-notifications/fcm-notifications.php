<?php
/*
Plugin Name: FCM Notifications
Description: Sends FCM notifications on new post creation.
*/

// Hook into the 'publish_post' action to execute the function when a new post is published
add_action('publish_post', 'send_fcm_notification');

function send_fcm_notification($post_id) {
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
    $device_tokens = get_device_tokens();

    error_log('Retrieved device tokens: ' . print_r($device_tokens, true));

    if (empty($device_tokens)) {
        // Log error if no device tokens found
        error_log('No device tokens found.');
        return;
    }

    // Send FCM notification for each device token
    foreach ($device_tokens as $device_token) {
        send_fcm_to_device($device_token, $token['access_token'], $post_id);
    }
}

function get_device_tokens() {
    global $wpdb;
    return $wpdb->get_col("SELECT token FROM {$wpdb->prefix}device_tokens");
}

function send_fcm_to_device($device_token, $access_token, $post_id) {
    $ch = curl_init("https://fcm.googleapis.com/v1/projects/web-notification-4ae2b/messages:send");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);

    // Customize your notification payload here
    $notification_payload = [
        "message" => [
            "token" => $device_token,
            "notification" => [
                "title" => "New Post Published",
                "body" => "A new post has been published on your website.",
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
        // Log the response for debugging
        error_log('FCM Notification Response: ' . $response);
    }

    curl_close($ch);
}
