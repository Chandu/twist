(function(host, $) {
	var onReadyFn = function() {
		var adminSettingsContainer = $("#twist-admin-settings-container");
		var pluginSlug = 	adminSettingsContainer.data("data-plugin-slug");
		var phoneInput = $("input.in-twist-phone", adminSettingsContainer)
		phoneInput.mask("+999 (999) - 999 - 99?99");
	};
	$(onReadyFn);
})(window, jQuery);