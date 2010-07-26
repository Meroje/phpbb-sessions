<?php
define('IN_PHPBB', true);
$phpbb_root_path =  './forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');
$error=array();
$data = array(
   'username'         => utf8_normalize_nfc(request_var('username', '', true)),
   'password'         => request_var('password', '', true),
   'password_confirm'   => request_var('password_confirm', '', true),
   'email'            => strtolower(request_var('email', '')),
   'email_confirm'      => strtolower(request_var('email_confirm', '')),
);
if (isset($_POST['submit']))
{
   $error = validate_data($data, array(
      'username'         => array(
         array('string', false, $config['min_name_chars'], $config['max_name_chars']),
         array('username', '')),
      'password'      => array(
         array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
         array('password')),
      'password_confirm'   => array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
      'email'            => array(
         array('string', false, 6, 60),
         array('email')),
      'email_confirm'      => array('string', false, 6, 60),
   ));
   $error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
   if (!sizeof($error))
   {
      if ($data['password'] != $data['password_confirm'])
      {
         $error[] = $user->lang['NEW_PASSWORD_ERROR'];
      }
      if ($data['email'] != $data['email_confirm'])
      {
         $error[] = $user->lang['NEW_EMAIL_ERROR'];
      }
   }
   if (!sizeof($error))
   {
      $group_name =  'REGISTERED';
      $sql = 'SELECT group_id
               FROM ' . GROUPS_TABLE . "
               WHERE group_name = '" . $db->sql_escape($group_name) . "'
                  AND group_type = " . GROUP_SPECIAL;
      $result = $db->sql_query($sql);
      $row = $db->sql_fetchrow($result);
      $db->sql_freeresult($result);
      if (!$row)
      {
         trigger_error('NO_GROUP');
      }
      $group_id = $row['group_id'];
      $user_row = array(
         'username'            => $data['username'],
         'user_password'         => phpbb_hash($data['password']),
         'user_email'         => $data['email'],
         'group_id'            => (int) $group_id,
         'user_timezone'         => (float) $config['board_timezone'],
         'user_dst'            => $config['board_dst'],
         'user_lang'            => basename($user->lang_name),
         'user_type'            => USER_NORMAL,
         'user_actkey'         => '',
         'user_ip'            => $user->ip,
         'user_regdate'         => time(),
         'user_inactive_reason'   => 0,
         'user_inactive_time'   => 0,
      );
      $user_id = user_add($user_row);
      if ($user_id === false)
      {
         trigger_error('NO_USER', E_USER_ERROR);
      }
      $url = append_sid('./index.php');
      die( '<html>
         <head>
            <META http-equiv="Refresh"
            content="10; URL=' . $url . '">
         </head>
         <body>
         Votre compte a été enregistré avec succès<br />
         Vous allez être maintenant redirigé vers <a href="' . $url . '">la page d\'index</a>
         </body>
      </html>');
   }
}
echo '<html>
<head>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
   <title>Vous enregistrer</title>
</head>
<body>
   <form method="post">
<h1>Vous enregistrer</h1>';
if (sizeof($error))
{
   echo '<font color="red"><b>' . implode('<br />', $error) . '</b></font>';;

}
?>
<table>
   <tr>
      <td align="right">Pseudonyme:</td>
      <td><input type="text" tabindex="1" name="username" size="25" value="<?php echo $data['username']; ?>" /></td>
   </tr>
   <tr>
      <td align="right">Mot de passe:</td>
      <td><input type="password" tabindex="2" name="password" size="25" value="<?php echo $data['password']; ?>" /></td>
   </tr>
   <tr>
      <td align="right">Confirmez votre mot de passe:</td>
      <td><input type="password" tabindex="3" name="password_confirm" size="25" value="<?php echo $data['password_confirm']; ?>" /></td>
   </tr>
   <tr>
      <td align="right">Email:</td>
      <td><input type="text" tabindex="4" name="email" size="25" maxlength="100" value="<?php echo $data['email']; ?>" /></td>
   </tr>
   <tr>
      <td align="right">Confirmez votre Email</td>
      <td><input type="text" tabindex="5" name="email_confirm" size="25" maxlength="100" value="<?php echo $data['email_confirm']; ?>" /></td>
   </tr>
   <tr>
      <td colspan="2" align="center">
         <input type="reset" value="Remettre &agrave; z&eacute;ro" name="reset" />&nbsp;
         <input type="submit" name="submit" id ="submit" value="S'enregistrer" />
      </td>
   </tr>
</table>
</form>
</body>
</html>