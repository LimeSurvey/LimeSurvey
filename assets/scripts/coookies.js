/**
 * Cookies.js, providing easy access to cookies thru the cookiejar object. Enabling so-called "subcookies" thru the subcookiejar 
 * object.
 * See this related blogpost for more information on how to use these objects:
 * 	<http://www.whatstyle.net/articles/28/subcookies>
 * Check out this other blogpost for information about the new version:
 *  <http://www.whatstyle.net/articles/46/subcookies_v2>
 * 
 * @author Harmen Janssen <http://www.whatstyle.net>
 * @version 2.0
 * 
 */

/* based on http://www.quirksmode.org/js/cookies.html, by Peter-Paul Koch */
var cookiejar = {
	/* set a cookie */
	bake: function(cookieName,cookieValue,days,path) {
		var expires='';
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			expires = "; expires="+date.toGMTString();
		}
		var thePath = '; path=/';
		if (path) {
			thePath = '; path=' + path;
		}
		document.cookie = cookieName+'='+escape(cookieValue)+expires+thePath;
		return true;
	},
	/* get a cookie value */
	fetch: function(cookieName) {
		var nameEQ = cookieName + '=';
		var ca = document.cookie.split(';');
		for (var i=0; i<ca.length; i++)	{
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1, c.length);
			}
			if (c.indexOf(nameEQ) == 0) {
				return unescape(c.substring(nameEQ.length, c.length));
			}
		}
		return null;
	},
	/* delete a cookie */
	crumble: function(cookieName) {
		return cookiejar.bake(cookieName,'',-1);
	}
};

/* circumventing browser restrictions on the number of cookies one can use */
var subcookiejar = {
	nameValueSeparator: '$$:$$',
	subcookieSeparator: '$$/$$',
	/* set a cookie. subcookieObj is a collection of cookies to be. Every member of subcookieObj is the name of the cookie, its value
	 * the cookie value
	 */
	bake: function(cookieName,subcookieObj,days,path) {
		var existingCookie;
		/* check for existing cookie */
		if (existingCookie = subcookiejar.fetch (cookieName)) {
			/* if a cookie by the same name is found, 
			 * append its values to the subcookieObj.
			 */
			for (var i in existingCookie) {
				if (!(i in subcookieObj)) {
					subcookieObj[i] = existingCookie[i];
				}
			}
		}
		var cookieValue = '';
		for (var i in subcookieObj)	{
			cookieValue += i + subcookiejar.nameValueSeparator;
			cookieValue += subcookieObj[i];
			cookieValue += subcookiejar.subcookieSeparator;
		}
		/* remove trailing subcookieSeparator */
		cookieValue = cookieValue.substring(0,cookieValue.length-subcookiejar.subcookieSeparator.length);
		return cookiejar.bake(cookieName,cookieValue,days,path);
	},
	/* get a subcookie */
	fetch: function(cookieName,subcookieName) {
		var cookieValue = cookiejar.fetch(cookieName);
		/* proceed only if a cookie was found */
		if (!cookieValue) {
			return null;
		}
		var subcookies = cookieValue.split(subcookiejar.subcookieSeparator);
		var cookieObj = {};
		for (var i=0,sclen=subcookies.length; i<sclen; i++)	{
			var sc = subcookies[i].split(subcookiejar.nameValueSeparator);
			cookieObj [sc[0]] = sc[1];
		}
		/* if subcookieName is given, return that subcookie if available, or null.
		 * else, return the entire cookie as an object literal
		 */
		if (subcookieName != undefined) {
			if (subcookieName in cookieObj) {
				return cookieObj[subcookieName];
			}
			return null;
		}
		return cookieObj;
	},
	/* delete a subcookie */
	crumble: function(cookieName,subcookieName,days,path) {
		var cookieValue = cookiejar.fetch(cookieName);
		if (!cookieValue) {
			return false;
		}
		var newCookieObj = {};
		var subcookies = cookieValue.split(subcookiejar.subcookieSeparator);
		for (var i=0, sclen=subcookies.length; i<sclen; i++)	{
			var sc = subcookies[i].split(subcookiejar.nameValueSeparator);
			if (sc[0] != subcookieName) {
				newCookieObj[sc[0]] = sc[1];
			}
		}
		return subcookiejar.bake(cookieName,newCookieObj,days,path);
	}
};