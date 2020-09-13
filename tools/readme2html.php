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
define( 'LIB_PATH', '/home/jsmoriss/svn/github/surniaulula/surniaulula.github.io/trunk/' );

require_once WP_PATH . 'wp-load.php';

require_once LIB_PATH . 'tools/markdown.php';

require_once LIB_PATH . 'tools/parse-readme.php';

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
	$readme_txt = $argv[1];
}

if ( $fh = @fopen( $readme_txt, 'rb' ) ) {

	$content = fread( $fh, filesize( $readme_txt ) );

	fclose( $fh );

} else {
	error_log( 'error opening ' . $readme_txt . ' for reading' );

	exit( 1 );
}

if ( empty( $content ) ) {

	error_log( 'no content read from ' . $readme_txt );

	exit( 1 );
}

$parser = new SuextParseReadme();
$info   = $parser->parse_readme_contents( $content );

if ( empty( $info ) ) {

	error_log( 'no info parsed from content' );

	exit( 1 );
}

if ( strpos( $info[ 'title' ], ' | ' ) ) {

	$title = preg_replace( '/^(.*) \| (.*)$/', '<h1>$1</h1><h3>$2</h3>', $info[ 'title' ] );

} else {

	$title = '<h1>' . $info[ 'title' ] . '</h1>';
}

echo $title . "\n\n";

echo '<table>' . "\n";

$trth = '<tr><th align="right" valign="top" nowrap>';
$thtd = '</th><td>';
$tdtr = '</td></tr>' . "\n";

if ( ! empty( $info[ 'plugin_name' ] ) ) {

	echo $trth . 'Plugin Name' . $thtd . $info[ 'plugin_name' ] . $tdtr;
}

if ( ! empty( $info[ 'short_description' ] ) ) {

	echo $trth . 'Summary' . $thtd . $info[ 'short_description' ] . $tdtr;
}

if ( ! empty( $info[ 'stable_tag' ] ) ) {

	echo $trth . 'Stable Version' . $thtd . $info[ 'stable_tag' ] . $tdtr;
}

if ( ! empty( $info[ 'requires_php' ] ) ) {

	echo $trth . 'Requires PHP' . $thtd . $info[ 'requires_php' ] . ' or newer' . $tdtr;
}

if ( ! empty( $info[ 'requires_at_least' ] ) ) {

	echo $trth . 'Requires WordPress' . $thtd . $info[ 'requires_at_least' ] . ' or newer' . $tdtr;
}

if ( ! empty( $info[ 'tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WordPress' . $thtd . $info[ 'tested_up_to' ] . $tdtr;
}

if ( ! empty( $info[ 'wc_tested_up_to' ] ) ) {

	echo $trth . 'Tested Up To WooCommerce' . $thtd . $info[ 'wc_tested_up_to' ] . $tdtr;
}

if ( ! empty( $info[ 'contributors' ] ) ) {

	echo $trth . 'Contributors' . $thtd . ( implode( ', ', $info[ 'contributors' ] ) ) . $tdtr;
}

if ( ! empty( $info[ 'donate_link' ] ) ) {

	echo $trth . 'WebSite URL</th><td><a href="' . $info[ 'donate_link' ] . '">' . $info[ 'donate_link' ] . '</a>' . $tdtr;
}

if ( ! empty( $info[ 'license' ] ) ) {

	echo $trth . 'License' . $thtd . ( empty( $info[ 'license_uri' ] ) ? $info[ 'license' ] :
		'<a href="' . $info[ 'license_uri' ] . '">' . $info[ 'license' ] . '</a>' ) . $tdtr;
}

if ( ! empty( $info[ 'tags' ] ) ) {

	echo $trth . 'Tags / Keywords' . $thtd . ( implode( ', ', $info[ 'tags' ] ) ) . $tdtr;
}

echo '</table>' . "\n\n";

if ( ! empty( $sections[ 'description' ] ) ) {

	echo '<h2>Description</h2>' . "\n\n";

	$info[ 'sections' ][ 'description' ] = preg_replace( 
		array( '/`([^`]*)`/' ),
		array( '<code>$1</code>' ),
		$info[ 'sections' ][ 'description' ]
	);

	echo $info[ 'sections' ][ 'description' ] . "\n\n";
}

if ( ! empty( $sections[ 'installation' ] ) ) {

	echo '<h2>Installation</h2>' . "\n\n";
	echo $info[ 'sections' ][ 'installation' ] . "\n\n";
}

if ( ! empty( $sections[ 'faq' ] ) ) {

	echo '<h2>Frequently Asked Questions</h2>' . "\n\n";
	echo $info[ 'sections' ][ 'faq' ] . "\n\n";
}

if ( ! empty( $sections[ 'screenshots' ] ) ) {

	echo '<h2>Screenshots</h2>' . "\n\n";

	if ( ! empty( $info[ 'screenshots' ] ) && ! empty( $info[ 'plugin_slug' ] ) ) {

		foreach ( $info[ 'screenshots' ] as $num => $screenshot ) {

			echo '<p align="center"><img align="center" src="https://surniaulula.github.io/' . 
				$info[ 'plugin_slug' ] . '/assets/screenshot-' . sprintf( '%02d', $num + 1 ) . '.png"/><br/>' . "\n";

			echo $screenshot . '</p>' . "\n\n";
		}
	}
}

if ( ! empty( $sections[ 'changelog' ] ) ) {

	echo '<h2>Changelog</h2>' . "\n\n";
	echo $info[ 'sections' ][ 'changelog' ] . "\n\n";
}

if ( ! empty( $sections[ 'notice' ] ) ) {

	echo '<h2>Upgrade Notice</h2>' . "\n\n";

	if ( ! empty( $info[ 'upgrade_notice' ] ) ) {

		foreach ( $info[ 'upgrade_notice' ] as $version => $notice ) {

			echo '<h4>' . $version . '</h4>' . "\n";
			echo '<p>' . $notice . '</p>' . "\n\n";
		}
	}
}
