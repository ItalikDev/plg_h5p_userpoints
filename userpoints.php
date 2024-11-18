<?php

/**
 * @package    pkg_h5p_userpoints
 * @author     Vitalii Butsykin <v.butsykin@gmail.com>
 * @copyright  2024 Vitalii Butsykin
 * @license    GNU General Public License ver. 2 or later
 */

defined('_JEXEC') or die('Restricted access');

use BlackSheepResearch\Component\UserPoints\Site\UserPoints;
use Joomla\CMS\Factory;

jimport('joomla.plugin.plugin');

class plgH5pUserpoints extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onh5p_alter_user_result(&$data, $result_id, $content_id, $user_id)
	{

		if (class_exists(UserPoints::class)) {
			$db = Factory::getDBO();
			$query = 'SELECT id FROM #__alpha_userpoints_rules' . ' WHERE plugin_function=' . $db->Quote('plgaup_h5presults');
			$db->setQuery($query);
			$exist = $db->loadResult();
			if (empty($exist)) {
				$insertdata = array(
					'rule_name' => $db->Quote('Results H5P quizzes'),
					'rule_description' => $db->Quote('Give points to user when the quiez end'),
					'rule_plugin' => $db->Quote('com_h5p'),
					'plugin_function' => $db->Quote('plgaup_h5presults'),
					'access' => 1,
					'points' => 0,
					'published' => 1,
					'system' => 0,
					'autoapproved' => 1
				);
				$query = 'INSERT INTO  #__alpha_userpoints_rules (' . implode(',', array_keys($insertdata)) . ') VALUES (' . implode(',', $insertdata) . ')';
				$db->setQuery($query);
				$db->execute();
			}

			if (!$user_id) {
				return;
			}

			$score = 0;

			if ($result_id) {
				$db->setQuery(sprintf(
					"SELECT score
					FROM #__h5p_results
					WHERE id = %d",
					$result_id
				));
				$score = $db->loadResult();
			}

			if($score == $data->score){
				return;
			}

			if ($score) {
				UserPoints::newpoints('plgaup_h5presults', '', '', 'Return of points for previous solution', -$score);
			}

			if ($data->score) {
				$db->setQuery(sprintf(
					"SELECT title
					FROM #__h5p_contents
					WHERE id = %d",			
					$content_id
				));
				$title = $db->loadResult();
				UserPoints::newpoints('plgaup_h5presults', '', '', $title, $data->score);
			}
		}
	}
}
