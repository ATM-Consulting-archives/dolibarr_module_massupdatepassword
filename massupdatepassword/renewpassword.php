<?php
/* Mass password update
 * Copyright (C) 2016		Florian Henry			<florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       /massupdatepassword//massupdatepassword/renewpassword.php
 *       \brief      Page to ask a new password
 */

define("NOLOGIN",1);	// This means this output page does not require to be logged.

$res = @include $path . '../../../main.inc.php'; // For root directory
if (! $res) {
	$res = @include $path . '../../main.inc.php'; // For "custom" directory
}
if (! $res) {
	die("Include of master fails");
}
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
if (! empty($conf->ldap->enabled)) require_once DOL_DOCUMENT_ROOT.'/core/class/ldap.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once "../class/massupdatepassword.class.php";


$langs->load("errors");
$langs->load("users");
$langs->load("companies");
$langs->load("ldap");
$langs->load("other");
$langs->load("massupdatepassword@massupdatepassword");

// Security check
if (! empty($conf->global->MUP_SECURITY_DISABLENEWPASSLINK))
{
    header("Location: ".DOL_URL_ROOT.'/');
    exit;
}

$action=GETPOST('action', 'alpha');
$mode=$dolibarr_main_authentication;
if (! $mode) $mode='http,dolibarr';
$authmode=explode(',',$dolibarr_main_authentication);

$username 		= GETPOST('username');
$currentpassword	= GETPOST('currentpassword');
$newpassword	= GETPOST('newpassword');
$conf->entity 	= (GETPOST('entity') ? GETPOST('entity') : 1);


if (GETPOST('dol_hide_leftmenu') || ! empty($_SESSION['dol_hide_leftmenu']))               $conf->dol_hide_leftmenu=1;
if (GETPOST('dol_hide_topmenu') || ! empty($_SESSION['dol_hide_topmenu']))                 $conf->dol_hide_topmenu=1;
if (GETPOST('dol_optimize_smallscreen') || ! empty($_SESSION['dol_optimize_smallscreen'])) $conf->dol_optimize_smallscreen=1;
if (GETPOST('dol_no_mouse_hover') || ! empty($_SESSION['dol_no_mouse_hover']))             $conf->dol_no_mouse_hover=1;
if (GETPOST('dol_use_jmobile') || ! empty($_SESSION['dol_use_jmobile']))                   $conf->dol_use_jmobile=1;


/**
 * Actions
 */

// Validate new password
if ($action == 'validatenewpassword' && $username && $currentpassword && $newpassword)
{
    $edituser = new User($db);
    $result=$edituser->fetch('',$username);
    if ($result < 0)
    {
    	$message=$langs->trans("ErrorLoginDoesNotExists",$username);
        setEventMessages(null, array($langs->trans("ErrorLoginDoesNotExists",$username)),'errors');
    }
    else
    {
    	
    	$login = checkLoginPassEntity($username,$currentpassword,$conf->entity,$authmode);
    	if ($login==$username)
    	{
    		$result = $edituser->setPassword($user, $newpassword, 0, 1);
    		if ($result < 0) {
    			$message=$langs->trans("ErrorFailedToSetNewPassword");
    			setEventMessages(null, array($langs->trans("ErrorFailedToSetNewPassword")),'errors');
    		} else {
    			$object = new MassUpdatePassword($db);
    			
    			$result = $object->updateRenewPasswordDate($edituser, $conf->global->MUP_DEFAULT_MONTH_NEXT_RENEW);
    			if ($result < 0) {
    				setEventMessages(null,$object->errors,'errors');
    			}
    			
    			
    			session_destroy();
    			Header('Location: '.dol_buildpath('/index.php',2));
    			exit;
    		}
    	}
    	else 
    	{
    		$message=$langs->trans("ErrorBadLoginPassword");
    		setEventMessages(null, array($langs->trans("ErrorBadLoginPassword")),'errors');
    	}
    }
}


/**
 * View
 */

$php_self = $_SERVER['PHP_SELF'];
$php_self.= $_SERVER["QUERY_STRING"]?'?'.$_SERVER["QUERY_STRING"]:'';

$dol_url_root = DOL_URL_ROOT;

// Title
$title='Dolibarr '.DOL_VERSION;
if (! empty($conf->global->MAIN_APPLICATION_TITLE)) $title=$conf->global->MAIN_APPLICATION_TITLE;

// Select templates
if (file_exists(DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/tpl/renewpassword.tpl.php"))
{
    $template_dir = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/tpl/";
}
else
{
    $template_dir = dol_buildpath('/massupdatepassword/tpl/',0);
}

// Note: $conf->css looks like '/theme/eldy/style.css.php'
$conf->css = "/theme/".(GETPOST('theme')?GETPOST('theme','alpha'):$conf->theme)."/style.css.php";
//$themepath=dol_buildpath((empty($conf->global->MAIN_FORCETHEMEDIR)?'':$conf->global->MAIN_FORCETHEMEDIR).$conf->css,1);
$themepath=dol_buildpath($conf->css,1);
if (! empty($conf->modules_parts['theme']))	// This slow down
{
	foreach($conf->modules_parts['theme'] as $reldir)
	{
		if (file_exists(dol_buildpath($reldir.$conf->css, 0)))
		{
			$themepath=dol_buildpath($reldir.$conf->css, 1);
			break;
		}
	}
}
$conf_css = $themepath."?lang=".$langs->defaultlang;

$jquerytheme = 'smoothness';
if (! empty($conf->global->MAIN_USE_JQUERY_THEME)) $jquerytheme = $conf->global->MAIN_USE_JQUERY_THEME;

if (file_exists(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/img/login_background.png'))
{
    $login_background = DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/login_background.png';
}
else
{
    $login_background = DOL_URL_ROOT.'/theme/login_background.png';
}

// Send password button enabled ?
$disabled='disabled';
if (preg_match('/dolibarr/i',$mode)) $disabled='';

// Show logo (search in order: small company logo, large company logo, theme logo, common logo)
$width=0;
$rowspan=2;
$urllogo=DOL_URL_ROOT.'/theme/login_logo.png';
if (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode('thumbs/'.$mysoc->logo_small);
}
elseif (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
	$width=128;
}
elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/img/dolibarr_logo.png'))
{
	$urllogo=DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/dolibarr_logo.png';
}
elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/dolibarr_logo.png'))
{
	$urllogo=DOL_URL_ROOT.'/theme/dolibarr_logo.png';
}

// Security graphical code
if (function_exists("imagecreatefrompng") && ! $disabled)
{
	$captcha = 1;
	$captcha_refresh = img_picto($langs->trans("Refresh"),'refresh','id="captcha_refresh_img"');
}

include $template_dir.'renewpassword.tpl.php';	// To use native PHP

llxFooter();
$db->close();
