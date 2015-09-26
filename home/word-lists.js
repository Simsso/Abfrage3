"use strict";

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


// single page application allows urls like
// ...#/word-lists/4 (<-- id)
$(window).on('page-word-lists', function(event, pageName, subPageName) {
  var hashListId = parseInt(subPageName); // subPageName (last part of the url #/xxx/this) defines the id of the loaded list
  if (hashListId === shownListId) return; // no reason to touch the DOM because the requested list hasn't changed
  
  
  if (isNaN(hashListId)) { // no valid subPageName given via hash
    if (shownListId !== -1) { // word list loaded
      // Can be the case if the user has loaded a list and switched to another page. 
      // After clicking on the navigation link #/word-lists again the old list is still loaded but the hash has to bee updated
      window.location.hash = '#/word-lists/' + shownListId;
      return;
    }
    else {
      // no list is requested and no list has been loaded
      showNoListSelectedInfo(true);
    }
  }
  else {
    // a valid list id has been passed via url
    loadWordList(parseInt(subPageName), true, false);
  }
});




// adds a new word list to the data base
//
// @param string name: the name of the new word list
// @param function|undefined callback: callback which will be called after finishing the Ajax-request
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
    data = handleAjaxResponse(data);
    callback(data);
  });
}

// event listener for form which adds new word lists to the data base
$(page['word-lists']).find('#word-list-add-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable button and text box to prevent resubmission
  $(page['word-lists']).find('#word-list-add-name').prop('disabled', true);
  $(page['word-lists']).find('#word-list-add-button').prop('disabled', true).attr('value', 'Creating list...');

  // call the server contacting function
  addWordList($(page['word-lists']).find('#word-list-add-name').val(), function(data) {
    // finished callback
    // re-enable the button and the text box
    $(page['word-lists']).find('#word-list-add-name').prop('disabled', false).val('');
    $(page['word-lists']).find('#word-list-add-button').prop('disabled', false).attr('value', 'Create list');

    refreshListOfWordLists(false, function() {
      // handle buttons and background colors indicating which list is currently shown
      setAllListRowsAsNotActive();
      // highlight the lists row by adding active class and hide button to view the list
      $(page['word-lists']).find('#list-of-word-lists tr[data-list-id=' + data.id + ']').addClass('active').find('input[type=button]').first().hide();
    }); // refresh the list of word lists without loading information

    // load the word list which has just been added
    loadWordList(data.id, true);
  });
});



// refresh list of word lists
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param function|undefined callback: callback which will be called after finishing the list refresh
// @param bool|undefined firstCall: if set to false (which is also the default value if undefined is passed) the showNoListSelectedInfo function will update the hash to "#/word-lists"
function refreshListOfWordLists(showLoadingInformation, callback, firstCall) {
  if (firstCall === undefined) firstCall = false;

  // loading information
  if (showLoadingInformation)
    $(page['word-lists']).find('#list-of-word-lists').html(loading);

  // reset all table row highlights and hidden buttons indicating which list is selected
  showNoListSelectedInfo(!firstCall);

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
      data = handleAjaxResponse(data);


      var output = "";
      // build HTML output string
      for (var i = 0; i < data.length; i++) { // add a row for each list
        output += '<tr ' + ((data[i].id == shownListId) ? 'class="active" ' : '') + 'data-action="edit" data-list-id="' + data[i].id + '" id="list-of-word-lists-row-' + data[i].id + '"><td>' + data[i].name + '</td></tr>';
      }

      // if there are no lists show the appropriate message
      if (output.length === 0) {
        output = noWordListOutput;
      }
      else {
        output = '<table class="box-table cursor-pointer"></tr>' + output + '</table>';
      }

      $(page['word-lists']).find('#list-of-word-lists').html(output); // update DOM with list of word lists

      // add event listeners for rows which have just been added
      $(page['word-lists']).find('#list-of-word-lists tr').on('click', function() {
        window.location.hash = '#/word-lists/' + $(this).data('list-id');
      });
    })
  );
}


// set all list rows as not active
// 
// handle buttons and background colors indicating which list is currently shown
// active lists have a blue border to the left of their name
function setAllListRowsAsNotActive() {
  $(page['word-lists']).find('#list-of-shared-word-lists tr, #list-of-word-lists tr').removeClass('active'); // un-highlights all table rows
}


// refresh list of shared word lists
// 
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param bool|undefined firstCall: if set to false (which is also the default value if undefined is passed) the showNoListSelectedInfo function will update the hash to "#/word-lists"
function refreshListOfSharedWordLists(showLoadingInformation, firstCall) {
  if (firstCall === undefined) firstCall = false;
  
  if (showLoadingInformation)
    $(page['word-lists']).find('#list-of-shared-word-lists').html(loading);

  showNoListSelectedInfo(!firstCall);


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
      data = handleAjaxResponse(data);

      var output = "";
      // build HTML output string
      for (var i = 0; i < data.length; i++) {
        // add table row of a single shared list
        output += '<tr ' + ((data[i].id == shownListId) ? 'class="active" ' : '') + 'data-action="' + ((data[i].permissions == 1)?'edit':'view') + '" data-list-id="' + data[i].id + '" data-sharing-id="' + data[i].sharing_id + '" id="list-of-shared-word-lists-row-' + data[i].sharing_id + '">';
        output += '<td>' + data[i].name + '</td>';
      }
      // if there are no shared lists show the appropriate message
      if (output.length === 0) {
        output = noSharedWordListOutput;
      }
      else {
        output = '<table class="box-table cursor-pointer">' + output + '</table>';
      }
      $(page['word-lists']).find('#list-of-shared-word-lists').html(output); // update the DOM

      // add event listeners for rows inside the list
      $(page['word-lists']).find('#list-of-shared-word-lists tr').on('click', function() {
        window.location.hash = '#/word-lists/' + $(this).data('list-id');
      });
    })
  );
}


