<?php
/**
 * Number of wikis -- a magic word to show the number of wikis on ShoutWiki
 *
 * @file
 * @ingroup Extensions
 * @date 13 April 2020
 * @author Jack Phoenix <jack@shoutwiki.com>
 * @license https://en.wikipedia.org/wiki/Public_domain Public domain
 */

use MediaWiki\MediaWikiServices;

class NumberOfWikis {

	public static function assignValue( $parser, &$cache, $magicWordId, &$ret ) {
		if ( $magicWordId == 'NUMBEROFWIKIS' ) {
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
			$key = $cache->makeKey( 'shoutwiki', 'numberofwikis' );
			$data = $cache->get( $key );

			if ( $data != '' ) {
				// We have it in cache? Oh goody, let's just use the cached value!
				wfDebugLog(
					'NumberOfWikis',
					'Got the amount of wikis from memcached'
				);
				// return value
				$ret = $cache[$magicWordId] = $data;
			} else {
				// Not cached → have to fetch it from the database
				$dbr = wfGetDB( DB_REPLICA );
				$res = $dbr->select(
					'wiki_list',
					'COUNT(*) AS count',
					[ 'wl_deleted' => 0 ], // ignore deleted wikis as per Jedimca0
					__METHOD__
				);

				wfDebugLog( 'NumberOfWikis', 'Got the amount of wikis from DB' );

				foreach ( $res as $row ) {
					// Store the count in cache...
					// (86400 = seconds in a day)
					$cache->set( $key, $row->count, 86400 );
					// ...and return the value to the user
					$ret = $cache[$magicWordId] = $row->count;
				}
			}
		}

		return true;
	}

	public static function variableIds( &$variableIds ) {
		$variableIds[] = 'NUMBEROFWIKIS';
		return true;
	}

}