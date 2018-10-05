<?php

namespace SIL;

use SMW\Query\Language\Conjunction;
use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ValueDescription;
use SMW\DataValueFactory;
use SMW\Store;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWPrintRequest as PrintRequest;
use SMWPropertyValue as PropertyValue;
use SMWQuery as Query;
use SMWDIBlob as DIBlob;
use Title;

/**
 * This class is the most critical component of SIL as it combines the store
 * interface with the cache interface.
 *
 * Any request either for a target link or language code lookup are channelled
 * through this class in order to make a decision whether to use an existing
 * cache entry or to make a "fresh" query request to the storage back-end.
 *
 * No other component of SIL should communicate to the store directly and let
 * the lookup class to handle those requests.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinksLookup {

	const NO_LANG = '';

	/**
	 * @var LanguageTargetLinksCache
	 */
	private $languageTargetLinksCache;

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @since 1.0
	 *
	 * @param LanguageTargetLinksCache $languageTargetLinksCache
	 */
	public function __construct( LanguageTargetLinksCache $languageTargetLinksCache ) {
		$this->languageTargetLinksCache = $languageTargetLinksCache;
	}

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 */
	public function setStore( Store $store ) {
		$this->store = $store;
	}


	/**
	 * @since 1.2
	 *
	 * @param Title $title
	 *
	 * @return Title
	 */
	public function getRedirectTargetFor( Title $title ) {
		return $this->store->getRedirectTarget( DIWikiPage::newFromTitle( $title ) )->getTitle();
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 */
	public function resetLookupCacheBy( Title $title ) {

		$this->languageTargetLinksCache->deleteLanguageTargetLinksFromCache(
			$this->findFullListOfReferenceTargetLinks( $title )
		);

		$this->languageTargetLinksCache->deletePageLanguageForTargetFromCache(
			$title
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param Title|null $title
	 * @param string $languageCode
	 */
	public function pushPageLanguageToLookupCache( Title $title = null, $languageCode ) {

		if ( $title !== null && $this->languageTargetLinksCache->getPageLanguageFromCache( $title ) === $languageCode ) {
			return;
		}

		$this->languageTargetLinksCache->pushPageLanguageToCache(
			$title,
			$languageCode
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLink $interlanguageLink
	 * @param Title|null $target
	 *
	 * @return array
	 */
	public function queryLanguageTargetLinks( InterlanguageLink $interlanguageLink, Title $target = null ) {

		$languageTargetLinks = $this->languageTargetLinksCache->getLanguageTargetLinksFromCache(
			$interlanguageLink
		);

		if ( is_array( $languageTargetLinks ) && $languageTargetLinks !== [] ) {
			return $languageTargetLinks;
		}

		$languageTargetLinks = [];

		if ( $target !== null && $interlanguageLink->getLanguageCode() !== '' ) {
			$languageTargetLinks[ $interlanguageLink->getLanguageCode() ] = $target;
		}

		$queryResult = $this->getQueryResultForInterlanguageLink( $interlanguageLink );

		$this->iterateQueryResultToFindLanguageTargetLinks(
			$queryResult,
			$languageTargetLinks
		);

		$this->languageTargetLinksCache->saveLanguageTargetLinksToCache(
			$interlanguageLink,
			$languageTargetLinks
		);

		return $languageTargetLinks;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 *
	 * @return string
	 */
	public function findPageLanguageForTarget( Title $title ) {

		// @note $title->getPageLanguage()->getLanguageCode() cannot be called
		// here as this would cause a recursive chain

		$lookupLanguageCode = $this->languageTargetLinksCache->getPageLanguageFromCache( $title );

		if ( $lookupLanguageCode !== null && $lookupLanguageCode !== false ) {
			return $lookupLanguageCode;
		}

		$lookupLanguageCode = $this->lookupLastPageLanguageForTarget( $title );

		$this->pushPageLanguageToLookupCache(
			$title,
			$lookupLanguageCode
		);

		return $lookupLanguageCode;
	}

	/**
	 * @since 1.1
	 *
	 * @param Title $title
	 *
	 * @return boolean
	 */
	public function hasSilAnnotationFor( Title $title ) {

		$propertyValues = $this->store->getPropertyValues(
			DIWikiPage::newFromTitle( $title ),
			new DIProperty( PropertyRegistry::SIL_CONTAINER )
		);

		return $propertyValues !== [];
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 *
	 * @return DIWikiPage[]|[]
	 */
	public function findFullListOfReferenceTargetLinks( Title $title ) {

		$linkReferences = [];

		try{
			$property = new DIProperty( PropertyRegistry::SIL_CONTAINER );
		} catch ( \Exception $e ) {
			return $linkReferences;
		}

		$propertyValues = $this->store->getPropertyValues(
			DIWikiPage::newFromTitle( $title ),
			$property
		);

		if ( !is_array( $propertyValues ) || $propertyValues === [] ) {
			return $linkReferences;
		}

		foreach ( $propertyValues as $containerSubject ) {

			$values = $this->store->getPropertyValues(
				$containerSubject,
				new DIProperty( PropertyRegistry::SIL_ILL_REF )
			);

			$linkReferences = array_merge( $linkReferences, $values );
		}

		return $linkReferences;
	}

	/**
	 * @return QueryResult
	 */
	private function getQueryResultForInterlanguageLink( InterlanguageLink $interlanguageLink ) {

		$description = new Conjunction();

		$languageDataValue = $interlanguageLink->newLanguageDataValue();

		$linkReferenceDataValue = $interlanguageLink->newLinkReferenceDataValue();

		$description->addDescription(
			new SomeProperty(
				$linkReferenceDataValue->getProperty(),
				new ValueDescription( $linkReferenceDataValue->getDataItem(), null, SMW_CMP_EQ )
			)
		);

		$propertyValue = DataValueFactory::getInstance()->newDataValueByType( '__pro' );
		$propertyValue->setDataItem( $languageDataValue->getProperty() );

		$description->addPrintRequest(
			new PrintRequest( PrintRequest::PRINT_PROP, null, $propertyValue )
		);

		$query = new Query(
			$description
		);

		if ( defined( 'SMWQuery::PROC_CONTEXT' ) ) {
			$query->setOption( Query::PROC_CONTEXT, 'SIL.InterlanguageLinksLookup' );
		}

		if ( defined( 'SMWQuery::NO_CACHE' ) ) {
			$query->setOption( Query::NO_CACHE, true );
		}

		//	$query->sort = true;
		//	$query->sortkey = array( $languageDataValue->getProperty()->getLabel() => 'asc' );

		// set query limit to certain threshold

		return $this->store->getQueryResult( $query );
	}

	private function iterateQueryResultToFindLanguageTargetLinks( $queryResult, array &$languageTargetLinks ) {

		while ( $resultArray = $queryResult->getNext() ) {
			foreach ( $resultArray as $row ) {

				$dataValue = $row->getNextDataValue();

				if ( $dataValue === false ) {
					continue;
				}

				$languageTargetLinks[ $dataValue->getWikiValue() ] = $row->getResultSubject()->getTitle();
			}
		}
	}

	private function lookupLastPageLanguageForTarget( Title $title ) {

		try{
			$property = new DIProperty( PropertyRegistry::SIL_CONTAINER );
		} catch ( \Exception $e ) {
			return self::NO_LANG;
		}

		$propertyValues = $this->store->getPropertyValues(
			DIWikiPage::newFromTitle( $title ),
			$property
		);

		if ( !is_array( $propertyValues ) || $propertyValues === [] ) {
			return self::NO_LANG;
		}

		$containerSubject = end( $propertyValues );

		$propertyValues = $this->store->getPropertyValues(
			$containerSubject,
			new DIProperty( PropertyRegistry::SIL_ILL_LANG )
		);

		$languageCodeValue = end( $propertyValues );

		if ( $languageCodeValue instanceOf DIBlob ) {
			return $languageCodeValue->getString();
		}

		return self::NO_LANG;
	}

}
