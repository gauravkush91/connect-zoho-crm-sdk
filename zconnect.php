<?php
/*
Plugin Name: Connect ZOHO CRM SDK 
Description: Connect to Zoho CRM API by PHP SDK 2.2.1. This plugin is used to estabilshed connection between zoho crm and Wordpress website.
By using PHP SDK, You can perform Zoho crm option from wordpress site to zoho crm.

Zoho PHP SDK functions you can call inside any wordpress hook and any template file.
Version: 1.0.0
Author: Gaurav K
Text Domain: zconnect
*/
if (!defined('ABSPATH'))
exit;

define('ZC_PREFIX','zc');
define('ZC_VERSION', '1.0.0');
define('ZC_PLUGINFILE', __FILE__ );
define('ZC_PLUGINPATH', __DIR__ );
define('ZC_SITE_URL', site_url());
define('ZC_PLUGIN_URL', untrailingslashit( plugins_url( '', ZC_PLUGINFILE ) ) );

zc_define_url_constants();

require ZC_PLUGINPATH.'/thirdparty/vendor/autoload.php';
require ZC_PLUGINPATH."/includes/zohoHelper.php";

register_activation_hook( __FILE__, 'zc_plugin_activate' );
register_deactivation_hook( __FILE__, 'zc_plugin_deactivate' );
add_action('admin_enqueue_scripts','zc_plugin_assets', 20 );
add_action('admin_menu','zc_setting');
add_action('admin_init','zc_option_init');
add_action('admin_init','zc_initialize_zohoconfig');
add_action('parse_request','zc_initialize_zohoconfig' );
add_action('init','zc_initialize_zohoconfig');

function zc_plugin_activate() {
}

function zc_plugin_deactivate() {
  update_option('zconnect_email','');
  update_option('zconnect_clientid','');
  update_option('zconnect_clientsecret','');
  update_option('zconnect_crmconnection','');
  update_option('zconnect_crm_mode','');
  update_option('zconnect_domain_name','');
  update_option('zconnect_contaction_status','');
  file_put_contents(ZC_PLUGIN_ZOHO_LIVETOKENFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_SANDBOXTOKENFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_LIVELOGFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_SANDBOXLOGFILEPATH,'');
}


function zc_define_url_constants() {
  zc_defaultdefinemethod('ZC_BASE_DIR_URI', plugin_dir_path(ZC_PLUGINFILE));
  zc_defaultdefinemethod('ZC_BASE_SLUG', 'connect-zoho-crm-sdk');
  zc_defaultdefinemethod('ZC_BASE_DIR', WP_PLUGIN_URL . '/' . ZC_BASE_SLUG . '/');
  zc_defaultdefinemethod('ZC_PLUGIN_NAME_SETTINGS', 'Connect Zoho CRM SDK Settings');
  zc_defaultdefinemethod('ZC_PLUGIN_VERSION', '1.0');
  zc_defaultdefinemethod('ZC_PLUGIN_NAME', 'Zoho Connect');
  zc_defaultdefinemethod('ZC_PLUGIN_BASE_URL', site_url() . '/wp-admin/admin.php?page='.ZC_BASE_SLUG);
  zc_defaultdefinemethod('ZC_TOKEN_PATH',ZC_PLUGINPATH."/crmTokens/live");
  zc_defaultdefinemethod('ZC_TOKEN_PATH_SANDBOX',ZC_PLUGINPATH."/crmTokens/sandbox");
  zc_defaultdefinemethod('ZC_PLUGIN_ZOHO_HOMEURL',ZC_SITE_URL);
  zc_defaultdefinemethod('ZC_PLUGIN_ZOHO_LIVELOGFILEPATH',ZC_PLUGINPATH."/crmTokens/live/ZCRMClientLibrary.log");
  zc_defaultdefinemethod('ZC_PLUGIN_ZOHO_SANDBOXLOGFILEPATH',ZC_PLUGINPATH."/crmTokens/sandbox/ZCRMClientLibrary.log");
  zc_defaultdefinemethod('ZC_PLUGIN_ZOHO_LIVETOKENFILEPATH',ZC_PLUGINPATH."/crmTokens/live/zcrm_oauthtokens.txt");
  zc_defaultdefinemethod('ZC_PLUGIN_ZOHO_SANDBOXTOKENFILEPATH',ZC_PLUGINPATH."/crmTokens/sandbox/zcrm_oauthtokens.txt");
  zc_defaultdefinemethod('ZC_DOWNLOAD_FILE_ZOHO_SDK_STATEMENT',ZC_PLUGIN_URL."/includes/zoho_sdk_use_statement.php");
}

function zc_defaultdefinemethod($name, $value) {
  if (!defined($name)) {
    define($name, $value);
  }
}

function zc_plugin_baseurl( $path = '' ) {
  $url = plugins_url( $path, ZC_PLUGINFILE );
  if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
    $url = 'https:' . substr( $url, 5 );
  }
  return $url;
}

