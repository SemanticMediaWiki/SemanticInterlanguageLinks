<?php

namespace SIL;

use SMW\Query\Language\Conjunction;
use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ValueDescription;

use SMW\Store;
use SMW\DIWikiPage;
use SMW\DIProperty;

use SMWPrintRequest as PrintRequest;
use SMWPropertyValue as PropertyValue;
use SMWQuery as Query;
use SMWDIBlob as DIBlob;

use Title;

/**
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
	 * @since 1.0
	 *
	 * @param Title $title
	 */
	public function invalidateLookupCache( Title $title ) {

		$this->languageTargetLinksCache
			->deleteLanguageTargetLinksFromCache( $this->findFullListOfReferenceTargetLinks( $title ) );

		$this->languageTargetLinksCache
			->deletePageLanguageForTargetFromCache( $title );
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title|null
	 * @param string $languageCode
	 */
	public function updatePageLanguageToLookupCache( Title $title = null, $languageCode ) {

		if ( $title !== null && $this->languageTargetLinksCache->getPageLanguageFromCache( $title ) === $languageCode ) {
			return;
		}

		$this->languageTargetLinksCache->updatePageLanguageToCache(
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

		if ( is_array( $languageTargetLinks ) && $languageTargetLinks !== array() ) {
			return $languageTargetLinks;
		}

		$languageTargetLinks = array();

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

		$this->updatePageLanguageToLookupCache(
			$title,
			$lookupLanguageCode
		);

		return $lookupLanguageCode;
	}

	/**
	 * @since 1.0
	 *
	 * @param Title $title
	 *
	 * @return DIWikiPage[]|[]
	 */
	public function findFullListOfReferenceTargetLinks( Title $title ) {

		$linkReferences = array();

		try{
			$property = new DIProperty( PropertyRegistry::SIL_CONTAINER );
		} catch ( \Exception $e ) {
			return $linkReferences;
		}

		$propertyValues = $this->store->getPropertyValues(
			DIWikiPage::newFromTitle( $title ),
			$property
		);

		if ( !is_array( $propertyValues ) || $propertyValues === array() ) {
			return $linkReferences;
		}

		foreach ( $propertyValues as $containerSubject ) {

			$values = $this->store->getPropertyValues(
				$containerSubject,
				new DIProperty( PropertyRegistry::SIL_REF )
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

		$propertyValue = new PropertyValue( '__pro' );
		$propertyValue->setDataItem( $languageDataValue->getProperty() );

		$description->addPrintRequest(
			new PrintRequest( PrintRequest::PRINT_PROP, null, $propertyValue )
		);

		$query = new Query(
			$description,
			false,
			false
		);

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

		if ( !is_array( $propertyValues ) || $propertyValues === array() ) {
			return self::NO_LANG;
		}

		$containerSubject = end( $propertyValues );

		$propertyValues = $this->store->getPropertyValues(
			$containerSubject,
			new DIProperty( PropertyRegistry::SIL_LANG )
		);

		$languageCodeValue = end( $propertyValues );

		if ( $languageCodeValue instanceOf DIBlob ) {
			return $languageCodeValue->getString();
		}

		return self::NO_LANG;
	}

}
