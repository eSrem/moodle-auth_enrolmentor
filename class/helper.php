<?php

class enrolmentor_helper {
	
	/**
	 * __construct() HIDE: WE'RE STATIC
	 */
	protected function __construct()
	{
		// static's only please!
	}
	
	/**
	 * get_enrolled_employees($roleid, $userid) 
	 * returns an array of user ids that resemble the userid's the user is enrolled in
	 *
	 */
	static public function get_enrolled_employees($roleid, $userid) {
		global $DB;
		$list = array();
		
		$sql  = "SELECT c.instanceid
				FROM {context} AS c
				JOIN {role_assignments} AS ra ON ra.contextid = c.id
				WHERE ra.roleid='{$roleid}'
				AND ra.userid='{$userid}'";
		
		$list = array_keys($DB->get_records_sql($sql));
		
		return $list;		
	}

	/**
	 * get_list_empolyees($user, $username)
	 * returns an array of user ids that resemble the userid's the user is enrolled in
	 *
	 */
	static public function get_list_employees($user, $username, $switch) {
		global $DB;
		$list = array();
		
		switch($switch->compare) {
			case 'username':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$username}'
				AND fieldid = '{$switch->profile_field}'";
				break;
			case 'id':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$user->id}'
				AND fieldid = '{$switch->profile_field}'";
				break;
			case 'email':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$user->email}'
				AND fieldid = '{$switch->profile_field}'";
				break;
		}
		
		$list = array_keys($DB->get_records_sql($sql));
		
		return $list;
	}
	
	/**
	 * get_profile_fields(null);
	 * returns an array of custom profile fields
	 *
	 */	
	static public function get_profile_fields() {
		global $DB;
		
		$fields = $DB->get_records_menu('user_info_field', null, null, $fields = 'id, shortname');

		return $fields;
	}
	
	/**
	 * doEnrol($toEnrol);
	 * returns an array of user ids that this user need to be enrolled in
	 *
	 */
	static public function doEnrol($toEnrol, $roleid, $user){
		foreach($toEnrol as $enrol) {
			//echo "<p>ik enrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($enrol)->id . "</p>";
			role_assign($roleid, $user->id, context_user::instance($enrol)->id, '', 0, '');
		}
	}
	
	/**
	 * doUnenrol($toUnenrol);
	 * returns an array of user ids thad this user need to be unenrolled in
	 *
	 */
	static public function doUnenrol($toUnenrol, $roleid, $user){
		foreach($toUnenrol as $unenrol) {
			//echo "<p>ik unenrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($unenrol)->id . "</p>";
			role_unassign($roleid, $user->id, context_user::instance($unenrol)->id, '', 0, '');
		}
	}	
}