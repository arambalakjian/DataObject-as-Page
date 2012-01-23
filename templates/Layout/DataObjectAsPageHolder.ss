<div class="typography">
	<% if Children %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>
	
	  	<% include BreadCrumbs %>
		
		<% control Items %>
			<h2>$Title</h2>
			<a href="$Link">View</a>
		<% end_control %>
		
	<% if Children %>
		</div>
	<% end_if %>
</div>
