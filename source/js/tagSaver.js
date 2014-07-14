/**
 * Created by alex on 30.03.14.
 */

var tagSaver = function (tagList, workDomain, cookie_ttl, rewriteCookie) {
	if (cookie_ttl === undefined) {
        cookie_ttl = 3600*24*365; // default: year ttl
    }
    if (rewriteCookie === undefined) {
    	rewriteCookie = true;
    }
	this.cookieManager = new cookieManager(workDomain, cookie_ttl, rewriteCookie);
	this.tagList = tagList;
	this.scan();
}

tagSaver.prototype.scan = function () {
    for (var i = 0, l = this.tagList.length; i < l; i++) {
        var tagName = this.tagList[i];
        var tagValue = null;
        if (null != (tagValue = this.checkTag(tagName))) {
        	writeCookie = true;
        	if (this.options.multimode) {
        		var mmCookie = this.options.mm_cookie_prefix + tagName;
        		var multimodeTime = this.cookieManager.getCookie(mmCookie);
        		var unix = Math.round(new Date()/1000);
        		if (multimodeTime == undefined) {
        			this.cookieManager.setCookie(mmCookie,  unix + this.options.multimode_ttl, {'expires': this.cookie_ttl, 'domain':this.workDomain, 'path':'/'});
        		} else if (multimodeTime < unix) {
        			writeCookie = false;
    			} 
        	}
        	if (writeCookie) {
        		this.cookieManager.setCookie(tagName, tagValue, {'expires': this.cookie_ttl, 'domain':this.workDomain, 'path':'/'});
        	}
        }
    }
};

tagSaver.prototype.checkTag = function (name) {
    if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
        return decodeURIComponent(name[1]);
    else
        return null;
};

tagSaver.options = {
	'multimode':false,
	'mm_cookie_prefix':'mm_saver_',
	'multimode_ttl':3600*24
};

tagSaver.configure = function(options) {
	for (var k in options) {
		this.options[k] = options[k];
	}
};
