# Semantic Interlanguage Links

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticInterlanguageLinks.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticInterlanguageLinks)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticInterlanguageLinks/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-interlanguage-links/version.png)](https://packagist.org/packages/mediawiki/semantic-interlanguage-links)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-interlanguage-links/d/total.png)](https://packagist.org/packages/mediawiki/semantic-interlanguage-links)

Semantic Interlanguage Links (a.k.a. SIL) is a [Semantic Mediawiki][smw] extension to create and manage
interlanguage links.

This extension helps to create interlanguage links and provides queryable annotations that:

- Can connect pages with similar content for different languages to be accessible via the [sitelink navigation][sitelink]
- Provides a `interlanguagelink` parser function to create cachable assignments
- Provides a `interlanguagelist` parser function to format a list of available language links
- Provides a `annotatedlanguage` parser function to return the language code of the current page
- To set the page content language of an article
- Integration with `Special:Search` to search `By Language`

This [video](https://vimeo.com/115871518) demonstrates the functionality of the Semantic Interlanguage Links extension.

## Requirements

- PHP 7.1 or later
- MediaWiki 1.31 or later
- [Semantic MediaWiki][smw] 3.0 or later

## Installation

The recommended way to install Semantic Interlanguage Links is using [Composer](http://getcomposer.org) with
[MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. If you do not have a "composer.local.json" file yet,
create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-interlanguage-links": "~2.1"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-interlanguage-links": "~2.1"

Remember to add a comma to the end of the preceding line in this section.

### Step 2

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command.

### Step 3

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticInterlanguageLinks' );


## Usage

The parser function `{{interlanguagelink: ... }}` provides an interface to declare multilingual content
using semantic annotations.

`{{interlanguagelink: language code | interlanguage reference }}`, the first argument specifies the language
code (e.g `es`, `ja`) of the content while the second argument contains an arbitrary reference (`interlanguage reference`)
that describes content of similar nature (content that should be connected to each other) for different languages.

![sil](https://cloud.githubusercontent.com/assets/1245473/9477943/450195e0-4b75-11e5-9cd4-61e2672eb8fa.png)

Further details and usage examples can be found [here](docs/README.md).

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticLanguageLinks/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticLanguageLinks/pulls)
* Ask a question on [the mailing list](https://www.semantic-mediawiki.org/wiki/Mailing_list)

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
[composer-local]: https://www.mediawiki.org/wiki/Composer/For_extensions#Specify_the_extensions_to_be_installed
[sitelink]: https://www.semantic-mediawiki.org/wiki/File:Extension-sil-sitelink.png
[iwlm]: https://www.mediawiki.org/wiki/Manual:$wgInterwikiMagic
[iwlp]: https://www.mediawiki.org/wiki/Manual:$wgExtraInterlanguageLinkPrefixes
