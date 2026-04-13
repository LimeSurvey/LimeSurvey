var assert = require('assert')

var Uri = (typeof(require) === 'function') ? require('../../Uri') : window.Uri

describe('Uri', function() {
  describe('ssh url', function() {
    it('should not add slashes when it is colon uri', function (){
      var u = new Uri('ssh://test.com:')
      u.setPath('relativePath')
      assert.equal(u.toString(), 'ssh://test.com:relativePath')
      u.setPath('/relativePath')
      assert.equal(u.toString(), 'ssh://test.com:/relativePath')
    })

    it('understand isColonUti', function (){
      var u = new Uri('ssh://test.com')
      u.isColonUri(true);
      u.setPath('relativePath')
      assert.equal(u.toString(), 'ssh://test.com:relativePath')
    })

    it('can remove and re-add isColonUti', function (){
      var u = new Uri('ssh://test.com:pathtest')
      u.isColonUri(false);
      u.setPath('relativePath')
      assert.equal(u.toString(), 'ssh://test.com/relativePath')
      u.isColonUri(true);
      assert.equal(u.toString(), 'ssh://test.com:relativePath')
    })
  })

  describe('ssh url extended', function() {
    it('keep port unset and numeric path', function (){
      assert.equal(new Uri('me:here@test.com::123/this/is/a/path').toString(), 'me:here@test.com::123/this/is/a/path')
    })

    it('should correctly add port', function (){
      var u = new Uri('test.com:')
      u.setPort(123);
      u.setPath('123this/is/a/path');
      assert.equal(u.toString(), 'test.com:123:123this/is/a/path')
    })

    it('keep port and empty path', function (){
      assert.equal(new Uri('me:here@test.com:12:').toString(), 'me:here@test.com:12')
    })
  })
})
