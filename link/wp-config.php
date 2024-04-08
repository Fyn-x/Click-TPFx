<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
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
define( 'DB_NAME', 'clicktpfx_link' );

/** Database username */
define( 'DB_USER', 'clicktpfx_link' );

/** Database password */
define( 'DB_PASSWORD', '.7j?ydzZ9ITs' );

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
define( 'AUTH_KEY',         'x@REitP:Qg`c/5K>^Zs dqQYfMK=z4VLhXeoVRGm{ei&A)=;}Qlo!L*Z^jv;Ozfp' );
define( 'SECURE_AUTH_KEY',  'P}!r`=9Y3o8*E Hf6aX(bO&dsS4)VT]E1Zg}4+gwNUa,jC~+(ZFd &19TG&-U(Oq' );
define( 'LOGGED_IN_KEY',    ',N0G9wg$|Ea:7vM@g7L65R@-+-*(~%<M~`krCI9aJ~h$_UhoA=H+D6M|0t!N)A)x' );
define( 'NONCE_KEY',        '-J7SMy*#q=snV:V!%Euk?~u%&X6W:}I[=n2miWbhNcchT}^Jv=BUMYKDjkJm4?|v' );
define( 'AUTH_SALT',        ':}7@U0<7lb{>7,J?u#[kY=6SFf!cgd<N_|dXM,}J,S@eSp){qlD+2sW0QdOZK!>p' );
define( 'SECURE_AUTH_SALT', 't>k(.WPU(wXNk6$lyfX?|RUh0^~`lVCV%GLf<10[MPzKB>)cs%l^4dx;V{#nrdyA' );
define( 'LOGGED_IN_SALT',   'YR|dP#43SG~1_6;tkMFqmmU4ctsjQ<(k~qmugX`BMmIF?BU>*kqp[arT >GCo[I~' );
define( 'NONCE_SALT',       'c$x#dlH/3:#ySrvYi)O$l^A]&FkG/`D50o#eGum<PSuJX}J.}7wjj^@}awo&vVWs' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'link_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

	

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'WP_MEMORY_LIMIT', '256M' );

}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