// show the information that no list is selected and update the vars
//
// @param bool updateHash: if set to true the hash will be updated to "#/word-lists"
function showNoListSelectedInfo(updateHash) {
  shownListId = -1;
  shownListData = null;
  
  if (updateHash === true) {
    window.location.hash = '#/word-lists';
  }

  $(page['word-lists']).find('#word-list-info .box-head > div').html("Word lists");
  $(page['word-lists']).find('#word-list-info .box-body').html('<p class="spacer-30">Create or select a word list to get started.</p>');
  $(page['word-lists']).find('#word-list-info-words').hide();
  $(page['word-lists']).find('#word-list-title').hide();
  $(page['word-lists']).find('#word-list-sharing').hide();
  $(page['word-lists']).find('#word-list-label').hide();
}



// load word list
//
// loads the word list from the data base and updates the local variables shownListId and shownListData
// fills the divs Words, Sharing, Labels and General Information with HTML-content
// adds events to the new HTML-content of the divs mentioned above
//
// @param int id: the id of the requested word list
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param bool|undefined showWordListPage: defines whether the page "Word lists" should be shown
function loadWordList(id, showLoadingInformation, showWordListPage) {
  // show loading information
  if (showLoadingInformation) {
    $(page['word-lists']).find('#word-list-info .box-head > div').html("Loading...");
    $(page['word-lists']).find('#word-list-info .box-body').html(loading);

    // hide all divs which will later show things like words, sharings, labels and the list name while the list loads
    $(page['word-lists']).find('#word-list-info-words').hide();
    $(page['word-lists']).find('#word-list-sharing').hide();
    $(page['word-lists']).find('#word-list-label').hide();
    $(page['word-lists']).find('#word-list-title').hide();
  }
  
  // a call of this method can force to show the page "word lists" with the parameter showWordListPage
  if (showWordListPage === true) {
    if (window.location.hash !== '#word-lists') {
      window.location.hash = '#word-lists';
    }
  }

  // handle buttons and background colors indicating which list is currently shown
  setAllListRowsAsNotActive();
  // highlight the lists row by adding active class and hide button to view the list
  $(page['word-lists']).find('#list-of-word-lists tr[data-list-id=' + id + '], #list-of-shared-word-lists tr[data-list-id=' + id + ']').addClass('active').find('input[type=button]').first().hide();

  shownListId = id;

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
      data = handleAjaxResponse(data);

      // refresh list of recently used lists
      refreshRecentlyUsed(false);

      // update the list data variable to the downloaded data
      shownListData = new List(
          data.list.id, 
          data.list.name, 
          data.list.creator, 
          data.list.comment, 
          data.list.language1,
          data.list.language2, 
          data.list.creationTime, 
          data.list.words
        );
      
      shownListData.labels = data.list.labels;
      
      Query.linkLoadedWordList(shownListData);
      
      // list doesn't exist or no permissions or deleted
      if (shownListData === null) {
        showNoListSelectedInfo(true); // update hash to /#/word-lists because the list is not available
        return;
      }
      
      var allowEdit = data.allowEdit;
      var allowSharing = data.allowSharing;

      // because the default value of language1 and language2 in the data base is nothing set it to "First language" and "Second language"
      // those vars are title of the bottom table, placeholder in the change language form and placeholder in the add new words form
      if (!shownListData.language1) shownListData.language1 = "First language";
      if (!shownListData.language2) shownListData.language2 = "Second language";

      // info box head and list name box
      $(page['word-lists']).find('#word-list-title .box-head').html(shownListData.name);
      $(page['word-lists']).find('#word-list-info .box-head > div').html("General");

      // info box body
      // add content depending on the users permissions (sharing and editing)
      var wordListInfoBoxBody = '';
      if (!allowSharing) { // not list owner
        wordListInfoBoxBody += '<p>' + shownListData.creator.firstname + ' ' + shownListData.creator.lastname + ' shares this list with you.</p>';
        wordListInfoBoxBody += '<p>You have permissions to ' + (allowEdit?'edit':'view') + ' ' + shownListData.creator.firstname + '\'s list.</p>';
        // add hide button
        wordListInfoBoxBody += '<input type="button" class="inline" value="Hide list" id="hide-shown-word-list"/>';
      }
      else {
        // list owner
        wordListInfoBoxBody += '<p>You own this list.</p>';
        wordListInfoBoxBody += '<p><form id="rename-list-form"><input type="text" id="rename-list-name" required="true" placeholder="List name" value="' + shownListData.name + '" class="inline"/>&nbsp;<input type="submit" value="Rename" id="rename-list-button" class="inline"/></form></p>';
        // add delete button
        wordListInfoBoxBody += '<input type="button" class="inline" value="Delete list" id="delete-shown-word-list"/>';
      }

      // var creationTime = new Date(parseInt(shownListData.creation_time) * 1000);
      // wordListInfoBoxBody += '<p>Creation date: ' + creationTime.toDefaultString() + '</p>';

      if (allowEdit) {
        // change language form
        wordListInfoBoxBody += '<div class="inline"><p><form id="change-language-form"><input id="word-list-language1" required="true" type="text" placeholder="First language" value="' + shownListData.language1 + '""/>&nbsp;<input id="word-list-language2" required="true" type="text" placeholder="Second language" value="' + shownListData.language2 + '" />&nbsp;<input type="submit" id="word-list-languages-button" value="Edit languages"/></form></p></div>';

        //wordListInfoBoxBody += '<label id="import-wrapper" class="button">Import...<input type="file" id="import-data" style="display: none; " /></label>';
      }
      else {

      }

      // add export button
      //wordListInfoBoxBody += '<input id="export-list" type="button" value="Export..." onclick="exportList()"/>';

      $(page['word-lists']).find('#word-list-info .box-body').html(wordListInfoBoxBody); // update DOM


      $(page['word-lists']).find('#words-add-language1').attr('placeholder', shownListData.language1);
      $(page['word-lists']).find('#words-add-language2').attr('placeholder', shownListData.language2);

      // sharing box
      if (allowSharing) {
        // refresh sharing box with loading information
        refreshListSharings(true, shownListData.id);
        $(page['word-lists']).find('#word-list-sharing').show();
      }
      else {
        $(page['word-lists']).find('#word-list-sharing').hide();
      }

      // list of words
      $(page['word-lists']).find('#shown-word-list-words-count').html(shownListData.words.length); // update word count
      
      if (shownListData.words.length === 0) { // no words added yet
        $(page['word-lists']).find('#words-in-list').html((allowEdit)?noWordsInList:noWordsInListDisallowEdit);
      }
      else {
        // add words of the list to the DOM
        var wordListHTML = "";
        for (var i = 0; i < shownListData.words.length; i++) {
          wordListHTML += getTableRowOfWord(shownListData.words[i].id, shownListData.words[i].language1, shownListData.words[i].language2, allowEdit);
        }
        wordListHTML = getTableOfWordList(wordListHTML, allowEdit, shownListData.language1, shownListData.language2);
        $(page['word-lists']).find('#words-in-list').html(wordListHTML);
      }

      // events
      // delete word list
      $(page['word-lists']).find('#delete-shown-word-list').on('click', function() {
        // show message box
        var messageBox = new MessageBox();
        messageBox.setTitle('Delete word list');
        messageBox.setContent('Do you want to delete the word list <span class="italic">' + shownListData.name + '</span>?');
        messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
        messageBox.setCallback(function(button) {
          if (button === 'Yes') {
            $(this).prop('disabled', true).attr('value', 'Deleting...');

            // call delete word list function and pass id of the list which will be deleted
            deleteWordList(shownListId, function() {
              showNoListSelectedInfo(true); // show the message that no list is shown at the moment
            });
          }
        });
        messageBox.show();
      });

      // hide word list (stop sharing)
      $(page['word-lists']).find('#hide-shown-word-list').on('click', function() {
        // show message box
        var messageBox = new MessageBox();
        messageBox.setTitle('Hide word list');
        messageBox.setContent('Do you want to hide the word list <span class="italic">' + shownListData.name + '</span>?');
        messageBox.setButtons(MessageBox.ButtonType.YesNoCancel);
        messageBox.setCallback(function(button) {
          if (button === 'Yes') {
            $(this).prop('disabled', true).attr('value', 'Hiding list...'); // disable button
            var sharingId = $(page['word-lists']).find('tr[data-list-id=' + shownListId + ']').data('sharing-id');
            // send server request to hide the shared list
            setSharingPermissionsBySharingId(sharingId, 0, function() {
              $(page['word-lists']).find('#list-of-shared-word-lists-row-' + sharingId).remove();

              // still rows left?
              if ($(page['word-lists']).find('#list-of-shared-word-lists tr').length == 1) {
                $(page['word-lists']).find('#list-of-shared-word-lists').html(noSharedWordListOutput); // show appropriate message if there are no lists to display
              }

              // because the shown list has just been removed update the screen to show the appropriate message
              showNoListSelectedInfo(true);
            });
          }
        });
        messageBox.show();
      });

      // rename form
      $(page['word-lists']).find('#rename-list-form').on('submit', function(e) {
        e.preventDefault();

        // disable button and inputs
        var nameInput = $(page['word-lists']).find('#rename-list-name'), submitButton = $(page['word-lists']).find('#rename-list-button');
        nameInput.prop('disabled', true);
        submitButton.prop('disabled', true).attr('value', 'Renaming...');

        var newListName = nameInput.val();
        // send information to the server
        renameList(shownListId, newListName, function(data) {
          // re-enable button and inputs
          nameInput.prop('disabled', false);
          submitButton.prop('disabled', false).attr('value', 'Rename');

          // update local list object
          shownListData.name = newListName;

          // update the information where the list name was shown
          $(page['word-lists']).find('#word-list-title .box-head').html(newListName); // on top of the page
          $(page['word-lists']).find('#list-of-word-lists-row-' + shownListId).children().first().html(newListName); // inside the list of word lists
        });
      });

      // change language form event listener
      $(page['word-lists']).find('#change-language-form').on('submit', function(e) {
        e.preventDefault();

        // disable inputs and button
        var lang1Input = $(page['word-lists']).find('#word-list-language1'), lang2Input = $(page['word-lists']).find('#word-list-language2'), submitButton = $(page['word-lists']).find('#word-list-languages-button');
        lang1Input.prop('disabled', true);
        lang2Input.prop('disabled', true);
        submitButton.prop('disabled', true).attr('value', 'Editing languages...');

        // read string values
        var lang1 = lang1Input.val(), lang2 = lang2Input.val();

        // send information to the server
        setWordListLanguages(shownListId, lang1, lang2, function() {

          // re-enable inputs and buttons
          lang1Input.prop('disabled', false);
          lang2Input.prop('disabled', false);
          submitButton.prop('disabled', false).attr('value', 'Edit languages');

          // update local list object
          shownListData.language1 = lang1;
          shownListData.language2 = lang2;

          // update the information where the list languages were shown
          // placeholder of word add form
          $(page['word-lists']).find('#words-add-language1').attr('placeholder', lang1);
          $(page['word-lists']).find('#words-add-language2').attr('placeholder', lang2);
          // word list table head
          $(page['word-lists']).find('#word-list-table').find('td').eq(0).html(lang1);
          $(page['word-lists']).find('#word-list-table').find('td').eq(1).html(lang2);
        });
      });



      // show divs which have been updated above
      $(page['word-lists']).find('#word-list-title').show();
      $(page['word-lists']).find('#word-list-info-words').show();

      if (allowEdit)
        $(page['word-lists']).find('#words-add').show();
      else
        $(page['word-lists']).find('#words-add').hide();

      // update label list with loading information
      getLabelList(true);
      $(page['word-lists']).find('#word-list-label').show();
    })
  );
}

