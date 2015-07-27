<?php

//* the folder were uploaded SOGo plugins will be saved (ISPC_WEB_TEMP_PATH . '/SOGoPlugins')
$conf['sogo_plugins_upload_dir'] = ISPC_ROOT_PATH . '/web/temp/SOGoPlugins';

if (!defined('SOGO_EXT_DEBUG_INFO') && (defined('DEVSYSTEM') && (DEVSYSTEM != 0 || DEVSYSTEM !== false)))
    define('SOGO_EXT_DEBUG_INFO', ($conf['log_priority'] == LOGLEVEL_DEBUG ? true : false));