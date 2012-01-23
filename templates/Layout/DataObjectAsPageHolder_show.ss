<div class="typography">
	
	<% if Children %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>

			<div id="Breadcrumbs">
			   	<p>$Breadcrumbs</p>
			</div>
			
			<% control Item %>
				<h2>$Title</h2>
			<% end_control %>
	
	<% if Children %>
		</div>
	<% end_if %>

</div>
