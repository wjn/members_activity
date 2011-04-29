<?php

/**
 * Requires a field in the Members section named "Name" to contain the Member's name.
 */

require_once ('lib/log_activities.php');

Class extension_members_activity extends Extension {
	protected $_log;

	public function about() {
		return array (
			'name' => 'Member Activty Logging',
			'version' => '1.0.0',
			'release-date' => '2010-03-20',
			'author' => array (
				'name' => 'Will Nielsen',
				'website' => 'http://pcpc.org',
				'email' => 'will.nielsen@pcpc.org'
			),
			'description' => 'This event records what registered members do ' .
			'and is dependent on the <em>Symphony Members Extension</em>.',
			'compatibility' => array (
				'2.0.7' => true
			)
		);
	}

	public function getSubscribedDelegates() {
		return array (
			array (
				'page' => '/blueprints/events/new/',
				'delegate' => 'AppendEventFilter',
				'callback' => 'addFilterToEventEditor'
			),

			array (
				'page' => '/blueprints/events/edit/',
				'delegate' => 'AppendEventFilter',
				'callback' => 'addFilterToEventEditor'
			),

			array (
				'page' => '/blueprints/events/new/',
				'delegate' => 'AppendEventFilterDocumentation',
				'callback' => 'addFilterDocumentationToEvent'
			),

			array (
				'page' => '/blueprints/events/edit/',
				'delegate' => 'AppendEventFilterDocumentation',
				'callback' => 'addFilterDocumentationToEvent'
			),

			array (
				'page' => '/system/preferences/',
				'delegate' => 'AddCustomPreferenceFieldsets',
				'callback' => 'appendPreferences'
			),

			array (
				'page' => '/frontend/',
				'delegate' => 'EventPreSaveFilter',
				'callback' => 'processEventData'
			),

			
		);
	}

	public function addFilterToEventEditor($context) {
		$context['options'][] = array (
			'members_activity',
			@ in_array('members_activity', $context['selected']),
			'Members Activity Logging'
		);
	}

	public function appendPreferences($context) {
		$group = new XMLElement('fieldset');
		$group->setAttribute('class', 'settings');
		$group->appendChild(new XMLElement('legend', 'Members Activity Logging'));

		$group->appendChild(new XMLElement('p', 'If you have questions about how to connect the members activity ' .
		'logging to the CMS look here for help in later versions.', array (
			'class' => 'help'
		)));

		$label = Widget :: Label();
		$select = Widget :: Select('settings[log-activity]', array (
			1,
			2,
			3,
			4
		));
		$label->setValue('Activty Log Value');
		$group->appendChild($label);
		$group->appendChild(new XMLElement('p', 'Select the Section which you are using for Members Activity Logging.', array (
			'class' => 'help'
		)));

		$context['wrapper']->appendChild($group);

	}

	public function addFilterDocumentationToEvent($context) {
		if (is_null($context['selected']) || !in_array('members_activity', $context['selected']))
			return;

		$context['documentation'][] = new XMLElement('h3', 'Members Activity Logging');

		$context['documentation'][] = new XMLElement('p', 'This event event, when executed by a  ' . DOMAIN .
		' user is recorded in the Activity Log. In addition to the event and user information, ' .
		'Web Browser Information, IP Address, Current and Referring pages, as well as an ' .
		'array of the POST variable is recorded.');

	}

	public function processEventData($context) {
		
		if (!in_array('members_activity', $context['event']->eParamFILTERS))
			return;


		new Log_Activities();
	}
	
		public static function baseURL(){
			return URL . '/symphony/extension/members_activity/';
		}

	public function fetchNavigation() {
		return array (
			array (
				'location' => 330,
				'name' => __('Activity Log'),
				'link' => '/activity_log/'
			)
		);
	}

	public function fetchActivityLog($offset = 0, $row_count = 50) {
		$sql = "SELECT * FROM `tbl_members_activity` ORDER BY `ID` DESC LIMIT {$row_count} OFFSET {$offset} ";

		if (!$rows = Symphony :: Database()->fetch($sql))
			return;
			
		return $rows;		

	}
	
	public function fetchDetailID($url)
	{
		return trim(trim($url, self::baseURL().'detail/'),'/');		
	}
	
	public function formatPostArray($p)
	{
		$p = strip_tags(htmlspecialchars_decode(html_entity_decode($p,ENT_QUOTES),ENT_QUOTES));

		$p = preg_replace('/\{\s\[/','</li></ul> [',$p);
		
		$p = preg_replace('/\"\s/', '</div> </div>', $p);
		$p = preg_replace('/\[\"/','<div class="post-element"><h4>',$p);
		$p = preg_replace('/\"\]\=\>/','</h4>',$p);
				
		$p = preg_replace('/\<div class\=\"post-element\"\>\<h4\>action\<\/h4\>/','<hr/><div id="form-action" class="post-element"><h3>Action</h3>',$p);

		$p = preg_replace('/\{/','',$p);
		$p = preg_replace('/}+/','',$p);
		
		
		// remove the "array(#)" string
		$p = preg_replace('/array\(\d+\)/','',$p);
		
		// formats the character type
		$p = preg_replace('/string\(\d+\)/', '<div class="data-type">Type: string</div>', $p);
		
		// formats value
		$p = preg_replace('/\s\"/', '<div class="value">Value: ', $p);
		
		// final cleanup
		$p = preg_replace('/\<div class\=\"post-element\"\>\<h4\>fields\<\/h4\>/','',$p);
		$p = preg_replace('/\<\/div\>\<\/div\>/','',$p);
		
		$p = preg_replace('#\</div\>\s\<li\>#','</li>',$p);
			
		
		$p = preg_replace('/\s\[\d+\]\=\>/','</li><li>',$p);
		$p = preg_replace('#</h4>\s+</li><li>#','</h4><li>',$p);
		$p = preg_replace('#</div>\s+</li><li>#','</li><li>',$p);
		

		
		return $p;
		
	}
	public function fetchDetail($id)
	{
		$row = self::fetchActivityDetailRecord($id);
		$row = $row[0];
	
		/* ============ LEFT COLUMN ======================= */
		
		$out .= "<dl id='column-left'>";
		
		$out .= "<dt>Activity Date</dt>";
		$out .= "<dd>".(($row['activity_date'] == NULL) ? 'NULL' : $row['activity_date'] )."</dd>";
		
		$out .= "<dt>Activity</dt>";
		$out .= "<dd class='capitalize'>" . preg_replace('/\-/',' ',(($row['activity'] == NULL) ? 'NULL' : $row['activity'] ))."</dd>";
		
		$out .= "<dt>Member Name (Username)</dt>";
		$out .= "<dd>".(($row['member_name'] == NULL) ? 'NULL' : $row['member_name'] )." (".(($row['member_username'] == NULL) ? 'NULL' : $row['member_username'] ).")</dd>";
		
		$out .= "<dt>Request URI</dt>";
		$out .= "<dd>".(($row['request_uri'] == NULL) ? 'NULL' : $row['request_uri'] )."</dd>";
		
		$out .= "<dt>Referring Page</dt>";
		$out .= "<dd>".(($row['referring_page'] == NULL) ? 'NULL' : $row['referring_page'] )."</dd>";
		
		$out .= "<dt>Browser Info</dt>";
		$out .= "<dd>".(($row['browser_info'] == NULL) ? 'NULL' : $row['browser_info'] )."</dd>";
		
		$out .= "<dt>IP Address : Port</dt>";
		$out .= "<dd>".(($row['ip_address'] == NULL) ? 'NULL' : $row['ip_address'])." : ".(($row['remote_port'] == NULL) ?  'NULL' : $row['remote_port'])."</dd>";
		
		$out .= "<dt>Remote Host</dt>";
		$out .= "<dd>".(($row['remote_host'] == NULL) ? 'NULL' : $row['remote_host'] )."</dd>";
		
		
		$out .= "</dl>";
		/* ============ RIGHT COLUMN ======================= */
		$out .= "<dl id='column-right'>";
		
		$out .= "<dt>\$_POST Array</dt>";
		$out .= "<dd>";
		$out .= self::formatPostArray($row['post_array']);
		$out .= "</dd>";
		
		$out .= "</dl>";
		
			
		return $out;	
	}
	
	public function fetchActivityDetailRecord($id=0)
	{
		$sql = "SELECT * FROM `tbl_members_activity` WHERE `ID` = {$id} LIMIT 1;";

		if (!$rows = Symphony :: Database()->fetch($sql))
			return;
			
		return $rows;		

	}
	
	public function fetchFooter()
	{
		$about = self::about();
		
		$out = "<div id='back-link'>";
		$out .= "<a href='../../activity_log/'>";
		$out .= "Return to " . $about['name'];
		$out .= "</a>";
		$out .= "</div>";
		
		return $out;
	}
	
	public function install() {
		# Create tables
		Symphony :: Database()->query("
							CREATE TABLE `tbl_members_activity`  (
							`ID` INT(11)  unsigned NOT NULL auto_increment,
							`activity_date` datetime NOT NULL,
							`member_username` VARCHAR(20) NOT NULL,
							`member_name` VARCHAR(50) NOT NULL,
							`post_array` TEXT NULL,
							`activity` VARCHAR(50) NOT NULL,
							`request_uri` VARCHAR(250) NOT NULL,
							`referring_page` VARCHAR(250) NOT NULL,
							`browser_info` TEXT NULL,
							`ip_address` VARCHAR(250) NOT NULL,
							`remote_host` VARCHAR(100) NOT NULL,
							`remote_port` VARCHAR(25) NOT NULL,
							PRIMARY KEY (`ID`),
							INDEX (  `member_username` ,
							`activity`, `remote_host` )
							);
						");
		return true;
	}

	public function uninstall() {

		Symphony :: Configuration()->remove('members_activity');
		$this->_Parent->saveConfig();
		Symphony :: Database()->query("DROP TABLE `tbl_members_activity`;");

		//ConfigurationAccessor::remove('members_activity');	
		$this->_Parent->Configuration->remove('members_activity');
		$this->_Parent->saveConfig();
	}

}
?>