// get table row of word
//
// @return string: the HTML of a single word row
function getTableRowOfWord(id, lang1, lang2, allowEdit) {
  return '<tr id="word-row-' + id + '"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + ((allowEdit)?'<td><input type="submit" class="inline" value="Edit" data-action="edit" form="word-row-' + id + '-form"/>&nbsp;<input type="button" class="inline" value="Remove" onclick="removeWord(' + id + ')"/><form id="word-row-' + id + '-form" onsubmit="editSaveWord(event, ' + id + ')"></form></td>':'') + '</tr>';
}

// get table of word list
//
// @return string: the HTML table around a given content of the word list
function getTableOfWordList(content, allowEdit, lang1, lang2) {
  return '<table id="word-list-table" class="box-table ' + ((allowEdit)?'button-right-column':'') + '"><tr class="bold cursor-default"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + (allowEdit?'<td></td>':'') + '</tr>' + content + '</table>';
}


// export word list
function exportList(list) {
  if (list === undefined)
    list = shownListData;

  var output = "";

  // convert the word list into a string
  for (var i = 0; i < list.words.length; i++) {
    // use "|" as separator between the two languages
    output += list.words[i].language1 + " | " + list.words[i].language2 + "\n";
  }

  // save the text
  // saveTextAsFile(output, list.name + '.txt');
  // TODO
}


