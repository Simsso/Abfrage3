<!-- Contact -->
<div id="content-contact">
  <div class="left-column">
    <div class="box">
      <div class="box-head"><? echo $l['Contact']; ?></div>
      <div class="box-body" id="contact-body">
        <p><? echo $l['Feel_free_to_send_me__']; ?></p>
        <form id="contact-form">
          <table>
            <tr>
              <td><? echo $l['Name']; ?></td>
              <td><input type="text" id="contact-name" required="required"/></td>
            </tr>
            <tr>
              <td><? echo $l['Email_address']; ?></td>
              <td><input type="email" id="contact-email" required="required"/></td>
            </tr>
            <tr>
              <td><? echo $l['Subject']; ?></td>
              <td><input type="text" id="contact-subject" required="required"/></td>
            </tr>
            <tr>
              <td><? echo $l['Message']; ?></td>
              <td><textarea id="contact-message" required="required"></textarea></td>
            </tr>
            <tr>
              <td><? echo $l['Bot_protection']; ?></td>
              <td><span id="contact-bot-question"><? echo rand(0, 10) . " + " . rand(0, 1) . "</span> = "; ?><input type="number" id="contact-bot-protection" style="width: 100px; " required="required"/></td>
            </tr>
            <tr>
              <td><input type="submit" value="<? echo $l['Send']; ?>" id="contact-submit"/></td>
              <td></td>
            </tr>
          </table>
        </form>
      </div>
    </div>

    <div class="box">
      <div class="box-head"><? echo $l['Social']; ?></div>
      <div class="box-body">
        <ul class="social-icons">
          <li>
            <a href="https://plus.google.com/106647445778912368795/posts" target="_blank">
              <img src="img/google+.png" alt="<? echo $l['Google_plus']; ?>" class="width-100px">
            </a>
          </li>
          <li>
            <a href="https://www.youtube.com/user/Simssos" target="_blank">
              <img src="img/youtube.png" alt="<? echo $l['Youtube']; ?>" class="width-100px">
            </a>
          </li>
          <li>
            <a href="https://github.com/Simsso" target="_blank">
              <img src="img/github.png" alt="<? echo $l['Github']; ?>" class="width-100px">
            </a>
          </li>
        </ul>
        <br class="clear-both">
      </div>
    </div>
  </div>

  <div class="right-column">
    <div class="box">
      <div class="box-head"><? echo $l['About_me']; ?></div>
      <div class="box-body">
        <div class="right-column-img-wrapper">
          <img src="img/timo-denk.jpg" class="right-column-img" alt="Timo Denk"/>
        </div>
        <? echo $l['P_About_me__']; ?>
      </div>
    </div>
  </div>
</div>

