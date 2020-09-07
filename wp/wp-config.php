<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'blog_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '</3#Cp1E=|#?E<0,&Jf32g(/67pl_M>eS0LE w#kRaK)uzK=;7()T:VuT?@Fe|ZU' );
define( 'SECURE_AUTH_KEY',  'i/T]e(13>ei)%OZ#FFADqhDR@g(u,X}9;YsECq|QOu_}^AqXe8hyDYJ^GmDc)@EN' );
define( 'LOGGED_IN_KEY',    'Fd7C)Y/4x2Uq7pBhyDdTfyi15tCi2AmXyUCccmlhVH6&qo8cOyJ[6J[kL$Vt{d3C' );
define( 'NONCE_KEY',        'wHD2Cp]}^I}v^BF9}%,16RqJnZ=EN^C.w%cpRrca)HJ>_dOxT9-[+u688.ut8fZ-' );
define( 'AUTH_SALT',        'QO@qU]Gynu(^x4*N1wSU76IcW<)bI`!-q.w_?Y=C8{Jc-Q4);7rc5MY@Mby8no7H' );
define( 'SECURE_AUTH_SALT', '{IHw+}]VVP=Llu5dGKW]qDM4_k2;:zi`xD[b+[(0k6rF;W@ *b]}rH4wsSV7[azY' );
define( 'LOGGED_IN_SALT',   'b7yGtu8FH$SptL.E;%dpLV|i=2|QALb9>x]0Sp}06(.>^yK~mVe+@-l}I6.0}m#q' );
define( 'NONCE_SALT',       '.8+Dp.bGJa`/;E~B~s#nk=gjY{ZAuhOh:6cbKS$3OC,LLdGd!=AOTi=exI.sC 3.' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
