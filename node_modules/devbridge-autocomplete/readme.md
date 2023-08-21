Devbridge Group accelerates software to market for enterprise clients through dedicated product teams, user experience and software engineering expertise.

[www.devbridge.com](http://www.devbridge.com/)

# Ajax Autocomplete for jQuery

Ajax Autocomplete for jQuery allows you to easily create
autocomplete/autosuggest boxes for text input fields.

It has no dependencies other than jQuery.

The standard jquery.autocomplete.js file is around 13KB when minified.

## API
The following sets up autocomplete for input fields where `options` is an object literal that defines the settings to use for the autocomplete plugin.  All available option settings are shown in the tables below.  
```js
$(selector).autocomplete(options);
```
### General settings (local and Ajax) 
| Setting | Default | Description |
| :--- | :--- | :--- |
| `noCache` | `false` | Boolean value indicating whether to cache suggestion results |
| `delimiter` | optional | String or RegExp, that splits input value and takes last part to as query for suggestions. Useful when for example you need to fill list of  comma separated values. |
| `minChars` | `1` | Minimum number of characters required to trigger autosuggest |
| `triggerSelectOnValidInput` | `true` | Boolean value indicating if `select` should be triggered if it matches suggestion |
| `preventBadQueries` | `true` | Boolean value indicating if it should prevent future Ajax requests for queries with the same root if no results were returned. E.g. if `Jam` returns no suggestions, it will not fire for any future query that starts with `Jam` |
| `autoSelectFirst` | `false` | If set to `true`, first item will be selected when showing suggestions |
| `beforeRender` | optional | `function (container, suggestions) {}` called before displaying the suggestions. You may manipulate suggestions DOM before it is displayed |
| `formatResult` | optional | `function (suggestion, currentValue) {}` custom function to format suggestion entry inside suggestions container |
| `formatGroup` | optional | `function (suggestion, category) {}` custom function to format group header |
| `groupBy` | optional | property name of the suggestion `data` object, by which results should be grouped |
| `maxHeight` | `300` | Maximum height of the suggestions container in pixels |
| `width` | `auto` | Suggestions container width in pixels, e.g.: 300, `flex` for max suggestion size and `auto` takes input field width |
| `zIndex` | `9999` | 'z-index' for suggestions container |
| `appendTo` | optional | Container where suggestions will be appended. Default value `document.body`. Can be jQuery object, selector or HTML element. Make sure to set `position: absolute` or `position: relative` for that element |
| `forceFixPosition` | `false` | Suggestions are automatically positioned when their container is appended to body (look at `appendTo` option), in other cases suggestions are rendered but no positioning is applied. Set this option to force auto positioning in other cases |
| `orientation` | `bottom` | Vertical orientation of the displayed suggestions, available values are `auto`, `top`, `bottom`.  If set to `auto`, the suggestions will be orientated it the way that place them closer to middle of the view port |
| `preserveInput` | `false` | If `true`, input value stays the same when navigating over suggestions |
| `showNoSuggestionNotice` | `false` | When no matching results, display a notification label |
| `noSuggestionNotice` | `No results` | Text or htmlString or Element or jQuery object for no matching results label |
| `onInvalidateSelection` | optional | `function () {}` called when input is altered after selection has been made. `this` is bound to input element |
| `tabDisabled` | `false` | Set to true to leave the cursor in the input field after the user tabs to select a suggestion |


### Event function settings (local and Ajax) 
| Event setting | Function description |
| :--- | :--- |
| `onSearchStart` | `function (params) {}` called before Ajax request. `this` is bound to input element |
| `onHint` | `function (hint) {}` used to change input value to first suggestion automatically |
| `onSearchComplete` | `function (query, suggestions) {}` called after Ajax response is processed. `this` is bound to input element. `suggestions` is an array containing the results |
| `transformResult` | `function(response, originalQuery) {}` called after the result of the query is ready. Converts the result into response.suggestions format |
| `onSelect` | `function (suggestion) {}` Callback function invoked when user selects suggestion from the list. `this` inside callback refers to input HtmlElement.|
| `onSearchError` | `function (query, jqXHR, textStatus, errorThrown) {}` called if Ajax request fails. `this` is bound to input element |
| `onHide` | `function (container) {}` called before container will be hidden |


### Local only settings
| Setting | Default | Description |
| :--- | :--- | :--- |
| `lookupLimit` | `no limit` | Number of maximum results to display for local lookup |
| `lookup` | n/a | Callback function or lookup array for the suggestions. It may be array of strings or `suggestion` object literals |
| `suggestion` | n/a | Not a settings, but in the context of above row, a suggestion is an object literal with the following format: `{ value: 'string', data: any }` |
| `lookupFilter` | n/a | `function (suggestion, query, queryLowerCase) {}` filter function for local lookups. By default it does partial string match (case insensitive) |

### Ajax only settings
| Setting | Default | Description |
| :--- | :--- | :--- |
| `serviceUrl` | n/a | Server side URL or callback function that returns serviceUrl string |
| `type` | `GET` | Ajax request type to get suggestions |
| `dataType` | `text` | type of data returned from server. Either `text`, `json`  or `jsonp`, which will cause the autocomplete to use jsonp. You may return a json object in your callback when using jsonp |
| `paramName` | `query` | The name of the request parameter that contains the query |
| `params` | optional | Additional parameters to pass with the request |
| `deferRequestBy` | `0` | Number of miliseconds to defer Ajax request |
| `ajaxSettings` | optional | Any additional [Ajax Settings](http://api.jquery.com/jquery.ajax/#jQuery-ajax-settings) that configure the jQuery Ajax request |

## Default Options

Default options for all instances can be accessed via `$.Autocomplete.defaults`.

## Instance Methods

Autocomplete instance has following methods:

* `setOptions(options)`: you may update any option at any time. Options are listed above.
* `clear`: clears suggestion cache and current suggestions.
* `clearCache`: clears suggestion cache.
* `disable`: deactivate autocomplete.
* `enable`: activates autocomplete if it was deactivated before.
* `hide`: hides suggestions.
* `dispose`: destroys autocomplete instance. All events are detached and suggestion containers removed.

There are two ways that you can invoke Autocomplete method. One is calling autocomplete on jQuery object and passing method name as string literal.
If method has arguments, arguments are passed as consecutive parameters:

```javascript
$('#autocomplete').autocomplete('disable');
$('#autocomplete').autocomplete('setOptions', options);
```

Or you can get Autocomplete instance by calling autcomplete on jQuery object without any parameters and then invoke desired method.

```javascript
$('#autocomplete').autocomplete().disable();
$('#autocomplete').autocomplete().setOptions(options);
```

## Usage

Html:

```html
<input type="text" name="country" id="autocomplete"/>
```

Ajax lookup:

```javascript
$('#autocomplete').autocomplete({
    serviceUrl: '/autocomplete/countries',
    onSelect: function (suggestion) {
        alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    }
});
```

Local lookup (no Ajax):

```javascript
var countries = [
    { value: 'Andorra', data: 'AD' },
    // ...
    { value: 'Zimbabwe', data: 'ZZ' }
];

$('#autocomplete').autocomplete({
    lookup: countries,
    onSelect: function (suggestion) {
        alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    }
});
```

Custom lookup function:
```javascript

$('#autocomplete').autocomplete({
    lookup: function (query, done) {
        // Do Ajax call or lookup locally, when done,
        // call the callback and pass your results:
        var result = {
            suggestions: [
                { "value": "United Arab Emirates", "data": "AE" },
                { "value": "United Kingdom",       "data": "UK" },
                { "value": "United States",        "data": "US" }
            ]
        };

        done(result);
    },
    onSelect: function (suggestion) {
        alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    }
});
```

## Styling

Generated HTML markup for suggestions is displayed below. You may style it any way you'd like.

```html
<div class="autocomplete-suggestions">
    <div class="autocomplete-group"><strong>NHL</strong></div>
    <div class="autocomplete-suggestion autocomplete-selected">...</div>
    <div class="autocomplete-suggestion">...</div>
    <div class="autocomplete-suggestion">...</div>
</div>
```

Style sample:

```css
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
```


## Response Format

Response from the server must be JSON formatted following JavaScript object:

```javascript
{
    // Query is not required as of version 1.2.5
    "query": "Unit",
    "suggestions": [
        { "value": "United Arab Emirates", "data": "AE" },
        { "value": "United Kingdom",       "data": "UK" },
        { "value": "United States",        "data": "US" }
    ]
}
```

Data can be any value or object. Data object is passed to formatResults function
and onSelect callback. Alternatively, if there is no data you can
supply just a string array for suggestions:

```json
{
    "query": "Unit",
    "suggestions": ["United Arab Emirates", "United Kingdom", "United States"]
}
```

## Non standard query/results

If your Ajax service expects the query in a different format, and returns data in a different format than the standard response,
you can supply the "paramName" and "transformResult" options:

```javascript
$('#autocomplete').autocomplete({
    paramName: 'searchString',
    transformResult: function(response) {
        return {
            suggestions: $.map(response.myData, function(dataItem) {
                return { value: dataItem.valueField, data: dataItem.dataField };
            })
        };
    }
})
```

## Grouping Results

Specify `groupBy` option of you data property if you wish results to be displayed in groups. For example, set `groupBy: 'category'` if your suggestion data format is:

```javascript
[
    { value: 'Chicago Blackhawks', data: { category: 'NHL' } },
    { value: 'Chicago Bulls', data: { category: 'NBA' } }
]
```

Results will be formatted into two groups **NHL** and **NBA**.

## Known Issues

If you use it with jQuery UI library it also has plugin named `autocomplete`. In this case you can use plugin alias `devbridgeAutocomplete`:

```javascript
$('.autocomplete').devbridgeAutocomplete({ ... });
```

It seems that for mobile Safari click events are only triggered if the CSS of the object being tapped has the cursor set to pointer:

    .autocomplete-suggestion { 
        cursor: pointer;
    }

See issue #542

## License

Ajax Autocomplete for jQuery is freely distributable under the
terms of an MIT-style [license](https://github.com/devbridge/jQuery-Autocomplete/blob/master/dist/license.txt).

Copyright notice and permission notice shall be included in all
copies or substantial portions of the Software.

## Authors

Tomas Kirda / [@tkirda](https://twitter.com/tkirda)
