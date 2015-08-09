var noWordListOutput = '<p class="spacer-top-15">You haven\'t created any wordlists yet.</p>';
var noSharedWordListOutput = '<p>There are no shared lists to show.</p>';
var listNotShared = '<p class="spacer-top-15">The selected list isn\'t shared with anyone. Only you can see it.</p>';
var noWordsInList = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var noWordsInListDisallowEdit = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var shownListId = -1;

$('#word-list-add-form').on('submit', function(e) {
    // dont visit action="..." page
    e.preventDefault();

    $('#word-list-add-name').prop('disabled', true);
    $('#word-list-add-button').prop('disabled', true).attr('value', 'Creating list...');
    addWordList($('#word-list-add-name').val(), function(data) {
        // finished callback
        $('#word-list-add-name').prop('disabled', false).val('');
        $('#word-list-add-button').prop('disabled', false).attr('value', 'Create list');

        refreshListOfWordLists(false);
        loadWordList(data.id, true, function() { }, true, true);
    });
});

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
        console.log(data);
        data = jQuery.parseJSON(data);

        callback(data);
    });
}

function refreshListOfWordLists(showLoadingInformation) {
    if (showLoadingInformation)
        $('#list-of-word-lists').html(loading);
    
    showNoListSelectedInfo();

    ajaxRequests.loadListOfWordLists.add(
        jQuery.ajax('server.php', {
            data: {
                action: 'list-of-word-lists'
            },
            type: 'GET',
            error: function(jqXHR, textStatus, errorThrown) {

            }
        }).done(function(data) {
            console.log(data);
            data = jQuery.parseJSON(data);

            var output = "";
            for (var i = 0; i < data.length; i++) {
                output += '<tr id="list-of-word-lists-row-' + data[i].id + '"><td>' + data[i].name + '</td><td><input type="button" class="inline" value="Edit" data-action="edit" data-list-id="' + data[i].id + '"/> <input type="button" class="inline" value="Delete" data-action="delete" data-list-id="' + data[i].id + '"/></td></tr>';
            }
            if (output.length == 0) {
                output = noWordListOutput;
            }
            else {
                output = '<table class="box-table button-right-column"><tr class="bold"><td>Name</td><td></td></tr>' + output + '</table>';
            }
            $('#list-of-word-lists').html(output);
            $('#list-of-word-lists input[type=button]').on('click', function() {
                $button = $(this);

                if ($button.data('action') == 'delete') { // delete list button click
                    $button.prop('disabled', true).attr('value', 'Deleting...');
                    deleteWordList($button.data('list-id'), true, function() { });

                    if ($button.data('list-id') == shownListId) {
                        showNoListSelectedInfo();
                    }
                }
                else if ($button.data('action') == 'edit') { // edit / show list button click
                    $('#list-of-word-lists input[type=button]').prop('disabled', false);
                    $('#list-of-word-lists input[type=button]').prop('disabled', false);
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, true, true);
                }
            });
        })
    );
}

function refreshListOfSharedWordLists(showLoadingInformation) {
    if (showLoadingInformation)
        $('#list-of-shared-word-lists').html(loading);
    
    showNoListSelectedInfo();

    ajaxRequests.loadListOfSharedWordLists.add(
        jQuery.ajax('server.php', {
            data: {
                action: 'list-of-shared-word-lists-with-user'
            },
            type: 'GET',
            error: function(jqXHR, textStatus, errorThrown) {

            }
        }).done(function(data) {
            console.log(data);
            data = jQuery.parseJSON(data);

            var output = "";
            for (var i = 0; i < data.length; i++) {
                output += '<tr id="list-of-shared-word-lists-row-' + data[i].sharing_id + '">';
                output += '<td>' + data[i].name + '</td>';
                output += '<td><input type="button" class="inline" value="' + ((data[i].permissions == 1)?'Edit':'View') + '" data-action="' + ((data[i].permissions == 1)?'edit':'view') + '" data-list-id="' + data[i].id + '"/> <input type="button" class="inline" value="Hide" data-action="delete-sharing" data-sharing-id="' + data[i].sharing_id + '"/></td></tr>';
            }
            if (output.length == 0) {
                output = noSharedWordListOutput;
            }
            else {
                output = '<table class="box-table button-right-column"><tr class="bold"><td>Name</td><td></td></tr>' + output + '</table>';
            }
            $('#list-of-shared-word-lists').html(output);
            $('#list-of-shared-word-lists input[type=button]').on('click', function() {
                $button = $(this);

                if ($button.data('action') == 'delete-sharing') { // delete list button click
                    $button.prop('disabled', true).attr('value', 'Hiding...');
                    setSharingPermissionsBySharingId($button.data('sharing-id'), 0, function() { 
                        $('#list-of-shared-word-lists-row-' + $button.data('sharing-id')).remove();
                        // still rows left
                        if ($('#list-of-shared-word-lists tr').length == 1) {
                            $('#list-of-shared-word-lists').html(noSharedWordListOutput);
                        }
                    });

                    if ($button.data('list-id') == shownListId) {
                        showNoListSelectedInfo();
                    }
                }
                else if ($button.data('action') == 'edit') { // edit / show list button click
                    $('#list-of-shared-word-lists input[type=button]').prop('disabled', false);
                    $('#list-of-word-lists input[type=button]').prop('disabled', false);
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, true, false);
                }
                else if ($button.data('action') == 'view') { // edit / show list button click
                    $('#list-of-shared-word-lists input[type=button]').prop('disabled', false);
                    $('#list-of-word-lists input[type=button]').prop('disabled', false);
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, false, false);
                }
            });
        })
    );
}

