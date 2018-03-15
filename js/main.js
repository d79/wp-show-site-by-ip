'use strict';

var wssbi_form_changed = false;

jQuery(document).ready(function($){

	/* TABS */
	var anchor = window.location.hash;
	var active = 0;
	var $tabs = $('.nav-tab-wrapper');
	$('form .form-table').removeClass('hidden');
	if(anchor)
		$('a', $tabs).each(function(i){
			if ($(this).attr('href') == anchor) active = i;
		});
	$('a', $tabs).eq(active).addClass('nav-tab-active');
	$('form > .form-table').not(':eq('+active+')').hide();
	// trick for correct referer
	var $referer = $('input[name=_wp_http_referer]');
	$('form').after('<input id="orig_referer" type="hidden" value="">');
	var $orig = $('#orig_referer');
	$orig.val($referer.val());
	$referer.val($orig.val() + anchor);
	// click function
	$tabs.on('click', 'a:not(.nav-tab-active)', function(){
		$('a.nav-tab-active', $tabs).removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('form > .form-table').hide();
		$('form > .form-table').eq($(this).index()).show();
		$referer.val($orig.val() + $(this).attr('href'));
		return false;
	});
	/* /TABS */

	$('#wssbi_guide_link').click(function(){
		$('#contextual-help-link-wrap button').click();
		$("html, body").animate({ scrollTop: 0 }, "slow");
	});

	/* SAVE ALERT */
	$('#wssbi-form').on('change', 'input', function(){
		wssbi_form_changed = true;
	});
	$('#submit').on('click', function(){
		wssbi_form_changed = false;
	});
	$(window).on('beforeunload', function(event) {
		if(wssbi_form_changed) {
			event.returnValue = wssbiL10n.saveAlert;
			return wssbiL10n.saveAlert;
		}
	});

	/* INIT ACE EDITOR */
	var editor = ace.edit("wssbi_head");
	editor.setTheme("ace/theme/tomorrow");
	editor.getSession().setMode("ace/mode/html");
	editor.getSession().setUseWorker(false);
	editor.setHighlightActiveLine(false);
	editor.getSession().setUseWrapMode(true);
	editor.setShowPrintMargin(false);
	var textarea = $('#wssbi_head_textarea');
	editor.getSession().setValue(textarea.val());
	editor.getSession().on('change', function(){
		textarea.val(editor.getSession().getValue());
	});
	editor.on("change", function() {
		window.wssbi_form_changed = true;
	});

	/* DELETE OLD HTML */
	$('#wssbi-old-html-notice .forget').click(function() {
		if( confirm(wssbiL10n.confirm_forget) ) {
			$.post(wssbiL10n.ajax_url, { 'action': 'wssbi_forget_old_html' });
			$('#wssbi-old-html-notice').fadeOut('slow', function() {
				$(this).closest('tr').remove();
			});
		}
	});

	/* TLITE TOOLTIPS */
	if( typeof tlite == 'function' ) {
		tlite(function (el) {
			return el.classList.contains('wssbi-help-tip') && { grav: 's' };
		});
	}

});