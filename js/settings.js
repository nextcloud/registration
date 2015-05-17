$(document).ready(function() {
	function saveSettings() {
		var post = $('#registered_user_group').serialize();

		$.post(OC.generateUrl('/apps/registration/settings'), post);
	}

	$('#registered_user_group').change(saveSettings);
});
