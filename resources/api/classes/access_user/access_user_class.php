<?php
/************************************************************************
Access_user Class ver. 1.86
Easy to use class to protect pages and register users

Copyright (c) 2004 - 2005, Olaf Lederer
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the finalwebsites.com nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

_________________________________________________________________________
available at http://www.finalwebsites.com 
Comments & suggestions: http://www.finalwebsites.com/contact.php
If you need help check this forum too:
http://olederer.users.phpclasses.org/discuss/package/1906/

*************************************************************************/

// session_start();
// error_reporting (E_ALL); // I use this only for testing
require("db_config.php"); // this path works for me...

require_once( $config['path']['base'] . "resources/api/classes/class.mysqli_database.php");

class Access_user {
	
	var $table_name = ''; 
	
	var $user;
	var $user_pw;
	var $accessLevel;
	var $user_full_name;
	var $user_info;
	var $user_email;
	var $save_login = "no";
	var $cookie_name = COOKIE_NAME;
	var $cookie_path = COOKIE_PATH; 
	var $is_cookie;
	
	var $count_visit;
	
	var $id;
	var $language = "en"; // change this property to use messages in another language 
	var $the_msg;
	var $login_page;
	var $main_page;
	var $password_page;
	var $deny_access_page;
	var $auto_activation = true;
	var $send_copy = false; // send a mail copy to the administrator (register only)
	
	var $webmaster_mail = WEBMASTER_MAIL;
	var $webmaster_name = WEBMASTER_NAME;
	var $admin_mail = ADMIN_MAIL;
	var $admin_name = ADMIN_NAME;

