var noWordListOutput = '<p class="spacer-top-15">You haven\'t created any wordlists yet.</p>';
var noSharedWordListOutput = '<p>There are no shared lists to show.</p>';
var listNotShared = '<p class="spacer-top-15">The selected list isn\'t shared with anyone. Only you can see it.</p>';
var noWordsInList = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var noWordsInListDisallowEdit = '<p class="spacer-top-15">The selected list doesn\'t contain any words yet.</p>';
var shownListId = -1;
var shownListData = null;
var noLabels = '<p>You don\'t have any labels.</p>';
var labels;

var expandedLabelsIds = []


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
                output += '<tr id="list-of-word-lists-row-' + data[i].id + '"><td>' + data[i].name + '</td><td><input type="button" class="inline" value="Edit" data-action="edit" data-list-id="' + data[i].id + '"/>&nbsp;<input type="button" class="inline" value="Delete" data-action="delete" data-list-id="' + data[i].id + '"/></td></tr>';
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
                    enableAllViewEditButtons();
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, true, true);
                }
            });
        })
    );
}

function enableAllViewEditButtons() {
    $('#list-of-word-lists input[type=button]').prop('disabled', false);
    $('#list-of-shared-word-lists input[type=button]').prop('disabled', false);
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
                output += '<td><input type="button" class="inline" value="' + ((data[i].permissions == 1)?'Edit':'View') + '" data-action="' + ((data[i].permissions == 1)?'edit':'view') + '" data-list-id="' + data[i].id + '"/>&nbsp;<input type="button" class="inline" value="Hide" data-action="delete-sharing" data-sharing-id="' + data[i].sharing_id + '" data-list-id="' + data[i].id + '"/></td></tr>';
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
                    enableAllViewEditButtons()
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, true, false);
                }
                else if ($button.data('action') == 'view') { // edit / show list button click
                    enableAllViewEditButtons();
                    $button.prop('disabled', true);
                    loadWordList($button.data('list-id'), true, function() { }, false, false);
                }
            });
        })
    );
}

function showNoListSelectedInfo() {
    $('#word-list-info .box-head > div').html("Word lists");
    $('#word-list-info .box-body').html('<p class="spacer-30">Create or select a word list to get started.</p>');
    $('#word-list-info-words').hide();
    $('#word-list-sharing').hide();
    $('#word-list-label').hide();
}

function loadWordList(id, showLoadingInformation, callback, allowEdit, allowSharing) {
    if (showLoadingInformation) {
        $('#word-list-info .box-head > div').html("Loading...");
        $('#word-list-info .box-body').html(loading);
        $('#word-list-info-words').hide();
        $('#word-list-sharing').hide();
        $('#word-list-label').hide();
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
            
            shownListData = data;
            
            // handle data types
            shownListData.creationTime = parseInt(shownListData.creationTime);
            shownListData.creator.id = parseInt(shownListData.creator.id);
            for (var i = 0; i < shownListData.labels.length; i++) {
                shownListData.labels[i].id = parseInt(shownListData.labels[i].id);
                shownListData.labels[i].parent_label = parseInt(shownListData.labels[i].parent_label);
                shownListData.labels[i].user = parseInt(shownListData.labels[i].user);
            }

            shownListId = id;

            // info box head
            $('#word-list-info .box-head > div').html(data.name);
            
            // info box body
            var wordListInfoBoxBody = '';
            if (!allowSharing) { // not list owner
                wordListInfoBoxBody += '<p>' + data.creator.firstname + ' ' + data.creator.lastname + ' shares this list with you.</p>';
                wordListInfoBoxBody += '<p>You have permissions to ' + (allowEdit?'edit':'view') + ' ' + data.creator.firstname + '\'s list.</p>';
            }
            else {
                wordListInfoBoxBody += '<p>You own this list.</p>';
            }
            
            var creationTime = new Date(parseInt(data.creation_time) * 1000);
            wordListInfoBoxBody += '<p>Creation date: ' + creationTime.toDefaultString() + '</p>';
            
            if (allowEdit) {
                wordListInfoBoxBody += '<label id="import-wrapper" class="button">Import...<input type="file" id="import-data" style="display: none; " /></label> ';
            }
            else {
                
            }
            
            wordListInfoBoxBody += '<input id="export-list" type="button" value="Export..." onclick="exportList()"/>';
            
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
            
            getLabelList(true);
            $('#word-list-label').show();
        })
    );
}

function exportList(list) {
    if (list == undefined)
        list = shownListData;
    
    var output = "";
    
    for (var i = 0; i < list.words.length; i++) {
        output += list.words[i].language1 + " | " + list.words[i].language2 + "\n";
    }
    
    saveTextAsFile(output, list.name + '.txt');
}

