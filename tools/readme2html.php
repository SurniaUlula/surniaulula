#!/usr/bin/php
<?php

/**
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

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

$parser =& SuextParseReadme::get_instance();

$plugin_readme = $parser->parse_readme_contents( $content );

if ( empty( $plugin_readme ) ) {

	error_log( 'no info parsed from content' );

	exit( 1 );
}

if ( strpos( $plugin_readme[ 'title' ], ' | ' ) ) {

	$title = preg_replace( '/^(.*) \| (.*)$/', '<h1>$1</h1><h3>$2</h3>', $plugin_readme[ 'title' ] );

} else {

	$title = '<h1>' . $plugin_readme[ 'title' ] . '</h1>';
}

echo $title . "\n\n";

echo '<table>' . "\n";

$trth = '<tr><th align="right" valign="top" nowrap>';
$thtd = '</th><td>';
$tdtr = '</td></tr>' . "\n";

if ( ! empty( $plugin_readme[ 'plugin_name' ] ) ) {

	echo $trth . 'Plugin Name' . $thtd . $plugin_readme[ 'plugin_name' ] . $tdtr;
}

if ( ! empty( $plugin_readme[ 'short_description' ] ) ) {

	echo $trth . 'Summary' . $thtd . $plugin_readme[ 'short_description' ] . $tdtr;
}

if ( ! empty( $plugin_readme[ 'stable_tag' ] ) ) {

	echo $trth . 'Stable Version' . $thtd . $plugin_readme[ 'stable_tag' ] . $tdtr;
}

if ( ! empty( $plugin_readme[ 'requires_php' ] ) ) {

	echo $trth . 'Requires PHP' . $thtd . $plugin_readme[ 'requires_php' ] . ' or newer' . $tdtr;
}

if ( ! empty( $plugin_readme[ 'requires_at_least' ] ) ) {

	echo $trth . 'Requires WordPress' . $thtd . $plugin_readme[ 'requires_at_least' ] . ' or newer' . $tdtr;
}

if ( ! empty( $plugin_readme[ 'tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WordPress' . $thtd . $plugin_readme[ 'tested_up_to' ] . $tdtr;
}

if ( ! empty( $plugin_readme[ 'wc_tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WooCommerce' . $thtd . $plugin_readme[ 'wc_tested_up_to' ] . $tdtr;
}

if ( ! empty( $plugin_readme[ 'contributors' ] ) ) {

	echo $trth . 'Contributors' . $thtd . ( implode( ', ', $plugin_readme[ 'contributors' ] ) ) . $tdtr;
}

if ( ! empty( $plugin_readme[ 'donate_link' ] ) ) {

	echo $trth . 'WebSite URL</th><td><a href="' . $plugin_readme[ 'donate_link' ] . '">' . $plugin_readme[ 'donate_link' ] . '</a>' . $tdtr;
}

if ( ! empty( $plugin_readme[ 'license' ] ) ) {

	echo $trth . 'License' . $thtd . ( empty( $plugin_readme[ 'license_uri' ] ) ? $plugin_readme[ 'license' ] :
		'<a href="' . $plugin_readme[ 'license_uri' ] . '">' . $plugin_readme[ 'license' ] . '</a>' ) . $tdtr;
}

if ( ! empty( $plugin_readme[ 'tags' ] ) ) {

	echo $trth . 'Tags / Keywords' . $thtd . ( implode( ', ', $plugin_readme[ 'tags' ] ) ) . $tdtr;
}

echo '</table>' . "\n\n";

if ( ! empty( $sections[ 'description' ] ) ) {

	echo '<h2>Description</h2>' . "\n\n";

	$plugin_readme[ 'sections' ][ 'description' ] = preg_replace( 
		array( '/`([^`]*)`/' ),
		array( '<code>$1</code>' ),
		$plugin_readme[ 'sections' ][ 'description' ]
	);

	echo $plugin_readme[ 'sections' ][ 'description' ] . "\n\n";
}

if ( ! empty( $sections[ 'installation' ] ) ) {

	echo '<h2>Installation</h2>' . "\n\n";
	echo $plugin_readme[ 'sections' ][ 'installation' ] . "\n\n";
}

if ( ! empty( $sections[ 'faq' ] ) ) {

	echo '<h2>Frequently Asked Questions</h2>' . "\n\n";
	echo $plugin_readme[ 'sections' ][ 'faq' ] . "\n\n";
}

if ( ! empty( $sections[ 'screenshots' ] ) ) {

	echo '<h2>Screenshots</h2>' . "\n\n";

	if ( ! empty( $plugin_readme[ 'screenshots' ] ) && ! empty( $plugin_readme[ 'plugin_slug' ] ) ) {

		foreach ( $plugin_readme[ 'screenshots' ] as $num => $screenshot ) {

			echo '<p align="center"><img align="center" src="https://surniaulula.github.io/' . 
				$plugin_readme[ 'plugin_slug' ] . '/assets/screenshot-' . sprintf( '%02d', $num + 1 ) . '.png"/><br/>' . "\n";

			echo $screenshot . '</p>' . "\n\n";
		}
	}
}

if ( ! empty( $sections[ 'changelog' ] ) ) {

	echo '<h2>Changelog</h2>' . "\n\n";
	echo $plugin_readme[ 'sections' ][ 'changelog' ] . "\n\n";
}

if ( ! empty( $sections[ 'notice' ] ) ) {

	echo '<h2>Upgrade Notice</h2>' . "\n\n";

	if ( ! empty( $plugin_readme[ 'upgrade_notice' ] ) ) {

		foreach ( $plugin_readme[ 'upgrade_notice' ] as $version => $notice ) {

			echo '<h4>' . $version . '</h4>' . "\n";
			echo '<p>' . $notice . '</p>' . "\n\n";
		}
	}
}
