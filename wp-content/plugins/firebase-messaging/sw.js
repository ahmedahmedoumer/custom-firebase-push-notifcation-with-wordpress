// document.addEventListener('DOMContentLoaded', () => {
//     if (!localStorage.getItem('fcm_token')) {
//         showPermissionDialog();
//     } else {
//         checkExistingToken(localStorage.getItem('fcm_token'));
//     }
// });

// function showPermissionDialog() {
//     const dialog = document.createElement('div');
//     dialog.innerHTML = `
//         <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: flex-start; justify-content: center; z-index: 9999;">
//             <div style="position: relative; top: 20px; background: white; padding: 20px; border-radius: 8px; text-align: center;">
//                 <p>We would like to send you notifications for the latest updates.</p>
//                 <button id="allow-notifications" style="margin-right: 10px;">Allow</button>
//                 <button id="later-notifications">I will do later</button>
//             </div>
//         </div>
//     `;
//     document.body.appendChild(dialog);

//     document.getElementById('allow-notifications').addEventListener('click', () => {
//         requestNotificationPermission();
//         document.body.removeChild(dialog);
//     });

//     document.getElementById('later-notifications').addEventListener('click', () => {
//         document.body.removeChild(dialog);
//     });
// }

// function requestNotificationPermission() {
//     // Notification.requestPermission().then(permission => {
//     //     if (permission === 'granted') {
//             navigator.serviceWorker.register("<?php echo plugin_dir_url(__FILE__); ?>sw.js").then(registration => {
//                 getToken(messaging, {
//                     serviceWorkerRegistration: registration,
//                     vapidKey: 'BJBtbjoZ4bhgodbc6pK9WUhRqoxVPU5q89JMbb8yoOv32iSJoi6GynuRRqG-IMCALcanT0RfNYvwrD7pBMWVQIM'
//                 }).then((currentToken) => {
//                     if (currentToken) {
//                         console.log("Token is: " + currentToken);
//                         localStorage.setItem('fcm_token', currentToken);
//                         storeToken(currentToken, true); // new visitor
//                     } else {
//                         console.log('No registration token available. Request permission to generate one.');
//                     }
//                 }).catch((err) => {
//                     console.log('An error occurred while retrieving token. ', err);
//                 });
//             });
//         // }
//     // });
// }

// function storeToken(token, isNew) {
//     // Send the token to the server via AJAX
//     jQuery.ajax({
//         type: 'POST',
//         url: my_ajax_object.ajaxurl,
//         data: {
//             action: 'store_device_token',
//             token: token,
//             is_new: isNew,
//             _ajax_nonce: '<?php echo wp_create_nonce('firebase_messaging'); ?>'
//         },
//         success: function(response) {
//             console.log(response); // Output the response from the server
//         },
//         error: function(xhr, status, error) {
//             console.error('Error storing token: ' + error); // Log any errors
//         }
//     });
// }

// function checkExistingToken(token) {
//     jQuery.ajax({
//         type: 'POST',
//         url: my_ajax_object.ajaxurl,
//         data: {
//             action: 'check_device_token',
//             token: token,
//             _ajax_nonce: '<?php echo wp_create_nonce('firebase_messaging'); ?>'
//         },
//         success: function(response) {
//             if (!response.success) {
//                 showPermissionDialog(); // Show dialog if the token is not recognized
//             }
//         },
//         error: function(xhr, status, error) {
//             console.error('Error checking token: ' + error); // Log any errors
//         }
//     });
// }
