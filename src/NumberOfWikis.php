<?php
/**
 * Number of wikis -- a magic word to show the number of wikis on ShoutWiki
 *
 * @file
 * @ingroup Extensions
 * @date 9 July 2026
 * @author Jack Phoenix <jack@shoutwiki.com>
 * @license https://en.wikipedia.org/wiki/Public_domain Public domain
 */

use MediaWiki\MediaWikiServices;

class NumberOfWikis {

	/**
	 * Fetch the # of wikis, either from cache, or if uncached, from the DB.
	 * Cache TTL is a whole day because this does not need to be 100% accurate 100% of the time.
	 *
	 * @param MediaWiki\Parser\Parser $parser
	 * @param array &$variableCache Allegedly UNUSED in MW 1.35+
	 * @param string $magicWordId Magic word ID to check for because we only care about one particular ID
	 * @param string &$ret The value we're returning (the # of wikis)
	 */
	public static function assignValue( $parser, &$variableCache, $magicWordId, &$ret ) {
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
				$ret = $variableCache[$magicWordId] = $data;
			} else {
				// Not cached → have to fetch it from the database
				$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
				$res = $dbr->select(
					'wiki_list',
					'COUNT(*) AS count',
					// ignore deleted wikis as per Jedimca0
					[ 'wl_deleted' => 0 ],
					__METHOD__
				);

				wfDebugLog( 'NumberOfWikis', 'Got the amount of wikis from DB' );

				foreach ( $res as $row ) {
					// Store the count in cache...
					// (86400 = seconds in a day)
					$cache->set( $key, $row->count, 86400 );
					// ...and return the value to the user
					$ret = $variableCache[$magicWordId] = $row->count;
				}
			}
		}
	}

	/**
	 * Register the magic word ID.
	 *
	 * @param array &$variableIds
	 */
	public static function variableIds( &$variableIds ) {
		$variableIds[] = 'NUMBEROFWIKIS';
	}

}