// edit save word button click event
//
// in the word list the user has the possibility to edit single words inline
// editing and saving the word can be done with the same button
// the editSaveWord function is the event listener of the related form
// the information whether the line is currently in edit or save mode is stored in a data attribute of the button (data('action'))
// it can have the values 'edit' which means that the user wants to edit the word by clicking the button
// the value 'save' means that the user has added the word and wants to save the changes by clicking
function editSaveWord(event, id) {
  event.preventDefault(); // stop form submission

  // jQuery vars of the important elements
  var row = $(page['word-lists']).find('#word-row-' + id); // the HTML row (<tr>)
  var editSaveButton = row.find('input[type=submit]'); // the button (<input type="button"/>)
  var cell1 = row.children().eq(0), cell2 = row.children().eq(1); // the first cell in the words table row (<td>)

  // edit button
  if (editSaveButton.data('action') == 'edit') { // edit mode
    // update the buttons value
    editSaveButton.data('action', 'save').attr('value', 'Save');

    // replace the words meanings with text boxes containing the meanings as value="" to allow editing by the user
    cell1.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language1-' + id + '" value="' + cell1.html() + '" />');
    cell2.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language2-' + id + '" value="' + cell2.html() + '" />');
  }

  // save button
  else {
    // disable the form elements
    var lang1Input = $(page['word-lists']).find('#word-edit-input-language1-' + id), lang2Input = $(page['word-lists']).find('#word-edit-input-language2-' + id);
    lang1Input.prop('disabled', true);
    lang2Input.prop('disabled', true);
    editSaveButton.prop('disabled', true).attr('value', 'Saving...');

    // send updated word information to the server
    saveWord(id, lang1Input.val(), lang2Input.val(), function() {
      // reset the table row (hide the input fields and re-enable the edit button)
      editSaveButton.prop('disabled', false).attr('value', 'Edit').data('action', 'edit');
      cell1.html(lang1Input.val());
      cell2.html(lang2Input.val());
    });
  }
}


// save word
//
// saves changed made to a word into the database
//
// @param int id: id of the word
// @param string lang1: first language
// @param string lang2: second language
// @param function callback: called with the server response data after sending the Ajax-request
function saveWord(id, lang1, lang2, callback) {
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
      data = handleAjaxResponse(data);
      callback(data);
  });
}


// set word list languages
//
// the user has the ability to define the language of the words stored in a word list
// this function saves the language in the data base
//
// @param int id: id of the word list
// @param string lang1: the first language of the list
// @param string lang2: the second language of the list
// @param function callback: called with the server response data after sending the Ajax-request
function setWordListLanguages(id, lang1, lang2, callback) {
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
    data = handleAjaxResponse(data);
    callback(data);
  });
}


