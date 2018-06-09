<?php
	include("interfaccia.php");
	head();
	topbar("prenotazioni");
	
	getStato();
	
	echo "<h1 class='titolo_pagina'>".Prenotazioni."</h1>";

	function form_registra() {
		echo "<form method='get' action='prenotazioni.php' onSubmit='return checkMail()'>";
		echo "<input type='hidden' name='stato' value='registrato'>";
		echo "<label for='nome'>".Nome.": </label>";
		echo "<input type='text' id='nome' name='nome' maxlength='250' required>";
		echo "<label for='telefono'>".Telefono.": </label>";
		echo "<input type='tel' id='telefono' name='telefono' required>"; /*pattern='[0-9]{10}'*/
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' maxlength='250' required>";
		echo "<label for='conferma_mail'>".Conferma_mail.": </label>";
		echo "<input type='email' id='conferma_mail' name='conferma_mail' maxlength='250' onpaste='return false;' ondrop='return false;' required>";
		echo "<label for='pass'>Password: </label>";
		echo "<input type='password' id='pass' name='pass' required>";
		echo "<input type='submit' value='".Registrati."'>";
		echo "</form>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='submit' value='".Annulla."'>";
		echo "</form>";
	}
		
	function form_prenotazione() {
		echo "<button onClick='spoilerNuovaPrenotazione()' id='btn_nuova_prenotazione'>".Nuova_prenotazione."</button>";
		echo "<div id='nuova_prenotazione' style='display:none'>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='inserisci'>";
		echo "<label for='data'>".Data.": </label>";
		echo "<input type='date' id='data' name='data' required>";
		echo "<label for='ora'>".Ora.": </label>";
		echo "<input type='time' id='ora' name='ora' required>";
		echo "<label for='nome'>".Nome_tavolo.": </label>";
		echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".placeholder."' required>";
		echo "<label for='num_persone'>".Numero_persone.": </label>";
		echo "<input type='number' id='num_persone' name='num_persone' min='1' required>";
		echo "<label for='richieste'>".Richieste_particolari."? </label>";
		echo "<textarea id='richieste' name='richieste' rows='6' cols='40' maxlength='250'></textarea>";
		echo "<input type='submit' value='".Inserisci."'>";
		echo "</form>";
		echo "</div>";
	}
	
	function form_accedi() {
		echo "<div style='width:100%; clear:both; float:left;'>";
		echo "<h2>".Accedi."</h2>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='login'>";
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' required>";
		echo "<label for='password'>Password: </label>";
		echo "<input type='password' id='password' name='password' required>";
		echo "<input type='submit' value='".Accedi."'>";
		echo "</form>";
		echo "<form method='get' action='prenotazioni.php'>";		
		echo "<input type='submit' value='".Annulla."'>";
		echo "<input type='hidden' name='stato' value=''>";
		echo "</form>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='submit' value='".Recupero_password."'>";
		echo "<input type='hidden' name='stato' value='recupera_password'>";
		echo "</form>";
		echo "</div>";
	}
	
	function pagina() {
		echo "<h2 class='intro_pagina'>".intro_prenotazione."</h2>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='submit' value='".Registrati."'>";
		echo "<input type='hidden' name='stato' value='registra'>";
		echo "</form>";
		echo "<form method='get' action='prenotazioni.php'>";
		echo "<input type='submit' value='".Accedi."'>";
		echo "<input type='hidden' name='stato' value='accedi'>";
		echo "</form>";
	}
	
	function paginaLogged() {
		$mail = null;
		$pass = null;
		if(!empty($_REQUEST["mail"]) and !empty($_REQUEST["password"])) {
			$mail = $_REQUEST["mail"];
			$pass = md5($_REQUEST["password"]);
		}
		if(empty($_REQUEST["mail"]) and empty($_REQUEST["password"]) and !empty($_SESSION["mail"]) and !empty($_SESSION["password"])) {
			$mail = $_SESSION["mail"];
			$pass = $_SESSION["password"];
		}
		if (empty($mail) and empty($password)) {
			pagina();
			tail();
			die();
		}
		$result = login($mail, $pass);
		if (!$result) {
			echo "<h2 class='errore'>".Login_errato."</h2>";
			form_accedi();
		} else {
			$_SESSION["mail"] = $result[1];
			$_SESSION["password"] = $result[3];
			$_SESSION["cod_utente"] = $result[0];
			echo "<h3 class='avviso'>".Login_riuscito.": ".$result[1]."</h3>";
			echo "<h4 class='avviso'>".Ultimo_accesso.": ".$result[2]."</h4>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='stato' value='logout'>
					<input type='submit' value='Logout'>
				</form>";
			
			echo "<form action='prenotazioni.php' method='get'>";
				echo "<select name='stato'>";
					echo "<option>Altre opzioni:</option>";
					echo "<option value='reset_password'>Reset password</option>";
					echo "<option value='disiscrizione'>".Cancella." account</option>";
					echo "<option value='cambia_email'>".cambio_mail."</option>";
				echo "</select>";
				echo "<input class='bottoneAllineato' type='submit' value='OK'>";
			echo "</form>";
			crea_tab_prenotazioni();
			echo "<p>".Ora_attuale.": <br/>";
			echo "<iframe src=\"https://freesecure.timeanddate.com/clock/i69cc1t8/n2177/tlit6/fn15/fs20/ahl/avb/tt0/th1/ta1/tb1\" frameborder=\"0\" width=\"372\" height=\"26\"></iframe></p>";
			echo "<h3>".Nuova_prenotazione."</h3>";
			form_prenotazione();
			echo "<h3>".Prenotazioni_inserite."</h3>";
			prenotazioni_utente();
		}
	}
	
	switch(getStato()) {
		
		case "registra":
			crea_tab_utenti();
			echo "<p>".registrazione_necessaria."</p>";
			form_registra();
			break;
			
		case "registrato":
			$nome = addslashes($_REQUEST["nome"]);
			$mail = $_REQUEST["mail"];
			$conferma_mail = $_REQUEST["conferma_mail"];
			$password = md5($_REQUEST["pass"]);
			$telefono = $_REQUEST["telefono"];
			if ($mail != $conferma_mail) {
				echo "<h2 class='errore'>".Mail_non_valida."</h2>";
				form_registra();
				break;
			}
			crea_utente($mail,$password,$nome,$telefono);
			echo "<h3 class='avviso'>".Registrazione_riuscita."</h3>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' value='OK'>
				</form>";
			break;
			
		case "accedi":
			form_accedi();
			break;
			
		case "login":
			paginaLogged();
			break;
			
		case "conferma_registrazione":
			registrazione_confermata($_REQUEST["hash"]);
			break;
			
		case "inserisci":
			$data = $_REQUEST["data"];
			$ora = $_REQUEST["ora"];
			$nome = addslashes($_REQUEST["nome"]);
			$num_persone = $_REQUEST["num_persone"];
			$richieste = addslashes($_REQUEST["richieste"]);
			inserisci_prenotazione($data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='get'>
						<input type='submit' value='OK'>
					</form>";
			break;
			
		case "modifica_prenotazione":
			echo "<h3>".Modifica_prenotazione."</h3>";
			$codice = $_REQUEST["codice"];
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='get'>";
			echo "<input type='hidden' name='stato' value='update_prenotazione'>";
			
			echo "<input type='hidden' name='codice' value='$codice'>";
			echo "<label for='data'>".Data.": </label>";
			echo "<input type='date' id='data' name='data' value='$dati[0]' required>";
			echo "<label for='ora'>".Ora.": </label>";
			echo "<input type='time' id='ora' name='ora' value='$dati[1]' required>";
			echo "<label for='nome'>".Nome_tavolo.": </label>";
			echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".placeholder."' value='$dati[2]' required>";
			echo "<label for='num_persone'>".Numero_persone.": </label>";
			echo "<input type='number' id='num_persone' name='num_persone' min='1' value='$dati[3]' required>";
			echo "<label for='richieste'>".Richieste_particolari."? </label>";
			echo "<textarea id='richieste' name='richieste' rows='6' cols='40' maxlength='250'>$dati[4]</textarea>";
			echo "<input type='submit' value='".Aggiorna."'>";
			echo "</form>";
			echo "<form method='get' action='prenotazioni.php'>
					<input type='submit' value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_prenotazione":
			echo "<h3>".Cancella_prenotazione."</h3>";
			$codice = $_REQUEST["codice"];
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='get'>";
			echo "<input type='hidden' name='stato' value='delete_prenotazione'>";
			echo "<input type='hidden' name='codice' value='$codice'>";
			echo "<label for='data'>".Data.": </label>";
			echo "<input type='date' id='data' name='data' value='$dati[0]' required readonly>";
			echo "<label for='ora'>".Ora.": </label>";
			echo "<input type='time' id='ora' name='ora' value='$dati[1]' required readonly>";
			echo "<label for='nome'>".Nome_tavolo.": </label>";
			echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".placeholder."' value='$dati[2]' required readonly>";
			echo "<label for='num_persone'>".Numero_persone.": </label>";
			echo "<input type='number' id='num_persone' name='num_persone' min='1' value='$dati[3]' required readonly>";
			echo "<label for='richieste'>".Richieste_particolari."? </label>";
			echo "<textarea id='richieste' name='richieste' rows='6' cols='40' maxlength='250' readonly>$dati[4]</textarea>";
			echo "<h4>".confermare."</h4>";
			echo "<input type='submit' value='".Cancella."'>";
			echo "</form>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='".Annulla."'>
				</form>";
			break;
			
		case "delete_prenotazione":
			$codice = $_REQUEST["codice"];
			$data = $_REQUEST["data"];
			$ora = $_REQUEST["ora"];
			$nome = addslashes($_REQUEST["nome"]);
			$num_persone = $_REQUEST["num_persone"];
			$richieste = addslashes($_REQUEST["richieste"]);
			delete_prenotazione($codice);
			mail_riepilogo_cancella($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='OK'>
				</form>";
			break;
		
		case "update_prenotazione":
			$codice_prenotazione = $_REQUEST["codice"];
			$data = $_REQUEST["data"];
			$ora = $_REQUEST["ora"];
			$nome = addslashes($_REQUEST["nome"]);
			$num_persone = $_REQUEST["num_persone"];
			$richieste = addslashes($_REQUEST["richieste"]);
			update_prenotazione($codice_prenotazione, $data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo_modifica($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='OK'>
				</form>";
			break;
			
		case "reset_password":
			echo "<h3>Reset password</h3>";
			echo "<form action='prenotazioni.php' method='get' onSubmit='return checkPw()'>";
			echo "<input type='hidden' name='stato' value='reset_password_conferma'>";
			echo "<label for='old_pw'>".Vecchia_password.": </label>";
			echo "<input type='password' id='old_pw' name='old_pw' required>";
			echo "<label for='new_pw'>".Nuova_password.": </label>";
			echo "<input type='password' name='new_pw' id='new_pw' required>";
			echo "<label for='new_pw_conferma'>".Nuova_password_conferma.": </label>";
			echo "<input type='password' name='new_pw_conferma' id='new_pw_conferma' required>";
			echo "<input type='submit' value='Reset'>";
			echo "</form>";
			echo "<form method='get' action='prenotazioni.php'>";		
			echo "<input type='submit' value='".Annulla."'>";
			echo "</form>";
			break;
			
		case "reset_password_conferma":
			$old_pw = md5($_REQUEST["old_pw"]);
			$new_pw = md5($_REQUEST["new_pw"]);
			$new_pw_conferma = md5($_REQUEST["new_pw_conferma"]);
			if (!reset_password($old_pw, $new_pw)) {
				echo "<h2 class='errore'>".Ricontrollare_password."</h2>";
				echo "<form action='prenotazioni.php' method='get'>
						<input type='hidden' name='stato' value='reset_password'>
						<input type='submit' value='OK'>
					</form>";
			} else {
				$_SESSION["password"] = md5($_REQUEST["new_pw"]);
				echo "<h3 class='avviso'>".Password_cambiata_correttamente."</h3>";
				echo "<form action='prenotazioni.php' method='get'>
						<input type='hidden' name='stato' value='accedi'>
						<input type='submit' value='OK'>
					</form>";
				unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			}
			break;
			
		case "recupera_password":
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='stato' value='recupera_pwd'>
					Email: <input type='email' name='email' required>
					<input type='submit' value='OK'>
				</form>";
			break;

		case "recupera_pwd":
			mail_recupero_password($_REQUEST["email"]);
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' value='OK'>
				</form>";
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "disiscrizione":
			echo "<h3>".confermare_eliminazione_account."</h3>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='stato' value='delete_account'>
					<input type='submit' value='".Conferma."'>
				</form>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='".Annulla."'>
				</form>";
			break;

		case "delete_account":
			delete_account();
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='OK'>
				</form>";
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "cambia_email":
			echo "<h2>".cambio_mail."</h2>";
			echo "<form action='prenotazioni.php' method='get'>
					".Nuovo_indirizzo.": 
					<input type='hidden' name='stato' value='cambio_mail'>
					<input type='mail' name='newmail' required>
					<input type='submit' value='OK'>
				</form>";
			echo "<form action='prenotazioni.php' method='get'>
					<input type='submit' value='".Annulla."'>
				</form>";
			break;
		
		case "cambio_mail":
			update_indirizzo_mail(addslashes($_REQUEST["newmail"]));
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "conferma_nuova_mail":
			confermata_nuova_mail($_REQUEST["hash"]);
			break;
						
		case "logout":
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			pagina();
			break;
		
		default:
			if (!empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($_SESSION["cod_utente"])) {
				paginaLogged();
			} else {
				crea_tab_utenti();
				pagina();
			}
			break;
	}
	
	tail();
?>
