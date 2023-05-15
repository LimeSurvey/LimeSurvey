var assert = require("assert")

var Uri = (typeof(require) === 'function') ? require('../Uri') : window.Uri

describe('Uri', function() {
  var u

  beforeEach(function() {
    u = new Uri('http://test.com')
  })

  it('can replace protocol', function() {
    u.protocol('https')
    assert.equal(u.toString(), 'https://test.com');
  })

  it('can replace protocol with colon suffix', function() {
    u.protocol('https:')
    assert.equal(u.toString(), 'https://test.com')
  })

  it('keeps authority prefix when protocol is removed', function() {
    u.protocol(null)
    assert.equal(u.toString(), '//test.com')
  })

  it('can disable authority prefix but keep protocol', function() {
    u.hasAuthorityPrefix(false)
    assert.equal(u.toString(), 'http://test.com')
  })

  it('can add user info', function() {
    u.userInfo('username:password')
    assert.equal(u.toString(), 'http://username:password@test.com')
  })

  it('can add user info with trailing at', function() {
    u.userInfo('username:password@')
    assert.equal(u.toString(), 'http://username:password@test.com')
  })

  it('can add a hostname to a relative path', function() {
    u = new Uri('/index.html')
    u.host('wherever.com')
    assert.equal(u.toString(), 'wherever.com/index.html')
  })

  it('can change a hostname ', function() {
    u.host('wherever.com')
    assert.equal(u.toString(), 'http://wherever.com')
  })

  it('should not add a port when there is no hostname', function() {
    u = new Uri('/index.html')
    u.port(8080)
    assert.equal(u.toString(), '/index.html')
  })

  it('should be able to change the port', function() {
    u.port(8080)
    assert.equal(u.toString(), 'http://test.com:8080')
  })

  it('should be able to add a path to a domain', function() {
    u = new Uri('test.com')
    u.path('/some/article.html')
    assert.equal(u.toString(), 'test.com/some/article.html')
  })

  it('should be able to change a path', function() {
    u.path('/some/article.html')
    assert.equal(u.toString(), 'http://test.com/some/article.html')
  })

  it('should be able to delete a path', function() {
    u = new Uri('http://test.com/index.html')
    u.path(null)
    assert.equal(u.toString(), 'http://test.com')
  })

  it('should be able to empty a path', function() {
    u = new Uri('http://test.com/index.html')
    u.path('')
    assert.equal(u.toString(), 'http://test.com')
  })

  it('should be able to add a query to nothing', function() {
    u = new Uri('')
    u.query('this=that&something=else')
    assert.equal(u.toString(), '?this=that&something=else')
  })

  it('should be able to add a query to a relative path', function() {
    u = new Uri('/some/file.html')
    u.query('this=that&something=else')
    assert.equal(u.toString(), '/some/file.html?this=that&something=else')
  })

  it('should be able to add a query to a domain', function() {
    u = new Uri('test.com')
    u.query('this=that&something=else')
    assert.equal(u.toString(), 'test.com/?this=that&something=else')
  })

  it('should be able to swap a query', function() {
    u = new Uri('www.test.com?this=that&a=1&b=2c=3')
    u.query('this=that&something=else')
    assert.equal(u.toString(), 'www.test.com/?this=that&something=else')
  })

  it('should be able to delete a query', function() {
    u = new Uri('www.test.com?this=that&a=1&b=2c=3')
    u.query(null)
    assert.equal(u.toString(), 'www.test.com')
  })

  it('should be able to empty a query', function() {
    u = new Uri('www.test.com?this=that&a=1&b=2c=3')
    u.query('')
    assert.equal(u.toString(), 'www.test.com')
  })

  it('should be able to add an anchor to a domain', function() {
    u = new Uri('test.com')
    u.anchor('content')
    assert.equal(u.toString(), 'test.com/#content')
  })

  it('should be able to add an anchor with a hash prefix to a domain', function() {
    u = new Uri('test.com')
    u.anchor('#content')
    assert.equal(u.toString(), 'test.com/#content')
  })

  it('should be able to add an anchor to a path', function() {
    u = new Uri('a/b/c/123.html')
    u.anchor('content')
    assert.equal(u.toString(), 'a/b/c/123.html#content')
  })

  it('should be able to change an anchor', function() {
    u = new Uri('/a/b/c/index.html#content')
    u.anchor('about')
    assert.equal(u.toString(), '/a/b/c/index.html#about')
  })

  it('should be able to empty an anchor', function() {
    u = new Uri('/a/b/c/index.html#content')
    u.anchor('')
    assert.equal(u.toString(), '/a/b/c/index.html')
  })

  it('should be able to delete an anchor', function() {
    u = new Uri('/a/b/c/index.html#content')
    u.anchor(null)
    assert.equal(u.toString(), '/a/b/c/index.html')
  })

  it('should be able to get single encoded values', function() {
    u = new Uri('http://example.com/search?q=%40')
    assert.equal(u.getQueryParamValue('q'), '@')
  })

  it('should be able to get double encoded values', function() {
    u = new Uri('http://example.com/search?q=%2540')
    assert.equal(u.getQueryParamValue('q'), '%40')
  })

  it('should be able to work with %40 values', function() {
    u = new Uri('http://example.com/search?q=%40&stupid=yes')
    u.deleteQueryParam('stupid')
    assert.equal(u.toString(), 'http://example.com/search?q=%40')
  })

  it('should be able to work with %25 values', function() {
    u = new Uri('http://example.com/search?q=100%25&stupid=yes')
    u.deleteQueryParam('stupid')
    assert.equal(u.toString(), 'http://example.com/search?q=100%25')
  })

  it('should insert missing slash when origin and path have no slash', function () {
    u = new Uri('http://test.com')
    u.setPath('relativePath')
    assert.equal(u.toString(), 'http://test.com/relativePath')
  })

  it('should remove extra slash when origin and path both provide a slash', function () {
    u = new Uri('http://test.com/')
    u.setPath('/relativePath')
    assert.equal(u.toString(), 'http://test.com/relativePath')
  })

  it('should remove extra slashes when origin and path both provide too many slashes', function () {
    u = new Uri('http://test.com//')
    u.setPath('//relativePath')
    assert.equal(u.toString(), 'http://test.com/relativePath')
  })

  it('should be able to clone a separate copy which does not share state', function() {
    var a = new Uri('?a=1'),
        b = a.clone().addQueryParam('b', '2')
    assert.notEqual(a.toString(), b.toString())
  })

  it('can add a trailing slash to the path', function() {
    var str = new Uri('http://www.example.com/path?arr=1&arr=2')
      .addTrailingSlash()
      .toString()
    assert.equal(str, 'http://www.example.com/path/?arr=1&arr=2')
  })

  it('preserves the format of file uris', function() {
    var str = 'file://c:/parent/child.ext'
    var uri = new Uri(str)
    assert.equal(uri.toString(), str)
  })

  it('correctly composes url encoded urls', function() {
     var originalQuery = '?k=%40v'
     var parsed = new Uri('http://example.com' + originalQuery)
     assert.equal(parsed.query(), originalQuery)
  })

  it('parse + character correctly', function() {
     var parsed = new Uri('http://example.com?test=a%2Bb')
     assert.equal(parsed.toString(), 'http://example.com/?test=a%2Bb')
  })

  it('Read + character correctly', function() {
     var test = new Uri('http://example.com?test=a%2Bb').getQueryParamValue('test')
     assert.equal(test, 'a+b')
  })

  it('parse space character encoded as + correctly', function() {
     var parsed = new Uri('http://example.com?test=a+b')
     assert.equal(parsed.toString(), 'http://example.com/?test=a%20b')
  })

  it('Read parsed space character encoded as + correctly', function() {
     var test = new Uri('http://example.com?test=a+b').getQueryParamValue('test')
     assert.equal(test, 'a b')
  })
})
