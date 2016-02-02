#!/usr/bin/php
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
 * \file scripts/massupdatepassword.php
 * \ingroup massupdatepassword
 * \brief This file massupdatepassword
 */
$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi') {
	echo "Error: You are using PHP for CGI. To execute ";
	echo $script_file;
	echo " from command line, you must use PHP for CLI mode.\n";
	exit();
}

if (! isset($argv[1]) || ! $argv[1]) {
	print "Usage: " . $script_file . " userlogin [mailing_id] \n";
	exit();
}

// Global variables
$version = '1.0.0';
$error = 0;

/*
 * -------------------- YOUR CODE STARTS HERE --------------------
 */
/* Set this define to 0 if you want to allow execution of your script
 * even if dolibarr setup is "locked to admin user only". */
define('EVEN_IF_ONLY_LOGIN_ALLOWED', 0);

/* Include Dolibarr environment
 * Customize to your needs
 */
$res = @include $path . '../../../master.inc.php'; // For root directory
if (! $res) {
	$res = @include $path . '../../master.inc.php'; // For "custom" directory
}
if (! $res) {
	die("Include of master fails");
}
// No timeout for this script
@set_time_limit(0);

// Set the default language
// $langs->setDefaultLang('en_US');

// Load translations for the default language
$langs->load("main");
$langs->load("massupdatepassword@massupdatepassword");
$langs->load("mails");

// Display banner and help
echo "***** " . $script_file . " (" . $version . ") *****\n";
if (! isset($argv[1])) {
	// Check parameters
	echo "Usage: " . $script_file . " userlogin \n";
	exit();
}

/* User and permissions loading
 * Loads user for login 'admin'.
 * Comment out to run as anonymous user. */
$userlogin = $argv[1];

$result = $user->fetch('', $userlogin);
if (! $result > 0) {
	dol_print_error('', $user->error);
	exit();
}
$user->getrights();

// Display banner and help
echo '--- start ' . dol_print_date(dol_now(), 'dayhourtext') . "\n";
echo 'userlogin=' . $userlogin . "\n";

$langs = new Translate("", $conf);
$new_tranlaste = 'fr_FR';
$langs->setDefaultLang($new_tranlaste);

// Examples for manipulating a class
dol_include_once('/massupdatepassword/class/massupdatepassword.class.php');
$object = new MassUpdatePassword($db);

echo '--- '. $langs->trans('MassUpdatePasswordOpe'). "\n";

$result = $object->updateMassUpdatePassword($user, array());
if ($result < 0) {
	echo '--- updateMassUpdatePassword error message=' . $object->error . "\n";
	$error ++;
}

/*
 * --------------------- YOUR CODE ENDS HERE ----------------------
 */

print '--- end  ' . dol_print_date(dol_now(), 'dayhourtext') . "\n";
// Error management
if (! $error) {
	// $db->commit();
	echo '--- end ok' . "\n";
	$exit_status = 0; // UNIX no errors exit status
} else {
	echo '--- end error code=' . $error . "\n";
	// $db->rollback();
	$exit_status = 1; // UNIX general error exit status
}

// Close database handler
$db->close();

// Return exit status code
return $exit_status;
