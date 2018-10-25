$('#show-password').click(function () {
	if ( $('#password').attr('type') == "password" ) {
		$('#password').attr('type', 'text');
	} else {
		$('#password').attr('type', 'password');
	}
});


var timezones = moment.tz.names();


var opts_list = $('#timezone').find('option');
opts_list.sort(function(a, b) { return $(a).text().localeCompare($(b).text()); });
$.each(opts_list, function(index, timezone) {
	var parts = $(timezone).text().split('/');
	$(timezone).remove();

	if (parts.length === 1) {
		if ($('#group-global').length === 0) {
			$('#timezone').append($('<optgroup/>', {
				id: 'group-global',
				label: t('registration', 'Global')
			}));
		}

		$('#group-global').append($('<option/>', {
			value: $(timezone).val(),
			text : $(timezone).text()
		}));
	} else {
		var group = $(timezone).val().split('/', 1);
		if ($('#group-' + group).length === 0) {
			$('#timezone').append($('<optgroup/>', {
				id: 'group-' + group,
				label: t('registration', group)
			}));
		}

		$('#group-' + group).append($('<option/>', {
			value: $(timezone).val(),
			text : $(timezone).text()
		}));
	}
});

var guess = moment.tz.guess();
if (guess.indexOf('/') !== -1) {
	var parts = guess.split('/');
	if (parts.length === 2) {
		guess = parts[0] + ' / ' + parts[1];
	}
}
$('#timezone').prepend('<option value="automatic">' + t('registration', 'Automatic') + ' (' + t('registration', guess) + ')</option>');


var timezone = $('#timezone').attr('data-value');
$('#timezone').val(timezone);
var language = $('#language').attr('data-value');
if (language === '') {
	language = window.navigator.userLanguage || window.navigator.language;
}
$('#language').val(language.substr(0, 2));
var country = $('#country').attr('data-value');
$('#country').val(country);
