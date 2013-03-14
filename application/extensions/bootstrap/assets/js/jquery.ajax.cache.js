// github.com/paulirish/jquery-ajax-localstorage-cache
// dependent on Modernizr's localStorage test
var storage,
    fail,
    uid;
try {
    uid = new Date;
    (storage = window.localStorage).setItem(uid, uid);
    fail = storage.getItem(uid) != uid;
    storage.removeItem(uid);
    fail && (storage = false);
} catch(e) {}

$.ajaxPrefilter(function (options, originalOptions, jqXHR) {

    if (!storage && options.localCache) {
        window.localStorage = new customStorage();
    }

    // Cache it ?
    if (!options.localCache) return;

    // do some cleanup first
    cleanOldStorage();

    var lifetl = options.cacheTTL || 1;

    var cacheKey = options.cacheKey ||
        options.url.replace(/jQuery.*/, '') + options.type + (options.data || '');

    if(options.url.match('/delete/i'))

    // isCacheValid is a function to validate cache
    if (options.isCacheValid && !options.isCacheValid()) {
        window.localStorage.removeItem(cacheKey);
    }
    // if there's a TTL that's expired, flush this item
    var ttl = window.localStorage.getItem(cacheKey + 'cachettl');
    if (ttl && ttl < +new Date()) {

        window.localStorage.removeItem(cacheKey);
        window.localStorage.removeItem(cacheKey + 'cachettl');
        ttl = 'expired';
    }

    var value = window.localStorage.getItem(cacheKey);
    if (value) {
        //In the cache? So get it, apply success callback & abort the XHR request
        // parse back to JSON if we can.
        if (options.dataType.indexOf('json') === 0) value = JSON.parse(value);
        options.success(value);
        // Abort is broken on JQ 1.5 :(
        jqXHR.abort();
    } else {

        //If it not in the cache, we change the success callback, just put data on localstorage and after that apply the initial callback
        if (options.success) {
            options.realsuccess = options.success;
        }
        options.success = function (data) {
            var strdata = data;
            if (this.dataType.indexOf('json') === 0) strdata = JSON.stringify(data);
            window.localStorage.setItem(cacheKey, strdata);
            if (options.realsuccess) options.realsuccess(data);
        };

        // store timestamp
        if (!ttl || ttl === 'expired') {

            lifespan = options.cacheTTLType || 's';

            switch (lifespan)
            {
                case 'm':
                    lifetl *= 60; // minutes
                    break;
                case 'h':
                    lifetl *= 3600; // hours
                    break;
            }
            window.localStorage.setItem(cacheKey + 'cachettl', +new Date() + 1000 * lifetl);
        }
    }
});
function cleanOldStorage(){
    if(window.localStorage.toString()=='[object Storage]')
    {
        for(i=window.localStorage.length-1; i >=0; i--)
        {
            key = window.localStorage.key(i);
            if(key && key.match(/cachettl/i)){
                value = window.localStorage.getItem(key);
                if(value < +new Date())
                {
                    window.localStorage.removeItem(key);    console.log(key + '  removed');
                    window.localStorage.removeItem(key.replace('cachettl',''));   console.log(key + 'cachettl  removed');
                }
            }
        }
    }
    else if(window.localStorage.items) {
        for(var key in window.localStorage.items){
            if(key && key.match(/cachettl/i)){
                value = window.localStorage.items[key];
                if(value < +new Date())
                {
                    window.localStorage.removeItem(key);     console.log(key + '  removed');
                    window.localStorage.removeItem(key.replace('cachettl',''));
                }
            }
        }
    }
}

function customStorage() {}

customStorage.prototype.items = Object();

customStorage.prototype.setItem = function (key, response) {
    this.items[key] = Object();
    this.items[key].response = response;
    return true;
}
customStorage.prototype.removeItem = function (key)
{
    if(this.items[key])
    {
        this.items[key] = null;
    }
}

customStorage.prototype.getItem = function (key) {

    // if cache does not exist
    if (this.items[key] == null)
        return false;

    // everything is passed - lets return the response
    return this.items[key].response;
}

customStorage.prototype.clear = function () {
    // flush all cache
    this.items = Object();
    return true;
}