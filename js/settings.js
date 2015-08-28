$(document).ready(function() {
	function saveSettings() {
		var post = $('#registration').serialize();
		$.post(OC.generateUrl('/apps/registration/settings'), post);
	}

	$('#registered_user_group').change(saveSettings);
	$('#allowed_domains').change(saveSettings);
	$('#registration').keypress(function(event) {
		if (event.keyCode === 13) {
			event.preventDefault();
		}
	});
});
