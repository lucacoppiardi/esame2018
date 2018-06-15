<?php
	include("interfaccia.php");
	head();
	topbar("prenotazioni");
	
	getStato();
	
	echo "<h1 class='titolo_pagina'>".Prenotazioni."</h1>";

	function form_registra() {
		echo "<form method='POST' action='prenotazioni.php' onSubmit='return checkMail()'>";
		echo "<input type='hidden' name='stato' value='registrato'>";
		echo "<label for='nome'>".Nome.": </label>";
		echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".Nome."' required>";
		echo "<label for='telefono'>".Telefono.": </label>";
		echo "<input type='tel' id='telefono' name='telefono' placeholder='".Telefono."' required>"; /*pattern='[0-9]{10}'*/
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' maxlength='250' required>";
		echo "<label for='conferma_mail'>".Conferma_mail.": </label>";
		echo "<input type='email' id='conferma_mail' name='conferma_mail' maxlength='250' onpaste='return false;' ondrop='return false;' placeholder='".Conferma_mail."' required>";
		echo "<label for='pass'>Password: </label>";
		echo "<input type='password' id='pass' name='pass' placeholder='Password' required>";
		echo "<input type='submit' class='bottone'  value='".Registrati."'>";
		echo "</form>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Annulla."'>";
		echo "</form>";
	}
		
	function form_prenotazione() {
		echo "<button onClick='spoilerNuovaPrenotazione()' id='btn_nuova_prenotazione'>".Nuova_prenotazione."</button>";
		echo "<div id='nuova_prenotazione' style='display:none'>";
		echo "<h3>".Nuova_prenotazione."</h3>";
		echo "<p>".Ora_attuale.": <br/>
			<iframe src=\"http://free.timeanddate.com/clock/i6a63iop/n215/tlit6/fn15/fs20/tct/pct/ahl/tt0/tw1/tm1/tb1\" frameborder=\"0\" width=\"300\" height=\"30\" allowTransparency=\"true\"></iframe>
			</p>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='inserisci'>";
		echo "<label for='data'>".Data.": </label>";
		echo "<input type='date' id='data' name='data' placeholder='".Data."' required>";
		echo "<label for='ora'>".Ora.": </label>";
		echo "<input type='time' id='ora' name='ora' placeholder='".Ora."' required>";
		echo "<label for='nome'>".Nome_tavolo.": </label>";
		echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".placeholder."' required>";
		echo "<label for='num_persone'>".Numero_persone.": </label>";
		echo "<input type='number' id='num_persone' name='num_persone' min='1' placeholder='".Numero_persone."' required>";
		echo "<label for='richieste'>".Richieste_particolari."? </label>";
		echo "<textarea id='richieste' name='richieste' rows='6' cols='40' maxlength='250' placeholder='".Richieste_particolari."'></textarea>";
		echo "<input type='submit' class='bottone'  value='".Inserisci."'>";
		echo "</form>";
		echo "</div>";
	}
	
	function form_accedi() {
		echo "<h2>".Accedi."</h2>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='login'>";
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' required>";
		echo "<label for='password'>Password: </label>";
		echo "<input type='password' id='password' name='password' placeholder='Password' required>";
		echo "<input type='submit' class='bottone'  value='".Accedi."'>";
		echo "</form>";
		echo "<form method='POST' action='prenotazioni.php'>";		
		echo "<input type='submit' class='bottone'  value='".Annulla."'>";
		echo "<input type='hidden' name='stato' value=''>";
		echo "</form>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Recupero_password."'>";
		echo "<input type='hidden' name='stato' value='recupera_password'>";
		echo "</form>";
	}
	
	function pagina() {
		echo "<h3 class='avviso'>".intro_prenotazione."</h3>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Registrati."' >";
		echo "<input type='hidden' name='stato' value='registra'>";
		echo "</form>";
		echo "<form method='POST' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Accedi."'>";
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
			echo "<h4 class='avviso'>".Ultimo_accesso.": ";
			if (!empty($result[2])) {
				echo $result[2];
			} else {
				echo Primo_accesso;
			}
			echo "</h4>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='logout'>
					<input type='submit' class='bottone'  value='Logout'>
				</form>";
			
			echo "<form action='prenotazioni.php' method='POST'>";
				echo "<select name='stato'>";
					echo "<option>".Seleziona_altre_impostazioni.": </option>";
					echo "<option disabled></option>";
					echo "<option value='reset_password'>Reset password</option>";
					echo "<option value='disiscrizione'>".Cancella." account</option>";
					echo "<option value='cambia_email'>".cambio_mail."</option>";
				echo "</select>";
				echo "<input class='bottoneAllineato' type='submit' value='OK'>";
			echo "</form>";
			crea_tab_prenotazioni();
			form_prenotazione();
			echo "<h3>".Prenotazioni_inserite."</h3>";
			prenotazioni_utente();
		}
	}
	
	switch(getStato()) {
		
		case "registra":
			crea_tab_utenti();
			echo "<h4 class='avviso'>".registrazione_necessaria."</h4>";
			form_registra();
			break;
			
		case "registrato":
			$nome = addslashes(htmlentities($_REQUEST["nome"]));
			$mail = addslashes(htmlentities($_REQUEST["mail"]));
			$conferma_mail = addslashes(htmlentities($_REQUEST["conferma_mail"]));
			$password = md5($_REQUEST["pass"]);
			$telefono = addslashes(htmlentities($_REQUEST["telefono"]));
			if ($mail != $conferma_mail) {
				echo "<h2 class='errore'>".Mail_non_valida."</h2>";
				form_registra();
				break;
			}
			crea_utente($mail,$password,$nome,$telefono);
			echo "<h3 class='avviso'>".Conferma_iscrizione_cliccando_link."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
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
			$data = addslashes(htmlentities($_REQUEST["data"]));
			$ora = addslashes(htmlentities($_REQUEST["ora"]));
			$nome = addslashes(htmlentities($_REQUEST["nome"]));
			$num_persone = addslashes(htmlentities($_REQUEST["num_persone"]));
			$richieste = addslashes(htmlentities($_REQUEST["richieste"]));
			inserisci_prenotazione($data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='POST'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			break;
			
		case "modifica_prenotazione":
			echo "<h3>".Modifica_prenotazione."</h3>";
			$codice = $_REQUEST["codice"];
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='POST'>";
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
			echo "<input type='submit' class='bottone'  value='".Aggiorna."'>";
			echo "</form>";
			echo "<form method='POST' action='prenotazioni.php'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_prenotazione":
			echo "<h3>".Cancella_prenotazione."</h3>";
			$codice = $_REQUEST["codice"];
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='POST'>";
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
			echo "<input type='submit' class='bottone'  value='".Cancella."'>";
			echo "</form>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "delete_prenotazione":
			$codice = $_REQUEST["codice"];
			$data = addslashes(htmlentities($_REQUEST["data"]));
			$ora = addslashes(htmlentities($_REQUEST["ora"]));
			$nome = addslashes(htmlentities($_REQUEST["nome"]));
			$num_persone = addslashes(htmlentities($_REQUEST["num_persone"]));
			$richieste = addslashes(htmlentities($_REQUEST["richieste"]));
			delete_prenotazione($codice);
			mail_riepilogo_cancella($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		case "update_prenotazione":
			$codice_prenotazione = $_REQUEST["codice"];
			$data = addslashes(htmlentities($_REQUEST["data"]));
			$ora = addslashes(htmlentities($_REQUEST["ora"]));
			$nome = addslashes(htmlentities($_REQUEST["nome"]));
			$num_persone = addslashes(htmlentities($_REQUEST["num_persone"]));
			$richieste = addslashes(htmlentities($_REQUEST["richieste"]));
			update_prenotazione($codice_prenotazione, $data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo_modifica($data, $ora, $nome, $num_persone, $richieste);
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "reset_password":
			echo "<h3>Reset password</h3>";
			echo "<form action='prenotazioni.php' method='POST' onSubmit='return checkPw()'>";
			echo "<input type='hidden' name='stato' value='reset_password_conferma'>";
			echo "<label for='old_pw'>".Vecchia_password.": </label>";
			echo "<input type='password' id='old_pw' name='old_pw' placeholder='".Vecchia_password."' required>";
			echo "<label for='new_pw'>".Nuova_password.": </label>";
			echo "<input type='password' name='new_pw' id='new_pw' placeholder='".Nuova_password."' required>";
			echo "<label for='new_pw_conferma'>".Nuova_password_conferma.": </label>";
			echo "<input type='password' name='new_pw_conferma' id='new_pw_conferma' placeholder='".Nuova_password_conferma."' required>";
			echo "<input type='submit' class='bottone'  value='Reset'>";
			echo "</form>";
			echo "<form method='POST' action='prenotazioni.php'>";		
			echo "<input type='submit' class='bottone'  value='".Annulla."'>";
			echo "</form>";
			break;
			
		case "reset_password_conferma":
			$old_pw = md5($_REQUEST["old_pw"]);
			$new_pw = md5($_REQUEST["new_pw"]);
			$new_pw_conferma = md5($_REQUEST["new_pw_conferma"]);
			if (!reset_password($old_pw, $new_pw)) {
				echo "<h2 class='errore'>".Ricontrollare_password."</h2>";
				echo "<form action='prenotazioni.php' method='POST'>
						<input type='hidden' name='stato' value='reset_password'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			} else {
				$_SESSION["password"] = md5($_REQUEST["new_pw"]);
				echo "<h3 class='avviso'>".Password_cambiata_correttamente."</h3>";
				echo "<form action='prenotazioni.php' method='POST'>
						<input type='hidden' name='stato' value='accedi'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
				unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			}
			break;
			
		case "recupera_password":
			echo "<h3>".Recupero_password."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='recupera_pwd'>
					<label for='email'>Email: </label>
					<input type='email' id='email' name='email' placeholder='Mail' required>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			echo "<form method='POST' action='prenotazioni.php'>";		
				echo "<input type='submit' class='bottone'  value='".Annulla."'>";
				echo "<input type='hidden' name='stato' value=''>";
			echo "</form>";
			break;

		case "recupera_pwd":
			mail_recupero_password($_REQUEST["email"]);
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "disiscrizione":
			echo "<h3 class='attenzione'>".confermare_eliminazione_account."?</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='delete_account'>
					<input type='submit' class='bottone'  value='".Conferma."'>
				</form>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;

		case "delete_account":
			delete_account();
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "cambia_email":
			echo "<h3>".cambio_mail."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<label for='newmail'>".Nuovo_indirizzo.": </label>
					<input type='hidden' name='stato' value='cambio_mail'>
					<input type='mail' id='newmail' name='newmail' placeholder='".Nuovo_indirizzo."' required>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
		
		case "cambio_mail":
			update_indirizzo_mail($_REQUEST["newmail"]);
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
