## Example ask queries

List all subjects (articles) that have a reference to the `Shogi` topic.

```
{{#ask:
 [[Has interlanguage link.Interlanguage reference::Shogi]]
 |?Some property
}}
```
List all available languages that are connected to the `Shogi` topic.

```
{{#ask:
 [[Interlanguage reference::Shogi]]
 |?Page content language
}}
```

The subject `将棋` is assigned to which language?

```
{{#ask:
 [[-Has interlanguage link::将棋]]
 |?Page content language
}}
```
