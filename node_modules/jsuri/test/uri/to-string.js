var assert = require('assert');

var Uri = (typeof(require) === 'function') ? require('../../Uri') : window.Uri

describe('Uri', function() {
  describe('toString()', function() {
    it('should convert empty constructor call to blank url', function() {
      assert.equal(new Uri().toString(), '')
    })

    it('can construct empty string', function() {
      assert.equal(new Uri().toString(), '')
    })

    it('can construct single slash', function() {
      assert.equal(new Uri('/').toString(), '/')
    })

    it('can construct a relative path with a trailing slash', function() {
      assert.equal(new Uri('tutorial1/').toString(), 'tutorial1/')
    })

    it('can construct a relative path with leading and trailing slashes', function() {
      assert.equal(new Uri('/experts/').toString(), '/experts/')
    })

    it('can construct a relative filename with leading slash', function() {
      assert.equal(new Uri('/index.html').toString(), '/index.html')
    })

    it('can construct a relative directory and filename', function() {
      assert.equal(new Uri('tutorial1/2.html').toString(), 'tutorial1/2.html')
    })

    it('can construct a relative parent directory', function() {
      assert.equal(new Uri('../').toString(), '../')
    })

    it('can construct a relative great grandparent directory', function() {
      assert.equal(new Uri('../../../').toString(), '../../../')
    })

    it('can construct a relative current directory', function() {
      assert.equal(new Uri('./').toString(), './')
    })

    it('can construct a relative current directory sibling doc', function() {
      assert.equal(new Uri('./index.html').toString(), './index.html')
    })

    it('can construct a simple three level domain', function() {
      assert.equal(new Uri('www.example.com').toString(), 'www.example.com')
    })

    it('can construct a simple absolute url', function() {
      assert.equal(new Uri('http://www.example.com/index.html').toString(), 'http://www.example.com/index.html')
    })

    it('can construct a secure absolute url', function() {
      assert.equal(new Uri('https://www.example.com/index.html').toString(), 'https://www.example.com/index.html')
    })

    it('can construct a simple url with a custom port', function() {
      assert.equal(new Uri('http://www.example.com:8080/index.html').toString(), 'http://www.example.com:8080/index.html')
    })

    it('can construct a secure url with a custom port', function() {
      assert.equal(new Uri('https://www.example.com:4433/index.html').toString(), 'https://www.example.com:4433/index.html')
    })

    it('can construct a relative path with a hash part', function() {
      assert.equal(new Uri('/index.html#about').toString(), '/index.html#about')
    })

    it('can construct a relative path with a hash part', function() {
      assert.equal(new Uri('/index.html#about').toString(), '/index.html#about')
    })

    it('can construct an absolute path with a hash part', function() {
      assert.equal(new Uri('http://example.com/index.html#about').toString(), 'http://example.com/index.html#about')
    })

    it('can construct a relative path with a query string', function() {
      assert.equal(new Uri('/index.html?a=1&b=2').toString(), '/index.html?a=1&b=2')
    })

    it('can construct an absolute path with a query string', function() {
      assert.equal(new Uri('http://www.test.com/index.html?a=1&b=2').toString(), 'http://www.test.com/index.html?a=1&b=2')
    })

    it('can construct an absolute path with a query string and hash', function() {
      assert.equal(new Uri('http://www.test.com/index.html?a=1&b=2#a').toString(), 'http://www.test.com/index.html?a=1&b=2#a')
    })

    it('can construct a url with multiple synonymous query values', function() {
      assert.equal(new Uri('http://www.test.com/index.html?arr=1&arr=2&arr=3&arr=3&b=2').toString(), 'http://www.test.com/index.html?arr=1&arr=2&arr=3&arr=3&b=2')
    })

    it('can construct a url with blank query value', function() {
      assert.equal(new Uri('http://www.test.com/index.html?arr=1&arr=&arr=2').toString(), 'http://www.test.com/index.html?arr=1&arr=&arr=2')
    })

    it('can construct a url without a scheme', function() {
      assert.equal(new Uri('//www.test.com/').toString(), '//www.test.com/')
    })

    it('can construct a path and single query kvp', function() {
      assert.equal(new Uri('/contacts?name=m').toString(), '/contacts?name=m')
    })

    it('returns successfully returns the origin with a scheme, auth, host and port', function() {
      assert.equal(new Uri('http://me:here@test.com:81/this/is/a/path').origin(), 'http://me:here@test.com:81')
    })
  })
})
