<?php
/**
*
* @package phpBB Extension - Online users avatar
* @copyright (c) 2016 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\onlineusersavatar\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\user		$user
	*
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.obtain_users_online_string_sql'		=> 'obtain_users_online_string_sql',
			'core.obtain_users_online_string_modify'	=> 'obtain_users_online_string_modify',
		);
	}

	public function obtain_users_online_string_sql($event)
	{
		$sql_ary = $event['sql_ary'];
		$sql_ary['SELECT'] .= ', u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height';
		$event['sql_ary'] = $sql_ary;
	}

	public function obtain_users_online_string_modify($event)
	{
		$u_online = $event['user_online_link'];
		$online_users = $event['online_users'];
		$online_userlist = $event['online_userlist'];

		$username = $replace = array();
		foreach ($event['rowset'] as $row)
		{
			if (!isset($u_online[$row['user_id']]))
			{
				continue;
			}

			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				if (isset($online_users['hidden_users'][$row['user_id']]))
				{
					$row['username'] = '<em>' . $row['username'] . '</em>';
				}
			}

			if (!empty($row['user_avatar']))
			{
				$replace_avatar = '<span class="useravatar">' . phpbb_get_user_avatar($row) . '</span> ' . $row['username'] .	'';
			}
			else
			{
				$no_avatar = generate_board_url() . "/styles/" . rawurlencode($this->user->style['style_path']) . '/theme/images/no_avatar.gif';
				$replace_avatar = '<span class="useravatar"><img src="' . $no_avatar . '" alt="" /></span> ' . $row['username'];
			}

			$username[] = $row['username'];
			$replace[] = $replace_avatar;
		}

		if (sizeof($username))
		{
			$online_userlist = str_replace($username, $replace, $online_userlist);
		}
		$event['online_userlist'] = $online_userlist;
	}
}
