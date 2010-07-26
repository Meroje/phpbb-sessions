<?php
define('IN_PHPBB', true);
$phpbb_root_path =  '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');

page_header('Inscription');

$error=array();
$success = false;
$message_error = '';
$url_redirect='';
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
      $success = true;
      $url_redirect = append_sid('./index.php');
      $message = $user->lang['ACCOUNT_ADDED'] .  '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . $url_redirect . '">', '</a>');
   }
   else
   {
      $message_error = implode('<br />', $error);
   }
}
$template->set_filenames(array('body' => 'register_body.html'));
$template->assign_vars(array(
   'S_SUCCESS'         => $success,
   'MESSAGE'         => $message,
   'MESSAGE_ERROR'      => $message_error,
   'U_REDIRECT'      => $url_redirect,
   'USERNAME'         => $data['username'],
   'PASSWORD'         => $data['password'],
   'PASSWORD_CONFIRM'   => $data['password_confirm'],
   'EMAIL'            => $data['email'],
   'EMAIL_CONFIRM'      => $data['email_confirm']
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
$template->display('body');
?>
