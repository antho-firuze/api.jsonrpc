<?php defined('FCPATH') OR exit('No direct script access allowed'); 

require(APPPATH.'libraries'.DIRECTORY_SEPARATOR.'F.php');
$f = new F;

/* Time Zone */ 
define('TIME_ZONE', 'Asia/Jakarta'); 
@date_default_timezone_set(TIME_ZONE);

/* Base URL */ 
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$http_alias = '';
$http_port = '';
if (strpos($http_host, 'www') > -1) {
	list($http_alias, $http_host, $http_dot) = explode('.', $http_host);
	$http_host = implode('.', [$http_host, $http_dot]);
}

if (strpos($http_host, ':') > -1)
	list($http_host, $http_port) = explode(':', $http_host);

/* List available domain */
$domain_devel = ['localhost','127.0.0.1','192.168.1.15','192.168.100.105'];
$domain_live = [
	'simpipro.com',
	'api.simpipro.com',
	'gateway-api.simpipro.com',
	'invest-api.simpipro.com',
	'yukbajalan-api.simpipro.com',
	'gl-api.simpipro.com',
	'pos-api.simpipro.com',
	'system-api.simpipro.com',
	'market-api.simpipro.com',
	'master-api.simpipro.com',
	'sharia-api.simpipro.com',
];
$domain = array_merge($domain_devel, $domain_live);
if (!in_array($http_host, $domain))
	$f->bare_response(FALSE, ['message' => "Domain name <strong>$http_host</strong> is not available !"]);

$http_host_full = $http_alias ? $http_alias.'.'.$http_host : $http_host;
$http_host_full = $http_port ? $http_host_full.':'.$http_port : $http_host_full;
define('PROTOCOL', $protocol);
define('HTTP_HOST', $http_host_full);

if (isset($_SERVER['REQUEST_METHOD']))			// for bypass php cli execute. (This REQUEST_METHOD is not exist in cli mode)
	define('HTTP_METHOD', $_SERVER['REQUEST_METHOD']);
	
/* Define BASE_URL. Implement on $config['base_url'] */
define('SEPARATOR', '/');
define('BASE_URL', PROTOCOL.HTTP_HOST.SEPARATOR); 

/* Init TMP/CACHE Folder */
define('DIR_TMP', '__tmp');
if (!file_exists(DIR_TMP) && !is_dir(DIR_TMP)) { mkdir(DIR_TMP); } 

if (in_array($http_host, $domain_devel))
	define('IS_LOCAL', TRUE);
else
	define('IS_LOCAL', FALSE);

/* Override php.ini config */
if (function_exists('ini_set')) {
	@ini_set('max_execution_time', 300);
	@ini_set('date.timezone', TIME_ZONE);
	@ini_set('post_max_size', '8M');
	@ini_set('upload_max_filesize', '2M');
	@ini_set('display_errors', IS_LOCAL ? on : off);					// on | off
	@ini_set('error_reporting', IS_LOCAL ? E_ALL : 0);					// 0 | E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE
}

/* Define default path. Implement on $route['default_controller'] */
$path_localhost = [
	5000 => 'frontend',
	5050 => 'jsonrpc',
	5051 => 'jsonrpc',
	5052 => 'jsonrpc',
	5053 => 'jsonrpc',
	5054 => 'jsonrpc',
	5055 => 'jsonrpc',
];
$path = [
	'localhost' 				=> $path_localhost[$http_port],
	'simpipro.com' 				=> 'frontend',
	'api.simpipro.com' 			=> 'jsonrpc',
	'system-api.simpipro.com' 	=> 'jsonrpc',
	'gateway-api.simpipro.com' 	=> 'jsonrpc',
	'gl-api.simpipro.com' 	=> 'jsonrpc',
	'invest-api.simpipro.com' 	=> 'jsonrpc',
	'pos-api.simpipro.com' 	=> 'jsonrpc',
	'yukbajalan-api.simpipro.com' 	=> 'jsonrpc',
	'market-api.simpipro.com' 	=> 'jsonrpc',
	'master-api.simpipro.com' 	=> 'jsonrpc',
	'sharia-api.simpipro.com' 	=> 'jsonrpc',
];
define('PATH', $path[$http_host]);

