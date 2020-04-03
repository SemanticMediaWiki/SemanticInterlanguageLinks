
* [Parser functions](01-parser-function.md) contains details and usage examples
* [Example `#ask` queries](02-ask-queries.md)
* [Technical notes](09-notes.md)

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

### Other features

- The page content language is preset with the language annotated by `interlanguagelink` together
  with an auto-updated sitelink navigation for pages that point to the same `interlanguage reference`.
- A set of predefined properties ( `Page content language`, `Interlanguage reference`,
  `Interwiki language`, `Interwiki reference`, and `Has interlanguage links`) are provided and can
  be used to create customized `#ask` queries (e.g `Has interlanguage links.Page content language`).
- If a category page contains a `Page content language` annotation, SIL will filter and display only pages
  that match that content language. In cases where no language has been assigned (or filtering has been disabled),
  the category page will display all pages without changes or filtering.

### Configuration

- `$silgCacheType = CACHE_ANYTHING;` is being set to be the default value to improve query lookups
   during each page view with cache invalidation being carried out during any delete, change or move action.
- `$wgHideInterlanguageLinks` is enabled (set to `true`), no sitelinks or annotations are created
  (in order to correspond to the MW default behaviour for interwiki links)
- If `$wgPageLanguageUseDB` was enabled and `Special:PageLanguage` assigned a different language from
  the annotated SIL value then the `Page content language` will be restored to provide consistency with the
  expected language
