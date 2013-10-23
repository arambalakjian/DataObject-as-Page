<% if $Items.MoreThanOnePage %>
	<div id="pageNumbers">
		<p>
			<% if $Items.NotFirstPage %>
				<a class="pages prev" href="$Items.PrevLink" title="View the previous page">Prev</a>
			<% end_if %>

	    	<% loop $Items.PaginationSummary(4) %>
				<% if $CurrentBool %>
					<span class="current">$PageNum</span>
				<% else %>
					<% if $Link %>
						<a href="$Link" title="View page number $PageNum">$PageNum</a>
					<% else %>
						&hellip;
					<% end_if %>
				<% end_if %>
			<% end_loop %>
		
			<% if $Items.NotLastPage %>
				<a class="pages next" href="$Items.NextLink" title="View the next page">Next</a>
			<% end_if %>
		</p>
	</div>
<% end_if %>
