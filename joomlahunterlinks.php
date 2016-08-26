<?php
/**
 * @copyright	Copyright (c) 2016 system. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * system - Joomla hunter links Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	system.Joomlahunterlinks
 */
class plgsystemJoomlahunterlinks extends JPlugin {

	/**
	 * Constructor.
	 *
	 * @param 	$subject
	 * @param	array $config
	 */
	function __construct(&$subject, $config = array()) {
		// call parent constructor
		parent::__construct($subject, $config);
	}

    private function isAjax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	public function onAfterRender() {
        
        $application = JFactory::getApplication();

		if($application->isAdmin() or $this->isAjax()) return false;
        if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed') {
			return;
		}

        $version = new JVersion();

        if (version_compare($version->RELEASE, '3.1') < 0) {
            $body = JResponse::getBody();
        } else {            
            $body = implode('', $application->getBody(true));
        }

		$multybite = preg_match('#.#u', $body) ? 'u' : '';

		$result = array();
        $list = $this->params->get('blacklist');
        $data = explode(';|;', $list);
		$links = isset($data[0]) ? array_unique(explode('&|&', $data[0])) : [];
		$blacklist = isset($data[1]) ?  array_unique(explode('&|&', $data[1])) : [];

        $pushlinks = array();

        $body = preg_replace_callback('#<a[^>]+href[\s]*=[\s]*("|\')([^\1]+)\1[^>]*>.*</a>#Uis'.$multybite, function($matches) use (&$pushlinks, $blacklist, $links) {
			$parts = parse_url($matches[2]);
            if ($matches[2] and isset($parts['host']) and $parts['host'] !== JURI::getHost() and !in_array($matches[2], $links) and !in_array($matches[2], $blacklist)) {
                $pushlinks[] = $matches[2];
            }
            if (in_array($matches[2], $blacklist)) {
                return '';
            }
			return $matches[0];
		}, $body);
        
        
        if (count($pushlinks)) {
            $links = array_filter(array_unique(array_merge($links, $pushlinks)));

            $links = implode('&|&', $links);
            $blacklist = implode('&|&', $blacklist);

            $this->params->set('blacklist', $links . ';|;' . $blacklist);
            $db = JFactory::getDBO();
            $db->setQuery('UPDATE #__extensions SET params = ' . $db->quote((string) $this->params) . ' WHERE name = "plg_system_joomlahunterlinks"');
            $db->execute();
        }
        
        $sometexts = !empty($this->params->get('sometext')) ? array_filter(array_unique(explode('&-|-&', rawurldecode($this->params->get('sometext'))))) : [];
        if (count($sometexts)) {
            $body = str_replace($sometexts, '', $body);
        }

        if (version_compare($version->RELEASE, '3.1') < 0) {
            JResponse::setBody($body);
        } else {
            $application->setBody($body);
        }
    }
}