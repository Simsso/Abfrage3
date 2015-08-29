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
    data = handleAjaxResponse(data);
    data.events.sort(function(a, b) { // sort by time of feed events
      if (a.time < b.time) return -1; 
      if (a.time > b.time) return 1; 
      return 0;
    });
    
    
    var feedHtml = '';
    
    for (var i = data.events.length - 1; i >= 0; i--) { // go through the array the other way around to display newest first
      var feedItem = data.events[i], info = data.events[i].info;
      switch (feedItem.type) {
        case 0: // user added
          feedHtml += '<p>' + info.firstname + ' has added you.</p>';
          break;
        case 1: // shared list
          feedHtml += '<p>' + info.user.firstname + ' gave you permissions to ' + ((info.permissions == 1)?'edit':'view') + ' their list <span class="italic">' + info.list.name + '</span>.</p>';
          break;
        case 2: // added word
          feedHtml += '<p>' + info.user.firstname + ' has added ' + info.amount + ' word' + ((info.amount !== 1) ? 's' : '') + ' to ' + ((info.user.id === info.list.creator) ? 'their' : ((info.user.id === data.user) ? 'your' : info.list_creator.firstname + '\'s')) + ' list <span class="italic">' + info.list.name + '</span>.</p>';
          break;
      }
    }
    
    if (feedHtml.length === 0) feedHtml = noFeedContent;
    $feed.html(feedHtml);
    
    if (callback !== undefined)
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