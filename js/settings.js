$(document).ready(function() {
	function saveSettings() {
		var post1 = $('#registered_user_group').serialize();
		var post2 = $('#allowed_domains').serialize();

		$.post(OC.generateUrl('/apps/registration/settings'), post1,post2);
	}

	$('#registered_user_group').change(saveSettings);
	$('#allowed_domains').change(saveSettings);
});