function getTableRowOfWord(id, lang1, lang2, allowEdit) {
    return '<tr id="word-row-' + id + '"><td>' + lang1 + '</td><td>' + lang2 + '</td>' + ((allowEdit)?'<td><input type="submit" class="inline" value="Edit" data-action="edit" form="word-row-' + id + '-form"/>&nbsp;<input type="button" class="inline" value="Remove" onclick="removeWord(' + id + ')"/><form id="word-row-' + id + '-form" onsubmit="editSaveWord(event, ' + id + ')"></form></td>':'') + '</tr>';
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


// label functions

function getLabelList(showLoadingInformation) {
    if (showLoadingInformation)
        $('#list-labels-list').html(loading);

    
    jQuery.ajax('server.php', {
        data: {
            action: 'get-labels-of-user'
        },
        type: 'GET',
        error: function(jqXHR, textStatus, errorThrown) {

        }
    }).done(function(data) {
        console.log(data);
        labels = jQuery.parseJSON(data);
        
        // handle data types
        for (var i = 0; i < labels.length; i++) {
            labels[i].id = parseInt(labels[i].id);
            labels[i].parent_label = parseInt(labels[i].parent_label);
            labels[i].user = parseInt(labels[i].user);
        }

        var html = getHtmlListOfLabelId(labels, 0, 0);
        
        if (html.length > 0) {
            html = '<table class="box-table button-right-column">' + html + '</table>';
        }
        else {
            html = noLabels;
        }
        
        $('#list-labels-list').html(html);
        $('#list-labels-list input[type=checkbox]').click( function(){
            var labelId = $(this).data('label-id');
            if($(this).is(':checked')) {
                attachListToLabel(labelId, shownListId, function() {
                    shownListData.labels.push(labels[getLabelIndexByLabelId(labels, labelId)]);
                });
            }
            else {
                detachListFromLabel(labelId, shownListId, function() {
                    shownListData.labels.splice(getLabelIndexByLabelId(shownListData.labels, labelId), 1);
                });
            }
        });
        
        

        $('.label-add-form').on('submit', function(e) {
            e.preventDefault();

            $button = $(this).children('.label-add-button').prop('disabled', true).attr('value', 'Adding label...');
            $nameInput = $(this).children('.label-add-name').prop('disabled', true);
            $parentSelect = $(this).children('.label-add-parent').prop('disabled', true);

            addLabel(shownListId, $nameInput.val(), $parentSelect.val(), function() {
                getLabelList(false);

                $button.prop('disabled', false).attr('value', 'Add label');
                $nameInput.prop('disabled', false).val('');
                $parentSelect.prop('disabled', false).val(null);
            });
        });
        
        $('.label-remove-form').on('submit', function(e) {
            e.preventDefault();

            $(this).children('.label-remove-select').prop('disabled', true);
            $(this).children('.label-remove-button').prop('disabled', true).attr('value', 'Removing...');

            var labelId = $(this).children('.label-remove-select').val();
            removeLabel(labelId, function() {
                $(this).children('.label-remove-select').prop('disabled', false);
                $(this).children('.label-remove-button').prop('disabled', false).attr('value', 'Remove label');
                shownListData.labels.splice(getLabelIndexByLabelId(shownListData.labels, labelId), 1);
                getLabelList(false);
            });
        });
        
        $('.label-add-sub-label').on('click', function() {
            $(this).hide().parent().parent().next().show().children().find('input[type=text]').first().focus();
        });
               
        $('.label-rename-form').on('submit', function(e) {
            e.preventDefault();
            
            
            var labelId = $(this).data('label-id');
            console.log(labelId);
            var $button = $('#label-rename-button-' + labelId);
            var $firstCell = $('#label-rename-table-cell-' + labelId);
            if ($button.data('action') == 'rename-edit') {
                var labelName = labels[getLabelIndexByLabelId(labels, labelId)].name;
                $firstCell.find('label span').html('');
                $firstCell.append('<input type="text" class="inline" value="' + labelName + '" required="true"/>');
                $button.data('action', 'rename-save');
            } 
            else {
                var $input = $firstCell.children('input').first();
                var newName = $input.val();
                
                $button.prop('disabled', true).attr('value', 'Renaming...');
                $input.prop('disabled', true);
                renameLabel(labelId, newName, function() {
                    $button.prop('disabled', false).attr('value', 'Rename').data('action', 'rename-edit');
                    $firstCell.children('input').remove();
                    $firstCell.find('label span').html('&nbsp;' + newName);
                    labels[getLabelIndexByLabelId(labels, labelId)].name = newName;
                });
            }
        });
        
        // expand single labels
        $('.small-exp-col-icon').on('click', function() {
            var $this = $(this);
            var expand = ($this.data('state') == 'collapsed');
            
            var i = 0;
            var $row = $this.parent().parent();
            var allFollowing = $row.nextAll();
            var selfIndenting = $row.data('indenting');
            while (allFollowing.eq(i).data('indenting') > selfIndenting || allFollowing.eq(i).data('indenting') == undefined) {
                if (allFollowing.eq(i).data('indenting') == selfIndenting + 1 || !expand) {
                    if (expand) 
                        allFollowing.eq(i).show();
                    else {
                        allFollowing.eq(i).hide();
                        allFollowing.eq(i).find('.small-exp-col-icon').attr('src', 'img/expand.svg').data('state', 'collapsed');
                        expandedLabelsIds.removeAll(parseInt(allFollowing.eq(i).data('label-id')));
                    }
                }
                i++;
            }
            
            if (expand) {
                $this.data('state', 'expanded').attr('src', 'img/collapse.svg');
                expandedLabelsIds.push(parseInt($row.data('label-id')));
            }
            else {
                $this.data('state', 'collapsed').attr('src', 'img/expand.svg');
                console.log($row);
                expandedLabelsIds.removeAll(parseInt($row.data('label-id')));
            }
        });
    });
}

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

function getSingleListElementOfLabelList(label, indenting) {
    var subLabelsCount = numberOfSubLabels(labels, label.id);
    var expanded = expandedLabelsIds.contains(label.id), parentExpanded = expandedLabelsIds.contains(label.parent_label);
    
    return '<tr data-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting == 0 || parentExpanded)?'':' style="display: none; "') + ' id="label-list-row-id-' + label.id + '"><form class="label-rename-form" id="label-rename-form-' + label.id + '" data-label-id="' + label.id + '"></form><td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount == 0) ? 16 : 0)) + 'px; " id="label-rename-table-cell-' + label.id + '">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;<label class="checkbox-wrapper"><input type="checkbox" data-label-id="' + label.id + '" ' + (labelAttachedToList(shownListData, label.id)?'checked="true"':'') + '/><span>&nbsp;' + label.name + '</span></label></td><td><input type="submit" form="label-rename-form-' + label.id + '" class="inline" id="label-rename-button-' + label.id + '" data-action="rename-edit" value="Rename" />&nbsp;<input type="button" class="label-add-sub-label inline" value="Add sub-label"/>&nbsp;<form class="label-remove-form inline"><input type="hidden" class="label-remove-select inline" value="' + label.id + '"/><input class="label-remove-button inline" type="submit" value="Remove" /></form></td></tr>';
}

