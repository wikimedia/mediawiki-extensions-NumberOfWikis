<?php
/**
 * Number of wikis -- a magic word to show the number of wikis on ShoutWiki
 *
 * @file
 * @ingroup Extensions
 * @version 0.2
 * @date March 3, 2011
 * @author Jack Phoenix <jack@shoutwiki.com>
 * @license http://en.wikipedia.org/wiki/Public_domain Public domain
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['variable'][] = array(
	'name' => 'Number of wikis',
	'version' => '0.2',
	'author' => 'Jack Phoenix',
	'description' => 'Adds <nowiki>{{NUMBEROFWIKIS}}</nowiki> magic word to show the number of wikis on ShoutWiki',
);

// Translations for {{NUMBEROFWIKIS}}
$dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles['NumberOfWikis'] = $dir . 'NumberOfWikis.i18n.magic.php';

$wgHooks['LanguageGetMagic'][] = 'wfNumberOfWikisMagicWord';
function wfNumberOfWikisMagicWord( &$magicWords, $langID ) {
	// tell MediaWiki that {{NUMBEROFWIKIS}} and all case variants found in
	// wiki text should be mapped to magic ID 'NUMBEROFWIKIS'
	// (0 means case-insensitive)
	$magicWords['NUMBEROFWIKIS'] = array( 0, 'NUMBEROFWIKIS' );
	return true;
}

$wgHooks['ParserGetVariableValueSwitch'][] = 'wfNumberOfWikisAssignValue';
function wfNumberOfWikisAssignValue( &$parser, &$cache, &$magicWordId, &$ret ) {
	global $wgMemc;

	if ( $magicWordId == 'NUMBEROFWIKIS' ) {
		$key = wfMemcKey( 'shoutwiki', 'numberofwikis' );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// We have it in cache? Oh goody, let's just use the cached value!
			wfDebugLog(
				'NumberOfWikis',
				'Got the amount of wikis from memcached'
			);
			// return value
			$ret = $data;
		} else {
			// Not cached → have to fetch it from the database
			global $wgSharedDB;
			$dbr = wfGetDB( DB_SLAVE, array(), $wgSharedDB );
			$res = $dbr->select(
				'wiki_list',
				'COUNT(*) AS count',
				array( 'wl_deleted' => 0 ), // ignore deleted wikis as per Jedimca0
				__METHOD__
			);
			wfDebugLog( 'NumberOfWikis', 'Got the amount of wikis from DB' );
			foreach( $res as $row ) {
				// Store the count in cache...
				// (86400 = seconds in a day)
				$wgMemc->set( $key, $row->count, 86400 );
				// ...and return the value to the user
				$ret = $row->count;
			}
		}
	}
	return true;
}

$wgHooks['MagicWordwgVariableIDs'][] = 'wfNumberOfWikisVariableIds';
function wfNumberOfWikisVariableIds( &$variableIds ) {
	$variableIds[] = 'NUMBEROFWIKIS';
	return true;
}