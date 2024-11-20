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
				$db_object = new \stdClass;
				$db_object->rule_name = 'Results H5P quizzes';
				$db_object->rule_description = 'Give points to user when the quiez end';
				$db_object->rule_plugin = 'com_h5p';
				$db_object->plugin_function = 'plgaup_h5presults';
				$db_object->sections = '';
				$db_object->categories = '';
				$db_object->content_items = '';
				$db_object->exclude_items = '';
				$db_object->emailbody = '';
				$db_object->published = 1;

				$db->insertObject('#__alpha_userpoints_rules', $db_object);
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
