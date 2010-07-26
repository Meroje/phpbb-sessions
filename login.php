<?php
define('IN_PHPBB', true);
$phpbb_root_path =  './forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup('');
if (isset($_GET['logout']))
{
   $user->session_kill();
   $user->session_begin();
}
if (isset($_POST['login']))
{
   $username = request_var('username', '', true);
   $password    = request_var('password', '', true);
   $autologin   = (!empty($_POST['autologin'])) ? true : false;
   $viewonline = (!empty($_POST['viewonline'])) ? 0 : 1;
   $admin = 0;
   $result = $auth->login($username, $password, $autologin, $viewonline, $admin);
   if ($result['status'] != LOGIN_SUCCESS)
   {
      $err = $user->lang[$result['error_msg']];
      if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
      {
         $err = (!$config['board_contact']) ? sprintf($user->lang[$result['error_msg']], '', '') : sprintf($user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
      }
   }
   else
   {
      $auth->acl($user->data);
   }
}
if ($user->data['user_id'] != ANONYMOUS)
{
   echo 'Bienvenue ' . $user->data['username'] . '<br />';
   echo '<a href="' . append_sid('mon_login.php?logout=true') . '">Déconnexion</a>';
}
else
{
if($err)
   {
      echo "<font color=red><b>$err</b></font>";
   }
?>
<form method="post">
   <table>
      <tr>
         <td align="right">Pseudo:</td>
         <td><input type="text" tabindex="1" name="username" size="25" /></td>
      </tr>
      <tr>
         <td align="right">Mot de passe:</td>
         <td><input type="password" tabindex="2" name="password" size="25" />
         <br /><a href="<?php echo append_sid("{$phpbb_root_path}ucp.$phpEx?mode=sendpassword"); ?>">J’ai oublié mon mot de passe</a>
         </td>
      </tr>
      <tr>
      
      </tr>
      <tr>
         <td>&nbsp;</td>
         <td><input type="checkbox" name="autologin" tabindex="3" /> Me connecter automatiquement à chaque visite</td>
      </tr>
      <tr>
         <td>&nbsp;</td>
         <td><input type="checkbox" name="viewonline" tabindex="4" /> Cacher mon statut en ligne pour cette session</td>
      </tr>
      <tr>
         <td colspan="2" align="center"><input type="submit" name="login" tabindex="5" value="Connexion" /></td>
      </tr>
   </table>
</form>
<?php
}
?>