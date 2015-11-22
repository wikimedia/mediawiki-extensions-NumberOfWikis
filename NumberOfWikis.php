<?php
/**
 * Number of wikis -- a magic word to show the number of wikis on ShoutWiki
 *
 * @file
 * @ingroup Extensions
 * @date 22 November 2015
 * @author Jack Phoenix <jack@shoutwiki.com>
 * @license https://en.wikipedia.org/wiki/Public_domain Public domain
 */

// Extension credits that will show up on Special:Version
$wgExtensionCredits['variable'][] = array(
	'name' => 'Number of wikis',
	'version' => '0.4',
	'author' => 'Jack Phoenix',
	'description' => 'Adds <nowiki>{{NUMBEROFWIKIS}}</nowiki> magic word to show the number of wikis on ShoutWiki',
);

$wgAutoloadClasses['NumberOfWikis'] = __DIR__ . '/NumberOfWikis.class.php';

// Translations for {{NUMBEROFWIKIS}}
$wgExtensionMessagesFiles['NumberOfWikisMagic'] = __DIR__ . '/NumberOfWikis.i18n.magic.php';

$wgHooks['ParserGetVariableValueSwitch'][] = 'NumberOfWikis::assignValue';
$wgHooks['MagicWordwgVariableIDs'][] = 'NumberOfWikis::variableIds';