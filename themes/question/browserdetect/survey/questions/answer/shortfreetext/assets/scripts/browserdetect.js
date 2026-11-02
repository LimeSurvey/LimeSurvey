
var BrowserCheck = function(options){
    "use strict";
    options = options || {};
    var platform = "Unknown";
    var platform_version = "X";
    var platformSet = false;
    var browser_version = "";
    var browser = "";
    var size_class = "";
    // Client hints let Bowser tell apart browsers sending the same user agent as Chrome (e.g. Brave)
    var parser = bowser.getParser(window.navigator.userAgent, window.navigator.userAgentData || false);
    var result = parser.getResult();

    // public methods
    var getRuntimeInfo = function(){
            return result;
        },
        getVersion = function(){
            // Keep the major.minor format of the answers saved with Bowser 1.x
            browser_version = result.browser.version ? result.browser.version.split('.').slice(0, 2).join('.') : 'X.X.X';
            return browser_version;
        },
        getBrowser = function(){
            browser = result.browser.name;
            return browser;
        },
        getPlatform = function(){
            if(platformSet!==true)
                _parsePlatformInfo();

            return platform;
        },
        getPlatformVersion = function(){
            if(platformSet!==true)
                _parsePlatformInfo();

            return platform_version;
        },
        getSizeClass = function(){
            if(platformSet!==true)
                _parsePlatformInfo();

            return size_class;
        },
        getBrowserInfoShort = function (){
            return  getBrowser() + " ("+ getVersion() +")";
        },
        getBrowserInfoLong = function (){
            return  getBrowser() + " ("+ getVersion() +") | "+getPlatform()+" ("+getPlatformVersion()+")";
        },
        /// private methods
        _getSizeClass = function(defaultClass){
            if(result.platform.type === 'mobile' || result.platform.type === 'tablet'){
                return result.platform.type;
            }
            return defaultClass;
        },
        _parsePlatformInfo = function(){
            var largeScreenClass = window.screen.width > 1920 ? 'desktop' : 'laptop';
            platform_version = result.os.version || "unknown";
            switch(result.os.name){
                case "macOS" :
                    platform = "Mac OSX";
                    size_class = largeScreenClass;
                    break;
                case "Windows" :
                    platform = "Microsoft Windows";
                    platform_version = result.os.versionName || "unknown";
                    size_class = largeScreenClass;
                    break;
                case "Windows Phone" :
                    platform = "Microsoft Windows Phone";
                    size_class = _getSizeClass('tablet');
                    break;
                case "Chrome OS" :
                    platform = "Chromebook with Chrome OS";
                    size_class = largeScreenClass;
                    break;
                case "Android" :
                    platform = "Android";
                    size_class = _getSizeClass('laptop');
                    break;
                case "WebOS" :
                    platform = "WebOS";
                    size_class = _getSizeClass('desktop');
                    break;
                case "Tizen" :
                    platform = "Tizen";
                    size_class = _getSizeClass('tablet');
                    break;
                case "Linux" :
                    platform = "Linux";
                    size_class = _getSizeClass('laptop');
                    break;
                case "iOS" :
                    switch((result.platform.model || "").toLowerCase()){
                        case "iphone" :
                            platform = "Apple iPhone";
                            break;
                        case "ipad" :
                            platform = "Apple iPad";
                            break;
                        case "ipod" :
                            platform = "Apple iPod";
                            break;
                        default:
                            platform = "Apple iOS product";
                    }
                    size_class = _getSizeClass('laptop');
                    break;
                case "BlackBerry" :
                    platform = "Blackberry";
                    size_class = _getSizeClass('laptop');
                    break;
                case "Bada" :
                    platform = "Samsung Bada OS";
                    size_class = _getSizeClass('laptop');
                    break;
                default:
                    platform = result.os.name || "Unknown platform";
                    platform_version = result.os.name ? platform_version : "unknown";
                    size_class = _getSizeClass(window.screen.width > 1920 ? 'desktop' : ( window.screen.width > 1280 ? 'laptop' : (window.screen.width > 720 ? 'tablet' : 'mobile')));
            }
            platformSet = true;
        };

    return {
        getBrowserInfoShort : getBrowserInfoShort,
        getBrowserInfoLong  : getBrowserInfoLong,
        getRuntimeInfo      : getRuntimeInfo,
        getVersion          : getVersion,
        getBrowser          : getBrowser,
        getPlatform         : getPlatform,
        getPlatformVersion  : getPlatformVersion,
        getSizeClass        : getSizeClass
    };
};