// remove word
// 
// removes a single word from a word list
//
// @param int id: id of the word to remove
function removeWord(id) {
  var listId = shownListId; // store the id of the list of the word which will be removed
  
  // update button
  var row = $(page['word-lists']).find('#word-row-' + id);
  var removeButton = row.find('* input[type=button]');
  removeButton.prop('disabled', true).attr('value', 'Removing...');

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
    data = handleAjaxResponse(data);
    
    // the user has loaded another list while the word was removed
    // there is no need to update the DOM (remove the row of the words table)
    if (shownListId !== listId) return; 
    
    for (var i = 0; i < shownListData.words.length; i++) {
      if (shownListData.words[i].id === id) {
        shownListData.words.splice(i, 1);
        break;
      }
    }
    
    $(page['word-lists']).find('#shown-word-list-words-count').html(shownListData.words.length); // update word count


    // remove the row of the removed word from the DOM
    row.remove();

    // show special message if no word is left
    if ($(page['word-lists']).find('#word-list-table tr').length == 1) {
      $(page['word-lists']).find('#word-list-table').html(noWordsInList);
    }
  });
}


// delete word list
//
// deletes a word list from the data base
// 
// @param int id: id of the word list to remove
// @param function callback: called with the server response data after sending the Ajax-request
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
    data = handleAjaxResponse(data);

    refreshRecentlyUsed(false);

    // remove the word list row from the DOM
    $(page['word-lists']).find('#list-of-word-lists-row-' + id).remove();

    // no list table row anymore (except from the th)
    if ($(page['word-lists']).find('#list-of-word-lists tr').length == 1) {
      $(page['word-lists']).find('#list-of-word-lists').html(noWordListOutput);
    }


    callback(data);
  });
}


// add new word form submit event listener
$(page['word-lists']).find('#words-add-form').on('submit', function(e) {
  e.preventDefault();

  // read input fields
  var lang1 = $(page['word-lists']).find('#words-add-language1').val(), lang2 = $(page['word-lists']).find('#words-add-language2').val();

  // clear input fields and focus the first one to allow the user to enter the next word immediately
  $(page['word-lists']).find('#words-add-language1').val('').focus();
  $(page['word-lists']).find('#words-add-language2').val('');

  // send word to the server
  addWord(lang1, lang2, true);
});


// add word
// 
// adds a new word the the shown list
//
// @param string lang1: first language
// @param string lang2: second language
// @param bool allowEdit: information whether the user is allowed to edit (necessary to add the word <tr> element with or without Edit and Delete button)
function addWord(lang1, lang2, allowEdit) {
  var listId = shownListId;
  jQuery.ajax('server.php', {
    data: {
      action: 'add-word',
      word_list_id: listId,
      lang1: lang1,
      lang2: lang2
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    data = handleAjaxResponse(data);
    
    if (shownListId !== listId) return; // the user has loaded another list while the word was added
    
    // update local object of the current list
    shownListData.words.push({
      id: data,
      list: shownListId,
      language1: lang1,
      language2: lang2,
      answers: null
    });

    // update word count
    $(page['word-lists']).find('#shown-word-list-words-count').html(shownListData.words.length); 

    
    // update the word list table
    if ($(page['word-lists']).find('#word-list-table').length === 0) { 
      // no words added yet
      var wordListHTML = getTableOfWordList("", allowEdit, shownListData.language1, shownListData.language2);
      $(page['word-lists']).find('#words-in-list').html(wordListHTML);
    }

    // add word row to the list of words
    $(page['word-lists']).find('#word-list-table tr:nth-child(1)').after(getTableRowOfWord(data, lang1, lang2, allowEdit));

    // new Toast('The word "' + lang1 + '" - "' + lang2 + '" has been added successfully.');
  });
}



// refresh list sharings
//
// refreshes the list of people who can see or edit the list
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
// @param int|undefined wordListId: id of the word list for which the information will be requested
function refreshListSharings(showLoadingInformation, wordListId) {
  // set id parameter to the shown list id if undefined has been passed
  if (wordListId === undefined)
    wordListId = shownListId;

  // show loading information
  $(page['word-lists']).find('#word-list-sharing').show();
  if (showLoadingInformation) {
    $(page['word-lists']).find('#list-sharings').html(loading);
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
      data = handleAjaxResponse(data);

      if (data.length === 0) { // list not shared yet
        $(page['word-lists']).find('#list-sharings').html(listNotShared); // show appropriate message
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
        output = '<table class="box-table button-right-column"><tr class="bold cursor-default"><td>Name</td><td>Permissions</td><td></td></tr>' + output + '</table>';

        $(page['word-lists']).find('#list-sharings').html(output); // display the output string

        // event listeners for the buttons just added
        // stop sharing button
        $(page['word-lists']).find('#list-sharings input[type=button]').on('click', function() {

          var button = $(this);
          button.prop('disabled', true).attr('value', 'Stopping sharing...'); // change button value and disable button

          // send message to server to stop sharing of the list
          setSharingPermissionsBySharingId(button.data('sharing-id'), 0, function() {

            // remove the row from the table
            $(page['word-lists']).find('#list-shared-with-row-' + button.data('sharing-id')).remove();

            // still rows left?
            if ($(page['word-lists']).find('#list-sharings tr').length == 1) {
              $(page['word-lists']).find('#list-sharings').html(listNotShared);
            }
          });
        });
      }
    })
  );
}


