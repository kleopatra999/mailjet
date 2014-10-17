<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/


require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));

if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));

require_once(dirname(__FILE__).'/mailjet.php');


$mj = new Mailjet();

$internalToken = null;
if (Tools::getIsset('internaltoken')) {
	$internalToken = Tools::getValue('internaltoken');
}

$adminDirName = null;
$maindirs = scandir(realpath(dirname(__FILE__) .'/../../'));
foreach ($maindirs as $dirName) {
	if (strpos($dirName, 'admin') !== false) {
		$adminDirName = $dirName;
	}
}

if (!$adminDirName) {
	throw new Exception('Admin dir must be found.');
}

//mail("astoyanov@mailjet.com", "", print_r($_POST, true));
if (Tools::getIsset('data'))
{
	$data = Tools::getValue('data');
	if (array_key_exists('apikey',$data))
	{
		$mj->account['API_KEY'] = $data['apikey'];
		$mj->account['SECRET_KEY'] = $data['secretkey'];

	
		try {
			$auth = $mj->auth($data['apikey'], $data['secretkey']);
			
			if ($auth) {
				$mj->updateAccountSettings();
				$mj->activateAllEmailMailjet();
			} 
		} catch (Exception $e) {
			//mail("astoyanov@mailjet.com", "", print_r($e, true));
		}

	}
	
	if (isset($data['next_step_url']) && $data['next_step_url'] == 'reseller/signup/welcome') {
		$response = array(
					"code"				=> 1,
					"continue"			=> true,
					"continue_address"	=> 'campaigns',
		);
		
		$link = new Link();
		$moduleTabRedirectLink = @$link->getAdminLink('moduleTabRedirect', true);
		
		$response = array(
				"code"				=> 1,
				"continue"			=> 0,
				"exit_url"			=> "http://".Configuration::get('PS_SHOP_DOMAIN'). '/' .$adminDirName . '/' . $mj->getAdminModuleLink(array(MailJetPages::REQUEST_PAGE_TYPE => 'HOME'), 'AdminModules', $internalToken)
				//"exit_url"			=> $mj->getAdminFullUrl().$mj->getAdminModuleLink(array(MailJetPages::REQUEST_PAGE_TYPE => 'HOME'))
				//"exit_url"			=> "http://mailjet.dream-me-up.fr/admin_dmu/index.php?controller=moduleTabRedirect&token=3d9d49481e6ca3a14998cd44ddc0b878",
		);
	} else {
		$response = array(
				"code"				=> 1,
				"continue"			=> true,
				"continue_address"	=> $data['next_step_url'],
		);
	}
	
	//mail("astoyanov@mailjet.com", "", print_r($_POST, true).print_r($response, true).$internalToken);

	echo Tools::jsonEncode($response);
}

?>