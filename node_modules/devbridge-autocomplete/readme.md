# Ajax Autocomplete for jQuery

Adds autocomplete / autosuggest dropdowns to text input fields.

The only runtime dependency is jQuery 3.0 or newer. TypeScript types are bundled.
The UMD bundle (`dist/jquery.autocomplete.js`) is ~13 KB minified;
an ESM build (`dist/jquery.autocomplete.esm.js`) is also published for modern bundlers.

## Install

```bash
npm install devbridge-autocomplete
```

Or load via a `<script>` tag — the UMD bundle registers `$.fn.autocomplete`
(and `$.fn.devbridgeAutocomplete`) on the global jQuery.

## Upgrading from 1.x

Version 2.0 drops support for jQuery 1.x / 2.x and IE-era browsers.
The plugin API and option names are unchanged — every existing call site
continues to work — but the peer dependency is now `jquery >=3.0` and the
build targets evergreen browsers (ES2020). If you cannot upgrade jQuery,
stay on the `1.5.x` release line.

## API

```js
$(selector).autocomplete(options);
```

`options` is an object literal. All recognized fields are listed in the
tables below.

### General settings (local and Ajax)

| Setting | Default | Description |
| :--- | :--- | :--- |
| `minChars` | `1` | Minimum characters typed before suggestions are fetched |
| `delimiter` | optional | `string` or `RegExp` that splits the input value; the last segment becomes the query. Useful for comma-separated value lists |
| `noCache` | `false` | If `true`, suggestion results are not cached |
| `preventBadQueries` | `true` | If `true`, suppresses future Ajax requests for any query that starts with a prefix that previously returned no results. E.g. once `Jam` returns nothing, `Jamai` will not fire |
| `triggerSelectOnValidInput` | `true` | If `true`, `onSelect` fires automatically when the input exactly matches a suggestion |
| `autoSelectFirst` | `false` | If `true`, the first item is selected when suggestions appear |
| `preserveInput` | `false` | If `true`, the input value does not change while navigating suggestions with arrow keys |
| `tabDisabled` | `false` | If `true`, pressing Tab keeps the cursor in the input field instead of selecting the highlighted suggestion |
| `showNoSuggestionNotice` | `false` | If `true`, displays a label when no suggestions match |
| `noSuggestionNotice` | `"No results"` | Text, HTML string, `Element`, or jQuery object used as the no-match label |
| `groupBy` | optional | Property on `suggestion.data` to group results by |
| `formatResult` | optional | `function (suggestion, currentValue)` — custom HTML for a single suggestion entry |
| `formatGroup` | optional | `function (suggestion, category)` — custom HTML for a group header |
| `beforeRender` | optional | `function (container, suggestions)` — called before the dropdown is shown; mutate the DOM here if needed |
| `onInvalidateSelection` | optional | `function ()` — fires when the input changes after a selection was made. `this` is the input element |
| `maxHeight` | `300` | Maximum dropdown height in pixels |
| `width` | `"auto"` | Dropdown width. Number (px), `"auto"` (matches the input), or `"flex"` (grows to widest suggestion) |
| `zIndex` | `9999` | `z-index` of the dropdown container |
| `orientation` | `"bottom"` | `"top"`, `"bottom"`, or `"auto"`. `"auto"` picks the side with more room |
| `appendTo` | `document.body` | jQuery object, selector, or HTMLElement. The target needs `position: absolute` or `position: relative` |
| `forceFixPosition` | `false` | Auto-positioning only runs when the dropdown is appended to `<body>`. Set this to `true` to force positioning in other parents |
| `containerClass` | `"autocomplete-suggestions"` | CSS class on the dropdown container. Override if you need a different style hook |

### Event callbacks (local and Ajax)

| Callback | Signature | Description |
| :--- | :--- | :--- |
| `onSearchStart` | `function (params)` | Fires before each lookup. Return `false` to cancel. `this` is the input element |
| `onSearchComplete` | `function (query, suggestions)` | Fires after each lookup resolves. `this` is the input element |
| `onSearchError` | `function (query, jqXHR, textStatus, errorThrown)` | Fires if the Ajax request fails. `this` is the input element |
| `onSelect` | `function (suggestion)` | Fires when the user picks a suggestion. `this` is the input element |
| `onHint` | `function (hint)` | Fires when the inline hint changes. `this` is the input element |
| `onHide` | `function (container)` | Fires before the dropdown is hidden |
| `transformResult` | `function (response, originalQuery)` | Normalizes the raw server response into `{ suggestions: [...] }`. Default: `JSON.parse` on a string response |

### Local-only settings

| Setting | Default | Description |
| :--- | :--- | :--- |
| `lookup` | optional | Either an array of suggestions or a callback `function (query, done)`. Arrays may be strings or `{ value, data }` objects |
| `lookupFilter` | optional | `function (suggestion, query, queryLowerCase)` — filter predicate. Default is a case-insensitive substring match |
| `lookupLimit` | unlimited | Maximum number of local matches to display |

A suggestion is an object of the form `{ value: string, data: any }`. `data`
is passed through untouched to `formatResult`, `onSelect`, and the grouping
key resolver.

### Ajax-only settings

