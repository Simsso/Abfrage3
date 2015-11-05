<div class="box" id="content-tour">
  <div class="box-body tour-container font-size-100">
    <div class="text-align-center">
      <table class="width-100 headline spacer-15">
        <tr>
          <td><h2><? echo $l['Welcome_to']; ?>&nbsp;</h2></td>
          <td><img class="height-46px" src="img/logo-white.svg"/></td>
        </tr>
      </table>
      <p>
        <? echo $l['Abfrage3_is_an_online__']; ?>
      </p>
      <p>
        <img src="/img/mockup-image.jpg" class="full-width">
      </p>
      <p class="italic"><? echo $l['Here_is_how_it_works']; ?>:</p>
    </div>


    <div class="tour-element">
      <hr class="spacer-30">
      <h2><? echo $l['Create_lists_and_add_words']; ?></h2>
      <div>
        <div class="col-l">
          <p><? echo $l['Define_a_name_and_create__']; ?></p>
        </div>
        <div class="col-r">
          <div class="box">
            <div class="box-head">
              <img src="img/server.svg">
              <? echo $l['Your_word_lists']; ?>
            </div>
            <div class="box-body">
              <input type="text" placeholder="<? echo $l['Word_list_name']; ?>">
              <input type="button" value="<? echo $l['Create_list']; ?>">
              <hr class="spacer-top-15">
              <div><table class="box-table cursor-pointer"><tbody><tr><td><? echo $l['My_first_word_list']; ?></td></tr><tr><td><? echo $l['Difficult_new_words']; ?></td></tr></tbody></table></div>
            </div>
          </div>
        </div>
        <br class="clear-both">

        <div class="col-l">
          <p><? echo $l['Add_words_to__']; ?></p>
          <p><? echo $l['If_you_already_have__p']; ?></p>
        </div>
        <div class="col-r">
          <div class="box">
            <div class="box-head">
              <img src="img/grid.svg">
              <? echo $l['Words']; ?>
            </div>
            <div class="box-body">
              <div>
                <input type="text" placeholder="<? echo $l['German']; ?>">
                <input type="text" placeholder="<? echo $l['English']; ?>">
                <input type="button" value="<? echo $l['Add_word']; ?>">
                <hr class="spacer-top-15">
              </div>
              <div>
                <table class="box-table button-right-column">
                  <tbody>
                    <tr class="bold cursor-default"><td><? echo $l['German']; ?></td><td><? echo $l['English']; ?></td><td></td></tr>
                    <tr><td>abwechslungsreich</td><td>diversified</td><td><input type="button" class="inline" value="<? echo $l['Edit']; ?>">&nbsp;<input type="button" class="inline" value="<? echo $l['Remove']; ?>"></td></tr>
                    <tr><td>Vorschlag</td><td>proposal</td><td><input type="button" class="inline" value="<? echo $l['Edit']; ?>">&nbsp;<input type="button" class="inline" value="<? echo $l['Remove']; ?>"></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <br class="clear-both">
      </div>
    </div>


    <div class="tour-element">
      <hr class="spacer-30">
      <h2><? echo $l['Learn_words']; ?></h2>
      <div>
        <div class="col-l">
          <? echo $l['P_Tour_learn_words__']; ?>
        </div>
        <div class="col-r">
          <div class="box" id="query-box">
            <div class="box-head">
              <img src="img/question.svg">
              <? echo $l['Test']; ?>
            </div>
            <div class="box-body">
              <div>
                <table class="width-100">
                  <tbody>
                    <tr>
                      <td class="width-150px"><span class="language"><? echo $l['English']; ?></span>:&nbsp;</td>
                      <td id="query-question">swift</td>
                    </tr>
                    <tr>
                      <td class="width-150px"><span class="language"><? echo $l['German']; ?></span>:&nbsp;</td>
                      <td>
                        <input type="text" id="query-answer" class="unremarkable width-100">
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <br class="clear-both">

        <div class="col-l">
          <? echo $l['P_Tour_learn_by_typing__']; ?>
        </div>
        <div class="col-r">
          <div class="box" id="query-box">
            <div class="box-body">
              <div id="query-div">
                <table class="width-100" id="query-content-table">
                  <tbody>
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang1"><? echo $l['English']; ?></span>:&nbsp;</td>
                      <td id="query-question">leverage</td>
                    </tr>
                    <tr>
                      <td class="width-150px"><span class="language" id="query-lang2"><? echo $l['German']; ?></span>:&nbsp;</td>
                      <td id="query-answer-table-cell-buttons" class="display-none" style="display: table-cell;">
                        <table class="width-100">
                          <tbody>
                            <tr>
                              <td class="width-33"><input id="query-answer-known" type="button" value="<? echo $l['I_know_']; ?>" class="height-50px width-100"></td>
                              <td class="width-33"><input id="query-answer-not-sure" type="button" value="<? echo $l['Not_sure_']; ?>" class="height-50px width-100"></td>
                              <td class="width-33"><input id="query-answer-not-known" type="button" value="<? echo $l['No_idea_']; ?>" class="height-50px width-100"></td>
                            </tr>
                          </tbody>
                        </table>
                        <div id="query-answer-buttons" style="display: none;"></div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <br class="clear-both">
      </div>
    </div>


    <div class="tour-element">
      <hr class="spacer-30">
      <h2><? echo $l['Share_your_word_lists']; ?></h2>
      <div>
        <div class="col-l">
          <? echo $l['P_Tour_share_lists']; ?>

        </div>
        <div class="col-r">
          <div class="box" id="word-list-sharing" style="display: block;">
            <div class="box-head">
              <img src="img/share.svg">
              <? echo $l['Share']; ?>
            </div>
            <div class="box-body">
              <input id="share-list-other-user-email" type="text" placeholder="<? echo $l['Email_address']; ?>" required="true">
              <select id="share-list-permissions" required="true">
                <option value="2"><? echo $l['Can_view']; ?></option>
                <option value="1"><? echo $l['Can_edit']; ?></option>
              </select>
              <input id="share-list-submit" type="button" value="<? echo $l['Share']; ?>">
              <hr class="spacer-top-15">
              <div id="list-sharings">
                <table class="box-table button-right-column">
                  <tbody>
                    <tr class="bold cursor-default"><td><? echo $l['Name']; ?></td><td></td><td></td></tr>
                    <tr><td><? echo $l['My_classmate']; ?></td><td><? echo $l['Can_view']; ?></td><td><input type="button" class="inline" value="<? echo $l['Stop_sharing']; ?>"></td></tr>
                    <tr><td><? echo $l['Another_guy']; ?></td><td><? echo $l['Can_edit']; ?></td><td><input type="button" class="inline" value="<? echo $l['Stop_sharing']; ?>"></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <br class="clear-both">

        <div class="col-l">
          <p><? echo $l['P_Tour_add_other_users__']; ?></p>
        </div>
        <div class="col-r">
          <div class="box">
            <div class="box-head">
              <img src="img/users.svg">
              <? echo $l['People_youve_added']; ?>
            </div>
            <div class="box-body" data-start-state="expanded">
              <div id="user-add-message"></div>
              <input id="user-add-email" type="email" placeholder="<? echo $l['Email_address']; ?>" required="true">
              <input id="user-add-button" type="button" value="<? echo $l['Add_user']; ?>">
              <hr class="spacer-top-15">
              <div id="people-you-have-added">
                <table class="box-table button-right-column">
                  <tbody>
                    <tr class="bold cursor-default"><td><? echo $l['Name']; ?></td><td><? echo $l['Email_Address']; ?></td><td></td></tr>
                    <tr><td><? echo $l['My_classmate']; ?></td><td>bla@gmail.com</td><td><input type="button" class="inline" value="<? echo $l['Remove']; ?>"></td></tr>
                    <tr><td><? echo $l['Another_guy']; ?></td><td>email2@gmail.com</td><td><input type="button" class="inline" value="<? echo $l['Remove']; ?>"></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <br class="clear-both">
      </div>
    </div>


    <!--<div class="tour-element">
<hr class="spacer-30">
<h2></h2>
<div>
<div class="col-l">
<p></p>
</div>
<div class="col-r">

</div>
<br class="clear-both">
</div>
</div>-->
  </div>
</div>
