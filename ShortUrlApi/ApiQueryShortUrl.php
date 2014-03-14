<?php
/**
 * @ingroup Extensions
 * @{
 * ApiQueryShortUrl Class
 *
 * @file
 * @{
 * @copyright © 2014 Daniel Norton d/b/a WeirdoSoft - www.weirdosoft.com
 *
 * @section License
 * **GPL v3**\n
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * \n\n
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * \n\n
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @}
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This file is an extension to MediaWiki software and is not designed for standalone use.\n" ;
	die( 1 ) ;
}


/**
 * Query API for ShortUrl page properties
 */
class ApiQueryShortUrl extends ApiQueryBase {

	/** Our API version */
	const VERSION = MW_EXT_SHORTURLAPI_VERSION ;

	/** module ID ( short 2- or 3-letter code ), without the trailing 'q' */
	const MID = MW_EXT_SHORTURLAPI_API_MID ;
	
	/** Limit the number of pages we’ll query in a single invocation.
	 * We impose this limit because we're doing an outer join, which
	 * can become expensive for large sets.
	 */
	const MAX_PAGES = 100 ;

	/** For parameters and semantics, see ApiQueryBase::__construct(). */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, self::MID . 'q' ) ;
	}

	/** For parameters and semantics, see ApiQueryBase::execute(). */
	public function execute() {
		$params = $this->extractRequestParams() ;
		$this->_executeQuery( $params ) ;
	}

	/** For parameters and semantics, see ApiQueryBase::getAllowedParams(). */
	public function getAllowedParams() {
		return array(
			'prop' => array(
				ApiBase::PARAM_DFLT => 'path',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => array(
					'code',
					'path',
				) ),
			'continue' => null,
		) ;
	}

	/** For parameters and semantics, see ApiQueryBase::getParamDescription(). */
	public function getParamDescription() {
		return array( array_merge( parent::getParamDescription(), array(
			'code' => 'Include the short URL code with the properties of a page',
			'path' => 'Include the URL path with the properties of a page',
		) ) ) ;
	}

	/** For parameters and semantics, see ApiQueryBase::getDescription(). */
	public function getDescription() {
		return 'This API extension adds a short URL (shorturl) property to the query action.' ;
	}

	/** For parameters and semantics, see ApiQueryBase::getExamples(). */
	public function getExamples() {
		return array(

			'api.php?action=query&prop=shorturl&suqprop=code&titles=Test%20page%201|Test%20page%202' =>
				'Query page information and include short URL codes for pages "Test 1" and "Test 2".',

			'api.php?action=query&prop=shorturl&titles=Test%20page%201|Test%20page%202' =>
				'Query page information and include short URL paths for pages "Test 1" and "Test 2".',

		) ;
	}

	/**
	 * Perform our part of the query.
	 *
	 * @param[in] array  $params array of parameters ( e.g. from ApiBase::extractRequestParams )
	 * @returns   array
	 */
	private function _executeQuery( $params ) {
		// Only operate on existing pages
		$pageids = array_keys( $this->getPageSet()->getGoodTitles() ) ;

		// Filter out already-processed pages
		if ( array_key_exists( 'continue', $params ) && ( $params['continue'] !== null ) ) {
			$cont = explode( '|', $params['continue'] ) ;
			$this->dieContinueUsageIf( count( $cont ) != 2 ) ;
			$contPage = (int)$cont[0] ;
			$pageids = array_filter( $pageids, function ( $v ) use ( $contPage ) {
				return $v >= $contPage ;
			} ) ;
		}
		if ( !count( $pageids ) ) {
			// Nothing to do
			return ;
		}

		// Apply MAX_PAGES, leaving any over the limit for a continue.
		sort( $pageids ) ;
		$continuePages = null ;
		if ( count( $pageids ) > self::MAX_PAGES ) {
			$continuePages = $pageids[self::MAX_PAGES] . '|0' ;
			$pageids = array_slice( $pageids, 0, self::MAX_PAGES ) ;
		}

		// determine which sub-properties have been requested
		$prop = array_flip( $params['prop'] ) ;
		$fieldCode = isset( $prop['code'] ) ;
		$fieldPath = isset( $prop['path'] ) ;

		// fetch the path template now if we'll need it while iterating
		if ( $fieldPath ) {
			$pathTemplate = ApiShortUrl::getPathTemplate() ;
		}
		
		$result = $this->getResult() ;

		// fetch and iterate over the results from the DB
		foreach ( $this->_queryDB( $pageids ) as $row ) {

			// convert the ShortUrl ID to its base-36 code
			$code = ApiShortUrl::codeFromId( $row->su_id ) ;

			// code requested?
			if ( $fieldCode ) {
				$result->addValue( array( 'query', 'pages', $row->page_id, ),
					'shorturl', array( 'code' => $code, )
				) ;
			}

			// path requested?
			if ( $fieldPath ) {
				$result->addValue( array( 'query', 'pages', $row->page_id, ),
					'shorturl', array( 'path' => preg_replace( '/^(.*)$/', $pathTemplate, $code ) )
				) ;
			}

		}
	}

	/**
	 * Perform the DB query
	 */
	private function _queryDB( $pageids ) {
		$this->resetQueryParams() ;
		$this->addTables( array( 'page', 'shorturls' ) ) ;
		$this->addFields( array( 'page_id', 'su_id', ) ) ;
		$this->addWhere ( array( 'page_id' => $pageids ) ) ;
		$this->addJoinConds( array( 'shorturls' => array(
			'LEFT OUTER JOIN',
			array(
				'page_title = su_title',    // TODO: change to join page_id with (indexed) su_page_id
			),
		) ) ) ;
		return $this->select( __METHOD__ ) ;
	}
	
}

/** @}*/
