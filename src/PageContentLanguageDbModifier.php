<?php

namespace SIL;

use LinkCache;
use MediaWiki\MediaWikiServices;
use Title;
use Wikimedia\Rdbms\Database;

/**
 * Handling Title::getDbPageLanguageCode and Special:PageLanguage to avoid possible
 * contradictory results when $wgPageLanguageUseDB is enabled.
 *
 * If wgPageLanguageUseDB is enabled then the PageContentLanguage hook is not
 * going to be called in case Special:PageLanguage assigned a pagelanguage which
 * could create a possible deviation between SIL annotation and the stored DB
 * `page_lang`.
 *
 * @license GPL-2.0-or-later
 * @since 1.3
 *
 * @author mwjames
 */
class PageContentLanguageDbModifier {

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var Database
	 */
	private $connection;

	/**
	 * @var LinkCache
	 */
	private $linkCache;

	/**
	 * @var bool
	 */
	private $isDbPageLanguage = false;

	/**
	 * @var string|false
	 */
	private $dbPageLanguage = false;

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 * @param Database|null $connection
	 * @param LinkCache|null $linkCache
	 */
	public function __construct( Title $title, ?Database $connection = null, ?LinkCache $linkCache = null ) {
		$this->title = $title;
		$this->connection = $connection;
		$this->linkCache = $linkCache;
	}

	/**
	 * @since 1.3
	 *
	 * @param bool $isDbPageLanguage
	 */
	public function markAsPageLanguageByDB( $isDbPageLanguage ) {
		$this->isDbPageLanguage = $isDbPageLanguage;
	}

	/**
	 * @since 1.3
	 *
	 * @param string $expectedLanguageCode
	 */
	public function updatePageLanguage( $expectedLanguageCode ) {
		if ( !$this->isDbPageLanguage ) {
			return null;
		}

		$expectedLanguageCode = strtolower( $expectedLanguageCode );

		// If the pagelanguage added via Special:PageLanguage is different from
		// what SIL is expecting then push for a DB update
		if ( $this->getDbPageLanguageCode() && $this->dbPageLanguage !== $expectedLanguageCode ) {
			$this->doUpdate( $expectedLanguageCode, $this->dbPageLanguage );
		}
	}

	/**
	 * @see Title::getDbPageLanguageCode
	 */
	private function getDbPageLanguageCode() {
		if ( $this->linkCache === null ) {
			$this->linkCache = MediaWikiServices::getInstance()->getLinkCache();
		}

		// check, if the page language could be saved in the database, and if so and
		// the value is not requested already, lookup the page language using LinkCache
		if ( $this->isDbPageLanguage && $this->dbPageLanguage === false ) {
			$this->linkCache->addLinkObj( $this->title );
			$this->dbPageLanguage = $this->linkCache->getGoodLinkFieldObj( $this->title, 'lang' );
		}

		return $this->dbPageLanguage;
	}

	/**
	 * @see Special:PageLanguage::onSubmit
	 */
	private function doUpdate( $expectedLanguageCode, $dbPageLanguage ) {
		$connection = $this->connection;
		$title = $this->title;

		if ( $connection === null ) {
			$connection = wfGetDB( DB_PRIMARY );
		}

		$method = __METHOD__;
		$connection->onTransactionCommitOrIdle( static function () use ( $connection, $expectedLanguageCode, $dbPageLanguage, $title, $method ) {
			$pageId = $title->getArticleID();

			$connection->update(
				'page',
				[
					'page_lang' => $expectedLanguageCode
				],
				[
					'page_id'   => $pageId,
					'page_lang' => $dbPageLanguage
				],
				$method
			);
		} );
	}

}