function zc_plugin_assets() {
  $pages_list = array(ZC_BASE_SLUG);
  if(sanitize_text_field(isset($_REQUEST['page'])) && in_array(sanitize_text_field($_REQUEST['page']), $pages_list)) {
    wp_register_style('zconnect-bootstrap-css',zc_plugin_baseurl('assets/css/bootstrap.css'));
      wp_enqueue_style('zconnect-bootstrap-css');
      wp_register_style('zconnect-style-css',zc_plugin_baseurl('assets/css/style.css'));
      wp_enqueue_style('zconnect-style-css');
      wp_register_script('zconnect-jquery-scrpit',zc_plugin_baseurl('assets/js/jquery-3.5.1.min.js'),array('jquery'),ZC_VERSION,true);
      wp_enqueue_script('zconnect-jquery-scrpit');
      wp_register_script('zconnect-scrpit',zc_plugin_baseurl('assets/js/script.js'),array('jquery'),ZC_VERSION,true);
      wp_enqueue_script('zconnect-scrpit');
  }
}

function zc_save_options($data){
  $zconnect_email         = $data['zconnect_email'];
  $zconnect_clientid      = $data['zconnect_clientid'];
  $zconnect_clientsecret  = $data['zconnect_clientsecret'];
  $zconnect_crmconnection = $data['zconnect_crmconnection'];
  $zconnect_crm_mode      = $data['zconnect_crm_mode'];
  $zconnect_domain_name   = $data['zconnect_domain_name'];
  update_option('zconnect_email',$zconnect_email);
  update_option('zconnect_clientid',$zconnect_clientid);
  update_option('zconnect_clientsecret',$zconnect_clientsecret);
  update_option('zconnect_crmconnection',$zconnect_crmconnection);
  update_option('zconnect_crm_mode',$zconnect_crm_mode);
  update_option('zconnect_domain_name',$zconnect_domain_name);
  update_option('zconnect_contaction_status','');
  file_put_contents(ZC_PLUGIN_ZOHO_LIVETOKENFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_SANDBOXTOKENFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_LIVELOGFILEPATH,'');
  file_put_contents(ZC_PLUGIN_ZOHO_SANDBOXLOGFILEPATH,'');
}

function zc_call_auth($code){
  global $zc_zoho_config;
  $zcObj = new ZCZohoHelper($zc_zoho_config);
  $connectedRequest = $zcObj->authConnect($code);
  if($connectedRequest["status"]=="success"){
    update_option('zconnect_contaction_status',"connected");
    echo "Zoho SDK Connected. Redirecting please wait.... <br/>";
    ?>
    <script type="text/javascript">
      setTimeout(function(){ window.location.href = "<?php echo ZC_PLUGIN_BASE_URL; ?>"; }, 3000);
    </script>
    <?php 
  }else{
    update_option('zconnect_contaction_status',"fail");
    echo "Zoho SDK Failed to connect.<br/>";
    echo "<pre>";
    print_r($connectedRequest["response"]);
    echo "</pre>";
  }
}

function zc_setting() {
  add_menu_page("Zoho CRM Settings", "Zoho Connect", 'manage_options',ZC_BASE_SLUG, 'zc_setting_page');
}

function zc_setting_page() {
  include_once 'includes/settings-page.php';
}

function zc_option_init(){
  register_setting( 'zconnect_option_group', 'zconnect_domain_name');
  register_setting( 'zconnect_option_group', 'zconnect_email');
  register_setting( 'zconnect_option_group', 'zconnect_clientid');
  register_setting( 'zconnect_option_group', 'zconnect_clientsecret');
  register_setting( 'zconnect_option_group', 'zconnect_crmconnection');
  register_setting( 'zconnect_option_group', 'zconnect_crm_mode');
}

add_action('plugins_loaded','zc_load_zohosdk');
function zc_load_zohosdk() {
}

function zc_initialize_zohoconfig(){
  global $zc_zoho_config;
  $zc_zoho_config         = [];
  $zconnect_email         = get_option('zconnect_email');
  $zconnect_clientid      = get_option('zconnect_clientid');
  $zconnect_clientsecret  = get_option('zconnect_clientsecret');
  $zconnect_crm_mode      = get_option('zconnect_crm_mode');
  $zconnect_domain_name   = get_option('zconnect_domain_name');
  $sandbox                = "false";
  if($zconnect_crm_mode=="yes"){
    $sandbox = "true";
  }

  if(!empty($zconnect_email) && !empty($zconnect_clientid) && !empty($zconnect_clientsecret)&& !empty($zconnect_domain_name) ){
    $zc_zoho_config = array(
      "client_id"                 => $zconnect_clientid,
      "client_secret"             => $zconnect_clientsecret,
      "redirect_uri"              => ZC_PLUGIN_BASE_URL,
      "currentUserEmail"          => $zconnect_email,
      "applicationLogFilePath"    => ZC_TOKEN_PATH, 
      "sandbox"                   => $sandbox,
      "apiBaseUrl"                => "www.zohoapis.".$zconnect_domain_name,
      "apiVersion"                => "v2",
      "access_type"               => "offline",
      "accounts_url"              => "https://accounts.zoho.".$zconnect_domain_name,
      "persistence_handler_class" => "ZohoOAuthPersistenceHandler",
      "token_persistence_path"    => ZC_TOKEN_PATH
    );
  
    if($sandbox =="true"){
      $zc_zoho_config["sandbox"]= "true";
      $zc_zoho_config["token_persistence_path"] = ZC_TOKEN_PATH_SANDBOX;
      $zc_zoho_config["applicationLogFilePath"] = ZC_TOKEN_PATH_SANDBOX;
    }
  }
}



