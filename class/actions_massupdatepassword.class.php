<?php
/* Mass password update
 * Copyright (C) 2016		Florian Henry			<florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file /massupdatepassword/class/actions_massupdatepassword.class.php
 * \ingroup massupdatepassword
 * \brief File of massupdatepassword
 */

/**
 * \class ActionsMassUpdatePassword
 * \brief Class to manage ActionsMassUpdatePassword
 */
class ActionsMassUpdatePassword
{
	protected $db;
	public $dao;
	public $error;
	public $errors = array ();
	public $resprints = '';
	
	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 */
	public function __construct($db) {
		$this->db = $db;
		$this->error = 0;
		$this->errors = array ();
	}
	
	/**
	 * 
	 * @param unknown $parameters
	 * @param unknown $object
	 * @param unknown $action
	 * @param unknown $hookmanager
	 */
	public function afterLogin($parameters, &$object, &$action, $hookmanager) {
		global $conf, $langs;
		
		require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
		
		$object_usr = new User($this->db);
		$result = $object_usr->fetch($object->id);
		$dttest = $this->db->jdate($object_usr->array_options['options_pwd_expiration_dt']);
		if (! empty($dttest)) {
			if ($dttest < dol_now()) {
				session_destroy();
				Header('Location: ' . dol_buildpath('/massupdatepassword/massupdatepassword/renewpassword.php', 2) . '?username=' . $object->login);
				exit();
			}
		}
		
		return 0;
	}
}