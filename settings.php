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
require_once($CFG->dirroot.'/auth/enrolmentor/class/helper.php');

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
	$profilefields = enrolmentor_helper::get_profile_fields();
	
	$settings->add(new admin_setting_configselect('auth_enrolmentor/role', get_string('enrolmentor_settingrole', 'auth_enrolmentor'), get_string('enrolmentor_settingrolehelp', 'auth_enrolmentor'), '', $rolenames));
	$settings->add(new admin_setting_configselect('auth_enrolmentor/compare', get_string('enrolmentor_settingcompare', 'auth_enrolmentor'), get_string('enrolmentor_settingcomparehelp', 'auth_enrolmentor'), 'username', array('username'=>'username','email'=>'email','id'=>'id')));
	$settings->add(new admin_setting_configselect('auth_enrolmentor/profile_field', get_string('enrolmentor_settingprofile_field', 'auth_enrolmentor'), get_string('enrolmentor_settingprofile_fieldhelp', 'auth_enrolmentor'), '', $profilefields));
}