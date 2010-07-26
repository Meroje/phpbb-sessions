<?php
define('IN_PHPBB', true);
$phpbb_root_path = '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('A Propos');

$forum_id = request_var('forum_id', 0);
$where = ($forum_id) ? " WHERE forum_id=$forum_id" : '';
$sql = 'SELECT forum_id,topic_id, topic_time, topic_title, topic_views, topic_replies, topic_poster, topic_first_poster_name, topic_first_poster_colour, topic_last_post_id, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour, topic_last_post_time
   FROM ' . TOPICS_TABLE . 
      $where .
      ' ORDER BY topic_time DESC ' .
      ' LIMIT 0 , 10 ';
$result = $db->sql_query($sql);
$template->set_filenames(array('body' => 'news_body.html'));
$template->assign_vars(array(
   'LAST_POST_IMG'            => $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
));
while($row = $db->sql_fetchrow($result))
{
   $topic_id = $row['topic_id'];
   $view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . (($row['forum_id']) ? $row['forum_id'] : $forum_id) . '&amp;t=' . $topic_id);
   $template->assign_block_vars('topicrow', array(
      'FIRST_POST_TIME'   => $user->format_date($row['topic_time']),
      'LAST_POST_AUTHOR'   => get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
      'LAST_POST_TIME'   => $user->format_date($row['topic_last_post_time']),
      'REPLIES'         => $row['topic_replies'],
      'TOPIC_AUTHOR'      => get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
      'TOPIC_TITLE'      => censor_text($row['topic_title']),
      'U_LAST_POST'      => $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#p' . $row['topic_last_post_id'],
      'U_VIEW_TOPIC'      => $view_topic_url,
      'VIEWS'            => $row['topic_views'],
   ));
}

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>
