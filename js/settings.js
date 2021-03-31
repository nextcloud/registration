$(document).ready(function() {
  function saveSettings() {
    OC.msg.startSaving('#registration_settings_msg');
    $.ajax({
      url: OC.generateUrl('/apps/registration/settings'),
      type: 'POST',
      data: $('#registration_settings_form').serialize(),
      success: function(data){
        OC.msg.finishedSaving('#registration_settings_msg', data);
      },
      error: function(data){
        OC.msg.finishedError('#registration_settings_msg', data.responseJSON.message);
      }
    });
  }

  $('#registration_settings_form').change(saveSettings);
  $('#registration').keypress(function(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
    }
  });
  $('#email_is_login').change(function(event) {
    if (event.target.checked) {
      $('.login-name-policy').addClass('hidden');
    } else {
      $('.login-name-policy').removeClass('hidden');
    }
  });
});
