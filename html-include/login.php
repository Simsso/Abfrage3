
<div class="box">
  <div class="box-head"><? echo $l['Login']; ?></div>
  <div class="box-body">
    <form method="post" name="login" action="server.php?action=login" data-submit-loading="true">
      <table>
        <tr>
          <td><? echo $l['Email_address']; ?></td>
          <td><input type="email" name="email" placeholder="" required="required" value="<? if(!$_GET['signup_success'] == "false") echo($_GET['email']); ?>"/></td>
        </tr>
        <tr>
          <td><? echo $l['Password']; ?></td>
          <td><input type="password" name="password" placeholder="" required="required"/></td>
        </tr>
        <tr>
          <td colspan="2" class="padding-v-6"><label><input type="checkbox" name="stay-logged-in" value="1" class="initial-width initial-height" checked/>&nbsp;$l:Stay_logged_in</label></td>  
        </tr>
        <tr>
          <td><input type="submit" value="$l:Login"/></td>
          <td></td>
        </tr>
        <!--<tr>
<td colspan="2" style="padding-top: 5px; "><a href="#"><small>Forgot your password?</small></a></td>
</tr>-->
      </table>
    </form>
  </div>
</div>