
var BrowserCheck = function(options){
    "use strict";
    options = options || {};
    var platform = "Unknown";
    var platform_version = "X";
    var platformSet = false;
    var browser_version = "";
    var browser = "";
    var size_class = "";
    var platformFlags = ["mac","windows","windowsphone","chromeos","android","webos","tizen","sailfish","linux","iphone","ipad","ipod","ios","blackberry","firefoxos","bada"];
    
    platformFlags = options.platformFlags === undefined ? platformFlags : options.platformFlags;

    // public methods
    var getRuntimeInfo = function(){
            return bowser;
        },
        getVersion = function(){
            browser_version = bowser.version || 'X.X.X';
            return browser_version;
        },
        getBrowser = function(){
            browser = bowser.name;
            return browser;
        },
        getPlatform = function(){
            if(platformSet!==true)
                _recursePlatforms();
            
            return platform;
        },
        getPlatformVersion = function(){
            if(platformSet!==true)
                _recursePlatforms();
            
            return platform_version;
        },
        getSizeClass = function(){
            if(platformSet!==true)
                _recursePlatforms();
            
            return size_class;
        },
        getBrowserInfoShort = function (){
            return  getBrowser() + " ("+ getVersion() +")";
        },
        getBrowserInfoLong = function (){
            return  getBrowser() + " ("+ getVersion() +") | "+getPlatform()+" ("+getPlatformVersion()+")";
        },
        /// private methods
        _recursePlatforms = function(iterator){
            iterator = iterator || 0;
            if(iterator>=platformFlags.length){
                _parsePlatformInfo('unknown');       
            } 
            var flag = platformFlags[iterator];
            if(bowser[flag] == true){
                return _parsePlatformInfo(flag);
            }
            return _recursePlatforms(++iterator);

        },
        _parsePlatformInfo = function(platformShort){
            switch(platformShort){
                case "mac" : 
                    platform = "Mac OSX"
                    platform_version = bowser.osversion || "unknown";
                    size_class = window.screen.width > 1920 ? 'desktop' : 'laptop';
                    break;
                case "windows" : 
                    platform = "Microsoft Windows"
                    platform_version = bowser.osversion || "unknown";
                    size_class = window.screen.width > 1920 ? 'desktop' : 'laptop';
                    break;
                case "windowsphone" : 
                    platform = "Microsoft Windows Phone"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : 'tablet';
                    break;
                case "chromeos" : 
                    platform = bowser.chromeBook ? "Chromebook with Chrome OS" : "Chrome OS"
                    platform_version = bowser.osversion || "unknown";
                    size_class = window.screen.width > 1920 ? 'desktop' : 'laptop';
                    break;
                case "android" : 
                    platform = "Android"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop');
                    break;
                case "webos" : 
                    platform = "WebOS"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'desktop');
                    break;
                case "tizen" : 
                    platform = "Tizen"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : 'tablet';
                    break;
                case "sailfish" : 
                    platform = "SailfishOS"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : 'tablet';
                    break;
                case "linux" : 
                    platform = "Linux"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop');
                    break;
                case "iphone" : 
                    platform = "Apple iPhone"
                    platform_version = bowser.osversion || "unknown";
                    size_class = 'mobile';
                    break;
                case "ipad" : 
                    platform = "Apple iPad"
                    platform_version = bowser.osversion || "unknown";
                    size_class = 'tablet';
                    break;
                case "ipod" : 
                    platform = "Apple iPod"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : 'tablet';
                    break;
                case "ios" : 
                    platform = "Apple iOS product"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop');
                    break;
                case "blackberry" :
                    platform = "Blackberry"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop'); 
                    break;
                case "firefoxos" : 
                    platform = "FireFox OS"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop'); 
                    break;
                case "bada" : 
                    platform = "Samsung Bada OS"
                    platform_version = bowser.osversion || "unknown";
                    size_class = bowser.mobile ? 'mobile' : (bowser.tablet ? 'tablet' : 'laptop'); 
                    break;
                case "unknown": //fallthrough
                default: 
                    platform = "Unknown platform"
                    platform_version = "unknown";
                    size_class = window.screen.width > 1920 ? 'desktop' : ( window.screen.width > 1280 ? 'laptop' : (window.screen.width > 720 ? 'tablet' : 'mobile'));
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
