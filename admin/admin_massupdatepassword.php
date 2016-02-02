<?php
/* Mass password update
 * Copyright (C) 2016 Florian HENRY <florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file admin/massupdatepassword.php
 * \ingroup massupdatepassword
 * \brief This file is an example module setup page
 * Put some comments here
 */
// Dolibarr environment
$res = @include ("../../main.inc.php"); // From htdocs directory
if (! $res) {
	$res = @include ("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/massupdatepassword.lib.php';
require_once "../class/massupdatepassword.class.php";

// Translations
$langs->load("massupdatepassword@massupdatepassword");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$monthupdate=GETPOST('monthupdate','int');
if (empty($monthupdate)) {
	$monthupdate=$conf->global->MUP_DEFAULT_MONTH_NEXT_RENEW;
}

/*
 * Actions
 */
if ($action == 'updatepassword_confirm' && $confirm = 'yes') {
	
	$object = new MassUpdatePassword($db);
	
	$result = $object->updateMassUpdatePassword($user, array ());
	if ($result < 0) {
		setEventMessages(null,$object->errors,'errors');
	} else {
		setEventMessages($langs->trans('Success'),null);
	}
}

if ($action == 'updaterenewdate_confirm' && $confirm = 'yes') {

	
	$object = new MassUpdatePassword($db);

	$result = $object->updateRenewPasswordDate($user, $monthupdate);
	if ($result < 0) {
		setEventMessages(null,$object->errors,'errors');
	} else {
		setEventMessages($langs->trans('Success'),null);
	}
}

/*
 * View
 */
$page_name = "MassUpdatePasswordSetup";
llxHeader('', $langs->trans($page_name));

if ($action == 'updatepassword') {
	$form = new Form($db);
	$text = $langs->trans("MassUpdatePasswordOpeText", dol_buildpath('/massupdatepassword/scripts/massupdatepassword.php') . ' ' . $user->login);
	$ret = $form->form_confirm($_SERVER['PHP_SELF'], $langs->trans("MassUpdatePasswordOpe"), $text, "updatepassword_confirm", '', '', 1, 250);
	if ($ret == 'html')
		print '<br>';
}

if ($action == 'updaterenewdate') {
	$form = new Form($db);
	$ret = $form->form_confirm($_SERVER['PHP_SELF'].'?monthupdate='.$monthupdate, $langs->trans("MassUpdatePasswordDtRenewConfirm",$monthupdate),  $langs->trans("MassUpdatePasswordDtRenewConfirm",$monthupdate), "updaterenewdate_confirm", '', '', 1, 250);
	if ($ret == 'html')
		print '<br>';
}

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = massupdatepasswordAdminPrepareHead();
dol_fiche_head($head, 'settings', $langs->trans("Module103985Name"), 0, "massupdatepassword@massupdatepassword");

// Setup page goes here
echo $langs->trans("MassUpdatePasswordSetupPage");


print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data" >';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="updaterenewdate">';

print '<table class="noborder" width="100%">';

print '<tr class="pair"><td>' . $langs->trans("MassUpdatePasswordMonthFromNow") . '</td>';
print '<td align="left">';
print '<input type="text"   name="monthupdate" value="' . $monthupdate. '" size="2" >';
print '</td>';
print '</tr>';

	print '<tr class="impair"><td colspan="2" align="right"><input type="submit" class="button" value="' . $langs->trans("Save") . '"></td>';
	print '</tr>';

print '</table>';
print '</form>';

print '<div class="tabsAction">';
print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=updatepassword">'.$langs->trans("MassUpdatePasswordOpe").'</a></div>'."\n";
print '</div>';

// Page end
dol_fiche_end();
llxFooter();
$db->close();
