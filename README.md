# connect-zoho-crm-sdk
Wordpress Plugin Connect to Zoho CRM API by PHP SDK 2.2.1. This plugin is used to estabilshed connection between zoho crm and Wordpress website. By using PHP SDK, You can perform Zoho crm option from wordpress site to zoho crm.


**=== Connect SDK for ZOHO CRM ===**
Tags: Zoho CRM SDK Connect WP, Zoho CRM Wordpress, Zoho CRM , CRM
Requires at least: 3.3
Author: Gaurav K
Tested up to: 5.4.1
Stable tag:1.7.2.4
License: GPLv2 or later
Version: 1.7.2.4
License URI: http://www.gnu.org/licenses/gpl-2.0.html



**== Description ==**

This plugin is used to estabilshed connection between zoho crm and Wordpress website.
By using PHP SDK, You can perform Zoho crm operation from wordpress site.

Zoho PHP SDK functions you can call inside any wordpress hook and any template file.

**== Installation ==**

1. Install the Wordpress plugin from Wordpress Marketplace. Upload the plugin folder to the /wp-content/plugins/ directory or install via the Add New Plugin menu.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Once you activate your Zoho CRM forms plugin, you'll be asked to authenticate access from Zoho CRM. To do that, simply  enter the email and Client ID and Secret key of your CRM account.

The plugin installation is now complete and the integration between Zoho CRM and Wordpress is established.



**== Frequently asked questions ==**



**== Screenshots ==**

1. Integrating Zoho CRM with Wordpress
2. Instruction to use zoho SDK function inside wordpress hook.

**== Changelog ==**

**== Instructions: ==**

**1) Copy this use statement and paste into your current theme functions.php at the very top**

      use zcrmsdk\crm\crud\ZCRMAttachment;
      use zcrmsdk\crm\crud\ZCRMCustomView;
      use zcrmsdk\crm\crud\ZCRMCustomViewCategory;
      use zcrmsdk\crm\crud\ZCRMCustomViewCriteria;
      use zcrmsdk\crm\crud\ZCRMEventParticipant;
      use zcrmsdk\crm\crud\ZCRMField;
      use zcrmsdk\crm\crud\ZCRMInventoryLineItem;
      use zcrmsdk\crm\crud\ZCRMJunctionRecord;
      use zcrmsdk\crm\crud\ZCRMLayout;
      use zcrmsdk\crm\crud\ZCRMLeadConvertMapping;
      use zcrmsdk\crm\crud\ZCRMLeadConvertMappingField;
      use zcrmsdk\crm\crud\ZCRMLookupField;
      use zcrmsdk\crm\crud\ZCRMModule;
      use zcrmsdk\crm\crud\ZCRMModuleRelatedList;
      use zcrmsdk\crm\crud\ZCRMModuleRelation;
      use zcrmsdk\crm\crud\ZCRMNote;
      use zcrmsdk\crm\crud\ZCRMOrgTax;
      use zcrmsdk\crm\crud\ZCRMPermission;
      use zcrmsdk\crm\crud\ZCRMPickListValue;
      use zcrmsdk\crm\crud\ZCRMPriceBookPricing;
      use zcrmsdk\crm\crud\ZCRMProfileCategory;
      use zcrmsdk\crm\crud\ZCRMTrashRecord;
      use zcrmsdk\crm\crud\ZCRMTax;
      use zcrmsdk\crm\crud\ZCRMTag;
      use zcrmsdk\crm\crud\ZCRMSection;
      use zcrmsdk\crm\crud\ZCRMRelatedListProperties;
      use zcrmsdk\crm\crud\ZCRMRecord;
      use zcrmsdk\crm\crud\ZCRMProfileSection;
      use zcrmsdk\crm\exception\ZCRMException;
      use zcrmsdk\crm\setup\org\ZCRMOrganization;
      use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
      use zcrmsdk\crm\setup\users\ZCRMProfile;
      use zcrmsdk\crm\setup\users\ZCRMRole;
      use zcrmsdk\crm\setup\users\ZCRMUser;
      use zcrmsdk\crm\setup\users\ZCRMUserCustomizeInfo;
      use zcrmsdk\crm\setup\users\ZCRMUserTheme;
      use zcrmsdk\oauth\ZohoOAuth;
      use zcrmsdk\oauth\ZohoOAuthClient;

**2) You can use any zoho SDK function into wordpress hooks function.**
<pre>
i) Define global variable inside function : global $zc_zoho_config;
ii) Sample code:

function testZohosdk(){
   global $zc_zoho_config;
   $returnResponse = array();
   $module="Leads";
   $recordID ="1234567890";
  try {
     ZCRMRestClient::initialize($zc_zoho_config);
     $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
     $response  = $moduleIns->getRecord($recordID);
     $record    = $response->getData();
     $returnResponse["status"] = "success";
     $returnResponse["response"] = $record;
   }catch (Exception $e) {
     $strError = "($module) ERROR: ".$e->getMessage();
     $returnResponse["code"]   = $e->getCode();
     $returnResponse["status"] = "error";
     $returnResponse["response"] = $strError;
   }
   return $returnResponse; 
}
add_action("get_header","testZohosdk");

   </pre>
         
**3) You can call the zoho sdk functions into you template file also.**
<pre>
i) Copy the required use statement from Step 
ii)Define global variable: global $zc_zoho_config;
iii) Add Sample Code

global $zc_zoho_config;
$returnResponse = array();
$module="Leads";
$recordID ="1234567890";
try {
  ZCRMRestClient::initialize($zc_zoho_config);
  $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
  $response  = $moduleIns->getRecord($recordID);
  $record    = $response->getData();
  $returnResponse["status"] = "success";
  $returnResponse["response"] = $record;
} catch (Exception $e) {
  $strError = "($module) ERROR: ".$e->getMessage();
  $returnResponse["code"]   = $e->getCode();
  $returnResponse["status"] = "error";
  $returnResponse["response"] = $strError;
}
print_r($returnResponse); 
</pre>


**== Screenshots ==**

1. Integrating Zoho CRM with Wordpress
2. Instruction to use zoho SDK function inside wordpress hook.

**== Changelog ==**
