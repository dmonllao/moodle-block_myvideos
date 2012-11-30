<?php // $Id: settings.php,v 1.3 2010/11/15 07:36:25 davmon Exp $

$vars = array();

$vars[] = new admin_setting_configtext('server', get_string('settingserver', 'block_myvideos'),
                   get_string('settingserver', 'block_myvideos'), '', PARAM_URL);

$vars[] = new admin_setting_configtext('username', get_string('settingusername', 'block_myvideos'),
                   get_string('settingusername', 'block_myvideos'), '', PARAM_TEXT);

$vars[] = new admin_setting_configpasswordunmask('password', get_string('settingpassword', 'block_myvideos'),
                   get_string('settingpassword', 'block_myvideos'), '');

$vars[] = new admin_setting_configtext('ffmpeg', get_string('settingffmpeg', 'block_myvideos'),
                   get_string('settingffmpegdesc', 'block_myvideos'), '/usr/bin/ffmpeg', PARAM_PATH);

$vars[] = new admin_setting_configtext('mencoder', get_string('settingmencoder', 'block_myvideos'),
                   get_string('settingmencoder', 'block_myvideos'), '/usr/bin/mencoder', PARAM_PATH);

$vars[] = new admin_setting_configtext('path', get_string('settingpath', 'block_myvideos'),
                   get_string('settingpath', 'block_myvideos'), '', PARAM_PATH);
                   
$vars[] = new admin_setting_configtext('moodlepath', get_string('settingmoodlepath', 'block_myvideos'),
                   get_string('settingmoodlepath', 'block_myvideos'), $CFG->dataroot.'/myvideos', PARAM_PATH);
                   
foreach ($vars as $var) {
    $var->plugin = 'blocks/myvideos';
    $settings->add($var);
}

?>