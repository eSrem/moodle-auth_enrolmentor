<?php
/**
 * Auto enrol mentors, parents or managers based on a custom profile field.
 *
 * @package    auth
 * @subpackage enrolmentor
 * @copyright  2013 Virgil Ashruf (v.ashruf@avetica.nl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $USER;

require_once($CFG->dirroot.'/user/profile/lib.php');

if ($ADMIN->fulltree) {

	// Get all roles and put their id's nicely into the configuration.
	$roles = get_all_roles();
	$i = 1;
	foreach($roles as $role) {
		$rolename[$i] = $role->shortname;
		$roleid[$i] = $role->id;
		$i++;
	}
	$rolenames = array_combine($roleid, $rolename);
	
	// NOTICE: This code is in place so that we can easily enable access to default profile fields.
	// Get all default profile fields that can be filled with information about the users mentor.
	// $auth = 'enrolmentor';
	// $authplugin = get_auth_plugin($auth);
	// $authfields = $authplugin->userfields;
	// $profilefields = array_combine($authfields, $authfields);	
	
	$sql = "SELECT shortname FROM mdl_user_info_field";
	$customfields_raw = $DB->get_records_sql($sql);
	$customfields_med = array_keys($customfields_raw);
	$customfields = array_combine($customfields_med, $customfields_med);
	
	//$allfields = array_merge($profilefields, $customfields);
		
	$settings->add(new admin_setting_configselect('auth_enrolmentor/role', get_string('enrolmentor_settingrole', 'auth_enrolmentor'), get_string('enrolmentor_settingrolehelp', 'auth_enrolmentor'), '', $rolenames));
	$settings->add(new admin_setting_configselect('auth_enrolmentor/compare', get_string('enrolmentor_settingcompare', 'auth_enrolmentor'), get_string('enrolmentor_settingcomparehelp', 'auth_enrolmentor'), 'username', array('username'=>'username','email'=>'email','id'=>'id')));

	// Currently the setting below is useless, because in our SQL we select based on Data = XYZ 
	//$settings->add(new admin_setting_configselect('auth_enrolmentor/profile_field', get_string('enrolmentor_settingprofile_field', 'auth_enrolmentor'), get_string('enrolmentor_settingprofile_fieldhelp', 'auth_enrolmentor'), '', $customfields));
}
