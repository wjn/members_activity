<?php
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.eventmanager.php');

class contentExtensionMembers_activityActivity_log extends AdministrationPage
{
		const EXT_NAME = 'members_activity';
		
		private $_driver;

		public function __construct(&$parent){
			parent::__construct($parent);
			$this->setTitle('Symphony &ndash; Activity Logging');
			$this->_driver = Administration::instance()->ExtensionManager->create('members_activity');
		}
		
		public function view($tableHead=NULL)
		{

			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/' . EXT_NAME . '/assets/style.css', 'screen', 9126341);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/' . EXT_NAME . '/assets/scripts.js', 9126342);
			

			$this->setPageType('table');
			$this->appendSubheading(__('Members Activity Log '));

			if(is_array($tableHead))
			{
				
					foreach ($tableHead as $h)
					{
						$aTbleHead[] = array(__($h),'col');
					}
			}
			else
			{
				$aTableHead = array(

					array(__('Date'), 'col'),
					array(__('Name'), 'col'),		
					array(__('Activity'), 'col'),		
					array(__('Request URI'), 'col'),		
					array(__('IP Address'), 'col'),		
					array(__('Remote Port'), 'col')		

				);	
				
			}			
		
			$log = $this->_driver->fetchActivityLog();
						
			$aTableBody = array();

			if(count($log) == 0 || !is_array($log) || is_null($log)){
				$aTableBody = array(
					Widget::TableRow(
						array(Widget::TableData(__('There are no logged activities in the database. Have you attached the "Members Activity Logging" filter to the event you want to log?'), 'inactive', NULL, count($aTableHead)))
					)
				);
			}
			
			else
			{
				
			    $sectionManager = new SectionManager($this->_Parent);
				
				$bEven = true;
				$aTableData = array();
				$i = 0;
				
				
				
				foreach($log as $row)
				{
					$i++;
					
					// public static function TableData($value, $class=NULL, $id=NULL, $colspan=NULL, array $attr=NULL)
					$aTableData = array(
					
								Widget::TableData(Widget::Anchor($row['activity_date'], extension_members_activity::baseURL() . 'detail/' . $row['ID'] . '/', NULL, 'content'),'active activity-date'),
								Widget::TableData($row['member_name'] . ' ('.$row['member_username'].')', 'active member-name'),
								Widget::TableData($row['activity'], 'active activity'),
								Widget::TableData(Widget::Anchor($row['request_uri'],$row['request_uri'],NULL,'content') , 'active request-uri'),
								Widget::TableData($row['ip_address'], 'active ip-address'),
								Widget::TableData($row['remote_port'], 'active remote-port'),
							);
							
					$aTableBody[] = Widget::TableRow($aTableData,($bEven ? 'odd' : NULL),$row['ID']);
					
					$bEven = !$bEven;

				}
								
			
			
			$table = Widget::Table(
				Widget::TableHead($aTableHead), 
				NULL, 
				Widget::TableBody($aTableBody)
			);
					
			$this->Form->appendChild($table);
			
//			$tableActions = new XMLElement('div');
//			$tableActions->setAttribute('class', 'actions');
//			
//			$options = array(
//				array(null, false, __('With Selected...')),
//				2 => array('delete-members', false, __('Delete Members')),
//				array('delete', false, __('Delete')),
//			);
//			
//			if(count($with_selected_roles) > 0){
//				$options[1] = array('label' => __('Move Members To'), 'options' => $with_selected_roles);
//			}
//			
//			ksort($options);
//			
//			$tableActions->appendChild(Widget::Select('with-selected', $options, array('id' => 'with-selected')));
//			$tableActions->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));
//			
//			$this->Form->appendChild($tableActions);			

		}
	}
}