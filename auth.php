<?php
/**
 * Auto enrol mentors, parents or managers based on a custom profile field.
 *
 * @package    auth
 * @subpackage enrolmentor
 * @copyright  2013 Virgil Ashruf (v.ashruf@avetica.nl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

class auth_plugin_enrolmentor extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_enrolmentor() {
        $this->authtype = 'enrolmentor';
        $this->config = get_config('auth_enrolmentor');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        global $CFG, $DB, $USER;
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        if ($password === 'changeme') {
            // force the change - this is deprecated and it makes sense only for manual auth,
            // because most other plugins can not change password easily or
            // passwords are always specified by users
            set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

   /**
    * Confirm the new user as registered. This should normally not be used,
    * but it may be necessary if the user auth_method is changed to manual
    * before the user is confirmed.
    *
    * @param string $username
    * @param string $confirmsecret
    */
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                $DB->set_field("user", "firstaccess", time(), array("id"=>$user->id));
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }


    /**
     * Processes and stores configuration data for this authentication plugin.
     * $this->config->somefield
     */
    function process_config($config) {
        // set to defaults if undefined

	if (!isset($config->mainrule_fld)) {
	    $config->mainrule_fld = '';
	}
	if (!isset($config->secondrule_fld)) {
	    $config->secondrule_fld = 'n/a';
	}
	if (!isset($config->replace_arr)) {
	    $config->replace_arr = '';
	}
	if (!isset($config->delim)) {
	    $config->delim = 'CR+LF';
	}
	if (!isset($config->donttouchusers)) {
	    $config->donttouchusers = '';
	}
	if (!isset($config->enableunenrol)) {
	    $config->enableunenrol = 0;
	}
        // save settings
        set_config('mainrule_fld', $config->mainrule_fld, 'auth_enrolmentor');
        set_config('secondrule_fld', $config->secondrule_fld, 'auth_enrolmentor');
        set_config('replace_arr', $config->replace_arr, 'auth_enrolmentor');
        set_config('delim', $config->delim, 'auth_enrolmentor');
        set_config('donttouchusers', $config->donttouchusers, 'auth_enrolmentor');
        set_config('enableunenrol', $config->enableunenrol, 'auth_enrolmentor');

        return true;
    }

    /**
     * Called when the user record is updated.
     * Modifies user in external database. It takes olduser (before changes) and newuser (after changes)
     * compares information saved modified information to external db.
     *
     * @param mixed $olduser     Userobject before modifications    (without system magic quotes)
     * @param mixed $newuser     Userobject new modified userobject (without system magic quotes)
     * @return boolean true if updated or update ignored; false if error
     *
     */
    function user_update($olduser, $newuser) {
        return true;
    }
    
    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     */
    function user_authenticated_hook(&$user, $username, $password) {
		global $DB, $SESSION;

		//Get the roleid we're going to assign.
		$roleid = $this->config->role;
			
		//Get all the user ids that we're a parent of.
		$profile_field = $this->config->profile_field;
		
		switch($this->config->compare) {
			case 'username':
				$sql = "SELECT userid FROM mdl_user_info_data
				WHERE Data = '{$username}'";
				break;
			case 'id':
				$sql = "SELECT userid FROM mdl_user_info_data
				WHERE Data = '{$user->id}'";
				break;
			case 'email':
				$sql = "SELECT userid FROM mdl_user_info_data
				WHERE Data = '{$user->email}'";
				break;
		}		
		
		$parents = $DB->get_records_sql($sql);
		
		$arraykeys = array_keys($parents);
		foreach($arraykeys as $arraykey) {			
			role_assign($roleid, $parents[$arraykey]->userid, context_user::instance($parents[$arraykey]->userid)->id, 'auth_enrolmentor', 0, '');
		}		
    }
}