	function Access_user( $mysql_host, $mysql_user, $mysql_pass, $mysql_name ) {
		$this->mysql_host = $mysql_host;
		$this->mysql_user = $mysql_user;
		$this->mysql_pass = $mysql_pass;
		$this->mysql_name = $mysql_name;
		$this->table_name = $mysql_name . ".users"; 
		$this->connected = $this->connect_db();
		$this->login_page = LOGIN_PAGE;
		$this->main_page = START_PAGE;
		$this->password_page = ACTIVE_PASS_PAGE;
		$this->deny_access_page = DENY_ACCESS_PAGE;
		$this->admin_page = ADMIN_PAGE;
	}
	
	
	function check_user($pass = "") {
		switch ($pass) {
			case "new": 
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE email = '%s' OR login = '%s'", $this->table_name, $this->user_email, $this->user);
			break;
			case "lost":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE email = '%s' AND active = 'y'", $this->table_name, $this->user_email);
			break;
			case "new_pass":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE pw = '%s' AND id = %d", $this->table_name, $this->user_pw, $this->id);
			break;
			case "active":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE id = %d AND active = 'n'", $this->table_name, $this->id);
			break;
			case "validate":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE id = %d AND tmpMail <> ''", $this->table_name, $this->id);
			break;
			default:
			$password = (strlen($this->user_pw) < 32) ? md5($this->user_pw) : $this->user_pw;
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE BINARY login = '%s' AND pw = '%s' AND active = 'y'", $this->table_name, $this->user, $password);
		}
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_result($result, 0, "test") == 1) {
			return true;
		} else {
			return false;
		}
	}
	// New methods to handle the access level	
	function get_accessLevel() {
		$this->user = (!isset($this->user)) ? $_SESSION['user'] : $this->user;
		$sql = sprintf("SELECT accessLevel FROM %s WHERE login = '%s' AND active = 'y'", $this->table_name, $this->user);
		if (!$result = mysql_query($sql)) {
		   $this->the_msg = $this->messages(14);
		} else {
			$this->accessLevel = mysql_result($result, 0, "accessLevel");
		}
		return $this->accessLevel;
	}
	function set_user() {
		$_SESSION['user'] = $this->user;
#		$_SESSION['pw'] = $this->user_pw;
		$this->get_user_info();
		$_SESSION['user_id'] = $this->id;
		if (isset($_SESSION['referer']) && $_SESSION['referer'] != "") {
			$next_page = $_SESSION['referer'];
			unset($_SESSION['referer']);
		} else {
			$next_page = $this->main_page;
		}

		$this->setUserPermissions();

		return true;
	}

	function connect_db() {
		$conn_str = @mysql_connect( $this->mysql_host, $this->mysql_user, $this->mysql_pass );
		if ($conn_str == false) return(false);
		mysql_select_db( $this->mysql_name ); # if there are problems with the tablenames inside the config file use this row 

		$connection_string="server={$this->mysql_host}; database={$this->mysql_name}; username={$this->mysql_user}; password={$this->mysql_pass};";
		$this->db = new MysqliDatabase($connection_string);

		return( true );


	}

	function login_user($user, $password) {
		if ($user != "" && $password != "") {
			$this->user = $user;
			$this->user_pw = $password;
			if ($this->check_user()) {
				$this->login_saver();
				if ($this->count_visit) {
					$this->reg_visit($user, $password);
				}
				$this->set_user();
				return true;
			} else {
				$this->the_msg = $this->messages(10);
				return false;
			}
		} else {
			$this->the_msg = $this->messages(11);
			return false;
		}
	}
	function login_saver() {
		if ($this->save_login == "no") {
			if (isset($_COOKIE[$this->cookie_name])) {
				$expire = time()-3600;
			} else {
				return;
			}
		} else {
			$expire = time()+2592000;
		}		
		$cookie_str = $this->user.chr(31).base64_encode($this->user_pw);
		setcookie($this->cookie_name, $cookie_str, $expire, $this->cookie_path);
	}
	function login_reader() {
		if (isset($_COOKIE[$this->cookie_name])) {
			$cookie_parts = explode(chr(31), $_COOKIE[$this->cookie_name]);
			$this->user = $cookie_parts[0];
			$this->user_pw = base64_decode($cookie_parts[1]);
			$this->is_cookie = true;
		}			 
	}
	function reg_visit($login, $pass) {
		$visit_sql = sprintf("UPDATE %s SET extraInfo = '%s' WHERE login = '%s' AND pw = '%s'", $this->table_name, date("Y-m-d H:i:s"), $login, md5($pass));
		mysql_query($visit_sql);
	}
	function log_out() {
		unset($_SESSION['user']);
		# unset($_SESSION['pw']);
		unset($_SESSION['user_id']);
		unset($_SESSION['user_permissions']);
	}
	function access_page($refer = "", $qs = "", $level = DEFAULT_ACCESS_LEVEL) {
		$refer_qs = $refer;
		$refer_qs .= ($qs != "") ? "?".$qs : "";
		if (isset($_SESSION['user'])/* && isset($_SESSION['pw'])*/) {
			$this->user = $_SESSION['user'];
			# $this->user_pw = $_SESSION['pw'];
			$this->get_accessLevel();
			if (!$this->check_user()) {
				$_SESSION['referer'] = $refer_qs;
				return( false );
// 				header("Location: ".$this->login_page);
			}
			if ($this->accessLevel < $level) {
				return( false );
// 				header("Location: ".$this->deny_access_page);
			}
		} else { 
			$_SESSION['referer'] = $refer_qs;
				return( false );
// 			header("Location: ".$this->login_page);
		} 
		
		return( true );
	}
	function get_user_info() {
		$sql_info = sprintf("SELECT realName, extraInfo, email, userId FROM %s WHERE login = '%s' AND pw = '%s'", $this->table_name, $this->user, md5($this->user_pw));
		$res_info = mysql_query($sql_info);
		$this->id = mysql_result($res_info, 0, "userId");
		$this->user_full_name = mysql_result($res_info, 0, "realName");
		$this->user_info = mysql_result($res_info, 0, "extraInfo");
		$this->user_email = mysql_result($res_info, 0, "email");
	}
	function update_user($new_password, $new_confirm, $new_name, $new_info, $new_mail) {
		if ($new_password != "") {
			if ($this->check_new_password($new_password, $new_confirm)) {
				$ins_password = $new_password;
				$update_pw = true;
			} else {
				return;
			}
		} else {
			$ins_password = $this->user_pw;
			$update_pw = false;
		}
		if (trim($new_mail) <> $this->user_email) {
			if  ($this->check_email($new_mail)) {
				$this->user_email = $new_mail;
				if (!$this->check_user("lost")) {
					$update_email = true;
				} else {
					$this->the_msg = $this->messages(31);
					return;
				}
			} else {
				$this->the_msg = $this->messages(16);
				return;
			}
		} else {
			$update_email = false;
			$new_mail = "";
		}
		$upd_sql = sprintf("UPDATE %s SET pw = %s, realName = %s, extraInfo = %s, tmpMail = %s WHERE userId = %d", 
			$this->table_name,
			$this->ins_string(md5($ins_password)),
			$this->ins_string($new_name),
			$this->ins_string($new_info),
			$this->ins_string($new_mail),
			$this->id);
		$upd_res = mysql_query($upd_sql);
		if ($upd_res) {
			if ($update_pw) {
				# $_SESSION['pw'] = $this->user_pw = $ins_password;
				if (isset($_COOKIE[$this->cookie_name])) {
					$this->save_login = "yes";
					$this->login_saver();
				}
			}
			$this->the_msg = $this->messages(30);
			if ($update_email) {
				if ($this->send_mail($new_mail, 33)) {
					$this->the_msg = $this->messages(27);
				} else {
					mysql_query(sprintf("UPDATE %s SET tmpMail = ''", $this->table_name));
					$this->the_msg = $this->messages(14);
				} 
			}
		} else {
			$this->the_msg = $this->messages(15);
		}
	}
	function check_new_password($pass, $pw_conform) {
		if ($pass == $pw_conform) {
			if (strlen($pass) >= PW_LENGTH) {
				return true;
			} else {
				$this->the_msg = $this->messages(32);
				return false;
			}
		} else {
			$this->the_msg = $this->messages(38);
			return false;
		}	
	}
	function check_email($mail_address) {
		if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mail_address)) {
			return true;
		} else {
			return false;
		}
	}
	function ins_string($value, $type = "") {
		$value = (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
		switch ($type) {
			case "int":
			$value = ($value != "") ? intval($value) : NULL;
			break;
			default:
			$value = ($value != "") ? "'" . $value . "'" : "''";
		}
		return $value;
	}
	function register_user($first_login, $first_password, $confirm_password, $first_name, $first_info, $first_email) {
		if ($this->check_new_password($first_password, $confirm_password)) {
			if (strlen($first_login) >= LOGIN_LENGTH) {
				if ($this->check_email($first_email)) {
					$this->user_email = $first_email;
					$this->user = $first_login;
					if ($this->check_user("new")) {
						$this->the_msg = $this->messages(12);
					} else {
						$sql = sprintf("INSERT INTO %s (id, login, pw, realName, extraInfo, email, accessLevel, active) VALUES (NULL, %s, %s, %s, %s, %s, %d, 'n')", 
							$this->table_name,
							$this->ins_string($first_login),
							$this->ins_string(md5($first_password)),
							$this->ins_string($first_name),
							$this->ins_string($first_info),
							$this->ins_string($this->user_email),
							DEFAULT_ACCESS_LEVEL);
						$ins_res = mysql_query($sql);
						if ($ins_res) {
							$this->id = mysql_insert_id();
							$this->user_pw = $first_password;
							if ($this->send_mail($this->user_email)) {
								$this->the_msg = $this->messages(13);
							} else {
								mysql_query(sprintf("DELETE FROM %s WHERE userId = %s", $this->table_name, $this->id));
								$this->the_msg = $this->messages(14);
							}
						} else {
							$this->the_msg = $this->messages(15);
						}
					}
				} else {
					$this->the_msg = $this->messages(16);
				}
			} else {
				$this->the_msg = $this->messages(17);
			}
		}
	}
	function validate_email($validation_key, $key_id) {
		if ($validation_key != "" && strlen($validation_key) == 32 && $key_id > 0) {
			$this->id = $key_id;
			if ($this->check_user("validate")) {
				$upd_sql = sprintf("UPDATE %s SET email = tmpMail, tmpMail = '' WHERE userId = %d AND pw = '%s'", $this->table_name, $key_id, $validation_key);
				if (mysql_query($upd_sql)) {
					$this->the_msg = $this->messages(18);
				} else {
					$this->the_msg = $this->messages(19);
				}
			} else {
				$this->the_msg = $this->messages(34);
			}
		} else {
			$this->the_msg = $this->messages(21);
		}
	}
	function activate_account($activate_key, $key_id) {
		if ($activate_key != "" && strlen($activate_key) == 32 && $key_id > 0) {
			$this->id = $key_id;
			if ($this->check_user("active")) {
				if ($this->auto_activation) {
					$upd_sql = sprintf("UPDATE %s SET active = 'y' WHERE userId = %s AND pw = '%s'", $this->table_name, $key_id, $activate_key);
					if (mysql_query($upd_sql)) {
						if ($this->send_confirmation($key_id)) {
							$this->the_msg = $this->messages(18);
						} else {
							$this->the_msg = $this->messages(14);
						}
					} else {
						$this->the_msg = $this->messages(19);
					}
				} else {
					if ($this->send_mail($this->admin_mail, 0, true)) {
						$this->the_msg = $this->messages(36);
					} else {
						$this->the_msg = $this->messages(14);
					}
				}
			} else {
				$this->the_msg = $this->messages(20);
			}
		} else {
			$this->the_msg = $this->messages(21);
		}
	}
	function send_confirmation($id) {
		$sql = sprintf("SELECT email FROM %s WHERE userId = %d", $this->table_name, $id);
		$user_email = mysql_result(mysql_query($sql), 0, "email");
		if ($this->send_mail($user_email, 37)) {
			return true;
		} else {
			return false;
		}
	}
	function send_mail($mail_address, $num = 29) {
		$header = "From: \"".$this->webmaster_name."\" <".$this->webmaster_mail.">\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Mailer: Olaf's mail script version 1.11\r\n";
		$header .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n";
		if (!$this->auto_activation) {
			$subject = "New user request...";
			$body = "New user registration on ".date("Y-m-d").":\r\n\r\nClick here to enter the admin page:\r\n\r\n"."http://".$_SERVER['HTTP_HOST'].$this->admin_page."?login_id=".$this->id;
		} else {
			$subject = $this->messages(28);
			$body = $this->messages($num);
		}
		if (mail($mail_address, $subject, $body, $header)) {
			return true;
		} else {
			return false;
		} 
	}
	function forgot_password($forgot_email) { 
		if ($this->check_email($forgot_email)) {
			$this->user_email = $forgot_email;
			if (!$this->check_user("lost")) {
				$this->the_msg = $this->messages(22);
			} else {
				$forgot_sql = sprintf("SELECT userId, pw FROM %s WHERE email = '%s'", $this->table_name, $this->user_email);
				if ($forgot_result = mysql_query($forgot_sql)) {
					$this->id = mysql_result($forgot_result, 0, "userId");
					$this->user_pw = mysql_result($forgot_result, 0, "pw");
					if ($this->send_mail($this->user_email, 35)) {
						$this->the_msg = $this->messages(23);
					} else {
							$this->the_msg = $this->messages(14);
					}
				} else {
					$this->the_msg = $this->messages(15);
				}
			}
		} else {
			$this->the_msg = $this->messages(16);
		}
	}
	function check_activation_password($controle_str, $id) {
		if ($controle_str != "" && strlen($controle_str) == 32 && $id > 0) {
			$this->user_pw = $controle_str;
			$this->id = $id;
			if ($this->check_user("new_pass")) {
				// this is a fix for version 1.76
				$sql_get_user = sprintf("SELECT login FROM %s WHERE pw = '%s' AND userId = %d", $this->table_name, $this->user_pw, $this->id);
				$get_user = mysql_query($sql_get_user);
				$this->user = mysql_result($get_user, 0, "login"); // end fix
				return true;
			} else {
				$this->the_msg = $this->messages(21);
				return false;
			}
		} else {
			$this->the_msg = $this->messages(21);
			return false;
		}
	}
	function activate_new_password($new_pass, $new_confirm, $old_pass, $user_id) {
		if ($this->check_new_password($new_pass, $new_confirm)) {
			$sql_new_pass = sprintf("UPDATE %s SET pw = '%s' WHERE pw = '%s' AND userId = %d", $this->table_name, md5($new_pass), $old_pass, $user_id);
			if (mysql_query($sql_new_pass)) {
				$this->the_msg = $this->messages(30);
				return true;
			} else {
				$this->the_msg = $this->messages(14);
				return false;
			}
		} else {
			return false;
		}
	}
	function messages($num) {
		$host = "http://".$_SERVER['HTTP_HOST'];
		switch ($this->language) {
			case "de":           
			$msg[10] = "Login und/oder Passwort finden keinen Treffer in der Datenbank.";
			$msg[11] = "Login und/oder Passwort sind leer!";
			$msg[12] = "Leider existiert bereits ein Benutzer mit diesem Login und/oder E-mailadresse.";
			$msg[13] = "Weitere Anweisungen wurden per E-mail versandt, folgen Sie nun den Instruktionen.";
			$msg[14] = "Es is ein Fehler entstanden probieren Sie es erneut.";
			$msg[15] = "Es is ein Fehler entstanden probieren Sie es sp�ter nochmal.";
			$msg[16] = "Die eingegebene E-mailadresse ist nicht g�ltig.";
			$msg[17] = "Das Feld login (min. ".LOGIN_LENGTH." Zeichen) muss eingegeben sein.";
			$msg[18] = "Ihr Benutzerkonto ist aktiv. Sie k�nnen sich nun anmelden.";
			$msg[19] = "Ihr Aktivierungs ist nicht g�ltig.";
			$msg[20] = "Da ist kein Konto zu aktivieren.";
			$msg[21] = "Der benutzte Aktivierung-Code is nicht g�ltig!";
			$msg[22] = "Keine Konto gefunden dass mit der eingegeben E-mailadresse �bereinkommt.";
			$msg[23] = "Kontrollieren Sie Ihre E-Mail um Ihr neues Passwort zu erhalten.";
			$msg[25] = "Kann Ihr Passwort nicht aktivieren.";
			$msg[26] = "";
			$msg[27] = "Kontrollieren Sie Ihre E-Mailbox und best�tigen Sie Ihre �nderung(en).";
			$msg[28] = "Ihre Anfrage best�tigen...";
			$msg[29] = "Hallo,\r\n\r\num Ihre Anfrage zu aktivieren klicken Sie bitte auf den folgenden Link:\r\n".$host.$this->login_page."?ident=".$this->id."&activate=".md5($this->user_pw)."&language=".$this->language;
			$msg[30] = "Ihre �nderung ist durchgef�hrt.";
			$msg[31] = "Diese E-mailadresse wird bereits genutzt, bitte w�hlen Sie eine andere.";
			$msg[32] = "Das Feld Passwort (min. ".PW_LENGTH." Zeichen) muss eingegeben sein.";
			$msg[33] = "Hallo,\r\n\r\nIhre neue E-mailadresse muss noch �berpr�ft werden, bitte klicken Sie auf den folgenden Link:\r\n".$host.$this->login_page."?id=".$this->id."&validate=".md5($this->user_pw)."&language=".$this->language;
			$msg[34] = "Da ist keine E-mailadresse zu �berpr�fen.";
			$msg[35] = "Hallo,\r\n\r\nIhr neues Passwort kann nun eingegeben werden, bitte klicken Sie auf den folgenden Link:\r\n".$host.$this->password_page."?id=".$this->id."&activate=".$this->user_pw."&language=".$this->language;
			$msg[36] = "Ihr Antrag ist verarbeitet und wird nun durch den Administrator kontrolliert. \r\nSie erhalten eine Nachricht wenn dies geschehen ist.";
			$msg[37] = "Hallo ".$this->user.",\r\n\r\nIhr Konto ist nun eigerichtet und Sie k�nnen sich anmelden.\r\n\r\nKlicken Sie hierf�r auf den folgenden Link:\r\n".$host.$this->login_page."\r\n\r\nmit freundlichen Gr�ssen\r\n".$this->admin_name;
			$msg[38] = "Das best&auml;tigte Passwort hat keine &Uuml;bereinstimmung mit dem ersten Passwort, bitte probieren Sie es erneut.";
			break;
			case "nl":
			$msg[10] = "Gebruikersnaam en/of wachtwoord vinden geen overeenkomst in de database.";
			$msg[11] = "Gebruikersnaam en/of wachtwoord zijn leeg!";
			$msg[12] = "Helaas bestaat er al een gebruiker met deze gebruikersnaam en/of e-mail adres.";
			$msg[13] = "Er is een e-mail is aan u verzonden, volg de instructies die daarin vermeld staan.";
			$msg[14] = "Het is een fout ontstaan, probeer het opnieuw.";
			$msg[15] = "Het is een fout ontstaan, probeer het later nog een keer.";
			$msg[16] = "De opgegeven e-mail adres is niet geldig.";
			$msg[17] = "De gebruikersnaam (min. ".LOGIN_LENGTH." teken) moet opgegeven zijn.";
			$msg[18] = "Het gebruikersaccount is aangemaakt, u kunt u nu aanmelden.";
			$msg[19] = "Kan uw account niet activeren.";
			$msg[20] = "Er is geen account te activeren.";
			$msg[21] = "De gebruikte activeringscode is niet geldig!";
			$msg[22] = "Geen account gevonden dat met de opgegeven e-mail adres overeenkomt.";
			$msg[23] = "Er is een e-mail is aan u verzonden, daarin staat hoe uw een nieuw wachtwoord kunt aanmaken.";
			$msg[25] = "Kan het wachtwoord niet activeren.";
			$msg[26] = "";
			$msg[27] = "Er is een e-mail is aan u verzonden, volg de instructies die daarin vermeld staan.";
			$msg[28] = "Bevestig uw aanvraag ...";
			$msg[29] = "Bedankt voor uw aanvraag,\r\n\r\nklik op de volgende link om de aanvraag te verwerken:\r\n".$host.$this->login_page."?ident=".$this->id."&activate=".md5($this->user_pw)."&language=".$this->language;
			$msg[30] = "Uw wijzigingen zijn doorgevoerd.";
			$msg[31] = "Dit e-mailadres bestaat al, gebruik en andere.";
			$msg[32] = "Het veld wachtwoord (min. ".PW_LENGTH." teken) mag niet leeg zijn.";
			$msg[33] = "Beste gebruiker,\r\n\r\nde nieuwe e-mailadres moet nog gevalideerd worden, klik hiervoor op de volgende link:\r\n".$host.$this->login_page."?id=".$this->id."&validate=".md5($this->user_pw)."&language=".$this->language;
			$msg[34] = "Er is geen e-mailadres te valideren.";
			$msg[35] = "Hallo,\r\n\r\nuw nieuw wachtwoord kan nu ingevoerd worden, klik op deze link om verder te gaan:\r\n".$host.$this->password_page."?id=".$this->id."&activate=".$this->user_pw."&language=".$this->language;
			$msg[36] = "U aanvraag is verwerkt en wordt door de beheerder binnenkort activeert. \r\nU krijgt bericht wanneer dit gebeurt is.";
			$msg[37] = "Hallo ".$this->user.",\r\n\r\nHet account is nu gereed en u kunt zich aanmelden.\r\n\r\nKlik hiervoor op de volgende link:\r\n".$host.$this->login_page."\r\n\r\nmet vriendelijke groet\r\n".$this->admin_name;
			$msg[38] = "Het bevestigings wachtwoord komt niet overeen met het wachtwoord, probeer het opnieuw.";
			break;
			case "fr":
			$msg[10] = "Le login et/ou mot de passe ne correspondent pas.";
			$msg[11] = "Le login et/ou mot de passe est vide !";
			$msg[12] = "D�sol�, un utilisateur avec le m�me email et/ou login existe d�j�.";
			$msg[13] = "V�rifiez votre email et suivez les instructions.";
			$msg[14] = "D�sol�, une erreur s'est produite. Veuillez r�essayer.";
			$msg[15] = "D�sol�, une erreur s'est produite. Veuillez r�essayer plus tard.";
			$msg[16] = "L'adresse email n'est pas valide.";
			$msg[17] = "Le champ \"Nom d'usager\" doit �tre compos� d'au moins ".LOGIN_LENGTH." carat�res.";
			$msg[18] = "Votre requete est compl�te. Enregistrez vous pour continuer.";
			$msg[19] = "D�sol�, nous ne pouvons pas activer votre account.";
			$msg[20] = "D�sol�, il n'y � pas d'account � activer.";
			$msg[21] = "D�sol�, votre clef d'authorisation n'est pas valide";
			$msg[22] = "D�sol�, il n'y � pas d'account actif avec cette adresse email.";
			$msg[23] = "Veuillez consulter votre email pour recevoir votre nouveau mot de passe.";
			$msg[25] = "D�sol�, nous ne pouvons pas activer votre mot de passe.";
			$msg[26] = "";
			$msg[27] = "Veuillez consulter votre email pour activer les modifications.";
			$msg[28] = "Votre requete doit etre ex�cuter...";
			$msg[29] = "Bonjour,\r\n\r\npour activer votre account clickez sur le lien suivant:\r\n".$host.$this->login_page."?ident=".$this->id."&activate=".md5($this->user_pw)."&language=".$this->language;
			$msg[30] = "Votre account � �t� modifi�.";
			$msg[31] = "D�sol�, cette adresse email existe d�j�, veuillez en utiliser une autre.";
			$msg[32] = "Le champ password (min. ".PW_LENGTH." char) est requis.";
			$msg[33] = "Bonjour,\r\n\r\nvotre nouvelle adresse email doit �tre valid�e, clickez sur le liens suivant:\r\n".$host.$this->login_page."?id=".$this->id."&validate=".md5($this->user_pw)."&language=".$this->language;
			$msg[34] = "Il n'y � pas d'email � valider.";
			$msg[35] = "Bonjour,\r\n\r\nPour entrer votre nouveaux mot de passe, clickez sur le lien suivant:\r\n".$host.$this->password_page."?id=".$this->id."&activate=".$this->user_pw."&language=".$this->language;
			$msg[36] = "Votre demande a �t� bien trait�e et d'ici peu l'administrateur va l 'activer. Nous vous informerons quand ceci est arriv�.";
			$msg[37] = "Bonjour ".$this->user.",\r\n\r\nVotre compte est maintenant actif et il est possible d'y avoir acc�s.\r\n\r\nCliquez sur le lien suivant afin de rejoindre la page d'acc�s:\r\n".$host.$this->login_page."\r\n\r\nCordialement\r\n".$this->admin_name;
			$msg[38] = "Le mot de passe de confirmation de concorde pas avec votre mot de passe. Veuillez r�essayer";
			break;
			default:
			$msg[10] = "Login and/or password did not match to the database.";
			$msg[11] = "Login and/or password is empty!";
			$msg[12] = "Sorry, a user with this login and/or e-mail address already exist.";
			$msg[13] = "Please check your e-mail and follow the instructions.";
			$msg[14] = "Sorry, an error occurred please try it again.";
			$msg[15] = "Sorry, an error occurred please try it again later.";
			$msg[16] = "The e-mail address is not valid.";
			$msg[17] = "The field login (min. ".LOGIN_LENGTH." char.) is required.";
			$msg[18] = "Your request is processed. Login to continue.";
			$msg[19] = "Sorry, cannot activate your account.";
			$msg[20] = "There is no account to activate.";
			$msg[21] = "Sorry, this activation key is not valid!";
			$msg[22] = "Sorry, there is no active account which match with this e-mail address.";
			$msg[23] = "Please check your e-mail to get your new password.";
			$msg[25] = "Sorry, cannot activate your password.";
			$msg[26] = ""; // not used at the moment
			$msg[27] = "Please check your e-mail and activate your modifikation(s).";
			$msg[28] = "Your request must be processed...";
// 			$msg[29] = "Hello,\r\n\r\nto activate your request click the following link:\r\n".$host.$this->login_page."?ident=".$this->id."&activate=".md5($this->user_pw)."&language=".$this->language;
			$msg[29] = "Hello,\r\n\r\nto activate your request click the following link:\r\n".$host.$this->login_page."?task=activate&ident=".$this->id."&activate=".md5($this->user_pw)."&language=".$this->language;
			$msg[30] = "Your account is modified.";
			$msg[31] = "This e-mail address already exist, please use another one.";
			$msg[32] = "The field password (min. ".PW_LENGTH." char) is required.";
			$msg[33] = "Hello,\r\n\r\nthe new e-mail address must be validated, click the following link:\r\n".$host.$this->login_page."?id=".$this->id."&validate=".md5($this->user_pw)."&language=".$this->language;
			$msg[34] = "There is no e-mail address for validation.";
// 			$msg[35] = "Hello,\r\n\r\nEnter your new password next, please click the following link to enter the form:\r\n".$host.$this->password_page."?id=".$this->id."&activate=".$this->user_pw."&language=".$this->language;
			$msg[35] = "Hello,\r\n\r\nEnter your new password next, please click the following link to enter the form:\r\n".$host.$this->login_page."?task=activate_password&id=".$this->id."&activate=".$this->user_pw."&language=".$this->language;

			$msg[36] = "Your request is processed and is pending for validation by the admin. \r\nYou will get an e-mail if it's done.";
			$msg[37] = "Hello ".$this->user.",\r\n\r\nThe account is active and it's possible to login now.\r\n\r\nClick on this link to access the login page:\r\n".$host.$this->login_page."\r\n\r\nkind regards\r\n".$this->admin_name;
			$msg[38] = "The confirmation password does not match the password. Please try again.";
		}
		return $msg[$num];
	}

// 	function is_logged_in(){
// 		return (isset($_SESSION['user']) && isset($_SESSION['pw'])) ? true : false;
// 	}

	function is_logged_in($_tSESSION = ''){
		if($_tSESSION != '') {
			return (isset($_tSESSION['user'])) ? true : false;
		} else {
			return (isset($_SESSION['user'])) ? true : false;
		}
	}

	public function setUserPermissions() {
		$query = sprintf(" SELECT * FROM `userPermissions` WHERE `userId` = %s", mysql_escape_string($this->id));
		$ret = $this->db->query_all($query);
		$_SESSION['user_permissions'] = $ret;
		return true;
	}


}
?>