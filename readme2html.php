#!/usr/bin/php
<?php

/**
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

define( 'WP_DEBUG', true );
define( 'WP_USE_THEMES', false );
define( 'WP_PATH', '/var/www/surniaulula/wordpress/' );
define( 'LIB_PATH', '/home/jsmoriss/svn/github/surniaulula/surniaulula.github.io/trunk/' );

require_once( WP_PATH.'wp-load.php' );
require_once( LIB_PATH.'lib/ext/markdown.php' );
require_once( LIB_PATH.'lib/ext/parse-readme.php' );

$sections = array(
	'description' => 1,
	'installation' => 1,
	'faq' => 1,
	'notes' => 1,
	'screenshots' => 0,
	'changelog' => 0,
	'notice' => 0,
);

$th = '<th align="right" valign="top" nowrap>';

if ( empty( $argv[1] ) ) {
	echo 'syntax: '.$argv[0].' {readme.txt}' . "\n";
	exit( 1 );
} else {
	$readme_txt = $argv[1];
}

if ( $fh = @fopen( $readme_txt, 'rb' ) ) {
	$content = fread( $fh, filesize( $readme_txt ) );
	fclose( $fh );
} else {
	error_log( 'error opening '.$readme_txt.' for reading' );
	exit( 1 );
}

if ( empty( $content ) ) {
	error_log( 'no content read from '.$readme_txt );
	exit( 1 );
}

$parser = new SuextParseReadme();
$info = $parser->parse_readme_contents( $content );

if ( empty( $info ) ) {
	error_log( 'no info parsed from content' );
	exit( 1 );
}

if ( strpos( $info['title'], ' - ' ) ) {
	$title = preg_replace( '/^(.*) - (.*)$/', '<h1>$1</h1><h3>$2</h3>', $info['title'] );
} else {
	$title = '<h1>'.$info['title'].'</h1>';
}

echo $title . "\n\n";
echo '<table>' . "\n";

if ( ! empty( $info['plugin_name'] ) ) {
	echo '<tr>'.$th.'Plugin Name</th><td>'.$info['plugin_name'].'</td></tr>' . "\n";
}

if ( ! empty( $info['short_description'] ) ) {
	echo '<tr>'.$th.'Summary</th><td>'.$info['short_description'].'</td></tr>' . "\n";
}

if ( ! empty( $info['stable_tag'] ) ) {
	echo '<tr>'.$th.'Stable Version</th><td>'.$info['stable_tag'].'</td></tr>' . "\n";
}

if ( ! empty( $info['requires_at_least'] ) ) {
	echo '<tr>'.$th.'Requires At Least</th><td>WordPress '.$info['requires_at_least'].'</td></tr>' . "\n";
}

if ( ! empty( $info['tested_up_to'] ) ) {
	echo '<tr>'.$th.'Tested Up To</th><td>WordPress '.$info['tested_up_to'].'</td></tr>' . "\n";
}

if ( ! empty( $info['contributors'] ) ) {
	echo '<tr>'.$th.'Contributors</th><td>'.( implode( ', ', $info['contributors'] ) ).'</td></tr>' . "\n";
}

if ( ! empty( $info['donate_link'] ) ) {
	echo '<tr>'.$th.'WebSite URL</th><td><a href="'.$info['donate_link'].'">'.$info['donate_link'].'</a></td></tr>' . "\n";
}

if ( ! empty( $info['license'] ) ) {
	echo '<tr>'.$th.'License</th><td>'.( empty( $info['license_uri'] ) ? 
		$info['license'] : '<a href="'.$info['license_uri'].'">'.$info['license'].'</a>' ).'</td></tr>' . "\n";
}

if ( ! empty( $info['tags'] ) ) {
	echo '<tr>'.$th.'Tags / Keywords</th><td>'.( implode( ', ', $info['tags'] ) ).'</td></tr>' . "\n";
}

echo '</table>' . "\n\n";

if ( ! empty( $sections['description'] ) ) {
	echo '<h2>Description</h2>';
	echo "\n\n";
	$info['sections']['description'] = preg_replace( 
		array( 
			'/`([^`]*)`/',
			'/(<p>)?(<img src="[^"]+"[^>]*) style="[^"]*float:[^"]+"([^>]*\/>)/',
			'/(<div style="[^"]*float:[^"]+"[^>]*>)/'
		),
		array( 
			'<code>$1</code>',
			'<p align="center">$2$3</p>$1',
			'<div style="text-align:center;width=100%;max-width:1200px;height:auto;margin:0 0 20px 0;">'
		),
		$info['sections']['description'] );
	echo $info['sections']['description'];
	echo "\n\n";
}

if ( ! empty( $sections['installation'] ) ) {
	echo '<h2>Installation</h2>';
	echo "\n\n";
	echo $info['sections']['installation'];
	echo "\n\n";
}

if ( ! empty( $sections['faq'] ) ) {
	echo '<h2>Frequently Asked Questions</h2>';
	echo "\n\n";
	echo $info['sections']['faq'];
	echo "\n\n";
}

if ( ! empty( $sections['notes'] ) ) {
	echo '<h2>Other Notes</h2>';
	echo "\n\n";
	echo $info['remaining_content'];
	echo "\n\n";
}

if ( ! empty( $sections['screenshots'] ) ) {
	echo '<h2>Screenshots</h2>' . "\n\n";
	if ( ! empty( $info['screenshots'] ) && ! empty( $info['plugin_slug'] ) ) {
		foreach ( $info['screenshots'] as $n => $ss ) {
			echo '<p align="center"><img align="center" src="https://surniaulula.github.io/'.
				$info['plugin_slug'].'/assets/screenshot-'.sprintf( '%02d', $n + 1 ).'.png"/><br/>' . "\n";
			echo $ss.'</p>';
			echo "\n\n";
		}
	}
}

if ( ! empty( $sections['changelog'] ) ) {
	echo '<h2>Changelog</h2>';
	echo "\n\n";
	echo $info['sections']['changelog'];
	echo "\n\n";
}

if ( ! empty( $sections['notice'] ) ) {
	echo '<h2>Upgrade Notice</h2>';
	echo "\n\n";
	if ( ! empty( $info['upgrade_notice'] ) ) {
		foreach ( $info['upgrade_notice'] as $v => $n ) {
			echo '<h4>'.$v.'</h4>' . "\n";
			echo '<p>'.$n.'</p>';
			echo "\n\n";
		}
	}
}

