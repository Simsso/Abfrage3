var queryLabels = null;
var queryAttachments = null;
var queryLists = null;

// get label list of user
function refreshQueryLabelList(showLoadingInformation) {
  if (showLoadingInformation)
    $('#query-label-selection').html(loading);

  // send request
  jQuery.ajax('server.php', {
    data: {
      action: 'get-query-data'
    },
    type: 'GET',
    error: function(jqXHR, textStatus, errorThrown) {

    }
  }).done(function(data) {
    console.log(data);
    var dataJSON = jQuery.parseJSON(data); // parse JSON
    console.log(dataJSON); // debug
    queryLabels = dataJSON.labels;
    queryAttachments = dataJSON.label_list_attachments;
    queryLists = dataJson.lists;


    // handle data types
    for (var i = 0; i < queryLabels.length; i++) {
      queryLabels[i].id = parseInt(queryLabels[i].id); // id is an integer
      queryLabels[i].parent_label = parseInt(queryLabels[i].parent_label); // parent label id is an integer
      queryLabels[i].user = parseInt(queryLabels[i].user); // label user id is an integer
    }
    
    $('#query-label-selection').html(getHtmlTableOfLabelsQuery(queryLabels));
    
    
    // checkbox click event
    $('#query-label-selection input[type=checkbox]').click( function(){
      // read label id from checkbox data tag
      var labelId = $(this).data('label-id');

      // checkbox has been checked
      if($(this).is(':checked')) {
        
      }
      // checkbox has been unchecked
      else { 
        
      }
    });
    
    // expand functionallity
    // expand single labels
    $('#query-label-selection .small-exp-col-icon').on('click', function() {
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


// label list functions

function getHtmlTableOfLabelsQuery(queryLabels) {
  // method returns the HTML code of the label list
  var html = getHtmlListOfLabelIdQuery(queryLabels, 0, 0);

  if (html.length > 0) {
    html = '<table class="box-table">' + html + '</table>';
  }
  else {
    // if there was no code returned there are no labels to show
    html = noLabels;
  }
  return html;
}

// get HTML list of label id
// returns the HTML list showing a label and it's sub-labels
function getHtmlListOfLabelIdQuery(queryLabels, id, indenting) {
  var output = '';
  var labelIds = getLabelIdsWithIndenting(queryLabels, indenting);
  for (var i = 0; i < labelIds.length; i++) {
    var currentLabel = queryLabels[getLabelIndexByLabelId(queryLabels, labelIds[i])];
    if (currentLabel.parent_label == id) {
      output += getSingleListElementOfLabelListQuery(currentLabel, indenting);
      output += getHtmlListOfLabelIdQuery(queryLabels, labelIds[i], indenting + 1);
    }
  }
  return output;
}

// returns the HTML-row of a single label
function getSingleListElementOfLabelListQuery(label, indenting) {
  var subLabelsCount = numberOfSubLabels(queryLabels, label.id);
  var expanded = false; // show all labels collapsed

  return '<tr data-label-id="' + label.id + '" data-indenting="' + indenting + '"' + ((indenting == 0)?'':' style="display: none; "') + '><td class="label-list-first-cell" style="padding-left: ' + (15 * indenting + 15 + ((subLabelsCount == 0) ? 16 : 0)) + 'px; ">' + ((subLabelsCount > 0)?'<img src="img/' + (expanded?'collapse':'expand') + '.svg" data-state="' + (expanded?'expanded':'collapsed') + '" class="small-exp-col-icon" />':'') + '&nbsp;<label class="checkbox-wrapper"><input type="checkbox" data-label-id="' + label.id + '"/><span>&nbsp;' + label.name + '</span></label></td></tr>';
}

refreshQueryLabelList(true);