function getLabelIndexByLabelId(labels, labelId) {
    for (var i = 0; i < labels.length; i++) {
        if (labelId == labels[i].id) {
            return i;
        }
    }
    return -1;
}

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

function isParentLabelOfId(labels, labelId) {
    // TODO
}

function labelAttachedToList(list, labelId) {
    for (var i = 0; i < list.labels.length; i++) {
        if (labelId == list.labels[i].id) {
            return true;
        }
    }
    return false;
}

function getLabelIdsWithIndenting(labels, indenting) {
    var selectedLabels = new Array();
    for (var i = 0; i < labels.length; i++) {
        if (getLabelIndenting(labels, i) == indenting) {
            selectedLabels.push(labels[i].id);
        }
    }
    return selectedLabels;
}

function getLabelIndenting(labels, index) {
    if (labels[index].parent_label == 0)
        return 0;
    
    return getLabelIndenting(labels, getLabelIndexByLabelId(labels, labels[index].parent_label)) + 1;
}

function addLabel(listId, name, parentId, callback) {
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
        console.log(data);
        callback();
    });
}

function attachListToLabel(labelId, listId, callback) {
    setLabelListAttachment(labelId, listId, 1, callback);
}

function detachListFromLabel(labelId, listId, callback) {
    setLabelListAttachment(labelId, listId, 0, callback);
}

function setLabelListAttachment(labelId, listId, attachment, callback) {
    if (listId == undefined) {
        listId = shownListId;
    }
    
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
        console.log(data);
        callback();
    });
}

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
        console.log(data);
        callback();
    });
}

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
        console.log(data);
        callback();
    });
}


// refresh functions
showNoListSelectedInfo();
refreshListOfWordLists(true);
refreshListOfSharedWordLists(true);