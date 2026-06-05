<?php

namespace SIL\Hooks;

use MediaWiki\Page\Hook\ArticlePurgeHook;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;
use SIL\InterlanguageLinksLookup;

/**
 * Resets the interlanguage lookup cache whenever a page that may carry SIL
 * annotations is purged, edited, deleted or moved.
 *
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */
class CacheInvalidationHooks implements ArticlePurgeHook, RevisionFromEditCompleteHook {

	public function __construct(
		private readonly InterlanguageLinksLookup $interlanguageLinksLookup
	) {
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticlePurge
	 */
	public function onArticlePurge( $wikiPage ): bool {
		$this->interlanguageLinksLookup->resetLookupCacheBy(
			$wikiPage->getTitle()
		);

		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RevisionFromEditComplete
	 */
	public function onRevisionFromEditComplete( $wikiPage, $rev, $originalRevId, $user, &$tags ): bool {
		$this->interlanguageLinksLookup->resetLookupCacheBy(
			$wikiPage->getTitle()
		);

		return true;
	}

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::BeforeDeleteSubjectComplete
	 */
	public function onSMW__SQLStore__BeforeDeleteSubjectComplete( $store, $title ): bool {
		$this->interlanguageLinksLookup->setStore( $store );
		$this->interlanguageLinksLookup->resetLookupCacheBy( $title );

		return true;
	}

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::BeforeChangeTitleComplete
	 */
	public function onSMW__SQLStore__BeforeChangeTitleComplete( $store, $oldTitle, $newTitle, $pageid, $redirid ): bool {
		$this->interlanguageLinksLookup->setStore( $store );

		$this->interlanguageLinksLookup->resetLookupCacheBy( $oldTitle );
		$this->interlanguageLinksLookup->resetLookupCacheBy( $newTitle );

		return true;
	}

}