function showNoListSelectedInfo() {
    $('#word-list-info .box-head > div').html("Word lists");
    $('#word-list-info .box-body').html('<p class="spacer-30">Create or select a word list to start editing.</p>');
    $('#word-list-info-words').hide();
    $('#word-list-sharing').hide();
}

function loadWordList(id, showLoadingInformation, callback, allowEdit, allowSharing) {
    if (showLoadingInformation) {
        $('#word-list-info .box-head > div').html("Loading...");
        $('#word-list-info .box-body').html(loading);
        $('#word-list-info-words').hide();
        $('#word-list-sharing').hide();
    }
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
            console.log(data);
            data = jQuery.parseJSON(data);

            shownListId = id;

            // info box head
            $('#word-list-info .box-head > div').html(data.name);
            
            // info box body
            var wordListInfoBoxBody = '';
            if (allowEdit) {
                
            }
            else {
                
            }
            
            wordListInfoBoxBody += '<input id="export-list" type="button" value="Export..." />';
            
            $('#word-list-info .box-body').html(wordListInfoBoxBody);

            // sharing box
            if (allowSharing) {
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
                var wordListHTML = "";
                for (var i = 0; i < data.words.length; i++) {
                    console.log(data.words[i]);
                    wordListHTML += getTableRowOfWord(data.words[i].id, data.words[i].language1, data.words[i].language2, allowEdit);
                }
                wordListHTML = getTableOfWordList(wordListHTML, allowEdit);
                $('#words-in-list').html(wordListHTML);
            }
            $('#word-list-info-words').show();
            if (allowEdit) 
                $('#words-add').show();
            else 
                $('#words-add').hide();
        })
    );
}

function getTableRowOfWord(id, lang1, lang2, allowEdit) {
    return '<tr id="word-row-' + id + '"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + ((allowEdit)?'<td><input type="submit" class="inline" value="Edit" data-action="edit" form="word-row-' + id + '-form"/> <input type="button" class="inline" value="Remove" onclick="removeWord(' + id + ')"/><form id="word-row-' + id + '-form" onsubmit="editSaveWord(event, ' + id + ')"></form></td>':'') + '</tr>';
}
                                                                                          
function getTableOfWordList(content, allowEdit) {
    return '<table id="word-list-table" class="box-table ' + ((allowEdit)?'button-right-column':'') + '"><tr class="bold"><td>First language</td><td>Second language</td>' + (allowEdit?'<td></td>':'') + '</tr>' + content + '</table>';
} 

function editSaveWord(event, id) {
    event.preventDefault();
    var $row = $('#word-row-' + id);
    var $editSaveButton = $row.find('input[type=submit]');
    var $cell1 = $row.children().eq(0), $cell2 = $row.children().eq(1);

    if ($editSaveButton.data('action') == 'edit') { // edit mode
        $editSaveButton.data('action', 'save').attr('value', 'Save');

        $cell1.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language1-' + id + '" value="' + $cell1.html() + '" />');
        $cell2.html('<input type="text" class="inline-both" form="word-row-' + id + '-form" id="word-edit-input-language2-' + id + '" value="' + $cell2.html() + '" />');
    }
    else { // save
        var $lang1Input = $('#word-edit-input-language1-' + id), $lang2Input = $('#word-edit-input-language2-' + id);
        $lang1Input.prop('disabled', true);
        $lang2Input.prop('disabled', true);
        $editSaveButton.prop('disabled', true).attr('value', 'Saving...');

        saveWord(id, $lang1Input.val(), $lang2Input.val(), function() {
            $editSaveButton.prop('disabled', false).attr('value', 'Edit').data('action', 'edit');
            $cell1.html($lang1Input.val());
            $cell2.html($lang2Input.val());
        });
    }
}

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
        callback();
    });
}

