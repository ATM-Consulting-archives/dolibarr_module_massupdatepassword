<?php
/* Mass password update
 * Copyright (C) 2016 Florian HENRY <florian.henry@open-concept.pro>
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
 * \file /massupdatepassword/class/massupdatepassword.class.php
 * \ingroup mailchimp
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';

/**
 * Put here description of your class
 */
class MassUpdatePassword extends CommonObject
{
	var $db; // !< To store db handler
	var $error; // !< To return error code (or message)
	var $errors = array (); // !< To return several error codes (or messages)
	
	
	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 */
	function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 *
	 * @param unknown $user
	 * @param array $exclude
	 */
	public function updateMassUpdatePassword($user, $exclude = array()) {
		global $conf, $langs;
		
		dol_syslog(get_class($this) . '::' . __METHOD__, LOG_DEBUG);
		
		$error = 0;
		
		$sql = 'SELECT rowid ';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'user ';
		$sql .= ' WHERE email IS NOT NULL ';
		$sql .= ' AND email<>\'\' ';
		$sql .= ' AND admin<>1 ';
		$sql .= ' AND statut=1 ';
		if (count($exclude)>0) {
			$sql .= ' AND rowid NOT IN ('.implode(',',$exclude).')';
		}
		
		$resql = $this->db->query($sql);
		if (! $resql) {
			
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}
		
		if (empty($error)) {
			while ( $obj_user = $this->db->fetch_object($resql) ) {
				
				$object = new User($this->db);
				
				$object->fetch($obj_user->rowid);
				
				
				if (filter_var($object->email, FILTER_VALIDATE_EMAIL)) {
				
					$newpassword = $object->setPassword($user, '', 0, 1);
					if ($newpassword < 0) {
						// Echec
						$error ++;
						$this->errors[] = $langs->trans("ErrorFailedToSetNewPassword");
						dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
					} else {
						// Succes
						$result = $object->send_password($user, $newpassword);
						if ($result < 0) {
							$error ++;
							$this->errors[] = $object->error;
							dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
						}
					}
				} else {
					dol_syslog(__METHOD__ . ' User login : '.$object->login.' email ('.$object->email.')is not correct, password not reseted', LOG_ERR);
				}
			}
		}
		
		if (empty($error)) {
			return 1;
		} else {
			$this->error = join(',', $this->errors);
			return - 1 * $error;
		}
	}
}
	