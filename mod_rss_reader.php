<?php

/**
 * Better RSS Reader
 * 
 * @license Proprietary Licence
 * @copyright (c) 2024 OxFaTech
 * 
 * This software is licensed under a proprietary license.
 * Any distribution, modification, or commercial use is prohibited without prior express written permission from the copyright holder.
 * For details, see the LICENSE.txt file.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Feed\Site\Helper\FeedHelper;

$rssurl = $params->get('rssurl', '');
$rssrtl = $params->get('rssrtl', 0);

$feed = FeedHelper::getFeed($params);

require ModuleHelper::getLayoutPath('mod_rss_reader', $params->get('layout', 'default'));