// share list with another user form submit event listener
$(page['word-lists']).find('#share-list-form').on('submit', function(e) {
  // dont visit action="..." page
  e.preventDefault();

  // disable form elements
  $(page['word-lists']).find('#share-list-other-user-email').prop('disabled', true);
  $(page['word-lists']).find('#share-list-permissions').prop('disabled', true);
  $(page['word-lists']).find('#share-list-submit').prop('disabled', true).attr('value', 'Sharing...');

  // send message to server
  var email = $(page['word-lists']).find('#share-list-other-user-email').val();
  setSharingPermissions(
    shownListId, // list id
    email, // email of the user to share the list with
    $(page['word-lists']).find('#share-list-permissions').val(), // permissions
    function(data) { // finished callback

      // re-enable the form elements
      $(page['word-lists']).find('#share-list-other-user-email').prop('disabled', false).val('');
      $(page['word-lists']).find('#share-list-permissions').prop('disabled', false);
      $(page['word-lists']).find('#share-list-submit').prop('disabled', false).attr('value', 'Share');

      // refresh the list of sharings without loading information
      refreshListSharings(false, shownListId);
      
      // user doesn't exist
      if (data.set_permissions === -1) {
        var messageBox = new MessageBox();
        messageBox.setTitle('Not shared');
        messageBox.setContent('Found no user with the given email-address (<span class="italic">' + email + '</span>).');
        messageBox.show();
      }
      // user hasn't added you
      // not working yet
      // TODO
      /*else if (!data.user_has_added_you) {
        var messageBox = new MessageBox();
        messageBox.setTitle('Not shared yet');
        messageBox.setContent('The other user can\'t see the list until they adds you in the Users section.');
        messageBox.show();
      }*/
    }
  );
});


// set sharing permissions by sharing id
// 
// set the sharing permissions by sharing id
// e.g. to delte a list sharing with a user set the permissions to 0
//
// @param int sharingId: the id of the sharing (primary index in share data base table)
// @param byte permissions: new permissions (can edit or view)
// @param function callback: called with the server response data after sending the Ajax-request
function setSharingPermissionsBySharingId(sharingId, permissions, callback) {
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
    data = handleAjaxResponse(data);

    // update the box which shows recently used word lists
    // it could be the case that the function call removed one list for the user and therefore a list entry of the recently used lists has to be removed as well
    refreshRecentlyUsed(false);

    // callback with server response data
    callback(data);
  });
}

// set sharing permissions
//
// set sharing permissions of a list by email of other user
//
// @param int listId: id of the list which will be shared
// @param string email: email address of the other user (who will see the list)
// @param byte permissions: permissions for the other user (nothing, view or edit)
// @param function callback: called with the server response data after sending the Ajax-request
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
    data = handleAjaxResponse(data);

    callback(data);
  });
}



// label functions

