<div class="typography">
	
	<% if Children %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>
	
	  	<% include BreadCrumbs %>
		
		<ul>
		<% control Items %>
			<li>
				<h2>$Title</h2>
				<p>$Content.FirstParagraph</p>
				<a href="$Link">View</a>
			</li>
		<% end_control %>
		</ul>
		
	<% if Children %>
		</div>
	<% end_if %>
</div>
