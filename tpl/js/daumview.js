

function doSyncDaumviewCategory() {
	
		exec_xml(
		'upgletyle_plugin_daumview',
		'syncDaumviewCategories',
		{},
		function(ret_obj){
			var error = ret_obj['error'];
			var message = ret_obj['message'];
			alert(message);
		},
		['error','message']
	);
}