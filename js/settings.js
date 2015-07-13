$(document).ready(function() {
	function saveSettings() {
		var post = $('#registration').serialize();
		console.log(post);
		$.post(OC.generateUrl('/apps/registration/settings'), post);
	}

	$('#registered_user_group').change(saveSettings);
	$('#allowed_domains').change(saveSettings);
});
