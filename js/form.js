$('#show-password').click(function () {
	if ( $('#password').attr('type') == "password" ) {
		$('#password').attr('type', 'text');
	} else {
		$('#password').attr('type', 'password');
	}
});


var timezones = moment.tz.names();

$('#timezone').append('<option value="automatic">' + t('registration', 'Automatic') + ' (' + moment.tz.guess() + ')</option>');

$.each(timezones, function(index, timezone) {
	$('#timezone').append('<option value="' + timezone + '">' + timezone + '</option>');
});

var timezone = $('#timezone').attr('data-value');
$('#timezone').val(timezone);
var language = $('#language').attr('data-value');
if (language === '') {
	language = window.navigator.userLanguage || window.navigator.language;
}
$('#language').val(language.substr(0, 2));
var country = $('#country').attr('data-value');
$('#country').val(country);
