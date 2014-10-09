<div class="pure-g">
	<div id="twist-spinner"></div>
	<div class="pure-u-1-1">
		<div	id="twist-tool-container"
				data-ajax-url="<?php echo admin_url( 'admin-ajax.php' ); ?>"
				data-templates-root="<?php echo TWIST__PLUGIN_URL."assets/views/" ?>"
		>
			<div class="pure-menu pure-menu-open pure-menu-horizontal" id="tool-tabs">
				<ul>
					<li><a class="list-receivers-link  tab-link" href="#list-receivers" data-target="#list-receivers-pane">Recipients</a></li>
					<li><a class="add-receiver-link tab-link" href="#add-new-receiver" data-target="#add-new-receiver-pane">Add</a></li>
					<li><a class="send-message-link tab-link" href="#send-message" data-target="#send-message-pane">Send Message</a></li>
					<li><a class="messages-log-link tab-link" href="#messages-log" data-target="#messages-log-pane">Messages Log</a></li>
				</ul>	
			</div>
			
			<div class="pure-tab-panes" id="tool-tabs-contents">
				<div class="pure-tab fade active in" id="list-receivers-pane">
					<div id="list-receivers-container" class="tool-container">
						
					</div>
				</div>
				<div class="pure-tab fade" id="add-new-receiver-pane">
					<div id="add-receiver-container" class="tool-container">
						<form class="form" role="form" id="add-receiver-form">
							<div class="message-pane hide"></div>
							<table class="form-table">
								<tr>
									<th scope="row">First Name</th>
									<td>
										<input type="text" class="pure-input-2-3" name="firstName" id="firstName" placeholder="First Name">	
									</td>
								</tr>
								<tr>
									<th scope="row">Last Name</th>
									<td>
										<input type="text" class="pure-input-2-3" name="lastName" id="lastName" placeholder="Last Name">
									</td>
								</tr>
								<tr>
									<th scope="row">Phone Number</th>
									<td>
										<input type="text" class="pure-input-2-3" name="phoneNumber" id="phoneNumber" placeholder="+XXX (XXX) - XXX - XXXX">
									</td>
								</tr>
								<tr>
									<th scope="row">Dept/Company</th>
									<td>
										<input type="text" class="pure-input-2-3" name="department" id="department">
									</td>
								</tr>
								<tr>
									<th scope="row"></th>
									<td>
										<div class="text-right">
											<button type="submit" class="button-primary">Save</button>		
										</div>	
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
				<div class="pure-tab fade" id="send-message-pane">
					<div id="send-message-container" class="tool-container">
						
					</div>
				</div>
				<div class="pure-tab fade" id="messages-log-pane">
					<div id="messages-log-container" class="tool-container">
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="fade" id="twist-modal-dialog">
	<div class="twist-modal-content">
		<div class="twist-modal-header">
			<h2 class="twist-modal-title"></h2>
		</div>
		<div class="twist-modal-body">
			
		</div>
		<div class="twist-modal-footer">
			
		</div>
	</div>
</div>

<script id="message-template" type="text/x-handlebars-template">
	{{#isSuccessResponse status}}
		<div class="updated">
			{{message}}
		</div>	
	{{else}}
		<div class="error">
			{{message}}
			<ul>
				{{#each errors}}
				<li>{{this}}</li>
				{{/each}}
			</ul>		
		</div>	
	{{/isSuccessResponse}}
</script>
