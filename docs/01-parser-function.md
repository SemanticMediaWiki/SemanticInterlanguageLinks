
## interlanguagelink

![sil](https://cloud.githubusercontent.com/assets/1245473/7594045/0d88d938-f919-11e4-9c79-8e8d166c507a.png)

If `Foo` and `Bar` are to represent similar content in different languages the it is assumed
they will share a commmon reference (mostly the page name of the main content e.e.g `Lorem ipsum`) making those
assignment link to each other through the sitelink navigation.

```text
Page: Foo

Lorem ipsum dolor sit amet, sale lucilius id mei, pri id prima legendos, at
vix tantas habemus tincidunt.

{{interlanguagelink:la|Lorem ipsum}}
```
```text
Page:Bar

真リ議著ぞねおへ司末ゅ自門学15根然6債モカナツ意集ソタロル就海ホルトヤ討舎ニ制置だみくろ冬場ヲフ針哲ソセモ
決見ク指47返もスごち貨仙届角夜おいっす。

{{interlanguagelink:ja|Lorem ipsum}}
```

### Interwiki annotation

In cases where an interwiki language link (e.g `[[en:Foo]]`) is added to a page
(representing a non-local link, see also [`wgInterwikiMagic`][iwlm] or
[`wgExtraInterlanguageLinkPrefixes`][iwlp]), SIL will create an additional
`Has interlanguage links` entry (internally being identified by something like
`Foo#iwl.en`).

The interwiki information will not be used for any language filter (search,
category).

## interlanguagelist

The parser function `{{interlanguagelist: interlanguage reference | template }}` can
generate a customizable language target link list for the provided `interlanguage reference`
and being formatted using a template with parameters:

- `target-link` being the target link
- `lang-code` the language code
- `lang-name` representing the localized language name for the language code
- `#` contains the list position

Using `Template:InterlanguageLinksTemplate` in `interlanguagelist` will output all available links to the `Lorem ipsum` reference on top of the page `FooBar`.

```text
Template:InterlanguageLinksTemplate

<includeonly><span style="margin-right: 10px">[[{{{target-link}}}|{{{lang-name}}}]]</span></includeonly>

```
```text
Page:FooBar

{{interlanguagelist:Lorem ipsum|InterlanguageLinksTemplate}}

```

## annotatedlanguage

The parser function is called either as `{{annotatedlanguage: }}` where it just returns the language code
for the current page (if one is annotated using SIL) or with `{{annotatedlanguage: template }}` to return content
as formatted using a template with parameters defined as:

- `target-link` being the target link
- `lang-code` the language code
- `lang-name` representing the localized language name for the language code

[iwlm]: https://www.mediawiki.org/wiki/Manual:$wgInterwikiMagic
[iwlp]: https://www.mediawiki.org/wiki/Manual:$wgExtraInterlanguageLinkPrefixes
