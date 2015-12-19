This file contains the RELEASE-NOTES of the Semantic Interlanguage Links (a.k.a. SIL) extension.

### 1.2.0 (2015-12-19)

* Added redirect handling for a `interlanguage link` reference
* #8 Removed multiple calls limitation, allow different interlanguage reference targets for
  same language on a content page but disallow for a page to define different languages
* Switched `PageLanguageCacheStrategy` from 'blob' to 'single'
* Localization updates from https://translatewiki.net

### 1.1.0 (2015-06-02)

* Only match results in `Special:Search` for the selected language that contains an annotation
* Fixed behaviour in `ByLanguageCategoryPage` for when languages are switched
* #32 Fixed exception in `Special:Search` for no results
* Localization updates from https://translatewiki.net

### 1.0.0 (2015-02-14)

* Initial release
* Added the `onoi/cache:~1.0` dependency
* Added `LanguageTargetLinksCache` to improve lookup performance
* Added an `INTERLANGUAGELINK` parser to easily create interlanguage related semantic annotations
* Added an `INTERLANGUAGELIST` parser to support template output for available interlanguage links
* Added a search profile to `Special:Search` to enable language filtering on the basis of avilable semantic annotations
* Added `ByLanguageCategoryViewer` to enable auto-filtering for pages within a catgory that declare a content language using the `INTERLANGUAGELINK` parser
* Added `InterwikiLanguageLinkFetcher` to recognize and support interwiki language links (e.g. `[[en:Foo]]`)
