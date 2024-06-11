<?php
/*
Plugin Name: Firebase Messaging
Description: Adds Firebase Messaging to the site to get device tokens for push notifications.
Version: 1.0
Author: Ahmed Oumer
*/

global $wpdb;
global $device_tokens_table;

$device_tokens_table = $wpdb->prefix . 'device_tokens';


// Enqueue Firebase scripts in the footer
function enqueue_firebase_scripts() {
    ?>
    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "AIzaSyA-66ZIon376N2xpzP8TQq0Xlstq-YfOwg",
            authDomain: "project-blog-test-d67dc.firebaseapp.com",
            projectId: "project-blog-test-d67dc",
            storageBucket: "project-blog-test-d67dc.appspot.com",
            messagingSenderId: "641626738940",
            appId: "1:641626738940:web:742f37ca9730cd20fe8779"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        navigator.serviceWorker.register("<?php echo plugin_dir_url(__FILE__); ?>sw.js").then(registration => {
            getToken(messaging, {
                serviceWorkerRegistration: registration,
                vapidKey: 'BDg4Saw-emQk2B0CmW0lbSv_Bsat60jZtLKTwmLndJsYf_a6btOU06CZUidF14amRoRtPVRws3q2XPkEKSfR6kA'
            }).then((currentToken) => {
                if (currentToken) {
                    console.log("Token is: " + currentToken);
                    // Send the token to your server
                    fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce('firebase_messaging'); ?>'
                        },
                        body: JSON.stringify({
                            action: 'store_device_token',
                            token: currentToken
                        })
                    }).then(response => response.json()).then(data => {
                        console.log("Token stored:", data);
                    }).catch(error => {
                        console.error("Error storing token:", error);
                    });
                } else {
                    // Show permission request UI
                    console.log('No registration token available. Request permission to generate one.');
                }
            }).catch((err) => {
                console.log('An error occurred while retrieving token. ', err);
                // ...
            });
        });
    </script>
    <?php
}

add_action('wp_footer', 'enqueue_firebase_scripts');

// Handle AJAX request to store device token
function store_device_token() {
    check_ajax_referer('firebase_messaging');

    $token = sanitize_text_field($_POST['token']);

    if (!empty($token)) {
        global $wpdb;
        global $device_tokens_table;

        // Check if the token already exists
        $existing_token = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $device_tokens_table WHERE token = %s",
            $token
        ));

        if ($existing_token) {
            wp_send_json_success('Token already exists.');
        } else {
            // Insert the token into the database
            $wpdb->insert(
                $device_tokens_table,
                array(
                    'token' => $token
                )
            );

            if ($wpdb->insert_id) {
                wp_send_json_success('Token stored successfully.');
            } else {
                wp_send_json_error('Failed to store token.');
            }
        }
    } else {
        wp_send_json_error('Token is empty.');
    }
}

add_action('wp_ajax_store_device_token', 'store_device_token');
add_action('wp_ajax_nopriv_store_device_token', 'store_device_token');
?>
