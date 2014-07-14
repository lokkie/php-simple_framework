
var cookieManager = function (domain, cookieTtl, rewriteCookie) {
	this.workDomain = domain;
	this.cookieTtl = cookieTtl === undefined ? 3600*24*365 : cookieTtl;
	this.rewriteCookie = rewriteCookie === undefined ? true : rewriteCookie;
};

cookieManager.prototype.setCookie = function(name, value, options, deleteMode) {
	if (deleteMode === undefined) { 
		deleteMode = false;
	}
	if (this.getCookie(name) !== undefined && (this.rewriteCookie || deleteMode)) {
	    options = options || {};
	    var expires = options.expires;
	    if (typeof expires == "number" && expires) {
	        var d = new Date();
	        d.setTime(d.getTime() + expires*1000);
	        expires = options.expires = d;
	    }
	    if (expires && expires.toUTCString) {
	        options.expires = expires.toUTCString();
	    }
	    value = encodeURIComponent(value);
	    var updatedCookie = name + "=" + value;
	    for(var propName in options) {
	        updatedCookie += "; " + propName;
	        var propValue = options[propName];
	        if (propValue !== true) {
	            updatedCookie += "=" + propValue;
	        }
	    }
	    document.cookie = updatedCookie;
	}
};

cookieManager.prototype.getCookie = function(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
};


cookieManager.prototype.deleteCookie = function(name) {
    this.setCookie(name, null, { expires: -1 }, true);
};

cookieManager.prototype.setRewriteMode = function(value) {
	var oldValue = this.rewriteCookie;
	this.rewriteCookie = value;
	return oldValue;
};