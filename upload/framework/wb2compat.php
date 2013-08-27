<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

define('WB2COMPAT',true);

define('WB_SERVER_ADDR', CAT_SERVER_ADDR );
define('WB_PATH', CAT_PATH);
define('WB_URL', CAT_URL);
define('ADMIN_PATH', CAT_ADMIN_PATH);
define('ADMIN_URL', CAT_ADMIN_URL);
define('THEME_URL', defined('CAT_THEME_URL') ? CAT_THEME_URL : CAT_URL.'/templates/'.DEFAULT_THEME );
define('LEPTON_SERVER_ADDR', CAT_SERVER_ADDR );
define('LEPTON_PATH', CAT_PATH);
define('LEPTON_URL', CAT_URL);
define('TABLE_PREFIX', CAT_TABLE_PREFIX );
define('DB_TYPE', CAT_DB_TYPE);
define('DB_HOST', CAT_DB_HOST);
define('DB_PORT', CAT_DB_PORT);
define('DB_USERNAME', CAT_DB_USERNAME);
define('DB_PASSWORD', CAT_DB_PASSWORD);
define('DB_NAME', CAT_DB_NAME);
define('WB_PREPROCESS_PREG', '/\[wblink([0-9]+)\]/isU' );
define('WBMAILER_DEFAULT_SENDERNAME', CATMAILER_DEFAULT_SENDERNAME );
// define WB_VERSION for backward compatibility
if (!defined('WB_VERSION')) define('WB_VERSION', '2.8.2');
if (!defined('TIMEZONE'))   define('TIMEZONE',DEFAULT_TIMEZONE_STRING);
// load old language file
include CAT_PATH.'/languages/old/'.LANGUAGE.'.php';

global $database, $wb, $admin;

require_once CAT_PATH.'/framework/class.database.php';
$database = new database();

// old template engine
require_once(CAT_PATH."/include/phplib/template.inc");

// old language definitions - needed for some older modules, like Code2
define('ENABLE_OLD_LANGUAGE_DEFINITIONS',true);
