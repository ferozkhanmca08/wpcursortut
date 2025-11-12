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
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpcursortut' );

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
define( 'AUTH_KEY',         '%&yG}TiNwxa #`{3vlIGnhC$oUm2z7=8cb!~Ch5[eCD;M]m x&p/On8~u qf-&*8' );
define( 'SECURE_AUTH_KEY',  '>}1eXK}3~>KAecRlOHWIrExo<OadE:aQwhU-@4zNbG^Ljc$`x5o Ihg1rw^M<+QF' );
define( 'LOGGED_IN_KEY',    'gxb |Ty|G waX>!5jP0Kbq)dxw+4}XLRZUDv`!6dAn?aq h9MYrdE}4B/L_177uR' );
define( 'NONCE_KEY',        '(ll,.]15!Gv/]=z@H+=x^HC-12iwClpa[}J`(w>- ~P42g4{i|/]e<`JV}.vH]H4' );
define( 'AUTH_SALT',        '&{Q>f:1}E%f.Buc-&#N%c-r:#%m_b?#aH6&1ge1b)@GpMX|ZO=pQGSI,g1DoUNt`' );
define( 'SECURE_AUTH_SALT', 'Mvxa[|nl~EWq^aRG1a@(zC%h<8`x D}:YD])[7$p)!B4S9NyQ*|/)Y{uxSeL2Cc}' );
define( 'LOGGED_IN_SALT',   '#h~;us9i5!gS|5hrx3q=]8K`o/U%*TP1KgO0JI4s_ucSB lN|!<fzlJ_(d|B^xB ' );
define( 'NONCE_SALT',       '`Jh5ud0#wp~JJ>KW_1eB7!8J>M)y,=bgr|7P,hXMF<C=U[SY2dUlBTPah-`>y[,T' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
