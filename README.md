# Semantic Interlanguage Links

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticInterlanguageLinks.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticInterlanguageLinks)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-interlanguage-links/version.png)](https://packagist.org/packages/mediawiki/semantic-interlanguage-links)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-interlanguage-links/d/total.png)](https://packagist.org/packages/mediawiki/semantic-interlanguage-links)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-interlanguage-links/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-interlanguage-links)

Semantic Interlanguage Links (a.k.a. SIL) is a [Semantic Mediawiki][smw] extension to
create and manage interlanguage links.

This extension helps to create interlanguage links and provides queryable annotations that:

- Can connect pages with similar content for different languages to be accessible via the [sitelink navigation][sitelink]
- Provides a `interlanguagelink` parser function to create cachable assignments
- Provides a `interlanguagelist` parser function to format a list of available language links
- To set the page content language of an article
- Integration with `Special:Search` to search `By Language`

This [video](https://vimeo.com/115871518) demonstrates the functionality of the Semantic Interlanguage Links extension.

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.23 or later
- [Semantic MediaWiki][smw] 2.1 or later

## Installation

The recommended way to install Semantic Interlanguage Links is by using [Composer][composer]
with an entry in MediaWiki's `composer.json`.

```json
{
	"require": {
		"mediawiki/semantic-interlanguage-links": "~1.2"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-interlanguage-links:~1.1`
2. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

The parser function `{{interlanguagelink: ... }}` provides an interface
to declare multilingual content using semantic annotations.

`{{interlanguagelink: language code | interlanguage reference }}`, the first
argument specifies the language code (e.g `es`, `ja`) of the content while
the second argument contains an arbitrary reference (`interlanguage reference`)
that describes content of similar nature (content that should be connected to
each other) for different languages.

![sil](https://cloud.githubusercontent.com/assets/1245473/9477943/450195e0-4b75-11e5-9cd4-61e2672eb8fa.png)

The parser function `{{interlanguagelist: ... }}` can generate a customizable
language target link list.

Further details and usage examples can be found [here](https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/blob/master/docs/01-parser-function.md)
together with some [#ask queries](https://github.com/SemanticMediaWiki/SemanticInterlanguageLinks/blob/master/docs/02-ask-queries.md).

### Other features

- The page content language is preset with the language annotated by `interlanguagelink` together
  with an auto-updated sitelink navigation for pages that point to the same `interlanguage reference`.
- A set of predefined properties ( `Page content language`, `Interlanguage reference`,
  `Interwiki language`, `Interwiki reference`, and `Has interlanguage links`) are provided and can
  be used to create customized `#ask` queries (e.g `Has interlanguage links.Page content language`).
- SIL provides a `by Language` `Special:Search` filtering option to match interlanguage property annotations
  for pre-selected pages. If the `by Language` profile (or the advanced profile) is used together with
  a specific language filter then any pre-selected article (provided by the `SearchEngine`) that does not match
  the language will be excluded from the result list. It may be necessary to broaden the limit before a match
  can be found because SIL does only compare languages against pre-selected results (it does not search by itself).
- If a category page contains a `Page content language` annotation, SIL will filter and display only pages
  that match that content language. In cases where no language has been assigned (or filtering has been disabled),
  the category page will display all pages without changes or filtering.

### Configuration

- `$GLOBALS['egSILCacheType'] = CACHE_ANYTHING;` is being set to be the default value to improve query lookups
   during each page view with cache invalidation being carried out during any delete, change or move action.
- `$GLOBALS['wgHideInterlanguageLinks']` is enabled (set to `true`), no sitelinks or annotations are created
  (in order to correspond to the MW default behaviour for interwiki links)
- If `$GLOBALS['wgPageLanguageUseDB']` was enabled and `Special:PageLanguage` assigned a different language from
  the annotated SIL value then the `Page content language` will be restored to provide consistency with the
  expected language

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticLanguageLinks/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticLanguageLinks/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

## Tests

This extension provides unit and integration tests that are run by a [continues integration platform][travis]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License, version 2 or later][gpl-licence].

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[contributors]: https://github.com/SemanticMediaWiki/SemanticLanguageLinks/graphs/contributors
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticLanguageLinks
[gpl-licence]: https://www.gnu.org/copyleft/gpl.html
[composer]: https://getcomposer.org/
[sitelink]: https://www.semantic-mediawiki.org/wiki/File:Extension-sil-sitelink.png
[iwlm]: https://www.mediawiki.org/wiki/Manual:$wgInterwikiMagic
[iwlp]: https://www.mediawiki.org/wiki/Manual:$wgExtraInterlanguageLinkPrefixes
