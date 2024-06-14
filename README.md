# WordPress Firebase Push Notifications Integration

This WordPress project integrates Firebase Cloud Messaging (FCM) for custom push notifications using three custom plugins: `fcm-notifications`, `firebase-messaging`, and `create-custom-column`.

## Features

- **FCM Notifications Plugin:** Sends push notifications to subscribed clients using Firebase.
- **Firebase Messaging Plugin:** Stores device tokens in WordPress database securely using VAPID key.
- **Create Custom Column Plugin:** Adds and manages a custom column `is_notified` in the posts database for tracking notification status.

## Requirements

- WordPress installation (local or hosted)
- XAMPP or similar local server environment
- Clone of this repository placed in `htdocs` or equivalent server root directory

## Installation

1. **Clone the Repository:**
   - Clone this repository into your local XAMPP environment:

     ```bash
     git clone https://github.com/your-repo-url.git
     ```

2. **Plugin Activation:**
   - Upload and activate the following plugins via WordPress admin panel:
     - `fcm-notifications`
     - `firebase-messaging`
     - `create-custom-column`

3. **Configuration:**
   - Navigate to WordPress admin dashboard:
     - Insert VAPID key in `firebase-messaging` plugin settings.
     - Set Firebase credential value in `firebase-messaging` plugin settings.
     - Insert private key value in appropriate plugin settings.

4. **Usage**

   - **Sending Push Notifications:**
     - Use the `fcm-notifications` plugin to send notifications to all subscribed clients.
   
   - **Managing Device Tokens:**
     - Device tokens are automatically stored in the WordPress database using the `firebase-messaging` plugin.
   
   - **Tracking Notification Status:**
     - The `create-custom-column` plugin adds a `is_notified` column to the posts database.
     - Use this column to track whether a post has been notified and send notifications again if needed.

## Troubleshooting

- **Plugin Activation Issues:**
  - Ensure all dependencies and plugins are activated and configured correctly.
  
- **Firebase Integration Errors:**
  - Verify Firebase SDK configuration and ensure correct key values are entered.

- **Database Management:**
  - Use WordPress database tools or phpMyAdmin for manual checks and updates if needed.

## Contributors

- Ahmed Oumer (https://github.com/ahmedahmedoumer)

## License

This project is free to customize and use on everywhere.
