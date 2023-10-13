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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'elementor_db' );

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
define( 'AUTH_KEY',         'peQ!xpyxY*7}A@za FlP4Cg`n8cwh{,]]!;$?F8LUjtF37GC,<TAVogp:45#<:P[' );
define( 'SECURE_AUTH_KEY',  'h&it+Hu1,[4pRje[Gtc?[ka;#Nn_@H,jjN0@tp^S`.N+(DDIV,9~mOz<LO<1;Co-' );
define( 'LOGGED_IN_KEY',    'JG3OV/dfmm^9.]T4Nf(L ,n$^*)c#OV~b]rH3uJc2A_0 L%dm]-N%SwV~LUN1M:{' );
define( 'NONCE_KEY',        'n{1sO 8T:xI`w]gsBs/+bxeol&:c#M[`>,9vlZxkO$b?z]|;DP3xEE q+>TM_T`Z' );
define( 'AUTH_SALT',        'g[/a(GJ|~GrrqFB5@- a&4Q&6Tq~bm86ZN1GcI}M]7{x ]^> FB xT97_mle[gea' );
define( 'SECURE_AUTH_SALT', ')[I#l-_6t4hXeB,BZE9L6/D!#[ut_{(vH?n<T-He(@!Kn]Cdq[4Y@5Y.iBG.jo~O' );
define( 'LOGGED_IN_SALT',   '2TPw{N$&Kp_s59,&r,HaNqFW*n]BzW8+KmmP2nR<(7roFu+363gQSJY=x( pYBK^' );
define( 'NONCE_SALT',       '6<PHAc#?axOFF,n<sV4_v_^o2WuG/!*z.l;3vLm%=aU/u,$pnLmMp`fNf}Rm0(s0' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */


define('WP_ALLOW_REPAIR', true);
/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