function removeWord(id) {
    var $row = $('#word-row-' + id);
    var $removeButton = $row.find('* input[type=button]');
    $removeButton.prop('disabled', true).attr('value', 'Removing...');

    jQuery.ajax('server.php', {
        data: {
            action: 'remove-word',
            word_id: id
        },
        type: 'GET',
        error: function(jqXHR, textStatus, errorThrown) {

        }
    }).done(function(data) {
        $row.remove();
    });
}

function deleteWordList(id) {
    jQuery.ajax('server.php', {
        data: {
            action: 'delete-word-list',
            word_list_id: id
        },
        type: 'GET',
        error: function(jqXHR, textStatus, errorThrown) {

        }
    }).done(function(data) {

        $('#list-of-word-lists-row-' + id).remove();
        // no list table row anymore (except from the th)
        if ($('#list-of-word-lists tr').length == 1) {
            $('#list-of-word-lists').html(noWordListOutput);
        }
    });
}

function saveListEdits() {
    if (shownListId == -1) 
        return;

}

$('#words-add-form').on('submit', function(e) {
    e.preventDefault();

    var lang1 = $('#words-add-language1').val(), lang2 = $('#words-add-language2').val();
    $('#words-add-language1').val('').focus();
    $('#words-add-language2').val('');

    addWord(lang1, lang2, true);
});

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
        if ($('#word-list-table').length == 0) { // no words added
            var wordListHTML = getTableOfWordList("", allowEdit);
            $('#words-in-list').html(wordListHTML);
        }
        $('#word-list-table tr:nth-child(1)').after(getTableRowOfWord(data, lang1, lang2, allowEdit));

        new Toast('The word "' + lang1 + '" - "' + lang2 + '" has been added successfully.');
    });
}

function refreshListSharings(showLoadingInformation, wordListId) { 
    if (wordListId == undefined)
        wordListId = shownListId;
    
    
    $('#word-list-sharing').show();
    if (showLoadingInformation) {
        $('#list-sharings').html(loading);
    }
    
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
            console.log(data);
            data = jQuery.parseJSON(data);

            if (data.length == 0) { // list not shared yet
                $('#list-sharings').html(listNotShared);
            }
            else { // list shared with at least one user
                var output = "";
                for (var i = 0; i < data.length; i++) {
                    output += '<tr id="list-shared-with-row-' + data[i].id + '">';
                    output += '<td>' + data[i].user.firstname + ' ' + data[i].user.lastname + '</td>';
                    output += '<td>' + ((data[i].permissions == 1)?'Can edit':'Can view') + '</td>';
                    output += '<td><input type="button" class="inline" value="Stop sharing" data-action="delete-sharing" data-sharing-id="' + data[i].id + '"/></td></tr>';
                }
                output = '<table class="box-table button-right-column"><tr class="bold"><td>Name</td><td>Permissions</td><td></td></tr>' + output + '</table>';

                $('#list-sharings').html(output);
                $('#list-sharings input[type=button]').on('click', function() {
                    $button = $(this);
                    $button.prop('disabled', true).attr('value', 'Stopping sharing...');
                    
                    setSharingPermissionsBySharingId($button.data('sharing-id'), 0, function() {
                        $('#list-shared-with-row-' + $button.data('sharing-id')).remove();
                        // still rows left
                        if ($('#list-sharings tr').length == 1) {
                            $('#list-sharings').html(listNotShared);
                        }
                    });
                });
            }
        })
    );
}

$('#share-list-form').on('submit', function(e) {
    // dont visit action="..." page
    e.preventDefault();

    $('#share-list-other-user-email').prop('disabled', true);
    $('#share-list-permissions').prop('disabled', true);
    $('#share-list-submit').prop('disabled', true).attr('value', 'Sharing...');
    
    setSharingPermissions(shownListId, $('#share-list-other-user-email').val(), $('#share-list-permissions').val(), function() {
        // finished callback
        $('#share-list-other-user-email').prop('disabled', false).val('');
        $('#share-list-permissions').prop('disabled', false);
        $('#share-list-submit').prop('disabled', false).attr('value', 'Share');

        refreshListSharings(false, shownListId);
    });
});
            
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
        console.log(data);
        data = jQuery.parseJSON(data);

        callback(data);
    });
}

function setSharingPermissions(listId, email, permissions, callback) {
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


// refresh functions
showNoListSelectedInfo();
refreshListOfWordLists(true);
refreshListOfSharedWordLists(true);