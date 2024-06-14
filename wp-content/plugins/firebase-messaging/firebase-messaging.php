<?php
/*
Plugin Name: Firebase Messaging
Description: Adds Firebase Messaging to the site to get device tokens for push notifications.
Version: 1.0
Author: Ahmed Oumer
*/

// Enqueue Firebase scripts in the footer
function enqueue_firebase_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Localize the script with the AJAX URL
    wp_localize_script('jquery', 'my_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('firebase-config', plugin_dir_url(__FILE__) . '../firebase-config.js', array(), null, true);

    ?>
    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js";

        import { firebaseConfig, vapidKey } from "<?php echo plugin_dir_url(__FILE__); ?>../firebase-config.js";
        
        // const firebaseConfig = {
        //     apiKey: "AIzaSyA-66ZIon376N2xpzP8TQq0Xlstq-YfOwg",
        //     authDomain: "project-blog-test-d67dc.firebaseapp.com",
        //     projectId: "project-blog-test-d67dc",
        //     storageBucket: "project-blog-test-d67dc.appspot.com",
        //     messagingSenderId: "641626738940",
        //     appId: "1:641626738940:web:742f37ca9730cd20fe8779",
        //     measurementId: "G-P97MTDEB20"
        //     };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        document.addEventListener('DOMContentLoaded', () => {
            if (!localStorage.getItem('fcm_token')) {
                showPermissionDialog();
            } else {
                checkExistingToken(localStorage.getItem('fcm_token'));
            }
        });

        function showPermissionDialog() {
            const dialog = document.createElement('div');
            dialog.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: flex-start; justify-content: center; z-index: 9999;">
                    <div style="position: relative; top: 20px; background: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <p>We would like to send you notifications for the latest updates.</p>
                        <button id="allow-notifications" style="margin-right: 10px;">Allow</button>
                        <button id="later-notifications">I will do later</button>
                    </div>
                </div>
            `;
            document.body.appendChild(dialog);

            document.getElementById('allow-notifications').addEventListener('click', () => {
                requestNotificationPermission();
                document.body.removeChild(dialog);
            });

            document.getElementById('later-notifications').addEventListener('click', () => {
                document.body.removeChild(dialog);
            });
        }

        function requestNotificationPermission() {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    navigator.serviceWorker.register("<?php echo plugin_dir_url(__FILE__); ?>sw.js").then(registration => {
                        getToken(messaging, {
                            serviceWorkerRegistration: registration,
                            vapidKey: vapidKey,
                        }).then((currentToken) => {
                            if (currentToken) {
                                console.log("Token is: " + currentToken);
                                localStorage.setItem('fcm_token', currentToken);
                                storeToken(currentToken, true); // new visitor
                            } else {
                                console.log('No registration token available. Request permission to generate one.');
                            }
                        }).catch((err) => {
                            console.log('An error occurred while retrieving token. ', err);
                        });
                    });
                }
            });
        }

        function storeToken(token, isNew) {
            // Send the token to the server via AJAX
            jQuery.ajax({
                type: 'POST',
                url: my_ajax_object.ajaxurl,
                data: {
                    action: 'store_device_token',
                    token: token,
                    is_new: isNew,
                    _ajax_nonce: '<?php echo wp_create_nonce('firebase_messaging'); ?>'
                },
                success: function(response) {
                    console.log(response); // Output the response from the server
                },
                error: function(xhr, status, error) {
                    console.error('Error storing token: ' + error); // Log any errors
                }
            });
        }

        function checkExistingToken(token) {
            jQuery.ajax({
                type: 'POST',
                url: my_ajax_object.ajaxurl,
                data: {
                    action: 'check_device_token',
                    token: token,
                    _ajax_nonce: '<?php echo wp_create_nonce('firebase_messaging'); ?>'
                },
                success: function(response) {
                    if (!response.success) {
                        showPermissionDialog(); // Show dialog if the token is not recognized
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking token: ' + error); // Log any errors
                }
            });
        }
    </script>
    <?php
}

add_action('wp_footer', 'enqueue_firebase_scripts');

// Handle AJAX request to store device token
function store_device_token() {
    check_ajax_referer('firebase_messaging', '_ajax_nonce');

    $token = sanitize_text_field($_POST['token']);
    $is_new = sanitize_text_field($_POST['is_new']);

    if (!empty($token)) {
        global $wpdb;

        // Check if the token already exists
        $existing_token = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}device_tokens WHERE token = %s",
            $token
        ));

        if ($existing_token) {
            wp_send_json_success('Token already exists.');
        } else {
            // Insert the token into the database
            $result = $wpdb->insert(
                $wpdb->prefix . 'device_tokens',
                array(
                    'token' => $token
                )
            );

            if ($result) {
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

// Handle AJAX request to check if device token exists
function check_device_token() {
    check_ajax_referer('firebase_messaging', '_ajax_nonce');

    $token = sanitize_text_field($_POST['token']);

    if (!empty($token)) {
        global $wpdb;

        // Check if the token already exists
        $existing_token = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}device_tokens WHERE token = %s",
            $token
        ));

        if ($existing_token) {
            wp_send_json_success('Token already exists.');
        } else {
            wp_send_json_error('Token not found.');
        }
    } else {
        wp_send_json_error('Token is empty.');
    }
}

add_action('wp_ajax_check_device_token', 'check_device_token');
add_action('wp_ajax_nopriv_check_device_token', 'check_device_token');
