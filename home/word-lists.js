// const strings
var noWordListOutput = '<p class="spacer-top-15">You haven\'t created any wordlists yet.</p>';
var noSharedWordListOutput = '<p>There are no shared lists to show.</p>';
var listNotShared = '<p class="spacer-top-15">The selected list isn\'t shared with anyone. Only you can see it.</p>';
var noWordsInList = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var noWordsInListDisallowEdit = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var noLabels = '<p>You don\'t have any labels.</p>';

var shownListId = -1; // stores the id of the word list which is shown at the moment (-1 if none)
var shownListData = null; // stores the data (words, creation date, etc.) of the word list which is shown at the moment (null if none)
var labels = null; // stores the labels of the user

var expandedLabelsIds = []; // stores which labels were expanded to expand them after refreshing the label HTML element



// adds a new word list
function addWordList(name, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'add-word-list',
      name: name
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    data = jQuery.parseJSON(data); // parse JSON

    callback(data);
  });
}

// event listener for form which adds new word lists
$('#word-list-add-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable button and text box to prevent resubmission
  $('#word-list-add-name').prop('disabled', true);
  $('#word-list-add-button').prop('disabled', true).attr('value', 'Creating list...');

  // call the server contacting function
  addWordList($('#word-list-add-name').val(), function(data) {
    // finished callback
    // re-enable the button and the text box
    $('#word-list-add-name').prop('disabled', false).val('');
    $('#word-list-add-button').prop('disabled', false).attr('value', 'Create list');

    refreshListOfWordLists(false, function() {
      // handle buttons and background colors indicating which list is currently shown
      setAllListRowsAsNotActive();
      // highlight the lists row by adding active class and hide button to view the list
      $('#list-of-word-lists tr[data-list-id=' + data.id + ']').addClass('active').find('input[type=button]').first().hide();
    }); // refresh the list of word lists without loading information

    // load the word list which has just been added
    loadWordList(data.id, true, function() { }, true, true);
  });
});



