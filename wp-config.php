<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'blogposter' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '}<[I{BIV4p}7n>+Y%:O6+]Ndc2t*P)ik3GVMedMrY.C<T=F&M+:g^nv_D@q+/TaN' );
define( 'SECURE_AUTH_KEY',  ';EqoubP%{<f|l,=x2@f!~F-F`sX8L*%-dC#qHc3lYfVzW&y98OFw~@*?(|-$IVv%' );
define( 'LOGGED_IN_KEY',    'OQh +O/NHGz 1szvT)GPU X:7#b0,p#S Z~3.uGGgJlAnq6j(2fvE=HY|a%4nQJ6' );
define( 'NONCE_KEY',        '1!<Zk&S~{Nah=/AL[Mtt*DkMiL.Iu9.} xK**#L_hS>{7:xa Ky_I wxD3=VT(L5' );
define( 'AUTH_SALT',        '~=5inN`Qthb ?Ib&fH&!K{0f`Ms+k`%s_7Bm_pOy*20DEvj=SyHJop^~F5f`-hSP' );
define( 'SECURE_AUTH_SALT', '($yGo3?xFPdlD@UMJsHv(7?N_A~(EF?([cS3/>zOmb$p%naXT)*~ri`1^mje}GG*' );
define( 'LOGGED_IN_SALT',   '6b1K5]fAvyx#4GnXOo/,N@fV*1Ad1/&] JL,5/jrz=X;1OE/Hody0G_LRTPf#dX-' );
define( 'NONCE_SALT',       '~iH#h}zI),MvO}s49Jl{Vn^{FO!xDOl;srx>gOOV*:f]/NSo12X,gY~O`.4Y|}LR' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );


// define('WP_HOME', 'http://localhost/');
// define('WP_SITEURL', 'http://localhost/wordpress/');

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
