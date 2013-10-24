<div class="typography">
	
	<% if $Children %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>

			<div id="Breadcrumbs">
			   	<p>$Breadcrumbs</p>
			</div>
			
			<% loop $Item %>
				<h2>$Title</h2>
				
				$Content
			<% end_loop %>
	
	<% if $Children %>
		</div>
	<% end_if %>

</div>