// refresh list of word lists
function refreshListOfWordLists(showLoadingInformation, callback) {
  // loading information
  if (showLoadingInformation)
    $('#list-of-word-lists').html(loading);

  // reset all table row highlights and hidden buttons indicating which list is selected
  showNoListSelectedInfo();

  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.loadListOfWordLists.add(
    jQuery.ajax('server.php', {
      data: {
        action: 'list-of-word-lists'
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {
      console.log(data); // debugging
      data = jQuery.parseJSON(data); // parse JSON

      var output = "";
      // build HTML output string
      for (var i = 0; i < data.length; i++) { // add a row for each list
        output += '<tr data-action="edit" data-list-id="' + data[i].id + '" id="list-of-word-lists-row-' + data[i].id + '"><td>' + data[i].name + '</td></tr>';
      }

      // if there are no lists show the appropriate message
      if (output.length == 0) {
        output = noWordListOutput;
      }
      else {
        output = '<table class="box-table cursor-pointer"></tr>' + output + '</table>';
      }

      $('#list-of-word-lists').html(output); // update DOM with list of word lists

      // add event listeners for rows which have just been added
      $('#list-of-word-lists tr').on('click', function() {
        $row = $(this);

        if ($row.data('action') == 'edit') { // edit / show list button click
          // edit / view call load word list function
          loadWordList($row.data('list-id'), true, function() { }, true, true);
        }
      });
    })
  );
}


// handle buttons and background colors indicating which list is currently shown
function setAllListRowsAsNotActive() {
  $('#list-of-shared-word-lists tr, #list-of-word-lists tr').removeClass('active'); // un-highlights all table rows
}


// refresh list of shared word lists (with the user)
function refreshListOfSharedWordLists(showLoadingInformation) {
  if (showLoadingInformation)
    $('#list-of-shared-word-lists').html(loading);

  showNoListSelectedInfo();


  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.loadListOfSharedWordLists.add(
    jQuery.ajax('server.php', {
      data: {
        action: 'list-of-shared-word-lists-with-user'
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {
      console.log(data); // debug
      data = jQuery.parseJSON(data); // parse JSON

      var output = "";
      // build HTML output string
      for (var i = 0; i < data.length; i++) {
        // add table row of a single shared list
        output += '<tr data-action="' + ((data[i].permissions == 1)?'edit':'view') + '" data-list-id="' + data[i].id + '" data-sharing-id="' + data[i].sharing_id + '" id="list-of-shared-word-lists-row-' + data[i].sharing_id + '">';
        output += '<td>' + data[i].name + '</td>';
      }
      // if there are no shared lists show the appropriate message
      if (output.length == 0) {
        output = noSharedWordListOutput;
      }
      else {
        output = '<table class="box-table cursor-pointer">' + output + '</table>';
      }
      $('#list-of-shared-word-lists').html(output); // update the DOM

      // add event listeners for rows inside the list
      $('#list-of-shared-word-lists tr').on('click', function() {
        $row = $(this);

        // detect button type with the data-action="xxx" attribute

        // edit and view shared list button loadWordList method calls differentiate in the fourth parameter which tells the function if the list is editbable or can just be viewed
        // edit shared list button
        if ($row.data('action') == 'edit') { // edit / show list button click
          loadWordList($row.data('list-id'), true, function() { }, true, false);
        }

        // view shared list button
        else if ($row.data('action') == 'view') { // edit / show list button click
          loadWordList($row.data('list-id'), true, function() { }, false, false);
        }
      });
    })
  );
}


// show the information that no list is selected and update the vars
function showNoListSelectedInfo() {
  shownListId = -1;
  shownListData = null;

  $('#word-list-info .box-head > div').html("Word lists");
  $('#word-list-info .box-body').html('<p class="spacer-30">Create or select a word list to get started.</p>');
  $('#word-list-info-words').hide();
  $('#word-list-title').hide();
  $('#word-list-sharing').hide();
  $('#word-list-label').hide();
}



// load word list
function loadWordList(id, showLoadingInformation, callback, allowEdit, allowSharing) {
  // show loading information
  if (showLoadingInformation) {
    $('#word-list-info .box-head > div').html("Loading...");
    $('#word-list-info .box-body').html(loading);

    // hide all divs which will later show things like words, sharings, labels and the list name while the list loads
    $('#word-list-info-words').hide();
    $('#word-list-sharing').hide();
    $('#word-list-label').hide();
    $('#word-list-title').hide();
  }

  // handle buttons and background colors indicating which list is currently shown
  setAllListRowsAsNotActive();
  // highlight the lists row by adding active class and hide button to view the list
  $('#list-of-word-lists tr[data-list-id=' + id + '], #list-of-shared-word-lists tr[data-list-id=' + id + ']').addClass('active').find('input[type=button]').first().hide();


  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.loadWordList.add(
    jQuery.ajax('server.php', {
      data: {
        action: 'get-word-list',
        word_list_id: id
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {
      console.log(data); // debugging
      data = jQuery.parseJSON(data); // parse JSON

      shownListData = data; // update the list data variable to the downloaded data
      shownListId = id;

      // because the default value of language1 and language2 in the data base is nothing set it to "First language" and "Second language"
      // those vars are title of the bottom table, placeholder in the change language form and placeholder in the add new words form
      if (!shownListData.language1) shownListData.language1 = "First language";
      if (!shownListData.language2) shownListData.language2 = "Second language";

      // info box head and list name box
      $('#word-list-title .box-head').html(shownListData.name);
      $('#word-list-info .box-head > div').html("General");

      // info box body
      // add content depending on the users permissions (sharing and editing)
      var wordListInfoBoxBody = '';
      if (!allowSharing) { // not list owner
        wordListInfoBoxBody += '<p>' + data.creator.firstname + ' ' + data.creator.lastname + ' shares this list with you.</p>';
        wordListInfoBoxBody += '<p>You have permissions to ' + (allowEdit?'edit':'view') + ' ' + data.creator.firstname + '\'s list.</p>';
        // add hide button
        wordListInfoBoxBody += '<input type="button" class="inline" value="Hide list" id="hide-shown-word-list"/>'
      }
      else {
        // list owner
        wordListInfoBoxBody += '<p>You own this list.</p>';
        wordListInfoBoxBody += '<p><form id="rename-list-form"><input type="text" id="rename-list-name" required="true" placeholder="List name" value="' + shownListData.name + '" class="inline"/>&nbsp;<input type="submit" value="Rename" id="rename-list-button" class="inline"/></form></p>';
        // add delete button
        wordListInfoBoxBody += '<input type="button" class="inline" value="Delete list" id="delete-shown-word-list"/>'
      }

      // var creationTime = new Date(parseInt(data.creation_time) * 1000);
      // wordListInfoBoxBody += '<p>Creation date: ' + creationTime.toDefaultString() + '</p>';

      if (allowEdit) {
        // change language form
        wordListInfoBoxBody += '<div class="inline"><p><form id="change-language-form"><input id="word-list-language1" required="true" type="text" placeholder="First language" value="' + shownListData.language1 + '""/>&nbsp;<input id="word-list-language2" required="true" type="text" placeholder="Second language" value="' + shownListData.language2 + '" />&nbsp;<input type="submit" id="word-list-languages-button" value="Edit languages"/></form></p></div>';

        //wordListInfoBoxBody += '<label id="import-wrapper" class="button">Import...<input type="file" id="import-data" style="display: none; " /></label>';
      }
      else {

      }

      // add export button
      wordListInfoBoxBody += '<input id="export-list" type="button" value="Export..." onclick="exportList()"/>';

      $('#word-list-info .box-body').html(wordListInfoBoxBody); // update DOM


      $('#words-add-language1').attr('placeholder', shownListData.language1);
      $('#words-add-language2').attr('placeholder', shownListData.language2);

      // sharing box
      if (allowSharing) {
        // refresh sharing box with loading information
        refreshListSharings(true, data.id);
        $('#word-list-sharing').show();
      }
      else {
        $('#word-list-sharing').hide();
      }

      // list of words
      if (data.words.length == 0) { // no words added yet
        $('#words-in-list').html((allowEdit)?noWordsInList:noWordsInListDisallowEdit);
      }
      else {
        // add words of the list to the DOM
        var wordListHTML = "";
        for (var i = 0; i < data.words.length; i++) {
          wordListHTML += getTableRowOfWord(data.words[i].id, data.words[i].language1, data.words[i].language2, allowEdit);
        }
        wordListHTML = getTableOfWordList(wordListHTML, allowEdit, shownListData.language1, shownListData.language2);
        $('#words-in-list').html(wordListHTML);
      }

      // events
      // delete word list
      $('#delete-shown-word-list').on('click', function() {
        $(this).prop('disabled', true).attr('value', 'Deleting...');

        // call delete word list function and pass id of the list which will be deleted
        deleteWordList(shownListId, function() {
          showNoListSelectedInfo(); // show the message that no list is shown at the moment
        });
      });

      // hide word list (stop sharing)
      $('#hide-shown-word-list').on('click', function() {
        $(this).prop('disabled', true).attr('value', 'Hiding list...'); // disable button
        var sharingId = $('tr[data-list-id=' + shownListId + ']').data('sharing-id');
        // send server request to hide the shared list
        setSharingPermissionsBySharingId(sharingId, 0, function() {
          $('#list-of-shared-word-lists-row-' + sharingId).remove();

          // still rows left?
          if ($('#list-of-shared-word-lists tr').length == 1) {
            $('#list-of-shared-word-lists').html(noSharedWordListOutput); // show appropriate message if there are no lists to display
          }

          // because the shown list has just been removed update the screen to show the appropriate message
          showNoListSelectedInfo();
        });
      });

      // rename form
      $('#rename-list-form').on('submit', function(e) {
        e.preventDefault();

        // disable button and inputs
        var $nameInput = $('#rename-list-name'), $submitButton = $('#rename-list-button');
        $nameInput.prop('disabled', true);
        $submitButton.prop('disabled', true).attr('value', 'Renaming...');

        var newListName = $nameInput.val();
        // send information to the server
        renameList(shownListId, newListName, function(data) {
          // re-enable button and inputs
          $nameInput.prop('disabled', false);
          $submitButton.prop('disabled', false).attr('value', 'Rename');

          // update local list object
          shownListData.name = newListName;

          // update the information where the list name was shown
          $('#word-list-title .box-head').html(newListName); // on top of the page
          $('#list-of-word-lists-row-' + shownListId).children().first().html(newListName); // inside the list of word lists
        });
      });

      // change language form
      $('#change-language-form').on('submit', function(e) {
        e.preventDefault();

        // disable inputs and button
        var $lang1Input = $('#word-list-language1'), $lang2Input = $('#word-list-language2'), $submitButton = $('#word-list-languages-button');
        $lang1Input.prop('disabled', true);
        $lang2Input.prop('disabled', true);
        $submitButton.prop('disabled', true).attr('value', 'Editing languages...');

        // read string values
        var lang1 = $lang1Input.val(), lang2 = $lang2Input.val();

        // send information to the server
        setWordListLanguages(shownListId, lang1, lang2, function(data) {

          // re-enable inputs and buttons
          $lang1Input.prop('disabled', false);
          $lang2Input.prop('disabled', false);
          $submitButton.prop('disabled', false).attr('value', 'Edit languages');

          // update local list object
          shownListData.language1 = lang1;
          shownListData.language2 = lang2;

          // update the information where the list languages were shown
          // placeholder of word add form
          $('#words-add-language1').attr('placeholder', lang1);
          $('#words-add-language2').attr('placeholder', lang2);
          // word list table head
          $('#word-list-table').find('td').eq(0).html(lang1);
          $('#word-list-table').find('td').eq(1).html(lang2);
        });
      });



      // show divs which have been updated above
      $('#word-list-title').show();
      $('#word-list-info-words').show();

      if (allowEdit)
        $('#words-add').show();
      else
        $('#words-add').hide();

      // update label list with loading information
      getLabelList(true);
      $('#word-list-label').show();
    })
  );
}

// returns the HTML string of a single word row
function getTableRowOfWord(id, lang1, lang2, allowEdit) {
  return '<tr id="word-row-' + id + '"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + ((allowEdit)?'<td><input type="submit" class="inline" value="Edit" data-action="edit" form="word-row-' + id + '-form"/>&nbsp;<input type="button" class="inline" value="Remove" onclick="removeWord(' + id + ')"/><form id="word-row-' + id + '-form" onsubmit="editSaveWord(event, ' + id + ')"></form></td>':'') + '</tr>';
}

// returns the HTML table arount a given content of the word list
function getTableOfWordList(content, allowEdit, lang1, lang2) {
  return '<table id="word-list-table" class="box-table ' + ((allowEdit)?'button-right-column':'') + '"><tr class="bold"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + (allowEdit?'<td></td>':'') + '</tr>' + content + '</table>';
}


// export word list
function exportList(list) {
  if (list == undefined)
    list = shownListData;

  var output = "";

  // convert the word list into a string
  for (var i = 0; i < list.words.length; i++) {
    // use "|" as separator between the two languages
    output += list.words[i].language1 + " | " + list.words[i].language2 + "\n";
  }

  // save the text
  saveTextAsFile(output, list.name + '.txt');
}


// edit or save word button click event
function editSaveWord(event, id) {
  event.preventDefault(); // stop form submission

  // jQuery vars of the important elements
  var $row = $('#word-row-' + id); // the HTML row (<tr>)
  var $editSaveButton = $row.find('input[type=submit]'); // the button (<input type="button"/>)
  var $cell1 = $row.children().eq(0), $cell2 = $row.children().eq(1); // the first cell in the words table row (<td>)

  // edit button
  if ($editSaveButton.data('action') == 'edit') { // edit mode
    // update the buttons value
    $editSaveButton.data('action', 'save').attr('value', 'Save');

    // replace the words meanings with text boxes containing the meanings as value="" to allow editing by the user
    $cell1.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language1-' + id + '" value="' + $cell1.html() + '" />');
    $cell2.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language2-' + id + '" value="' + $cell2.html() + '" />');
  }

  // save button
  else {
    // disable the form elements
    var $lang1Input = $('#word-edit-input-language1-' + id), $lang2Input = $('#word-edit-input-language2-' + id);
    $lang1Input.prop('disabled', true);
    $lang2Input.prop('disabled', true);
    $editSaveButton.prop('disabled', true).attr('value', 'Saving...');

    // send updated word information to the server
    saveWord(id, $lang1Input.val(), $lang2Input.val(), function() {
      // reset the table row (hide the input fields and re-enable the edit button)
      $editSaveButton.prop('disabled', false).attr('value', 'Edit').data('action', 'edit');
      $cell1.html($lang1Input.val());
      $cell2.html($lang2Input.val());
    });
  }
}


// save word
function saveWord(id, lang1, lang2, callback) {
  // parameters: word id, languages
  jQuery.ajax('server.php', {
    data: {
      action: 'update-word',
      word_id: id,
      lang1: lang1,
      lang2: lang2
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debug
    callback(data);
  });
}


// set language of word list
function setWordListLanguages(id, lang1, lang2, callback) {
  // parameters: word list id, languages
  jQuery.ajax('server.php', {
    data: {
      action: 'set-word-list-languages',
      word_list_id: id,
      lang1: lang1,
      lang2: lang2
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    callback(data);
  });
}


// remove word
function removeWord(id) {
  // update button
  var $row = $('#word-row-' + id);
  var $removeButton = $row.find('* input[type=button]');
  $removeButton.prop('disabled', true).attr('value', 'Removing...');

  // send message to server
  jQuery.ajax('server.php', {
    data: {
      action: 'remove-word',
      word_id: id
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging

    // remove the row of the removed word from the DOM
    $row.remove();

    // show special message if no word is left
    if ($('#word-list-table tr').length == 1) {
      $('#word-list-table').html(noWordInList);
    }
  });
}


// delete word list
function deleteWordList(id, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'delete-word-list',
      word_list_id: id
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    // remove the word list row from the DOM
    $('#list-of-word-lists-row-' + id).remove();

    // no list table row anymore (except from the th)
    if ($('#list-of-word-lists tr').length == 1) {
      $('#list-of-word-lists').html(noWordListOutput);
    }
    callback();
  });
}


// add new word form submit event listener
$('#words-add-form').on('submit', function(e) {
  e.preventDefault();

  // read input fields
  var lang1 = $('#words-add-language1').val(), lang2 = $('#words-add-language2').val();

  // clear input fields and focus the first one to allow the user to enter the next word immediately
  $('#words-add-language1').val('').focus();
  $('#words-add-language2').val('');

  // send word to the server
  addWord(lang1, lang2, true);
});


// add word
function addWord(lang1, lang2, allowEdit) {
  jQuery.ajax('server.php', {
    data: {
      action: 'add-word',
      word_list_id: shownListId,
      lang1: lang1,
      lang2: lang2
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data);

    if ($('#word-list-table').length == 0) { // no words added yet
      var wordListHTML = getTableOfWordList("", allowEdit, shownListData.language1, shownListData.language2);
      $('#words-in-list').html(wordListHTML);
    }

    // add word row to the list of words
    $('#word-list-table tr:nth-child(1)').after(getTableRowOfWord(data, lang1, lang2, allowEdit));

    // new Toast('The word "' + lang1 + '" - "' + lang2 + '" has been added successfully.');
  });
}



// refresh list sharings
function refreshListSharings(showLoadingInformation, wordListId) {
  // set id parameter to the shown list id if undefined has been passed
  if (wordListId == undefined)
    wordListId = shownListId;

  // show loading information
  $('#word-list-sharing').show();
  if (showLoadingInformation) {
    $('#list-sharings').html(loading);
  }


  // add the Ajax-request to the request manager to make sure that there is only one ajax request of this type running at one moment
  ajaxRequests.refreshListSharings.add(
    jQuery.ajax('server.php', {
      data: {
        'action': 'get-sharing-info-of-list',
        'word_list_id': wordListId
      },
      type: 'GET',
      error: function(jqXHR, textStatus, errorThrown) {

      }
    }).done(function(data) {
      console.log(data); // debug
      data = jQuery.parseJSON(data); // parse JSON

      if (data.length == 0) { // list not shared yet
        $('#list-sharings').html(listNotShared); // show appropriate message
      }
      else { // list shared with at least one user
        var output = "";
        // add row for each sharing to output string
        for (var i = 0; i < data.length; i++) {
          output += '<tr id="list-shared-with-row-' + data[i].id + '">';
          output += '<td>' + data[i].user.firstname + ' ' + data[i].user.lastname + '</td>';
          output += '<td>' + ((data[i].permissions == 1)?'Can edit':'Can view') + '</td>';
          output += '<td><input type="button" class="inline" value="Stop sharing" data-action="delete-sharing" data-sharing-id="' + data[i].id + '"/></td></tr>';
        }
        // add table to output string
        output = '<table class="box-table button-right-column"><tr class="bold"><td>Name</td><td>Permissions</td><td></td></tr>' + output + '</table>';

        $('#list-sharings').html(output); // display the output string

        // event listeners for the buttons just added
        // stop sharing button
        $('#list-sharings input[type=button]').on('click', function() {

          $button = $(this);
          $button.prop('disabled', true).attr('value', 'Stopping sharing...'); // change button value and disable button

          // send message to server to stop sharing of the list
          setSharingPermissionsBySharingId($button.data('sharing-id'), 0, function() {

            // remove the row from the table
            $('#list-shared-with-row-' + $button.data('sharing-id')).remove();

            // still rows left?
            if ($('#list-sharings tr').length == 1) {
              $('#list-sharings').html(listNotShared);
            }
          });
        });
      }
    })
  );
}


// share list form submit event listener
$('#share-list-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable form elements
  $('#share-list-other-user-email').prop('disabled', true);
  $('#share-list-permissions').prop('disabled', true);
  $('#share-list-submit').prop('disabled', true).attr('value', 'Sharing...');

  // send message to server
  setSharingPermissions(shownListId, $('#share-list-other-user-email').val(), $('#share-list-permissions').val(), function() {
    // finished callback

    // re-enable the form elements
    $('#share-list-other-user-email').prop('disabled', false).val('');
    $('#share-list-permissions').prop('disabled', false);
    $('#share-list-submit').prop('disabled', false).attr('value', 'Share');

    // refresh the list of sharings without loading information
    refreshListSharings(false, shownListId);
  });
});


// set sharing permissions
function setSharingPermissionsBySharingId(sharingId, permissions, callback) {
  // parameters: sharing id, new permissions (can edit or view), callback function
  jQuery.ajax('server.php', {
    data: {
      action: 'set-sharing-permissions-by-sharing-id',
      sharing_id: sharingId,
      permissions: permissions
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    data = jQuery.parseJSON(data); // parse JSON

    callback(data);
  });
}

// set sharing permissions
function setSharingPermissions(listId, email, permissions, callback) {
  // parameters: list of the id which will be shared, other user eamil, permissions (can edit or view), callback function
  jQuery.ajax('server.php', {
    data: {
      action: 'set-sharing-permissions',
      word_list_id: listId,
      email: email,
      permissions: permissions
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data);
    data = jQuery.parseJSON(data);

    callback(data);
  });
}



// label functions

// get label list of user
function getLabelList(showLoadingInformation) {
  if (showLoadingInformation)
    $('#list-labels-list').html(loading);

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-labels-of-user'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debug
    labels = jQuery.parseJSON(data); // parse JSON

    // refreshQueryLabelSelection(labels);



    $('#list-labels-list').html(getEditableHtmlTableOfLabels(labels)); // update DOM
    
    
    // open small menu for single label event trigger
    $('#list-labels-list img.small-menu-open-image').on('click', function(e) {
      $(this).next().toggleClass('display-none');
      e.stopPropagation(); // prevent triggering click event on body which listens for click to close the popup
    });
    $('#list-labels-list .small-menu input').on('click', function(e) {
      $(this).parents('.small-menu').addClass('display-none');
    });
    $('#list-labels-list .small-menu').on('click', function(e) { e.stopPropagation(); }); // prevent triggering click event on body which listens for click to close the popup

    // just added checkboxes event listener
    // the checkboxes allow the user to attach the list to a label by checking the checkbox
    $('#list-labels-list input[type=checkbox]').click( function(){
      // read label id from checkbox data tag
      var labelId = $(this).data('label-id');

      // checkbox has been checked
      if($(this).is(':checked')) { // add list to label
        attachListToLabel(labelId, shownListId, function() {
          // update list object by adding the label
          shownListData.labels.push(labels[getLabelIndexByLabelId(labels, labelId)]);
        });
      }
      // checkbox has been unchecked
      else { // detach list from label
        detachListFromLabel(labelId, shownListId, function() {
          // update list object by removing the label
          shownListData.labels.splice(getLabelIndexByLabelId(shownListData.labels, labelId), 1);
        });
      }
    });


    // add new label form event listener
    $('.label-add-form').on('submit', function(e) {
      e.preventDefault();

      // disable form elements
      $button = $(this).children('.label-add-button').prop('disabled', true).attr('value', 'Adding label...');
      $nameInput = $(this).children('.label-add-name').prop('disabled', true);
      $parentSelect = $(this).children('.label-add-parent').prop('disabled', true);

      expandedLabelsIds.push(parseInt($parentSelect.val())); // expand parent label of newly added label
      
      // send message to the server
      addLabel(shownListId, $nameInput.val(), $parentSelect.val(), function() {
        // after adding successfully refresh the label list without loading information
        getLabelList(false);

        // re-enable form elements
        $button.prop('disabled', false).attr('value', 'Add label');
        $nameInput.prop('disabled', false).val('');
        $parentSelect.prop('disabled', false).val(null);
      });
    });

    // remove label form submit event listener
    $('.label-remove-form').on('submit', function(e) {
      e.preventDefault();

      // update form children
      $(this).children('.label-remove-select').prop('disabled', true);
      $(this).children('.label-remove-button').prop('disabled', true).attr('value', 'Removing...');

      var labelId = $(this).children('.label-remove-select').val(); // read label id

      // remove label server request
      removeLabel(labelId, function() {
        // re-enable form children
        $(this).children('.label-remove-select').prop('disabled', false);
        $(this).children('.label-remove-button').prop('disabled', false).attr('value', 'Remove label');

        // update local list object
        shownListData.labels.splice(getLabelIndexByLabelId(shownListData.labels, labelId), 1);

        // update label list without loading information
        getLabelList(false);
      });
    });

    // add sub label event listener
    $('.label-add-sub-label').on('click', function() {
      // show the "add sub label form" which is hidden in the following <tr>
      $(this).hide().parent().parent().parent().next().show().children().find('input[type=text]').first().focus();
    });

    // label rename form event listener
    $('.label-rename-form').on('submit', function(e) {
      e.preventDefault();

      // get label id from data tag of the form
      var labelId = $(this).data('label-id');
      var $button = $('#label-rename-button-' + labelId);
      var $firstCell = $('#label-rename-table-cell-' + labelId);

      // edit name
      if ($button.data('action') == 'rename-edit') {
        var labelName = labels[getLabelIndexByLabelId(labels, labelId)].name;
        $firstCell.find('label span').html('');
        $firstCell.append('&nbsp;<input type="text" form="label-rename-form-' + labelId + '" class="inline" value="' + labelName + '" required="true"/>');
        $button.data('action', 'rename-save');
      }

      // submit edits
      else {
        var $input = $firstCell.children('input').first();
        var newName = $input.val();

        $button.prop('disabled', true).attr('value', 'Renaming...');
        $input.prop('disabled', true);

        // send new name to the server
        renameLabel(labelId, newName, function() {
          $button.prop('disabled', false).attr('value', 'Rename').data('action', 'rename-edit');
          $firstCell.children('input').remove();
          $firstCell.find('label span').html('&nbsp;' + newName);

          // update local label object
          labels[getLabelIndexByLabelId(labels, labelId)].name = newName;
        });
      }
    });


    // expand single labels
    $('#list-labels-list .small-exp-col-icon').on('click', function() {
      var $this = $(this);
      var expand = ($this.data('state') == 'collapsed');

      var i = 0;
      var $row = $this.parent().parent();
      var allFollowing = $row.nextAll();
      var selfIndenting = $row.data('indenting');
      // show all following rows which have a higher indenting (are sub-labels) or don't have an indenting (are "add sub-label" formular rows)
      while (allFollowing.eq(i).length > 0 && (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') == undefined)) {
        if (allFollowing.eq(i).data('indenting') == selfIndenting + 1 || !expand) {
          if (expand) // expand
            allFollowing.eq(i).show();

          else { // collapse
            allFollowing.eq(i).hide();
            allFollowing.eq(i).find('.small-exp-col-icon').attr('src', 'img/expand.svg').data('state', 'collapsed');

            // refresh array of expanded labels
            expandedLabelsIds.removeAll(parseInt(allFollowing.eq(i).data('label-id')));
          }
        }
        i++;
      }

      if (expand) {
        $this.data('state', 'expanded').attr('src', 'img/collapse.svg'); // flip image
        expandedLabelsIds.push(parseInt($row.data('label-id'))); // refresh array of expanded labels
      }
      else {
        $this.data('state', 'collapsed').attr('src', 'img/expand.svg'); // flip image
        expandedLabelsIds.removeAll(parseInt($row.data('label-id'))); // refresh array of expanded labels
      }
    });
  });
}

// close small menu for single label event trigger
$('body').on('click', function() {
  $('.small-menu').addClass('display-none');
}); 


function getEditableHtmlTableOfLabels(labels) {
  // method returns the HTML code of the label list
  var html = getHtmlListOfLabelId(labels, 0, 0);

  if (html.length > 0) {
    html = '<table class="box-table button-right-column">' + html + '</table>';
  }
  else {
    // if there was no code returned there are no labels to show
    html = noLabels;
  }
  return html;
}

// get HTML list of label id
// returns the HTML list showing a label and it's sub-labels
function getHtmlListOfLabelId(labels, id, indenting) {
  var output = '<tr' + ((indenting == 0)?'':' style="display: none; "') + '><td colspan="2" style="padding-left: ' + (15 * indenting + 15 + ((indenting == 0) ? 0 : 16)) + 'px; text-align: left; "><form class="label-add-form inline"><input type="hidden" class="label-add-parent" value="' + id + '"/><input class="label-add-name inline" style="margin-left: -8px; " type="text" placeholder="Label name" required="true"/>&nbsp;<input class="label-add-button inline" type="submit" value="Add label"/></form></td>';
  var labelIds = getLabelIdsWithIndenting(labels, indenting);
  for (var i = 0; i < labelIds.length; i++) {
    var currentLabel = labels[getLabelIndexByLabelId(labels, labelIds[i])];
    if (currentLabel.parent_label == id) {
      output += getSingleListElementOfLabelList(currentLabel, indenting);
      output += getHtmlListOfLabelId(labels, labelIds[i], indenting + 1);
    }
  }
  return output;
}

// returns the HTML-row of a single label
function getSingleListElementOfLabelList(label, indenting) {
  var subLabelsCount = numberOfSubLabels(labels, label.id);
  var expanded = expandedLabelsIds.contains(label.id), parentExpanded = expandedLabelsIds.contains(label.parent_label); // label is expanded?

  var output = '<tr data-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting == 0 || parentExpanded)?'':' style="display: none; "') + ' id="label-list-row-id-' + label.id + '">';
  output += '<form class="label-rename-form" id="label-rename-form-' + label.id + '" data-label-id="' + label.id + '"></form>';
  output += '<td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount == 0) ? 16 : 0)) + 'px; " id="label-rename-table-cell-' + label.id + '">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;<label class="checkbox-wrapper"><input type="checkbox" data-label-id="' + label.id + '" ' + (labelAttachedToList(shownListData, label.id)?'checked="true"':'') + '/><span>&nbsp;' + label.name + '</span></label></td>';
  output += '<td><img class="small-menu-open-image" src="img/menu-small.svg" /><div class="small-menu display-none"><input type="submit" class="width-100" form="label-rename-form-' + label.id + '" id="label-rename-button-' + label.id + '" data-action="rename-edit" value="Rename" /><br><input type="button" class="label-add-sub-label width-100" value="Add sub-label"/><br><form class="label-remove-form inline"><input type="hidden" class="label-remove-select" value="' + label.id + '"/><input class="label-remove-button width-100" type="submit" value="Remove" /></form></td></div></tr>';
  return output;
}

// returns the index of a label id in the given "labels" array
function getLabelIndexByLabelId(labels, labelId) {
  for (var i = 0; i < labels.length; i++) {
    if (labelId == labels[i].id) {
      return i;
    }
  }
  return -1;
}

// returns the number of sub-labels a labelId has
function numberOfSubLabels(labels, labelId) {
  var count = 0;
  var indenting = getLabelIndenting(labels, getLabelIndexByLabelId(labels, labelId));
  var oneIndentingMore = getLabelIdsWithIndenting(labels, indenting + 1);
  for (var i = 0; i < oneIndentingMore.length; i++) {
    if (labels[getLabelIndexByLabelId(labels, oneIndentingMore[i])].parent_label == labelId) {
      count++;
    }
  }
  return count;
}

// returns true if the label is attached to the given list
function labelAttachedToList(list, labelId) {
  for (var i = 0; i < list.labels.length; i++) {
    if (labelId == list.labels[i].id) {
      return true;
    }
  }
  return false;
}

// returns all label ids with a specified indenting
function getLabelIdsWithIndenting(labels, indenting) {
  var selectedLabels = new Array();
  for (var i = 0; i < labels.length; i++) {
    if (getLabelIndenting(labels, i) == indenting) {
      selectedLabels.push(labels[i].id);
    }
  }
  return selectedLabels;
}

// returns the indenting of a label
function getLabelIndenting(labels, index) {
  if (labels[index].parent_label == 0)
    return 0;

  return getLabelIndenting(labels, getLabelIndexByLabelId(labels, labels[index].parent_label)) + 1;
}

// add label
function addLabel(listId, name, parentId, callback) {

  // send Ajax request
  jQuery.ajax('server.php', {
    data: {
      action: 'add-label',
      label_name: name,
      parent_label_id: parentId
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    callback();
  });
}

// attaches the given list to a label
function attachListToLabel(labelId, listId, callback) {
  setLabelListAttachment(labelId, listId, 1, callback);
}

// detaches the given list from a label
function detachListFromLabel(labelId, listId, callback) {
  setLabelListAttachment(labelId, listId, 0, callback);
}

// set label list attachment (0 = detached; 1 = attached)
function setLabelListAttachment(labelId, listId, attachment, callback) {
  if (listId == undefined) {
    listId = shownListId;
  }

  // send Ajax-request
  jQuery.ajax('server.php', {
    data: {
      action: 'set-label-list-attachment',
      label_id: labelId,
      list_id: listId,
      attachment: attachment
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    callback(data);
  });
}

// remove label
function removeLabel(labelId, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'remove-label',
      label_id: labelId
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    callback(data);
  });
}

// rename label
function renameLabel(labelId, labelName, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'rename-label',
      label_id: labelId,
      label_name: labelName
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    callback(data);
  });
}

// rename word list
function renameList(listId, listName, callback) {
  jQuery.ajax('server.php', {
    data: {
      action: 'rename-word-list',
      word_list_id: listId,
      word_list_name: listName
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data); // debugging
    callback(data);
  });
}


// refresh functions
showNoListSelectedInfo();
refreshListOfWordLists(true);
refreshListOfSharedWordLists(true);
