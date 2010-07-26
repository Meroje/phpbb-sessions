<?php
define('IN_PHPBB', true);
$phpbb_root_path =  '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('Mon Login');

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
$template->set_filenames(array('body' => 'mon_login_body.html'));
$template->assign_vars(array(
   'TITLE' => ($user->data['user_id'] != ANONYMOUS) ? $user->lang['WELCOME'] : $user->lang['LOGIN'],
   'S_REGISTERED' => ($user->data['user_id'] != ANONYMOUS) ? true : false,
   'S_ERROR' => $err,
   'USERNAME' => $user->data['username'],
   'U_LOGOUT' => append_sid('mon_login.php?logout=true'),
   'U_SEND_PASSWORD' => append_sid("{$phpbb_root_path}ucp.$phpEx?mode=sendpassword")
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
$template->display('body');
?>