// get label list
//
// downloads the label list of the user and updates the DOM
// updating the DOM is complex because of the sub-label structure
// adds event listeners to added HTML-elements
//
// @param bool showLoadingInformation: defines whether the loading animation is shown or not
function getLabelList(showLoadingInformation) {
  if (showLoadingInformation)
    $(page['word-lists']).find('#list-labels-list').html(loading);

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-labels-of-user'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {    
    labels = handleAjaxResponse(data);

    // refreshQueryLabelSelection(labels);

    $(page['word-lists']).find('#list-labels-list').html(getEditableHtmlTableOfLabels(labels)); // update DOM
    
    
    // open small menu for single label event trigger
    $(page['word-lists']).find('#list-labels-list img.small-menu-open-image').on('click', function(e) {
      $(page['word-lists']).find('.small-menu').addClass('display-none');
      $(this).next().removeClass('display-none');
      e.stopPropagation(); // prevent triggering click event on body which listens for click to close the popup
    });
    $(page['word-lists']).find('#list-labels-list .small-menu input').on('click', function(e) {
      $(this).parents('.small-menu').addClass('display-none');
    });
    $(page['word-lists']).find('#list-labels-list .small-menu').on('click', function(e) { e.stopPropagation(); }); // prevent triggering click event on body which listens for click to close the popup

    // just added checkboxes event listener
    // the checkboxes allow the user to attach the list to a label by checking the checkbox
    $(page['word-lists']).find('#list-labels-list input[type=checkbox]').click( function(){
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
    $(page['word-lists']).find('.label-add-form').on('submit', function(e) {
      e.preventDefault();

      // disable form elements
      var button = $(this).children('.label-add-button').prop('disabled', true).attr('value', 'Adding label...');
      var nameInput = $(this).children('.label-add-name').prop('disabled', true);
      var parentSelect = $(this).children('.label-add-parent').prop('disabled', true);

      expandedLabelsIds.push(parseInt(parentSelect.val())); // expand parent label of newly added label
      
      // send message to the server
      addLabel(nameInput.val(), parentSelect.val(), function(data) {
        // after adding successfully refresh the label list without loading information
        getLabelList(false);

        // re-enable form elements
        button.prop('disabled', false).attr('value', 'Add label');
        nameInput.prop('disabled', false).val('');
        parentSelect.prop('disabled', false).val(null);
      });
    });

    // remove label form submit event listener
    $(page['word-lists']).find('.label-remove-form').on('submit', function(e) {
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
    $(page['word-lists']).find('.label-add-sub-label').on('click', function() {
      // show the "add sub label form" which is hidden in the following <tr>
      $(this).hide().parent().parent().parent().next().show().children().find('input[type=text]').first().focus();
    });

    // label rename form event listener
    $(page['word-lists']).find('.label-rename-form').on('submit', function(e) {
      e.preventDefault();

      // get label id from data tag of the form
      var labelId = $(this).data('label-id');
      var button = $(page['word-lists']).find('#label-rename-button-' + labelId);
      var $firstCell = $(page['word-lists']).find('#label-rename-table-cell-' + labelId);

      // edit name
      if (button.data('action') == 'rename-edit') {
        var labelName = labels[getLabelIndexByLabelId(labels, labelId)].name;
        $firstCell.find('label span').html('');
        $firstCell.append('&nbsp;<input type="text" form="label-rename-form-' + labelId + '" class="inline" value="' + labelName + '" required="true"/>');
        button.data('action', 'rename-save');
      }

      // submit edits
      else {
        var input = $firstCell.children('input').first();
        var newName = input.val();

        button.prop('disabled', true).attr('value', 'Renaming...');
        input.prop('disabled', true);

        // send new name to the server
        renameLabel(labelId, newName, function() {
          button.prop('disabled', false).attr('value', 'Rename').data('action', 'rename-edit');
          $firstCell.children('input').remove();
          $firstCell.find('label span').html('&nbsp;' + newName);

          // update local label object
          labels[getLabelIndexByLabelId(labels, labelId)].name = newName;
        });
      }
    });


    // expand single labels
    $(page['word-lists']).find('#list-labels-list .small-exp-col-icon').on('click', function() {
      var $this = $(this);
      var expand = ($this.data('state') == 'collapsed');

      var i = 0;
      var row = $this.parent().parent();
      var allFollowing = row.nextAll();
      var selfIndenting = row.data('indenting');
      // show all following rows which have a higher indenting (are sub-labels) or don't have an indenting (are "add sub-label" formular rows)
      while (allFollowing.eq(i).length > 0 && (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') === undefined)) {
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
        expandedLabelsIds.push(parseInt(row.data('label-id'))); // refresh array of expanded labels
      }
      else {
        $this.data('state', 'collapsed').attr('src', 'img/expand.svg'); // flip image
        expandedLabelsIds.removeAll(parseInt(row.data('label-id'))); // refresh array of expanded labels
      }
    });
  });
}

// close small menu for single label event trigger
$('body').on('click', function() {
  $(page['word-lists']).find('.small-menu').addClass('display-none');
}); 


// get editable HTML table of labels
//
// @param Label[] labels: array of label information received from the server
//
// @return string: HTML of the table
function getEditableHtmlTableOfLabels(labels) {
  // method returns the HTML code of the label list
  var html = getHtmlListOfLabelId(labels, 0, 0);

  if (html.length > 0) {
    html = '<table class="box-table button-right-column no-flex">' + html + '</table>';
  }
  else {
    // if there was no code returned there are no labels to show
    html = noLabels;
  }
  return html;
}

// get HTML list of label id
//
// @param Label[] labels: array of label information received from the server
// @param int id: id of the label
// @param int indenting: the indenting of the current label (0..n)
//
// @return string: the HTML list showing a label and its sub-labels
function getHtmlListOfLabelId(labels, id, indenting) {
  var output = '<tr' + ((indenting === 0)?'':' style="display: none; "') + ' class="cursor-default"><td colspan="2" style="padding-left: ' + (15 * indenting + 15 + ((indenting === 0) ? 0 : 16)) + 'px; text-align: left; "><form class="label-add-form inline"><input type="hidden" class="label-add-parent" value="' + id + '"/><input class="label-add-name inline" style="margin-left: -8px; " type="text" placeholder="Label name" required="true"/>&nbsp;<input class="label-add-button inline" type="submit" value="Add label"/></form></td>';
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

// get single list element of label list
// 
// @param Label label: label object of the label for which the HTML is requested
// @param int indenting: indenting of the label for which the HTML is requested (0..n)
//
// @return string: HTML table row (<tr>) of a single label
function getSingleListElementOfLabelList(label, indenting) {
  var subLabelsCount = numberOfSubLabels(labels, label.id);
  var expanded = expandedLabelsIds.contains(label.id), parentExpanded = expandedLabelsIds.contains(label.parent_label); // label is expanded?

  var output = '<tr data-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting === 0 || parentExpanded)?'':' style="display: none; "') + ' id="label-list-row-id-' + label.id + '">';
  output += '<form class="label-rename-form" id="label-rename-form-' + label.id + '" data-label-id="' + label.id + '"></form>';
  output += '<td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount === 0) ? 16 : 0)) + 'px; " id="label-rename-table-cell-' + label.id + '">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;<label class="checkbox-wrapper"><input type="checkbox" data-label-id="' + label.id + '" ' + (labelAttachedToList(shownListData, label.id)?'checked="true"':'') + '/><span>&nbsp;' + label.name + '</span></label></td>';
  output += '<td><img class="small-menu-open-image" src="img/menu-small.svg" /><div class="small-menu display-none"><input type="submit" class="width-100" form="label-rename-form-' + label.id + '" id="label-rename-button-' + label.id + '" data-action="rename-edit" value="Rename" /><br><input type="button" class="label-add-sub-label width-100" value="Add sub-label"/><br><form class="label-remove-form inline"><input type="hidden" class="label-remove-select" value="' + label.id + '"/><input class="label-remove-button width-100" type="submit" value="Remove" /></form></td></div></tr>';
  return output;
}


// get label index by label id
//
// @param Label[] labels: array of labels in which to search for the labelId
// @param int labelId: id of the label
//
// @return int: index of a label id in the passed labels array
function getLabelIndexByLabelId(labels, labelId) {
  for (var i = 0; i < labels.length; i++) {
    if (labelId == labels[i].id) {
      return i;
    }
  }
  return -1;
}


// number of sub labels
//
// @param Label[] labels: array of labels in which to work
// @param int labelId: id of the label for which to determine the number of sub labels
//
// @return int: number of sub-labels the label with id labelId has
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


// label attached to list
//
// @param List list: list object
// @param int labelId: id of the label
//
// @return bool: true if the label is attached to the given list object
function labelAttachedToList(list, labelId) {
  for (var i = 0; i < list.labels.length; i++) {
    if (labelId == list.labels[i].id) {
      return true;
    }
  }
  return false;
}


// get label ids with indenting
// 
// @param Label[] labels: array of labels in which to work
// @param int indenting: indenting
//
// @return int[]: all label ids with the specified indenting
function getLabelIdsWithIndenting(labels, indenting) {
  var selectedLabels = [];
  for (var i = 0; i < labels.length; i++) {
    if (getLabelIndenting(labels, i) === indenting) {
      selectedLabels.push(labels[i].id);
    }
  }
  return selectedLabels;
}


// get label indenting
// 
// @param Label[] labels: array of labels in which to work
// @param int index: index of the label in the passed labels array
//
// @return int: the indenting of the given label
function getLabelIndenting(labels, index) {
  if (labels[index] === undefined) return undefined;
  if (labels[index].parent_label === 0)
    return 0;

  return getLabelIndenting(labels, getLabelIndexByLabelId(labels, labels[index].parent_label)) + 1;
}

// add label
// 
// add a new label
//
// @param string name: name of the new label
// @param int parentId: id of the parent label
// @param function callback: callback function with Ajax-response as first parameter
function addLabel(name, parentId, callback) {

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
    data = handleAjaxResponse(data);
    callback(data);
  });
}


// attach list to label
//
// attaches the given list the given label
//
// @param int labelId: id of the label to attach the list to
// @param int listId: id of the list to attach the label
// @param function callback: callback function with Ajax-response as first parameter
function attachListToLabel(labelId, listId, callback) {
  setLabelListAttachment(labelId, listId, 1, callback);
}

// detach list from label
//
// detaches the given list from the given label
//
// @param int labelId: id of the label to detach the list from
// @param int listId: id of the list to detatch the label
// @param function callback: callback function with Ajax-response as first parameter
function detachListFromLabel(labelId, listId, callback) {
  setLabelListAttachment(labelId, listId, 0, callback);
}

// set label list attachment
// 
// attaches or detaches a list from a label
// 
// @param int labelId: id of the label
// @param int listId: id of the list
// @param byte attachment: 0 = detached; 1 = attached
// @param function callback: callback function with Ajax-response as first parameter
function setLabelListAttachment(labelId, listId, attachment, callback) {
  if (listId === undefined) {
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
    data = handleAjaxResponse(data);
    callback(data);
  });
}

// remove label
//
// removes a label (not from a list but generally from the user)
//
// @param int labelId: id of the label
// @param function callback: callback function with Ajax-response as first parameter
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
    data = handleAjaxResponse(data);
    callback(data);
  });
}

