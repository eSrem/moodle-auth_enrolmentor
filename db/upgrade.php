<?php

function xmldb_auth_enrolmentor_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2012032914) {
        $sql = "UPDATE {config_plugins} SET plugin = 'auth_enrolmentor' WHERE plugin = 'auth/enrolmentor'";
        $DB->execute($sql);

        echo $OUTPUT->notification('Update plugin configugation', 'notifysuccess');
    }

    return true;
}
