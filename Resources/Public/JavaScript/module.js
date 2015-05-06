jQuery(function() {

	function getSelectedUids(el) {
		var selected = new Array();
		if(el.attr("data-uid") && el.attr("data-uid").length > 0) {
			selected.push(el.attr("data-uid"));
		} else {
			jQuery('#formhandler-module .mark-row:checked').each(function() {
				selected.push(jQuery(this).attr("value"));
			});
		}
		return selected.join(",");
	}
	jQuery("#formhandler-module .filterForm .select-all-pages").on("click", function() {
		jQuery(this).closest('.input-group').find('INPUT.form-control').val("");
	});
	jQuery("#formhandler-module A.select-all").on("click", function(e) {
		e.preventDefault();
		var allCheckboxes = jQuery('#formhandler-module .mark-row');
		var activeCheckboxes = jQuery('#formhandler-module .mark-row:checked');
		if(allCheckboxes.length === activeCheckboxes.length) {
			allCheckboxes.prop("checked", false);
		} else {
			allCheckboxes.prop("checked", true);
		}
	});
	jQuery("#formhandler-module .process-selected-actions A.pdf").on("click", function(e) {
		e.preventDefault();
		var selectedUids = getSelectedUids();
		if(selectedUids.length > 0) {
			jQuery('#process-selected-form-export INPUT.filetype').attr("value", "pdf");
			jQuery('#process-selected-form-export INPUT.logDataUids').attr("value", selectedUids);
			jQuery('#process-selected-form-export').submit();
		}
	});
	jQuery("#formhandler-module .process-selected-actions A.csv").on("click", function(e) {
		e.preventDefault();
		var selectedUids = getSelectedUids();
		if(selectedUids.length > 0) {
			jQuery('#process-selected-form-export INPUT.filetype').attr("value", "csv");
			jQuery('#process-selected-form-export INPUT.logDataUids').attr("value", selectedUids);
			jQuery('#process-selected-form-export').submit();
		}
	});
	jQuery("#formhandler-module A.delete").on("click", function(e) {
		e.preventDefault();
		var infoElement = jQuery(this).find('SPAN.delete-info'); 
		var selectedUids = getSelectedUids(infoElement);
		if(selectedUids.length > 0) {
			var modal = top.TYPO3.Modal.confirm(infoElement.data('title'), infoElement.data('message'), top.TYPO3.Severity.warning, [
				{
					text: infoElement.data('button-close-text') || TYPO3.lang['button.cancel'] || 'Cancel',
					active: true,
					name: 'cancel'
				},
				{
					text: infoElement.data('button-ok-text') || TYPO3.lang['button.delete'] || 'Delete',
					btnClass: 'btn-warning',
					name: 'delete'
				}
			]);
			modal.on('button.clicked', function(e) {
				if (e.target.name === 'cancel') {
					top.TYPO3.Modal.dismiss();
				} else if (e.target.name === 'delete') {
					top.TYPO3.Modal.dismiss();
					jQuery('#process-selected-form-delete INPUT.logDataUids').attr("value", selectedUids);
					jQuery('#process-selected-form-delete').submit();
				}
			});
		}
	});
});
