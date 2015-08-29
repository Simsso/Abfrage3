var $feed = $('#feed'), 
    noFeedContent = '<p>Nothing new since last login.</p>',
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
          feedHtml += '<tr><td><img src="img/users.svg"></td><td>' + info.firstname + ' has added you.</td></tr>';
          break;
        case 1: // shared list
          feedHtml += '<tr><td><img src="img/share.svg"></td><td>' + info.user.firstname + ' gave you permissions to ' + ((info.permissions == 1)?'edit':'view') + ' their list <span class="italic">' + info.list.name + '</span>.</td></tr>';
          break;
        case 2: // added word
          feedHtml += '<tr><td><img src="img/add.svg"></td><td>' + info.user.firstname + ' has added ' + info.amount.toEnglishString() + ' word' + ((info.amount !== 1) ? 's' : '') + ' to ' + ((info.user.id === info.list.creator) ? 'their' : ((info.list.creator === data.user) ? 'your' : info.list_creator.firstname + '\'s')) + ' list <span class="italic">' + info.list.name + '</span>.</td></tr>';
          break;
      }
    }
    
    if (feedHtml.length === 0) feedHtml = noFeedContent;
    else feedHtml = '<table class="feed-table box-table">' + feedHtml + '</table>';
    
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