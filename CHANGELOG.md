### 1.0.0

Released on 2015-01-31

* Initial release
* Added the `onoi/cache:~1.0` dependency
* Added `LanguageTargetLinksCache` to improve lookup performance
* Added an `INTERLANGUAGELINK` parser to easily create interlanguage related semantic annotations 
* Added an `INTERLANGUAGELIST` parser to support template output for available interlanguage links
* Added a search profile to `Special:Search` to enable language filtering on the basis of avilable semantic annotations
* Added `ByLanguageCategoryViewer` to enable auto-filtering for pages within a catgory that declare a content language using the `INTERLANGUAGELINK` parser
* Added `InterwikiLanguageLinkFetcher` to recognize and support interwiki language links (e.g. `[[en:Foo]]`)
