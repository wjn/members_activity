<?php
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.eventmanager.php');

class contentExtensionMembers_activityDetail extends AdministrationPage
{
		const EXT_NAME = 'members_activity';
		
		private $_driver;

		public function __construct(&$parent){
			parent::__construct($parent);
			$this->setTitle('Symphony &ndash; Activity Logging Detail');
			$this->_driver = Administration::instance()->ExtensionManager->create('members_activity');
		}
		
		public function view($tableHead=NULL)
		{
			$id = extension_members_activity::fetchDetailID($_SERVER["REQUEST_URI"]);

			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/' . self::EXT_NAME . '/assets/style.css', 'screen', 9126341);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/' . self::EXT_NAME . '/assets/scripts.js', 9126342);
						
			$log = $this->_driver->fetchActivityDetailRecord($id);
			

			$this->setPageType('table');
			$this->appendSubheading(__('Members Activity Log :: Detail for Entry ID ' . $id ) );

			$aTableBody = array();
			
			if(count($log) == 0 || !is_array($log) || is_null($log)){
				$aTableBody = array(
					Widget::TableRow(
						array(Widget::TableData(__('There is no log entry that matches this ID. '), 'inactive', NULL, count($aTableHead)))
					)
				);
			}
			else
			{
					$aTableBody = array(
					Widget::TableRow(
						array(Widget::TableData(extension_members_activity::fetchDetail($id), NULL, 'activity-log-detail', count($aTableHead)))
					),
					Widget::TableRow(
						array(Widget::TableData(extension_members_activity::fetchFooter(), NULL, 'activity-log-detail-footer', count($aTableHead)))
					)
				);
				
			}
			
				$table = Widget::Table(
				NULL, 
				NULL, 
				Widget::TableBody($aTableBody)
			);
					
			$this->Form->appendChild($table);
			
			
		}
}