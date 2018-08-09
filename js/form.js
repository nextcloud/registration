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
		var parts = timezone.split('/');
		if (parts.length === 1) {
			if ($('#group-global').length === 0) {
				$('#timezone').append($('<optgroup/>', {
					id: 'group-global',
					label: t('calendar', 'Global')
				}));
			}

			$('#group-global').append($('<option/>', {
				value: timezone,
				text : timezone
			}));
		} else {
			var group = timezone.split('/', 1);
			if ($('#group-' + group).length === 0) {
				$('#timezone').append($('<optgroup/>', {
					id: 'group-' + group,
					label: group
				}));
			}

			$('#group-' + group).append($('<option/>', {
				value: timezone,
				text : timezone
			}));
		}
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
