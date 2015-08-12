// contact
$('#contact-form').on('submit', function(e) {
    // dont visit action="..." page
    e.preventDefault();

    var botQuestion = $('#contact-bot-question').html().split(' + ');
    if (parseInt(botQuestion[0]) + parseInt(botQuestion[1]) != $('#contact-bot-protection').val())
    {
        alert("You haven't answered the bot question correctly.");
        return;
    }

    // prevent multiple submissions
    $('#contact-submit').prop('disabled', true);
    $('#contact-submit').attr('value', 'Sending...');

    $.post('server.php', { 
        action: 'contact',
        name: $('#contact-name').val(), 
        email: $('#contact-email').val(),
        subject: $('#contact-subject').val(),
        message: $('#contact-message').val()
    }).done(function(data) { $('#contact-body').html(data); });
});   

// submit forms loading screen
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