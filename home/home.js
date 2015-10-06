"use strict";

var Home = {};

$(window).on('page-user', function(event, pageName, subPageName) {
  // sub page user called
  //
});

// feed
Home.Feed = {};

// feed HTML element
Home.Feed.domElement = $(page['home']).find('#feed'), 
    Home.Feed.noFeedContentString = '<p>Nothing new since last login.</p>',
    Home.Feed.since = -1; // show the feed since unix time (-1 = since last login)


// refresh feed
//
// refreshes the feed DOM element
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param function|undefined callback: callback with Ajax-request response as first parameter
function refreshFeed(l) { Home.Feed.download(l); }
Home.Feed.download = function(showLoadingInformation, callback) {
  if (showLoadingInformation) {
    Home.Feed.domElement.html(loading);
  }

  // send request to get the feed
  jQuery.ajax('server.php', {
    data: {
      action: 'get-feed',
      since: Home.Feed.since
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);

    // sort feed events by time
    data.events.sort(function(a, b) { 
      if (a.time < b.time) return -1; 
      if (a.time > b.time) return 1; 
      return 0;
    });
    
    
    var feedHtml = '';
    
    // go through the array the other way around to display newest first
    for (var i = data.events.length - 1; i >= 0; i--) { 
      var feedItem = data.events[i], info = data.events[i].info;
      feedHtml += '<tr><td>';
      
      // depending on the type of the feed item show a different text and a different image
      switch (feedItem.type) {
        case 0: // user added
          feedHtml += '<img src="img/users.svg"></td><td>' + info.firstname + ' ' + info.lastname + ' has added you.';
          break;
        case 1: // shared list
          feedHtml += '<img src="img/share.svg"></td><td>' + info.user.firstname + ' ' + info.user.lastname + ' gave you permissions to ' + ((info.permissions == 1)?'edit':'view') + ' their list <a href="#/word-lists/' + info.list.id + '">' + info.list.name + '</a>.';
          break;
        case 2: // added word
          feedHtml += '<img src="img/add.svg"></td><td>' + info.user.firstname + ' ' + info.user.lastname + ' has added ' + info.amount.toEnglishString() + ' word' + ((info.amount !== 1) ? 's' : '') + ' to ' + ((info.user.id === info.list.creator) ? 'their' : ((info.list.creator === data.user) ? 'your' : info.list_creator.firstname + '\'s')) + ' list <a href="#/word-lists/' + info.list.id + '">' + info.list.name + '</a>.';
          break;
      }
      // time of the feed element
      feedHtml += '&nbsp;<span class="feed-time">' + (new Date(feedItem.time * 1000)).toDefaultString() + '</span></td></tr>';
    }
    
    // nothing in the feed
    if (feedHtml.length === 0) feedHtml = Home.Feed.noFeedContentString;
    else feedHtml = '<table class="feed-table box-table no-flex">' + feedHtml + '</table>';
    
    Home.Feed.domElement.html(feedHtml);
    
    if (callback !== undefined)
      callback(data);
  });
}

// button load whole feed event listener
$(page['home']).find('#feed-load-all').on('click', function() {
  $(page['home']).find('#feed-load-all').prop('disabled', true).attr('value', 'Loading all...');
  Home.Feed.since = 0;
  Home.Feed.download(false, function() {
    $(page['home']).find('#feed-load-all').hide();
  });
});



// recently used
Home.RecentlyUsed = {};

Home.RecentlyUsed.domElement = $(page['home']).find('#recently-used'), Home.RecentlyUsed.nothingString = '<p>No recently used lists found.</p>';

// refresh recently used
//
// refreshes the div content showing the recently used lists of a user 
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function refreshRecentlyUsed(l) { Home.RecentlyUsed.download(l); }
Home.RecentlyUsed.download = function(showLoadingInformation) {
  if (showLoadingInformation) 
    Home.RecentlyUsed.domElement.html(loading);
  
  // send request to get the feed
  jQuery.ajax('server.php', {
    data: {
      action: 'get-last-used-n-lists',
      limit: 8
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
    
    // no recently used lists
    if (data.length === 0) {
      Home.RecentlyUsed.domElement.html(Home.RecentlyUsed.nothingString);
    }
    else {
      // add a table
      var html = '<table class="box-table cursor-pointer">';

      // iterate through all recently used lists and add <tr> element
      for (var i = 0; i < data.length; i++) {
        html += '<tr data-list-id="' + data[i].id + '"><td>' + data[i].name + '</td></tr>';
      }
      html += '</table>';

      // update the DOM
      Home.RecentlyUsed.domElement.html(html);

      // add event listener to make lists clickable (link to #/word-lists/xxx)
      Home.RecentlyUsed.domElement.find('tr').on('click', function() {
        window.location.href = '#/word-lists/' + $(this).data('list-id');
      });
    }
  });
};


// initial loading
Home.Feed.download(true);
Home.RecentlyUsed.download(true);