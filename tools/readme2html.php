#!/usr/bin/php
<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

$_SERVER['HTTP_HOST'] = 'surniaulula.com';	// Required to prevent Polylang error message.

define( 'WP_DEBUG', true );
define( 'WP_USE_THEMES', false );
define( 'WP_PATH', '/var/www/wpadm/wordpress/' );
define( 'LIB_PATH', WP_PATH . 'wp-content/plugins/wpsso/lib/ext/' );

require_once WP_PATH . 'wp-load.php';

$sections = array(
	'description'  => 1,
	'installation' => 1,
	'faq'          => 1,
	'screenshots'  => 0,
	'changelog'    => 0,
	'notice'       => 0,
);

if ( empty( $argv[1] ) ) {

	echo 'syntax: ' . $argv[0] . ' {readme.txt}' . "\n";

	exit( 1 );

} else {

	$readme_path = $argv[ 1 ];
}

if ( $fh = @fopen( $readme_path, 'rb' ) ) {

	$content = fread( $fh, filesize( $readme_path ) );

	fclose( $fh );

} else {

	error_log( 'error opening ' . $readme_path . ' for reading' );

	exit( 1 );
}

if ( empty( $content ) ) {

	error_log( 'no content read from ' . $readme_path );

	exit( 1 );
}

if ( ! class_exists( 'SuextParseReadme' ) ) {

	require_once LIB_PATH . 'parse-readme.php';
}

$readme_parser =& SuextParseReadme::get_instance();

$readme_info = $readme_parser->parse_content( $content );

if ( empty( $readme_info ) ) {

	error_log( 'no info parsed from content' );

	exit( 1 );
}

if ( strpos( $readme_info[ 'title' ], ' | ' ) ) {

	$title = preg_replace( '/^(.*) \| (.*)$/', '<h1>$1</h1><h3>$2</h3>', $readme_info[ 'title' ] );

} else {

	$title = '<h1>' . $readme_info[ 'title' ] . '</h1>';
}

echo $title . "\n\n";

echo '<table>' . "\n";

$trth = '<tr><th align="right" valign="top" nowrap>';
$thtd = '</th><td>';
$tdtr = '</td></tr>' . "\n";

if ( ! empty( $readme_info[ 'plugin_name' ] ) ) {

	echo $trth . 'Plugin Name' . $thtd . $readme_info[ 'plugin_name' ] . $tdtr;
}

if ( ! empty( $readme_info[ 'short_description' ] ) ) {

	echo $trth . 'Summary' . $thtd . $readme_info[ 'short_description' ] . $tdtr;
}

if ( ! empty( $readme_info[ 'stable_tag' ] ) ) {

	echo $trth . 'Stable Version' . $thtd . $readme_info[ 'stable_tag' ] . $tdtr;
}

if ( ! empty( $readme_info[ 'requires_php' ] ) ) {

	echo $trth . 'Requires PHP' . $thtd . $readme_info[ 'requires_php' ] . ' or newer' . $tdtr;
}

if ( ! empty( $readme_info[ 'requires_at_least' ] ) ) {

	echo $trth . 'Requires WordPress' . $thtd . $readme_info[ 'requires_at_least' ] . ' or newer' . $tdtr;
}

if ( ! empty( $readme_info[ 'tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WordPress' . $thtd . $readme_info[ 'tested_up_to' ] . $tdtr;
}

if ( ! empty( $readme_info[ 'wc_tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WooCommerce' . $thtd . $readme_info[ 'wc_tested_up_to' ] . $tdtr;
}

if ( ! empty( $readme_info[ 'contributors' ] ) ) {

	echo $trth . 'Contributors' . $thtd . ( implode( $glue = ', ', $readme_info[ 'contributors' ] ) ) . $tdtr;
}

if ( ! empty( $readme_info[ 'donate_link' ] ) ) {

	echo $trth . 'WebSite URL</th><td><a href="' . $readme_info[ 'donate_link' ] . '">' . $readme_info[ 'donate_link' ] . '</a>' . $tdtr;
}

if ( ! empty( $readme_info[ 'license' ] ) ) {

	echo $trth . 'License' . $thtd . ( empty( $readme_info[ 'license_uri' ] ) ? $readme_info[ 'license' ] :
		'<a href="' . $readme_info[ 'license_uri' ] . '">' . $readme_info[ 'license' ] . '</a>' ) . $tdtr;
}

if ( ! empty( $readme_info[ 'tags' ] ) ) {

	echo $trth . 'Tags / Keywords' . $thtd . ( implode( $glue = ', ', $readme_info[ 'tags' ] ) ) . $tdtr;
}

echo '</table>' . "\n\n";

if ( ! empty( $sections[ 'description' ] ) ) {

	echo '<h2>Description</h2>' . "\n\n";

	$readme_info[ 'sections' ][ 'description' ] = preg_replace(
		array( '/`([^`]*)`/' ),
		array( '<code>$1</code>' ),
		$readme_info[ 'sections' ][ 'description' ]
	);

	echo $readme_info[ 'sections' ][ 'description' ] . "\n";
}

if ( ! empty( $sections[ 'installation' ] ) ) {

	if ( trim( $readme_info[ 'sections' ][ 'installation' ] ) ) {

		echo '<h2>Installation</h2>' . "\n\n";
		echo $readme_info[ 'sections' ][ 'installation' ] . "\n";
	}
}

if ( ! empty( $sections[ 'faq' ] ) ) {

	if ( trim( $readme_info[ 'sections' ][ 'faq' ] ) ) {

		echo '<h2>Frequently Asked Questions</h2>' . "\n\n";
		echo $readme_info[ 'sections' ][ 'faq' ] . "\n";
	}
}

if ( ! empty( $sections[ 'screenshots' ] ) ) {

	if ( ! empty( $readme_info[ 'screenshots' ] ) && ! empty( $readme_info[ 'plugin_slug' ] ) ) {

		echo '<h2>Screenshots</h2>' . "\n\n";

		foreach ( $readme_info[ 'screenshots' ] as $num => $screenshot ) {

			echo '<p align="center">';

			echo '<img align="center" src="https://surniaulula.github.io/' .
				$readme_info[ 'plugin_slug' ] . '/assets/screenshot-' . sprintf( '%02d', $num + 1 ) . '.png"/><br/>' . "\n";

			echo $screenshot . '</p>' . "\n\n";
		}
	}
}

if ( ! empty( $sections[ 'changelog' ] ) ) {

	echo '<h2>Changelog</h2>' . "\n\n";
	echo $readme_info[ 'sections' ][ 'changelog' ] . "\n";
}

if ( ! empty( $sections[ 'notice' ] ) ) {

	if ( ! empty( $readme_info[ 'upgrade_notice' ] ) ) {

		echo '<h2>Upgrade Notice</h2>' . "\n\n";

		foreach ( $readme_info[ 'upgrade_notice' ] as $version => $notice ) {

			echo '<h4>' . $version . '</h4>' . "\n\n";
			echo '<p>' . $notice . '</p>' . "\n\n";
		}
	}
}
