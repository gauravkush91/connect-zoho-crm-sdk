<div class="wrap wpzoho_connect container">
  <h2><?php echo ZC_PLUGIN_NAME_SETTINGS; ?></h2>
  <div class="row">
    <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
      <form id='custom-zoho-options' method='post' action='<?php echo ZC_PLUGIN_BASE_URL; ?>'>
        <?php settings_fields('zconnect_option_group'); ?>
        <?php do_settings_sections('zconnect_option_group'); ?>
        <table class="form-table">
          <tbody>
            <tr>
              <th><label>Status:</label></th>
              <td>
                <?php
                  if(isset($_POST['submit_option']) && !empty($_POST['submit_option'])){
                    zc_save_options($_POST);
                    ?>
                    <script type="text/javascript">
                    window.location.href = "<?php echo ZC_PLUGIN_BASE_URL; ?>";
                    </script>
                    <?php
                  }
                  $zconnect_email             = get_option('zconnect_email');
                  $zconnect_clientid          = get_option('zconnect_clientid');
                  $zconnect_clientsecret      = get_option('zconnect_clientsecret');
                  $zconnect_crm_mode          = get_option('zconnect_crm_mode');
                  $zconnect_domain_name       = get_option('zconnect_domain_name');
                  $zconnect_contaction_status = get_option('zconnect_contaction_status');
                  if(empty($zconnect_domain_name)){
                    $zconnect_domain_name ="com";
                  }
                  if(empty($zconnect_crm_mode)){
                    $zconnect_crm_mode = "no";
                  }

                  if(empty($zconnect_contaction_status)){
                    echo "<b>".strtoupper("Not Connected")."</b>";
                  }else{
                    echo "<b>".strtoupper("$zconnect_contaction_status")."</b>";
                  }
                  if(empty($zconnect_contaction_status) || $zconnect_contaction_status=="fail"){
                    $fromButtonName ="Save";
                    $zohoConnectButtonName= "Connect";
                  }else{
                    $fromButtonName ="Update";
                    $zohoConnectButtonName= "Re-Connect";
                  }
                  global $zc_zoho_config;
                  if(isset($_REQUEST['download-file']) && $_REQUEST['download-file']=="yes" ){
                    download_file(ZC_DOWNLOAD_FILE_ZOHO_SDK_STATEMENT);
                  }
                ?>
              </td>
            </tr>
            <tr>
              <th><label>1) Zoho Domain</label></th>
              <td>
                <?php $domain =["com","eu","in","com.au"];?>
                <select id="zc_domain_name" class="regular-text" name="zconnect_domain_name">
                  <?php
                  foreach($domain as $key => $value){
                    $selected ="";
                    if($zconnect_domain_name ==$value){ $selected ="selected";}
                    echo "<option ".$selected." value='".$value."'>".$value."</option>";
                  }
                  ?>
                </select>
                <p class='description'>The name of the region the account is configured</p>
              </td>
            </tr>
            <tr>
              <th><label>2) CRM Mode</label></th>
              <td>
                <?php $crm_mode =["no"=>"Live","yes"=>"Sandbox"];?>
                <select id="zc_domain_name" class="regular-text" name="zconnect_crm_mode">
                  <?php
                  foreach($crm_mode as $key => $value){
                    $selected ="";
                    if($zconnect_crm_mode ==$key){ $selected ="selected";}
                    echo "<option ".$selected." value='".$key."'>".$value."</option>";
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
              <th><label>3) Zoho Email</label></th>
              <td>
                <div class="custom-zoho-option-wrap">
                  <input type="email" class="regular-text" name="zconnect_email" value="<?php echo esc_attr(get_option('zconnect_email')); ?>" class="" required="true"/>
                </div>
              </td>
            </tr>
            <tr>
              <th><label>4) Client ID</label><br/></th>
              <td>
                <div class="custom-zoho-option-wrap">
                  <input type="text" class="regular-text" name="zconnect_clientid"  value="<?php echo esc_attr(get_option('zconnect_clientid')); ?>" class="" required="true" />
                  <p class='description'><a href="https://accounts.zoho.<?php echo $zconnect_domain_name; ?>/developerconsole" target="_blank">How to create client id and Screct key</a></p>
                </div>
              </td>
            </tr>
            <tr>
              <th><label>5) Client Secret</label><br/></th>
              <td>
                <div class="custom-zoho-option-wrap">
                  <input type="text" class="regular-text" name="zconnect_clientsecret" value="<?php echo esc_attr(get_option('zconnect_clientsecret')); ?>" class="" required="true" />
                   <p class='description'>Created in the developer console</p>
                </div>
              </td>
            </tr>
            <tr>
              <th><label>6) Homepage URL</label><br/></th>
              <td>
                <div class="custom-zoho-option-wrap">
                  <input type="url" class="regular-text" id=""  name="zconnect_homepageurl" value="<?php echo ZC_PLUGIN_ZOHO_HOMEURL; ?>" class=""readonly />
                  <p class='description'>(Copy this URL and use in Homepage URL of Zoho CRM app.)<p>
                </div>
              </td>
            </tr>
            <tr>
              <th><label>7) Authorized Redirect URIs</label><br/></th>
              <td>
                  <div class="custom-zoho-option-wrap">
                    <input type="url" class="regular-text" name="zconnect_redirecturi" value="<?php echo ZC_PLUGIN_BASE_URL; ?>" class="" readonly />
                    <p class='description'>(Copy this URL and use in Authorized Redirect URIs of Zoho CRM app.)<p>
                    <input type="hidden" name="zconnect_crmconnection" value="<?php echo esc_attr(get_option('zconnect_crmconnection')); ?>">
                  </div>
              </td>
            </tr>
            <?php
            if(!empty($zconnect_email) && !empty($zconnect_clientid) && !empty($zconnect_clientsecret) && !empty($zconnect_domain_name)){
            ?>
             <tr>
              <th>
                <label>8) Connect Zoho SDK</label>
              </th>
              <td>
                <a href="https://accounts.zoho.<?php echo $zconnect_domain_name; ?>/oauth/v2/auth?scope=ZohoCRM.users.ALL,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.org.ALL,ZohoCRM.bulk.read,aaaserver.profile.ALL&client_id=<?php echo $zconnect_clientid; ?>&response_type=code&access_type=offline&redirect_uri=<?php echo ZC_PLUGIN_BASE_URL; ?>" target="_blank" class="cus_generate_auth">
                <?php echo $zohoConnectButtonName; ?> 
                </a>
              </td>
            </tr>
            <?php
            }
            ?>
            <tr>
              <td colspan="2">
                <?php if($fromButtonName=="Update"){
                  echo "<p class='description'>On update info.Connection will be reset.</p>";
                }
                ?>
                <input type="submit" name="submit_option" value="<?php echo $fromButtonName; ?>" class="button button-primary">
              </td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>
    <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">

      <?php 
      if(isset($_REQUEST['code']) && !empty($_REQUEST['code'])){
        zc_call_auth($_REQUEST['code']);
      }
      if(!empty($zconnect_contaction_status) && $zconnect_contaction_status=="connected"): ?>
        <b style="font-size:21px">Instructions: </b> <br/>
        <p><b style="font-size:16px">1) Copy this use statement and paste into your current theme functions.php at the very top</b>
          <br/>
        <code>
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
        </code>
        </p>
        <p><b style="font-size:16px">2) You can use any zoho SDK function into wordpress hooks function.</b><br/>
          i) Define global variable inside function : <code>global $zc_zoho_config;</code><br/>
          ii) Sample code:<br/>
          <code>
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
              } catch (Exception $e) {
                $strError = "($module) ERROR: ".$e->getMessage();
                $returnResponse["code"]   = $e->getCode();
                $returnResponse["status"] = "error";
                $returnResponse["response"] = $strError;
              }
              return $returnResponse; 
            }
            add_action("get_header","testZohosdk");
          </code>
        </p>
         <p><b style="font-size:16px">3) You can call the zoho sdk functions into you template file also.</b><br/>
          i) Copy the required use statement from Step 1<br/>
          ii) Define global variable: <code>global $zc_zoho_config;</code><br/>
          iii) Add Sample Code<br/>
          <code>
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
          </code>
         </p>
      <?php endif; ?>
    </div>
  </div>

</div>
