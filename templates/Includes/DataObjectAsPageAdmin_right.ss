<div id="ModelAdminPanel">

<% if EditForm %>
	$EditForm
<% else %>
  <div id="list_view_loading">Loading...</div>
	<form id="Form_EditForm" action="admin?executeForm=EditForm" method="post" enctype="multipart/form-data">		
	</form>
<% end_if %>

</div>

<p id="statusMessage" style="visibility:hidden"></p>