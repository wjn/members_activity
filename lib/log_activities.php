<?php
//set_include_path(EXTENSIONS);
/**
 * Requires the use of the Symphony Members Extension v 1.0 beta 3 and Symphony 2.2.1
 */
require_once(EXTENSIONS . "/members/extension.driver.php");
require_once(EXTENSIONS . "/members/lib/member.symphony.php");

/*
 * This is a PHP library that handles calling Memebers Activity Logging.
 *
 * Copyright (c) 2010 Will Nielsen -- http://nielsendigital.com
 *
 * AUTHOR:
 *   Will Nielsen
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


Class Log_Activities extends SymphonyMember {
	// wjn 2011-05-04
	public function __construct() {
		$this->_collectInfo();
		$this->_logActivity();
	}

	protected function _collectInfo() {
		/* generate a preformatted var_dump for easier human viewing */
		ob_start();
		echo "<pre>";
		echo var_dump($_POST['fields']);
		echo "</pre>";
		$post = ob_get_contents();
		ob_end_clean();

		$memberID = Members::getMemberID();
		// gets member information from the member id ($_POSTS['fields']['owner'])
		$this->_member = Members::initialiseMemberObject($memberID);
		
		
		$this->_info['member-name'] = $this->memberName();
		
		// timestamp for log
		$this->_info['activity_date'] = date('Y-m-d h:i:s');


		$this->_info['member-username'] = $this->memberUsername();


		// pulls the member name (Last, First) from the _data[47]['value'] array address
		$this->_info['post-array'] = General :: Sanitize($post);

		$this->_info['activity'] = General :: Sanitize(key($_POST['action']));
		$this->_info['request-uri'] = $_SERVER['REQUEST_URI'];
		$this->_info['referring-page'] = $_SERVER['HTTP_REFERRER'];
		$this->_info['browser-info'] = $_SERVER['HTTP_USER_AGENT'];
		$this->_info['ip-address'] = $_SERVER['REMOTE_ADDR'];
		$this->_info['remote-host'] = $_SERVER['REMOTE_HOST'];
		$this->_info['remote-port'] = $_SERVER['REMOTE_PORT'];
	}

	protected function _logActivity()
	{
		ob_start();
		echo "<pre>"; echo var_dump($_POST); echo "</pre>";
		$post_array = ob_get_contents();
		ob_end_clean();
		$post_array = htmlspecialchars($post_array, ENT_QUOTES);
			
			
		$actions_string = '';
		foreach($_POST['action'] as $key => $val)
		{
			$actions_string .= $key;
			if(end($_POST['action']) != $val)
			{
				$actions_string .= ',';
			}
		}
			
		$sql = "INSERT INTO `tbl_members_activity` (" .
					"activity_date," .
					"member_username," .
					"member_name," .
					"post_array," .
					"activity," .
					"request_uri," .
					"referring_page," .
					"browser_info," .
					"ip_address," .
					"remote_host," .
					"remote_port" .
					") VALUES (" .
					"'{$this->_info['activity_date']}'," .
					"'{$this->_info['member-username']}'," .
					"'{$this->_info['member-name']}'," .
					"'{$post_array}'," .
					"'{$actions_string}'," .
					"'{$this->_info['request-uri']}'," .
					"'{$this->_info['referring-page']}'," .
					"'{$this->_info['browser-info']}'," .
					"'{$this->_info['ip-address']}'," .
					"'{$this->_info['remote-host']}'," .
					"'{$this->_info['remote-port']}' " .
					");";

		return Symphony::Database()->query($sql);
			
	}

	// wjn addition 2011-05-04

	protected function memberName(){
		$id = Symphony::Database()->fetchVar('id', 0, "SELECT `id` FROM `tbl_fields` WHERE `parent_section` = '".extension_Members::getMembersSection()."' AND `element_name` = 'Name' LIMIT 1");
		
		return $this->Member->getData($id, true)->value;
	}
	protected function memberUsername(){
		$id = Symphony::Database()->fetchVar('id', 0, "SELECT `id` FROM `tbl_fields` WHERE `parent_section` = '".extension_Members::getMembersSection()."' AND `element_name` = 'Username' LIMIT 1");
		
		return $this->Member->getData($id, true)->value;
	}
	




}