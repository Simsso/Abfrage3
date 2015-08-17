// contact form submit event lsitener
$('#contact-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // check if the bot question was answered correctly
  var botQuestion = $('#contact-bot-question').html().split(' + ');
  if (parseInt(botQuestion[0]) + parseInt(botQuestion[1]) != $('#contact-bot-protection').val())
  {
    alert("You haven't answered the bot question correctly."); // inform the user
    return; // abort sending
  }

  // prevent multiple submissions
  $('#contact-submit').prop('disabled', true);
  $('#contact-submit').attr('value', 'Sending...');

  // send data to the server
  $.post('server.php', {
    action: 'contact',
    name: $('#contact-name').val(),
    email: $('#contact-email').val(),
    subject: $('#contact-subject').val(),
    message: $('#contact-message').val()
  }).done(function(data) {
    $('#contact-body').html(data); // after sending replace the form with the server response
  });
});

// submit forms loading screen
// when submitting the login or sign up form show a nice loading screen
$('form[data-submit-loading=true]').on('submit', function(e) {
  $('body').append(loadingFullscreen);
  $('.sk-three-bounce.fullscreen').css('opacity', '1');

  // delay form submit
  var form = this;
  e.preventDefault();
  setTimeout(function () {
    form.submit();
  }, 500);
});
