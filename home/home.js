var $feed = $('#feed'), 
    noFeedContent = '<p>Nothing new to display here.</p>',
    feedSince = -1; // since last login

function refreshFeed(showLoadingInformation, callback) {
  if (showLoadingInformation) {
    $feed.html(loading);
  }

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-feed',
      since: feedSince
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    data = jQuery.parseJSON(data);
    console.log(data);
    var feedHtml = '';
    
    for (var i = 0; i < data.sharedLists.length; i++) {
      feedHtml += '<p>' + data.sharedLists[i].user.firstname + ' gave you permissions to ' + ((data.sharedLists[i].permissions == 1)?'edit':'view') + ' the list <span class="italic">' + data.sharedLists[i].list.name + '</span>.</p>';
    }
    
    for (var i = 0; i < data.usersAdded.length; i++) {
      feedHtml += '<p>' + data.usersAdded[i].firstname + ' has added you.</p>';
    }
    
    if (feedHtml.length === 0) feedHtml = noFeedContent;
    $feed.html(feedHtml);
    
    callback(data);
  });
}

$('#feed-load-all').on('click', function() {
  $('#feed-load-all').prop('disabled', true).attr('value', 'Loading all...');
  feedSince = 0;
  refreshFeed(false, function() {
    $('#feed-load-all').hide();
  });
});

refreshFeed(true, function() {});