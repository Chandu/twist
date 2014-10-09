(function(host, $, Templatr, Handlebars, undefined) {
	var phoneMask = "+999 (999) - 999 - 999?9";
	Handlebars.registerHelper('isSuccessResponse', function(val, options) {
		if(val == 0) {
			return options.fn(this);
		} else {
			return options.inverse(this);
		}
	});

	Handlebars.registerHelper('translateStatus', function(val, options) {
		if(val == 1) {
			return "Active";
		} else {
			return "Inactive";
		}
	});


	//http://stackoverflow.com/questions/17670279/set-selected-value-in-drop-down-using-handlebars-js
	Handlebars.registerHelper("equalsTo", function(v1, v2, options) { 
		if(v1 == v2) { return options.fn(this); } 
		else { return options.inverse(this); } 
	});
	
	var onReadyFn = function() {

		var twistSpinner = $("#twist-spinner");
		var twistToolContainer= $("#twist-tool-container");
		var ajaxUrl = twistToolContainer.data("ajaxUrl");
		var templatesRoot = twistToolContainer.data("templatesRoot");
		var listReceiversContainer = $("#list-receivers-container");
		var addReceiverForm = $("#add-receiver-form");
		var messageTemplateFn = Handlebars.compile($("#message-template").html());
		var twistModalDialog = $("#twist-modal-dialog");
		var twistModalBody = $(".twist-modal-body", twistModalDialog);
		var twistModalTitle = $(".twist-modal-title", twistModalDialog);
		var sendMessageContainer = $("#send-message-container");
		var messagesLogContainer = $("#messages-log-container");

		var toolTabs = $("#tool-tabs");
		var toolTabsContents = $("#tool-tabs-contents");

		Templatr.setTemplatesRoot(templatesRoot);

		addReceiverForm.find("#phoneNumber").mask(phoneMask);

		var loadReceivers = function loadReceivers() {
			$.getJSON(ajaxUrl, {
				 "action" : "get_twist_receivers"	
			}).done(function(data) {
				Templatr.get("list-receivers")
				.done(function(templateFn){
					listReceiversContainer.empty().append(templateFn(data.data));
				});
			});
		};

		var showLoadReceiversPanel = function showLoadReceiversPanel() {
			loadReceivers();
		};

		var showAddReceiverPanel = function showAddReceiverPanel() {

		};

		var showMessagesLogPanel = function showMessagesLogPanel(start, limit) {
			$.getJSON(ajaxUrl, {
				 "action" : "get_messages_log",
				 "start": start,
				 "limit": limit
			}).done(function(data) {
				Templatr.get("messages-log")
				.done(function(templateFn){
					var chunks = [];
					for(var i=0; i< data.total_pages; i++) {
						chunks.push(i+1);
					}
					data.chunks = chunks;
					messagesLogContainer.empty().append(templateFn(data));
				});
			});
		};

		var showSendMessagePanel = function showSendMessagePanel() {
			$.getJSON(ajaxUrl, {
				 "action" : "get_active_twist_receivers"	
			}).done(function(data) {
				Templatr.get("send-message")
				.done(function(templateFn){
					sendMessageContainer.empty().append(templateFn(data.data));
				});
			});
		};

		messagesLogContainer.on("click", "a.send-messages", function(e){
			e.preventDefault();
			twistToolContainer.find("a.send-message-link").click();
		});

		messagesLogContainer.on("click", "a.show-message-template", function(e){
			e.preventDefault();
			twistModalBody.empty().append($(this).closest("tr").data("messageTemplate"));
			twistModalDialog.modal();
		});

		messagesLogContainer.on("click", "a.page-link", function(e){
			e.preventDefault();
			var page = $(this).data("page");
			var limit = $(this).data("limit");
			showMessagesLogPanel(page * limit, limit);
		});

		listReceiversContainer.on("click", "a.add-receiver", function(e){
			e.preventDefault();
			twistToolContainer.find("a.add-receiver-link").click();
		});

		listReceiversContainer.on("click", "a.edit-receiver", function(e){
			e.preventDefault();
			$.getJSON(ajaxUrl, {
			 "action" : "get_twist_receiver",
			 "receiverId" : $(this).data("receiverId")
			}).done(function(data) {
				Templatr.get("edit-receiver")
				.done(function(templateFn) {
					twistModalTitle.html("Edit Receiver");
					twistModalBody.empty().append(templateFn(data.receiver));
					twistModalBody.find("#phoneNumber").mask(phoneMask);
					twistModalDialog.modal();
				});
			});
		});

		listReceiversContainer.on("click", "a.delete-receiver", function(e){
			e.preventDefault();
			var messagePane = $("div.message-pane", listReceiversContainer).addClass("hide");
			var $this = $(this);
			if(confirm("Are you sure you want to delete this recepient?")) {
				$.post(ajaxUrl, {
					"action" : "delete_twist_receiver",
					"receiverId":  $this.data("receiverId")
				}).done(function(data){
					messagePane.empty().append(messageTemplateFn(data)).removeClass("hide");
					if(data.status == 0) {
						$this.closest("tr").remove();
					}
				})
				.fail(function(jqXhr, status){
					messagePane.empty().append(messageTemplateFn({
						message: status
					})).removeClass("hide");
				});;
				
			}
		});

		addReceiverForm.on("submit", function(e) {
			e.preventDefault();
			var messagePane = $("div.message-pane", this).addClass("hide");
			
			var postData = $(this).serialize();
			postData  = postData + "&action=add_twist_receiver";
			$.post(ajaxUrl, postData)
			.done(function(data){
				messagePane.empty().append(messageTemplateFn(data)).removeClass("hide");
				addReceiverForm.find("input[type='text']").val("");
			})
			.fail(function(jqXhr, status){
				messagePane.empty().append(messageTemplateFn({
					message: status
				})).removeClass("hide");
			});
		} );

		twistModalBody.on("submit", "#edit-receiver-form", function(e){
			e.preventDefault();
			var messagePane = $("div.message-pane", twistModalBody).addClass("hide");
			$editReceiverForm = $(this);
			var postData = $editReceiverForm.serialize() + "&action=update_twist_receiver";
			$.post(ajaxUrl, postData)
			.done(function(data){
				messagePane.empty().append(messageTemplateFn(data)).removeClass("hide");
				if(data.status == 0) {
					loadReceivers();
				}
			})
			.fail(function(jqXhr, status){
				messagePane.empty().append(messageTemplateFn({
					message: status
				})).removeClass("hide");
			});
		}); 

		twistToolContainer.on("click", "a.tab-link", function (e) {
			e.preventDefault();
			var $this = $(this);
			toolTabsContents.find(".pure-tab").hide();
			toolTabsContents.find($(this).data("target")).show();
			if($this.is("a.list-receivers-link")) {
				showLoadReceiversPanel();
			} else if($this.is("a.add-receiver-link")) {
				showAddReceiverPanel();
			} else if ($this.is("a.send-message-link")) {
				showSendMessagePanel();
			} else if ($this.is("a.messages-log-link")) {
				showMessagesLogPanel();
			}
		});

		sendMessageContainer.on("click", "a.toggle-select", function(e){
			e.preventDefault();
			var $this = $(this);
			var select = $this.data("select");
			$("#message-receviers", sendMessageContainer).find("input.receiver-selection").prop("checked", parseInt(select, 10));
		});

		sendMessageContainer.on("click", "a.message-help", function(e) {
			e.preventDefault();
			$("#message-help").show("slow");
		});

		sendMessageContainer.on("click", "a.close-message-help", function(e) {
			e.preventDefault();
			$("#message-help").hide("slow");
		});

		sendMessageContainer.on("keyup", "#message", function(e) {
			$("#message-char-count").html($(this).val().length + " character/s entered.");
		});
		

		sendMessageContainer.on("submit", "#send-message-form", function(e){
			e.preventDefault();
			var messagePane = $("div.message-pane", sendMessageContainer).addClass("hide");
			var postData = $(this).serialize();
			postData  = postData + "&action=send_twist_messages";
			$.post(ajaxUrl, postData)
			.done(function(data){
				if(data.status == 0) {
					twistModalTitle.html("SMS Results");
					Templatr.get("send-message-response")
					.done(function(templateFn){
						twistModalBody.empty().append(templateFn(data));
						twistModalDialog.modal();
					});
				} else {
					messagePane.empty().append(messageTemplateFn(data)).removeClass("hide");
				}
			})
			.fail(function(jqXhr, status){
				messagePane.empty().append(messageTemplateFn({
					message: status
				})).removeClass("hide");
			});
		});



		twistToolContainer.find("a.tab-link").first().click();

	};

	$(onReadyFn);
})(window, jQuery, Templatr, Handlebars);