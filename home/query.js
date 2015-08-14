 // loads labels from the server
 function getQuerySelectionLabels(callback) {
    jQuery.ajax('server.php', {
        data: {
            action: 'get-labels-of-user'
        },
        type: 'GET',
        error: function(jqXHR, textStatus, errorThrown) {

        }
    }).done(function(data) {
        console.log(data);
        var labels = jQuery.parseJSON(data);
        
        callback(labels);
    });
}