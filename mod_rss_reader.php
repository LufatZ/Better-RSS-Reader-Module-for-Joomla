<?php

/**
 * Better RSS Reader
 *
 * @package MOD_RSS_READER
 * @version 1.0.0
 * @author Lucas Damme lufatz@oxfatech.de
 * @license Proprietary Licence
 * @copyright (c) 2024 OxFaTech
 * @link https://oxfatech.de
 *
 * This module fetches and displays RSS feeds with customizable options.
 * The software is licensed under a proprietary license. Redistribution,
 * modification, or commercial use is prohibited without prior express
 * written permission from the copyright holder.
 * For details, see the LICENCE.txt file
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require ModuleHelper::getLayoutPath('mod_rss_reader', $params->get('layout', 'default'));