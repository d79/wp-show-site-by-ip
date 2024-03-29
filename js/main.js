'use strict';

let wssbi_form_changed = false;

jQuery(document).ready(function($){

	/* TABS */
	const anchor = window.location.hash;
	let active = 0;
	const $tabs = $('.nav-tab-wrapper');
	$('form .form-table').removeClass('hidden');
	if(anchor)
		$('a', $tabs).each(function(i){
			if ($(this).attr('href') == anchor) active = i;
		});
	$('a', $tabs).eq(active).addClass('nav-tab-active');
	$('form > .form-table').not(':eq('+active+')').hide();
	// trick for correct referer
	const $referer = $('input[name=_wp_http_referer]');
	$('form').after('<input id="orig_referer" type="hidden" value="">');
	const $orig = $('#orig_referer');
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
	const editor = ace.edit("wssbi_head");
	editor.setTheme("ace/theme/tomorrow");
	editor.getSession().setMode("ace/mode/html");
	editor.getSession().setUseWorker(false);
	editor.setHighlightActiveLine(false);
	editor.getSession().setUseWrapMode(true);
	editor.setShowPrintMargin(false);
	const textarea = $('#wssbi_head_textarea');
	editor.getSession().setValue(textarea.val());
	editor.getSession().on('change', function(){
		textarea.val(editor.getSession().getValue());
	});
	editor.on("change", function() {
		window.wssbi_form_changed = true;
	});

	const editor2 = ace.edit("wssbi_iplist");
	editor2.setTheme("ace/theme/tomorrow");
	editor2.getSession().setMode("ace/mode/html");
	editor2.getSession().setUseWorker(false);
	editor2.setHighlightActiveLine(false);
	editor2.getSession().setUseWrapMode(true);
	editor2.setShowPrintMargin(false);
	const textarea2 = $('#wssbi_iplist_textarea');
	editor2.getSession().setValue(textarea2.val());
	editor2.getSession().on('change', function(){
		textarea2.val(editor2.getSession().getValue());
	});
	editor2.on("change", function() {
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