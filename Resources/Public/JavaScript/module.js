jQuery(function() {

	function getSelectedUids() {
		var selected = new Array();
		jQuery('#formhandler-module .mark-row:checked').each(function() {
			selected.push(jQuery(this).attr("value"));
		});
		return selected.join(",");
	}
	jQuery('#formhandler-module .filterForm .select-all-pages').click(function() {
		jQuery(this).closest('.input-group').find('INPUT.form-control').val("");
	});
	jQuery('#formhandler-module A.select-all').click(function(e) {
		e.preventDefault();
		var allCheckboxes = jQuery('#formhandler-module .mark-row');
		var activeCheckboxes = jQuery('#formhandler-module .mark-row:checked');
		if(allCheckboxes.length === activeCheckboxes.length) {
			allCheckboxes.prop("checked", false);
		} else {
			allCheckboxes.prop("checked", true);
		}
	});
	jQuery('#formhandler-module .process-selected-actions A.pdf').click(function(e) {
		e.preventDefault();
		var selectedUids = getSelectedUids();
		if(selectedUids.length > 0) {
			jQuery('#process-selected-form-export INPUT.filetype').attr("value", "pdf");
			jQuery('#process-selected-form-export INPUT.logDataUids').attr("value", selectedUids);
			jQuery('#process-selected-form-export').submit();
		}
	});
	jQuery('#formhandler-module .process-selected-actions A.csv').click(function(e) {
		e.preventDefault();
		var selectedUids = getSelectedUids();
		if(selectedUids.length > 0) {
			jQuery('#process-selected-form-export INPUT.filetype').attr("value", "csv");
			jQuery('#process-selected-form-export INPUT.logDataUids').attr("value", selectedUids);
			jQuery('#process-selected-form-export').submit();
		}
	});
	jQuery('#formhandler-module .process-selected-actions A.delete').click(function(e) {
		e.preventDefault();
		var selectedUids = getSelectedUids();
		if(selectedUids.length > 0) {
			jQuery('#process-selected-form-delete INPUT.logDataUids').attr("value", selectedUids);
			jQuery('#process-selected-form-delete').submit();
		}
	});
});
