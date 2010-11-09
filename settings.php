<?php // $Id: settings.php,v 1.2 2010/11/09 11:56:13 davmon Exp $

$vars = array();

$vars[] = new admin_setting_configtext('server', get_string('settingserver', 'block_myvideos'),
                   get_string('settingservertext', 'block_myvideos'), '', PARAM_URL);

$vars[] = new admin_setting_configtext('username', get_string('settingusername', 'block_myvideos'),
                   get_string('settingusernametext', 'block_myvideos'), '', PARAM_TEXT);

$vars[] = new admin_setting_configpasswordunmask('password', get_string('settingpassword', 'block_myvideos'),
                   get_string('settingpasswordtext', 'block_myvideos'), '');
                   
$vars[] = new admin_setting_configtext('path', get_string('settingpath', 'block_myvideos'),
                   get_string('settingpathtext', 'block_myvideos'), '', PARAM_PATH);
                   
$vars[] = new admin_setting_configtext('moodlepath', get_string('settingmoodlepath', 'block_myvideos'),
                   get_string('settingmoodlepathtext', 'block_myvideos'), $CFG->dataroot.'/myvideos', PARAM_PATH);
                   
foreach ($vars as $var) {
    $var->plugin = 'blocks/myvideos';
    $settings->add($var);
}

?>
