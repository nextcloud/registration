document.addEventListener('DOMContentLoaded', function() {
	// Password toggle
	$('#showadminpass').click(() => {
		const passwordTextField = $('#password')
		if (passwordTextField.attr('type') === 'password') {
			passwordTextField.attr('type', 'text')
		} else {
			passwordTextField.attr('type', 'password')
		}
	})

	// Disable submit after first click
	$('form').submit(() => {
		// prevent duplicate form submissions
		$(this).find(':submit').attr('disabled', 'disabled')
		$(this).find(':submit')[0].value = t('registration', 'Loading …')
	})
})