// if (in_array($http_host, $domain_live)) {
// 	$api_url = PROTOCOL.HTTP_HOST; 	// 'https://api.simpipro.com';
// } else {
// 	$api_url = PROTOCOL.HTTP_HOST; 	// 'http://'.$http_host.':5050';
// }
define('REPOS_URL', BASE_URL.'__repository'.SEPARATOR);
define('REPOS_DIR', __dir__.DIRECTORY_SEPARATOR.'__repository'.DIRECTORY_SEPARATOR);
define('API_URL', BASE_URL);

// Prefix folder in application/model (for jsonrpc)
$prefix_localhost = [
	5050 => 'wl-api',
	5051 => 'gateway-api',
	5052 => 'olap-api',
	5053 => 'system-api',
	5054 => 'market-api',
	5055 => 'master-api',
];
$prefix = [
	'localhost' 				=> $prefix_localhost[$http_port],
	'api.simpipro.com' 			=> 'wl-api',
	'system-api.simpipro.com' 	=> 'system-api',
	'gateway-api.simpipro.com' 	=> 'gateway-api',
	'gl-api.simpipro.com' 	=> 'gl-api',
	'invest-api.simpipro.com' 	=> 'invest-api',
	'pos-api.simpipro.com' 	=> 'pos-api',
	'yukbajalan-api.simpipro.com' 	=> 'yukbajalan-api',
	'market-api.simpipro.com' 	=> 'market-api',
	'master-api.simpipro.com' 	=> 'master-api',
	'sharia-api.simpipro.com' 	=> 'sharia-api',
];
define('PREFIX_FOLDER', $prefix[$http_host]);

// Database Name Config
$database_localhost = [
	5050 => 'cloud_simpi',
	5051 => 'simpi_gateway',
	5052 => 'simpi_olap',
	5053 => 'simpi_system',
	5054 => 'simpi_market',
	5055 => 'simpi_master',
];
/* $database = [
	'localhost' 				=> $database_localhost[$http_port],
	'api.simpipro.com' 			=> '',
	'system-api.simpipro.com' 	=> 'simpi_system',
	'gateway-api.simpipro.com' 	=> 'simpi_gateway',
	'olap-api.simpipro.com' 	=> 'simpi_olap',
	'market-api.simpipro.com' 	=> 'simpi_market',
	'master-api.simpipro.com' 	=> 'simpi_master',
]; */
$database = [
	'localhost' 				=> $database_localhost[$http_port],
	'api.simpipro.com' 			=> '',
	'system-api.simpipro.com' 	=> 'simpi',
	'gateway-api.simpipro.com' 	=> 'simpi',
	'gl-api.simpipro.com' 	=> 'simpi',
	'invest-api.simpipro.com' 	=> 'simpi',
	'pos-api.simpipro.com' 	=> 'simpi',
	'yukbajalan-api.simpipro.com' 	=> 'simpi',
	'market-api.simpipro.com' 	=> 'simpi',
	'master-api.simpipro.com' 	=> 'simpi',
	'sharia-api.simpipro.com' 	=> 'simpi',
];
define('DATABASE_NAME', $database[$http_host]);

define('API_GATEWAY',   IS_LOCAL ? 'http://localhost:5051' : 'https://gateway-api.simpipro.com');
define('API_GL', 		IS_LOCAL ? 'http://localhost:5052' : 'https://gl-api.simpipro.com');
define('API_INVEST', 		IS_LOCAL ? 'http://localhost:5052' : 'https://invest-api.simpipro.com');
define('API_POS', 		IS_LOCAL ? 'http://localhost:5052' : 'https://pos-api.simpipro.com');
define('API_YUKBAJALAN', 		IS_LOCAL ? 'http://localhost:5052' : 'https://yukbajalan-api.simpipro.com');
define('API_SYSTEM', 	IS_LOCAL ? 'http://localhost:5053' : 'https://system-api.simpipro.com');
define('API_MARKET', 	IS_LOCAL ? 'http://localhost:5054' : 'https://market-api.simpipro.com');
define('API_MASTER', 	IS_LOCAL ? 'http://localhost:5055' : 'https://master-api.simpipro.com');
define('API_SHARIA', 	IS_LOCAL ? 'http://localhost:5055' : 'https://sharia-api.simpipro.com');

define('DATABASE_SYSTEM', 'simpi');
define('DATABASE_MARKET', 'simpi');
define('DATABASE_MASTER', 'simpi');
define('DATABASE_GL', 'simpi');
define('DATABASE_INVEST', 'simpi');
define('DATABASE_POS', 'simpi');
define('DATABASE_YUKBAJALAN', 'simpi');
define('DATABASE_GATEWAY', 'simpi');
define('DATABASE_SHARIA', 'simpi');