| Setting | Default | Description |
| :--- | :--- | :--- |
| `serviceUrl` | required for Ajax | A URL string, or `function (query)` returning a URL string |
| `type` | `"GET"` | HTTP method |
| `dataType` | `"text"` | `"text"`, `"json"`, or `"jsonp"`. `jsonp` is recognized by jQuery's Ajax layer |
| `paramName` | `"query"` | Name of the request parameter holding the query |
| `params` | optional | Extra parameters merged into every request |
| `ignoreParams` | `false` | If `true`, suppresses the query-string `data` payload. Useful when `serviceUrl` is a callback that builds the entire URL itself |
| `deferRequestBy` | `0` | Milliseconds to wait before firing the request, so fast typists don't trigger one per keystroke |
| `ajaxSettings` | optional | Extra [jQuery Ajax settings](http://api.jquery.com/jquery.ajax/#jQuery-ajax-settings) merged into the request |

## Default options

Defaults are accessible (and writable) at `$.Autocomplete.defaults`.

## Instance methods

| Method | Description |
| :--- | :--- |
| `setOptions(options)` | Updates any option(s) at any time |
| `clear()` | Clears the suggestion cache and the current suggestions |
| `clearCache()` | Clears only the suggestion cache |
| `disable()` | Deactivates autocomplete |
| `enable()` | Reactivates autocomplete after `disable()` |
| `hide()` | Hides the dropdown |
| `dispose()` | Destroys the instance, detaches all event handlers, and removes the dropdown container |

There are two ways to call a method. Pass the method name as a string,
followed by any arguments:

```js
$('#autocomplete').autocomplete('disable');
$('#autocomplete').autocomplete('setOptions', options);
```

Or grab the Autocomplete instance by calling `autocomplete()` with no
arguments, then invoke the method on it:

```js
$('#autocomplete').autocomplete().disable();
$('#autocomplete').autocomplete().setOptions(options);
```

## Usage

HTML:

```html
<input type="text" name="country" id="autocomplete" />
```

Ajax lookup:

```js
$('#autocomplete').autocomplete({
    serviceUrl: '/autocomplete/countries',
    onSelect: function (suggestion) {
        console.log(`Picked: ${suggestion.value}, ${suggestion.data}`);
    },
});
```

Local lookup (no Ajax):

```js
const countries = [
    { value: 'Andorra', data: 'AD' },
    // ...
    { value: 'Zimbabwe', data: 'ZZ' },
];

$('#autocomplete').autocomplete({
    lookup: countries,
    onSelect: function (suggestion) {
        console.log(`Picked: ${suggestion.value}, ${suggestion.data}`);
    },
});
```

Custom lookup function — do whatever you want, then call `done` with the
result:

```js
$('#autocomplete').autocomplete({
    lookup: function (query, done) {
        done({
            suggestions: [
                { value: 'United Arab Emirates', data: 'AE' },
                { value: 'United Kingdom', data: 'UK' },
                { value: 'United States', data: 'US' },
            ],
        });
    },
    onSelect: function (suggestion) {
        console.log(`Picked: ${suggestion.value}, ${suggestion.data}`);
    },
});
```

## Styling

The generated dropdown markup looks like this — style any of the classes
to taste:

```html
<div class="autocomplete-suggestions">
    <div class="autocomplete-group"><strong>NHL</strong></div>
    <div class="autocomplete-suggestion autocomplete-selected">...</div>
    <div class="autocomplete-suggestion">...</div>
    <div class="autocomplete-suggestion">...</div>
</div>
```

Minimal CSS to get started:

```css
.autocomplete-suggestions { border: 1px solid #999; background: #fff; overflow: auto; }
.autocomplete-suggestion  { padding: 2px 5px; white-space: nowrap; overflow: hidden; cursor: pointer; }
.autocomplete-selected    { background: #f0f0f0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399ff; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
```

`cursor: pointer` on `.autocomplete-suggestion` is required for tap-to-select
to fire on mobile Safari — see issue #542.

## Response format

The server must respond with JSON in this shape:

```json
{
    "suggestions": [
        { "value": "United Arab Emirates", "data": "AE" },
        { "value": "United Kingdom", "data": "UK" },
        { "value": "United States", "data": "US" }
    ]
}
```

`data` may be any value or object — it is passed unchanged to
`formatResult`, `onSelect`, and the grouping key resolver. If you have no
`data`, a plain string array is also accepted:

```json
{ "suggestions": ["United Arab Emirates", "United Kingdom", "United States"] }
```

The optional `query` field on the response was required up to version
1.2.5; newer versions ignore it.

## Non-standard request / response format

If your service expects a differently named query parameter or returns a
non-standard payload, use `paramName` and `transformResult`:

```js
$('#autocomplete').autocomplete({
    paramName: 'searchString',
    transformResult: function (response) {
        return {
            suggestions: response.myData.map((item) => ({
                value: item.valueField,
                data: item.dataField,
            })),
        };
    },
});
```

## Grouping results

Set `groupBy` to a property name on `suggestion.data` to render group
headers. For example, `groupBy: 'category'` with this data:

```js
[
    { value: 'Chicago Blackhawks', data: { category: 'NHL' } },
    { value: 'Chicago Bulls', data: { category: 'NBA' } },
];
```

renders two groups, **NHL** and **NBA**.

## Known issues

jQuery UI also defines a plugin named `autocomplete`. When both are loaded,
this plugin yields and registers only `$.fn.devbridgeAutocomplete`, so use
the alias:

```js
$('.autocomplete').devbridgeAutocomplete({ ... });
```

## Security

To report a vulnerability, **please do not open a public GitHub issue.**
Use GitHub's [Private Vulnerability Reporting](https://github.com/tkirda/jQuery-Autocomplete/security/advisories/new)
form on this repository — it routes the report directly to the
maintainers. Full policy and supported-version table:
[SECURITY.md](https://github.com/tkirda/jQuery-Autocomplete/blob/master/SECURITY.md).

## License

Ajax Autocomplete for jQuery is freely distributable under the terms of an
MIT-style [license](https://github.com/tkirda/jQuery-Autocomplete/blob/master/LICENSE).
The copyright and permission notices must be included with any copy or
substantial portion of the software.

## Authors

Tomas Kirda / [@tkirda](https://x.com/tkirda)
