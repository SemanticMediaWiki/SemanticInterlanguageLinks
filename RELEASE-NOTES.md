This file contains the RELEASE-NOTES of the **Semantic Interlanguage Links** (a.k.a. SIL) extension.

### 2.1.0

Released on April 2, 2020.

* Minimum requirement for
  * PHP changed to version 7.1 and later
* #75 Removed search functionality now accessible via the [`SEARCH FORM SCHEMA`](https://www.semantic-mediawiki.org/wiki/Help:Schema/Type/SEARCH_FORM_SCHEMA) provided by Semantic MediaWiki
* Localization updates from https://translatewiki.net


### 2.0.0

Released on September 4, 2019.

* Minimum requirement for
  * PHP changed to version 7.0 and later
  * MediaWiki changed to version 1.31.0 and later
  * Semantic MediaWiki changed to version 3.0 and later
* #71 Adds support for extension registration via "extension.json"  
  → Now you have to use `wfLoadExtension( 'SemanticInterlanguageLinks' );` in the "LocalSettings.php" file to invoke the extension
* Localization updates from https://translatewiki.net


### 1.5.0

Released on October 8, 2018.

* Minimum requirement for
  * PHP changed to version 5.6 and later
  * Semantic MediaWiki changed to version 2.5 and later
* Fixed issue with `DIProperty::findPropertyLabel`
* #59 Removed the useage of deprecated `wfBCP47()` global function
* Localization updates from https://translatewiki.net

### 1.4.0

Released on July 29, 2017.

* Minimum requirement for
  * PHP changed to version 5.5 and later
  * Semantic MediaWiki changed to version 2.4 and later
  * MediaWiki changed to version 1.27 and later
* Fixed "Call to a member function getPrefixedText on null"
* #50 Added the `annotatedlanguage` parser function
* #44 Always to return lowercase language code
* Localization updates from https://translatewiki.net

### 1.3.0

Released on July 9, 2016.

* Added `PageContentLanguageDbModifier` to handle possible deviations caused by an
  enabled `wgPageLanguageUseDB` setting
* #38 Added access to `PageContentLanguageOnTheFlyModifier` in `InterlanguageLinkParserFunction` allowing
  the language code to be temporarily available while the content is still being process
  (important when `DataValueFactory` seeks access to a subject)
* #37 Added check whether `LanguageLinkAnnotator` can actually add annotations
* #35 Added `InMemoryLruCache` to `PageContentLanguageOnTheFlyModifier`
* #33 Fixed language code in `Special:Search` to conform with IETF (ISO 639, BCP 47) norm
* Localization updates from https://translatewiki.net

### 1.2.0

Released on December 19, 2015.

* Added redirect handling for a `interlanguage link` reference
* #8 Removed multiple calls limitation, allow different interlanguage reference targets for
  same language on a content page but disallow for a page to define different languages
* Switched `PageLanguageCacheStrategy` from 'blob' to 'single'
* Localization updates from https://translatewiki.net

### 1.1.0

Released on June 2, 2015.

* Only match results in `Special:Search` for the selected language that contains an annotation
* Fixed behaviour in `LanguageFilterCategoryPage` for when languages are switched
* #32 Fixed exception in `Special:Search` for no results
* Localization updates from https://translatewiki.net

### 1.0.0

Released on Feburary 14, 2015.

* Initial release
* Added the `onoi/cache:~1.0` dependency
* Added `LanguageTargetLinksCache` to improve lookup performance
* Added an `INTERLANGUAGELINK` parser to easily create interlanguage related semantic annotations
* Added an `INTERLANGUAGELIST` parser to support template output for available interlanguage links
* Added a search profile to `Special:Search` to enable language filtering on the basis of avilable semantic annotations
* Added `LanguageFilterCategoryViewer` to enable auto-filtering for pages within a catgory that declare a content language using the `INTERLANGUAGELINK` parser
* Added `InterwikiLanguageLinkFetcher` to recognize and support interwiki language links (e.g. `[[en:Foo]]`)
