<?php
/*
 * Created on Mar 21, 2010
 *
 */

	require_once(TOOLKIT . '/class.event.php');
	
	Class eventlog_activity extends Event{
		
		const ROOTELEMENT = 'log-activity';
		
		public $eParamFILTERS = array(
			
		);
			
		public static function about(){
			return array(
					 'name' => 'Log Activity',
					 'author' => array(
							'name' => 'Will Nielsen',
							'website' => 'http://pcpc.org',
							'email' => 'webmaster@pcpc.org'),
					 'version' => '1.0',
					 'release-date' => '2010-03-20T20:16:53+00:00',
					 'trigger-condition' => 'action[banner-delete]');	
		}

		public static function getSource(){
			return '10';
		}

		public static function allowEditorToParse(){
			return true;
		}

		
		public function load(){			
			if(isset($_POST['action']['log-activity'])) return $this->__trigger();
		}
		
		protected function __trigger(){
			include(TOOLKIT . '/events/event.section.php');
			return $result;
		}		

	}

 