// rename label
// 
// renames a label
// 
// @param int labelId: id of the label
// @param string labelName: new name of the label
// @param function callback: callback function with Ajax-response as first parameter
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
    data = handleAjaxResponse(data);
    callback(data);
  });
}

// rename list
// 
// renames a word list
// @param int listId: id of the list to rename
// @param string listName: new name for the list
// @param function callback: callback function with Ajax-response as first parameter
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
    data = handleAjaxResponse(data);
    refreshRecentlyUsed(false);
    callback(data);
  });
}

// load word array from string
//
// parses word array from string to allow importing word lists
//
// @param string string: raw data of the words like "meaning 1 (languageSeparator) meaning 2 (wordSeparator) next word (languageSeparator) blabla (wordSeparator)"
// @param string wordSeparator: string separating single words e.g. "\n"
// @param string languageSeparator: string separating the two languages of a word 
// @param int|undefined listId: can be the list id of the word objects which will be returned
//
// @return object: attributes store the actual data
// @return array object.word: array of type Word containig the parsed words
// @return array object.error: array of all rows which have not been parsed because of syntax errors
function loadWordArrayFromString(string, wordSeparator, languageSeparator, listId) {
  var notImported = [], word = [];

  var line = string.split(wordSeparator);
  for (var i = 0; i < line.length; i++) {
    var meaning = line[i].split(languageSeparator);
    if (meaning.length !== 2) { // no valid word found error
      notImported.push(line[i]);
      continue; // next iteration
    }   
    else {
      meaning[0] = meaning[0].trim();
      meaning[1] = meaning[1].trim();

      if (meaning[0].length === 0 && meaning[1].length === 0) {
        // both words don't have a content
        notImported.push(line[i]);
        continue;
      }
      else {
        word.push(new Word(undefined, listId, meaning[0], meaning[1], []));
      }
    }
  }

  return {
    word: word,
    error: notImported
  };
}


// initial load functions
refreshListOfWordLists(true, undefined, true); // load list of word lists
refreshListOfSharedWordLists(true, true); // load list of shared word lists
