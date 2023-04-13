# jsUri

URI parsing and manipulation for node.js and the browser.

[![Build Status](https://travis-ci.org/derek-watson/jsUri.png)](https://travis-ci.org/derek-watson/jsUri)

[![NPM](https://nodei.co/npm/jsuri.png)](https://nodei.co/npm/jsuri/)

[![spm package](http://spmjs.io/badge/jsuri)](http://spmjs.io/package/jsuri)

Pass any URL into the constructor:

```js
var uri = new Uri('http://user:pass@www.test.com:81/index.html?q=books#fragment')
```

Use property methods to get at the various parts:

```js
uri.protocol()    // http
uri.userInfo()    // user:pass
uri.host()        // www.test.com
uri.port()        // 81
uri.path()        // /index.html
uri.query()       // q=books
uri.anchor()      // fragment
```

Property methods accept an optional value to set:

```js
uri.protocol('https')
uri.toString()    // https://user:pass@www.test.com:81/index.html?q=books#fragment

uri.host('mydomain.com')
uri.toString()    // https://user:pass@mydomain.com:81/index.html?q=books#fragment
```

Chainable setter methods help you compose strings:

```js
new Uri()
    .setPath('/archives/1979/')
    .setQuery('?page=1')                   // /archives/1979?page=1

new Uri()
    .setPath('/index.html')
    .setAnchor('content')
    .setHost('www.test.com')
    .setPort(8080)
    .setUserInfo('username:password')
    .setProtocol('https')
    .setQuery('this=that&some=thing')      // https://username:password@www.test.com:8080/index.html?this=that&some=thing#content

new Uri('http://www.test.com')
    .setHost('www.yahoo.com')
    .setProtocol('https')                  // https://www.yahoo.com
```

## Query param methods

Returns the first query param value for the key:

```js
new Uri('?cat=1&cat=2&cat=3').getQueryParamValue('cat')             // 1
```

Returns all query param values for the given key:

```js
new Uri('?cat=1&cat=2&cat=3').getQueryParamValues('cat')            // [1, 2, 3]
```

Internally, query key/value pairs are stored as a series of two-value arrays in the Query object:

```js
new Uri('?a=b&c=d').query().params                  // [ ['a', 'b'], ['c', 'd']]
```

Add query param values:

```js
new Uri().addQueryParam('q', 'books')               // ?q=books

new Uri('http://www.github.com')
    .addQueryParam('testing', '123')
    .addQueryParam('one', 1)                        // http://www.github.com/?testing=123&one=1

// insert param at index 0
new Uri('?b=2&c=3&d=4').addQueryParam('a', '1', 0)  // ?a=1&b=2&c=3&d=4
```

Replace every query string parameter named `key` with `newVal`:

```js
new Uri().replaceQueryParam('page', 2)     // ?page=2

new Uri('?a=1&b=2&c=3')
    .replaceQueryParam('a', 'eh')          // ?a=eh&b=2&c=3

new Uri('?a=1&b=2&c=3&c=4&c=5&c=6')
    .replaceQueryParam('c', 'five', '5')   // ?a=1&b=2&c=3&c=4&c=five&c=6
```

Removes instances of query parameters named `key`:

```js
new Uri('?a=1&b=2&c=3')
    .deleteQueryParam('a')                 // ?b=2&c=3

new Uri('test.com?a=1&b=2&c=3&a=eh')
    .deleteQueryParam('a', 'eh')           // test.com/?a=1&b=2&c=3
```

Test for the existence of query parameters named `key`:

```js
new Uri('?a=1&b=2&c=3')
    .hasQueryParam('a')                    // true

new Uri('?a=1&b=2&c=3')
    .hasQueryParam('d')                    // false
```

Create an identical URI object with no shared state:

```js
var baseUri = new Uri('http://localhost/')

baseUri.clone().setProtocol('https')   // https://localhost/
baseUri                                // http://localhost/
```

This project incorporates the [parseUri](http://blog.stevenlevithan.com/archives/parseuri) regular expression by Steven Levithan.
