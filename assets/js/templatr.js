(function(host, $, Handlebars){
	var templatesRoot;
	var templatesCache = {

	};

	var _setTemplatesRoot = function _setTemplatesRoot(rootPath) {
		templatesRoot = rootPath;
	};

	var _getTemplate = function _getTemplate(templateName) {
		var dfd = new jQuery.Deferred();
		if(templatesCache[templateName]) {
			dfd.resolve(templatesCache[templateName]);
		} else {
			if(!templatesRoot) {
				throw  "Templates path is not set.";
			}
			$.get(templatesRoot + templateName + ".html")
			.done(function(data) {
				var templateFn = Handlebars.compile(data);
				templatesCache[templateName] = templateFn;
				dfd.resolve(templateFn);
			}).fail(function(jqXhr, status){
				dfd.reject(status)
			});
		}
		return dfd.promise();
	};

	host.Templatr =  host.Templatr || {
		get: _getTemplate,
		setTemplatesRoot: _setTemplatesRoot
	};
})(window, jQuery, Handlebars);