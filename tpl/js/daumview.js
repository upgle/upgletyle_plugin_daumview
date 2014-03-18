
function doSyncDaumview(doc_srl) {

    var params = new Array();
    params['doc_srl'] = doc_srl;
	
	exec_xml(
		'upgletyle_plugin_daumview',
		'syncDaumview',
		params,
		function(ret_obj){
			var error = ret_obj['error'];
			var message = ret_obj['message'];
			alert(message);
		},
		['error','message']
	);

}


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