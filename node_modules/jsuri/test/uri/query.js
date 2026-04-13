var assert = require('assert');

var Uri = (typeof(require) === 'function') ? require('../../Uri') : window.Uri

describe('Uri', function() {
  describe('query', function() {

    it('correctly parses query param expressions with multiple = separators', function() {
      var back = new Uri('?back=path/to/list?page=1').getQueryParamValue('back')
      assert.equal(back, 'path/to/list?page=1')
    })

    describe('construction', function() {
      var q

      it('should decode entities when parsing', function(){
        q = new Uri('?email=user%40example.com')
        assert.equal(q.getQueryParamValue('email'), 'user@example.com')
      })

      it('should include an equal sign if there was one present without a query value', function() {
        q = new Uri('?11=')
        assert.equal(q.toString(), '?11=')
      })

      it('should not include an equal sign if one was not present originally', function() {
        q = new Uri('?11')
        assert.equal(q.toString(), '?11')
      })

      it('should preserve missing equals signs across many keys', function() {
        q = new Uri('?11&12&13&14')
        assert.equal(q.toString(), '?11&12&13&14')
      })

      it('should preserve missing equals signs in a mixed scenario', function() {
        q = new Uri('?11=eleven&12=&13&14=fourteen')
        assert.equal(q.toString(), '?11=eleven&12=&13&14=fourteen')
      })

      it('should correctly parse the uri if an @ sign is present after the host part of the url', function(){
        q = new Uri('http://github.com/username?email=user@example.com&11=eleven')
        assert.equal(q.host(), 'github.com')
        assert.equal(q.path(), '/username')
        assert.equal(q.getQueryParamValue('email'), 'user@example.com')
        assert.equal(q.getQueryParamValue('11'), 'eleven')
      })
    })

    describe('manipulation', function() {
      var q

      it('should return the first value for each query param', function() {
        q = new Uri('?a=1&a=2&b=3&b=4&c=567')
        assert.equal(q.getQueryParamValue('a'), '1')
        assert.equal(q.getQueryParamValue('b'), '3')
        assert.equal(q.getQueryParamValue('c'), '567')
      })

      it('should return arrays for multi-valued query params', function() {
        q = new Uri('?a=1&a=2&b=3&b=4&c=567')
        assert.equal(q.getQueryParamValues('a')[0], '1')
        assert.equal(q.getQueryParamValues('a')[1], '2')
        assert.equal(q.getQueryParamValues('b')[0], '3')
        assert.equal(q.getQueryParamValues('b')[1], '4')
        assert.equal(q.getQueryParamValues('c')[0], '567')
      })

      it('should be able to add a new query param to a blank url', function() {
        q = new Uri('').addQueryParam('q', 'books')
        assert.equal(q.toString(), '?q=books')
      })

      it('can add a query param with a value of zero', function() {
        q = new Uri('').addQueryParam('pg', 0)
        assert.equal(q.toString(), '?pg=0')
      })

      it('should be able to delete a query param', function() {
        q = new Uri('?a=1&b=2&c=3&a=eh').deleteQueryParam('b')
        assert.equal(q.toString(), '?a=1&c=3&a=eh')
      })

      it('should be able to delete a query param by value', function() {
        q = new Uri('?a=1&b=2&c=3&a=eh').deleteQueryParam('a', 'eh')
        assert.equal(q.toString(), '?a=1&b=2&c=3')
      })

      it('should be able to add a null param', function() {
        q = new Uri('?a=1&b=2&c=3').addQueryParam('d')
        assert.equal(q.toString(), '?a=1&b=2&c=3&d=')
      })

      it('should be able to add a key and a value', function() {
        q = new Uri('?a=1&b=2&c=3').addQueryParam('d', '4')
        assert.equal(q.toString(), '?a=1&b=2&c=3&d=4')
      })

      it('should be able to prepend a key and a value', function() {
        q = new Uri('?a=1&b=2&c=3').addQueryParam('d', '4', 0)
        assert.equal(q.toString(), '?d=4&a=1&b=2&c=3')
      })

      it('should return query param values correctly', function() {
        q = new Uri('').addQueryParam('k', 'value@example.com')
        assert.equal(q.getQueryParamValue('k'), 'value@example.com')
      })

      it('should escape param values correctly', function() {
        q = new Uri('http://example.com').addQueryParam('k', 'user@example.org')
        assert.equal(q.toString(), 'http://example.com/?k=user%40example.org')
      })

      it('should be able to delete and replace a query param', function() {
        q = new Uri('?a=1&b=2&c=3').deleteQueryParam('a').addQueryParam('a', 'eh')
        assert.equal(q.toString(), '?b=2&c=3&a=eh')
      })

      it('should not do anything if passed no params', function() {
        q = new Uri('?a=1&b=2&c=3').addQueryParam()
        assert.equal(q.toString(), '?a=1&b=2&c=3')
      })

      it('should be able to directly replace a query param', function() {
        q = new Uri('?a=1&b=2&c=3').replaceQueryParam('a', 'eh')
        assert.equal(q.toString(), '?a=eh&b=2&c=3')
      })

      it('should remove an extra question mark', function() {
        q = new Uri('??a=1&b=2&c=3').replaceQueryParam('a', 4)
        assert.equal(q.toString(), '?a=4&b=2&c=3')
      })

      it('should remove a param without a key', function() {
        q = new Uri('?=1&b=2&c=3').replaceQueryParam('a', 4)
        assert.equal(q.toString(), '?b=2&c=3&a=4')
      })

      it('should be able to replace a query param value that does not exist', function() {
        q = new Uri().replaceQueryParam('page', 2)
        assert.equal(q.toString(), '?page=2')
      })

      it('should be able to replace a nonexistent query param value when others exist', function() {
        q = new Uri('?a=1').replaceQueryParam('page', 2)
        assert.equal(q.toString(), '?a=1&page=2')
      })

      it('should be able to replace only a query param value with a specified value', function() {
        q = new Uri('?page=1&page=2').replaceQueryParam('page', 3, 1)
        assert.equal(q.toString(), '?page=3&page=2')
      })

      it('should be able to replace only a query param value with a specified string value', function() {
        q = new Uri('?a=one&a=two').replaceQueryParam('a', 'three', 'one')
        assert.equal(q.toString(), '?a=three&a=two')
      })

      it('should be able to replace a param value with a specified value that does not exist', function() {
        q = new Uri('?page=4&page=2').replaceQueryParam('page', 3, 1)
        assert.equal(q.toString(), '?page=4&page=2')
      })

      it('should replace a param value with an empty value if not provided a value', function() {
        q = new Uri('?page=4&page=2').replaceQueryParam('page')
        assert.equal(q.toString(), '?page=')
      })

      it('should be able to handle multiple values for the same key', function() {
        q = new Uri().addQueryParam('a', 1)
        assert.equal(q.toString(), '?a=1')
        assert.equal(q.getQueryParamValues('a').length, 1)
        q.addQueryParam('a', 2)
        assert.equal(q.toString(), '?a=1&a=2')
        assert.equal(q.getQueryParamValues('a').length, 2)
        q.addQueryParam('a', 3)
        assert.equal(q.toString(), '?a=1&a=2&a=3')
        assert.equal(q.getQueryParamValues('a').length, 3)
        q.deleteQueryParam('a', 2)
        assert.equal(q.toString(), '?a=1&a=3')
        assert.equal(q.getQueryParamValues('a').length, 2)
        q.deleteQueryParam('a')
        assert.equal(q.toString(), '')
        assert.equal(q.getQueryParamValues('a').length, 0)
      })

      it('should not add a trailing slash if one is already present', function () {
        q = new Uri('stuff/').addTrailingSlash();
        assert.equal(q.toString(), 'stuff/')
      })

      it('should add a trailing slash to an empty uri', function () {
        q = new Uri().addTrailingSlash();
        assert.equal(q.toString(), '/')
      })
    })

    describe('semicolon as query param separator', function() {
      var q

      it('should replace semicolons with ampersands', function() {
        q = new Uri('?one=1;two=2;three=3')
        assert.equal(q.toString(), '?one=1&two=2&three=3')
      })

      it('should replace semicolons with ampersands, delete the first param and add another', function() {
        q = new Uri('?one=1;two=2;three=3&four=4').deleteQueryParam('one').addQueryParam('test', 'val', 1)
        assert.equal(q.toString(), '?two=2&test=val&three=3&four=4')
      })
    })

    describe('comparing encoded vs. non or partially encoded query param keys and values', function() {
      var q

      it('is able to find the value of an encoded multiword key from a non encoded search', function() {
        q = new Uri('?a=1&this%20is%20a%20multiword%20key=value&c=3')
        assert.equal(q.getQueryParamValue('this is a multiword key'), 'value')
      })

      it('is able to on the fly decode an encoded param value', function() {
        q = new Uri('?a=1&b=this%20is%20a%20multiword%20val&c=3')
        assert.equal(q.getQueryParamValue('b'), 'this is a multiword val')
      })

      it('is able to on the fly decode a space-encoded param value', function() {
        q = new Uri('?a=1&b=this is a multiword value&c=3')
        assert.equal(q.getQueryParamValue('b'), 'this is a multiword value')
      })

      it('is able to on the fly decode a double-encoded param value', function() {
        q = new Uri('?a=1&b=this%2520is%2520a%2520multiword%2520value&c=3')
        assert.equal(q.getQueryParamValue('b'), 'this%20is%20a%20multiword%20value')
      })

      it('is able to find all value s of an encoded multiword key from a non encoded search', function() {
        q = new Uri('?a=1&this%20is%20a%20multiword%20key=value&c=3')
        assert.equal(q.getQueryParamValues('this is a multiword key')[0], 'value')
      })

      it('is be able to delete a multiword encoded key', function() {
        q = new Uri('?a=1&this%20is%20a%20multiword%20key=value&c=3').deleteQueryParam('this is a multiword key')
        assert.equal(q.toString(), '?a=1&c=3')
      })

      it('is able to replace a multiword query param', function() {
        q = new Uri('?this is a multiword key=1').replaceQueryParam('this%20is%20a%20multiword%20key', 2)
        assert.equal(q.toString(), '?this%20is%20a%20multiword%20key=2')
      })

      it('should be able to search for a plus-separated word pair', function() {
        q = new Uri('?multi+word+key=true').replaceQueryParam('multi word key', 2)
        assert.equal(q.toString(), '?multi word key=2')
      })
    })

    describe('testing for the existence of query params', function() {
      q = new Uri('?this=that')
      assert(q.hasQueryParam('this'))
      assert(!q.hasQueryParam('theother'))
    })
  })
})
