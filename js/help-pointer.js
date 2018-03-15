jQuery(document).ready( function($) {

	var pointer = wssbiHelpPointer;

	options = $.extend( pointer.options, {
		close: function() {
			$.post( ajaxurl, {
				pointer: pointer.p_id,
				action: 'dismiss-wp-pointer'
			});
		}
	});

	$(pointer.target).pointer( options ).pointer('open');

});