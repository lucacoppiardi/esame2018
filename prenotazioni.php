<?php

	/* prenotazioni.php
	 * 
	 * Questa pagina permette di:
	 * - registrarsi o eseguire il login per inserire o modificare le proprie prenotazioni,
	 * - recuperare la password se dimenticata,
	 * - gestire i dati personali del proprio account (mail, password, nome, telefono)
	 */

	include("interfaccia.php");
	head();
	topbar("prenotazioni");
	
	echo "<h1 class='titolo_pagina'>".Prenotazioni."</h1>";

	function form_registra() { /* stampa il form per registrarsi */
		echo "<form method='post' action='prenotazioni.php' onSubmit='return checkMail()'>";
		echo "<input type='hidden' name='stato' value='registrato'>"; /* passo allo stato in cui salvo i dati inseriti nel db */
		echo "<label for='nome'>".Nome.": </label>";
		echo "<input type='text' id='nome' name='nome' maxlength='250' placeholder='".Nome."' required>";
		echo "<label for='telefono'>".Telefono.": </label>";
		echo "<input type='tel' id='telefono' name='telefono' placeholder='".Telefono."' required>";
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' maxlength='250' required>";
		echo "<label for='conferma_mail'>".Conferma_mail.": </label>";
		echo "<input type='email' id='conferma_mail' name='conferma_mail' maxlength='250' onpaste='return false;' ondrop='return false;' placeholder='".Conferma_mail."' required>";
		echo "<label for='pass'>Password: </label>";
		echo "<input type='password' id='pass' name='pass' placeholder='Password' pattern='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}' required>";
		echo "<br style='clear:both'>";
		echo "<p>".requisiti_password."</p>";
		echo "<input type='submit' class='bottone'  value='".Registrati."'>";
		echo "</form>";
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Annulla."'>";
		echo "</form>";
	}
		
	function form_prenotazione() { /* stampa il form per inserire una prenotazione */
		echo "<button onClick='spoilerNuovaPrenotazione()' id='btn_nuova_prenotazione'>".Nuova_prenotazione."</button>";
		echo "<div id='nuova_prenotazione' style='display:none'>";
		echo "<h3>".Nuova_prenotazione."</h3>";
		echo "<p>".Ora_attuale.": <br/>
			<iframe src=\"http://free.timeanddate.com/clock/i6a63iop/n215/tlit6/fn15/fs20/tct/pct/ahl/tt0/tw1/tm1/tb1\" frameborder=\"0\" width=\"300\" height=\"30\" allowTransparency=\"true\"></iframe>
			</p>"; // includo un'orologio con l'ora esatta
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='inserisci'>"; /* passo allo stato dove salvo la prenotazione nel db */
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
	
	function form_accedi() { /* stampa il form per accedere */
		echo "<h2>".Accedi."</h2>";
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='hidden' name='stato' value='login'>"; /* passo allo stato per controllare i dati inseriti */
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' required>";
		echo "<label for='password'>Password: </label>";
		echo "<input type='password' id='password' name='password' placeholder='Password' required>";
		echo "<input type='submit' class='bottone'  value='".Accedi."'>";
		echo "</form>";
		echo "<form method='post' action='prenotazioni.php'>";		
		echo "<input type='submit' class='bottone'  value='".Annulla."'>";
		echo "<input type='hidden' name='stato' value=''>";
		echo "</form>";
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Recupero_password."'>";
		echo "<input type='hidden' name='stato' value='recupera_password'>"; /* passo allo stato col form per recuperare la password */
		echo "</form>";
	}
	
	function pagina() { /* cliccando "Prenotazioni" ci si può registrare o accedere */
		echo "<h3 class='avviso'>".intro_prenotazione."</h3>";
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Registrati."' >";
		echo "<input type='hidden' name='stato' value='registra'>"; /* passa al form di registazione */
		echo "</form>";
		echo "<form method='post' action='prenotazioni.php'>";
		echo "<input type='submit' class='bottone'  value='".Accedi."'>";
		echo "<input type='hidden' name='stato' value='accedi'>"; /* passa al form di login */
		echo "</form>";
	}
	
	function paginaLogged() { /* controllo se ho utenti loggati e mostro i loro dati */
		$mail = null;
		$pass = null;
		$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
		if (!empty($_REQUEST["mail"]) and !empty($_REQUEST["password"])) { // accesso dal form di login
			$mail = htmlentities($_REQUEST["mail"], ENT_QUOTES);
			$pass = md5($_REQUEST["password"].$salt);
		}
		if (empty($_REQUEST["mail"]) and empty($_REQUEST["password"]) and !empty($_SESSION["mail"]) and !empty($_SESSION["password"])) { // utente già loggato
			$mail = $_SESSION["mail"];
			$pass = $_SESSION["password"];
		}
		if (empty($mail) and empty($password)) { // utente non loggato
			pagina();
			tail();
			die();
		}
		$result = login($mail, $pass); // controllo esistenza utente
		if (!$result) { // dati errati/inesistenti
			echo "<h2 class='errore'>".Login_errato."</h2>";
			form_accedi();
		} else {
			/* salvo nella session i dati dell'utente loggato */
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
			echo "<form action='prenotazioni.php' method='post'>
					<input type='hidden' name='stato' value='logout'>
					<input type='submit' class='bottone'  value='Logout'>
				</form>"; // disconnessione
			
			echo "<form action='prenotazioni.php' method='post'>"; /* opzioni relative al proprio account */
				echo "<select name='stato'>";
					echo "<option>".Seleziona_altre_impostazioni.": </option>";
					echo "<option disabled></option>";
					echo "<option value='reset_password'>Reset password</option>";
					echo "<option value='disiscrizione'>".Cancella." account</option>";
					echo "<option value='cambia_email'>".cambio_mail."</option>";
					echo "<option value='cambia_dati_personali'>".cambia_dati_personali."</option>";
				echo "</select>";
				echo "<input class='bottoneAllineato' type='submit' value='OK'>";
			echo "</form>";
			
			crea_tab_prenotazioni(); // se non esiste già creo la tabella con le prenotazioni
			form_prenotazione(); // form per inserire nuova prenotazione
			echo "<h3>".Prenotazioni_inserite."</h3>";
			prenotazioni_utente(); // prenotazioni già inserite dell'utente
		}
	}
	
	switch(getStato()) {
		
		case "registra":
			crea_tab_utenti(); /* se non esiste già creo la tabella degli utenti */
			echo "<h4 class='avviso'>".registrazione_necessaria."</h4>";
			form_registra(); // form per la registrazione
			break;
			
		case "registrato": /* ricevo i dati dal form di registrazione e li salvo nel db */
			$nome = htmlentities($_REQUEST["nome"], ENT_QUOTES);
			$mail = htmlentities($_REQUEST["mail"], ENT_QUOTES);
			$conferma_mail = htmlentities($_REQUEST["conferma_mail"], ENT_QUOTES);
			$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
			$password = md5($_REQUEST["pass"].$salt);
			$telefono = htmlentities($_REQUEST["telefono"], ENT_QUOTES);
			echo "<h3 class='avviso'>".Conferma_iscrizione_cliccando_link."</h3>";
			crea_utente($mail,$password,$nome,$telefono); // iscrivo l'utente, riceverà una mail per controllare se l'indirizzo è valido ed attivare l'account
			echo "<form action='prenotazioni.php' method='post'>
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
			
		case "conferma_registrazione": /* l'utente ha confermato il suo account, ha un indirizzo mail valido */
			registrazione_confermata(htmlentities($_REQUEST["hash"], ENT_QUOTES));
			break;
			
		case "inserisci": /* ricevo i dati dal form della prenotazione e li salvo nel db */
			$data = htmlentities($_REQUEST["data"], ENT_QUOTES);
			$ora = htmlentities($_REQUEST["ora"], ENT_QUOTES);
			$nome = htmlentities($_REQUEST["nome"], ENT_QUOTES);
			$num_persone = htmlentities($_REQUEST["num_persone"], ENT_QUOTES);
			$richieste = htmlentities($_REQUEST["richieste"], ENT_QUOTES);
			inserisci_prenotazione($data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo($data, $ora, $nome, $num_persone, $richieste); /* il cliente riceve una mail con i dati della prenotazione che ha inserito */
			echo "<form action='prenotazioni.php' method='post'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			break;
			
		case "modifica_prenotazione": /* dato il codice della prenotazione da modificare, ne seleziono i dati e li mostro per la modifica */
			echo "<h3>".Modifica_prenotazione."</h3>";
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='post'>";
			echo "<input type='hidden' name='stato' value='update_prenotazione'>"; /* passo allo stato per l'aggiornamento dei dati sul db */
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
			echo "<form method='post' action='prenotazioni.php'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_prenotazione": /* dato il codice della prenotazione da cancellare, ne seleziono i dati e li mostro */
			echo "<h3>".Cancella_prenotazione."</h3>";
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_prenotazione($codice);
			echo "<form action='prenotazioni.php' method='post'>";
			echo "<input type='hidden' name='stato' value='delete_prenotazione'>"; /* passo allo stato per la cancellazione dei dati dal db */
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
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "delete_prenotazione": /* confermata cancellazione prenotazione */
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$data = htmlentities($_REQUEST["data"], ENT_QUOTES);
			$ora = htmlentities($_REQUEST["ora"], ENT_QUOTES);
			$nome = htmlentities($_REQUEST["nome"], ENT_QUOTES);
			$num_persone = htmlentities($_REQUEST["num_persone"], ENT_QUOTES);
			$richieste = htmlentities($_REQUEST["richieste"], ENT_QUOTES);
			delete_prenotazione($codice);
			mail_riepilogo_cancella($data, $ora, $nome, $num_persone, $richieste); // invio un promemoria al cliente coi dati della prenotazione che ha cancellato
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		case "update_prenotazione": /* confermata modifica prenotazione */
			$codice_prenotazione = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$data = htmlentities($_REQUEST["data"], ENT_QUOTES);
			$ora = htmlentities($_REQUEST["ora"], ENT_QUOTES);
			$nome = htmlentities($_REQUEST["nome"], ENT_QUOTES);
			$num_persone = htmlentities($_REQUEST["num_persone"], ENT_QUOTES);
			$richieste = htmlentities($_REQUEST["richieste"], ENT_QUOTES);
			update_prenotazione($codice_prenotazione, $data, $ora, $nome, $num_persone, $richieste);
			mail_riepilogo_modifica($data, $ora, $nome, $num_persone, $richieste); // invio un promemoria al cliente coi dati della prenotazione che ha modificato
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "reset_password": /* form per cambiare password */
			echo "<h3>Reset password</h3>";
			echo "<form action='prenotazioni.php' method='post' onSubmit='return checkPw()'>";
			echo "<input type='hidden' name='stato' value='reset_password_conferma'>"; // passo allo stato per il cambio password
			echo "<label for='old_pw'>".Vecchia_password.": </label>";
			echo "<input type='password' id='old_pw' name='old_pw' placeholder='".Vecchia_password."' required>";
			echo "<label for='new_pw'>".Nuova_password.": </label>";
			echo "<input type='password' name='new_pw' id='new_pw' placeholder='".Nuova_password."' required>";
			echo "<label for='new_pw_conferma'>".Nuova_password_conferma.": </label>";
			echo "<input type='password' name='new_pw_conferma' id='new_pw_conferma' placeholder='".Nuova_password_conferma."' required>";
			echo "<input type='submit' class='bottone'  value='Reset'>";
			echo "</form>";
			echo "<form method='post' action='prenotazioni.php'>";		
			echo "<input type='submit' class='bottone'  value='".Annulla."'>";
			echo "</form>";
			break;
			
		case "reset_password_conferma": /* conferma cambio password */
			$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
			$old_pw = md5($_REQUEST["old_pw"].$salt);
			$new_pw = md5($_REQUEST["new_pw"].$salt);
			if (!reset_password($old_pw, $new_pw)) { /* se la vecchia password non corrisponde a quella nel db, l'utente deve ripetere la procedura di cambio password */
				echo "<h2 class='errore'>".Ricontrollare_password."</h2>";
				echo "<form action='prenotazioni.php' method='post'>
						<input type='hidden' name='stato' value='reset_password'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			} else { /* se la password è stata cambiata, disconnetto l'utente */
				unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
				echo "<h3 class='avviso'>".Password_cambiata_correttamente."</h3>";
				echo "<form action='prenotazioni.php' method='post'>
						<input type='hidden' name='stato' value='accedi'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			}
			break;
			
		case "recupera_password": /* form per recuperare la password */
			echo "<h3>".Recupero_password."</h3>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='hidden' name='stato' value='recupera_pwd'>"; // passo allo stato per l'invio del link per il recupero
				echo "<label for='email'>Email: </label>
					<input type='email' id='email' name='email' placeholder='Mail' required>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			echo "<form method='post' action='prenotazioni.php'>";		
				echo "<input type='submit' class='bottone'  value='".Annulla."'>";
				echo "<input type='hidden' name='stato' value='accedi'>";
			echo "</form>";
			break;

		case "recupera_pwd": /* invia una mail al cliente con un link per resettare la password se dimenticata */
			echo "<h3>".Recupero_password."</h3>";
			$mail = htmlentities($_REQUEST["email"], ENT_QUOTES);
			mail_recupero_password($mail);
			echo "<h4 class='avviso'>".Clicca_link_per_recupero_password."</h4>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='hidden' name='stato' value=''>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "reset_password_link": /* form per cambiare password (dal link ricevuto via mail, se dimenticata) */
			echo "<h3>Reset password</h3>";
			echo "<form action='prenotazioni.php' method='post' onSubmit='return checkPw()'>";
			echo "<input type='hidden' name='stato' value='reset_password_conferma_link'>"; // passo allo stato per il cambio password sul db
			echo "<label for='new_pw'>".Nuova_password.": </label>";
			echo "<input type='password' name='new_pw' id='new_pw' placeholder='".Nuova_password."' required>";
			echo "<label for='new_pw_conferma'>".Nuova_password_conferma.": </label>";
			echo "<input type='password' name='new_pw_conferma' id='new_pw_conferma' placeholder='".Nuova_password_conferma."' required>";
			echo "<input type='hidden' name='mail' value='".$_REQUEST["mail"]."'>";
			echo "<input type='hidden' name='hash' value='".$_REQUEST["hash"]."'>";
			echo "<input type='submit' class='bottone'  value='Reset'>";
			echo "</form>";
			echo "<form method='post' action='prenotazioni.php'>";		
			echo "<input type='submit' class='bottone'  value='".Annulla."'>";
			echo "</form>";
			break;
			
		case "reset_password_conferma_link": /* conferma cambio password se dimenticata */
			$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
			$new_pw = md5($_REQUEST["new_pw"].$salt);
			$mail = htmlentities($_REQUEST["mail"], ENT_QUOTES);
			$hash = htmlentities($_REQUEST["hash"], ENT_QUOTES);
			if (!reset_password_link($mail, $hash, $new_pw)) { /* se la vecchia password non corrisponde a quella nel db, l'utente deve ripetere la procedura di cambio password */
				echo "<h2 class='errore'>".Ricontrollare_password."</h2>";
				echo "<form action='prenotazioni.php' method='post'>
						<input type='hidden' name='stato' value='reset_password'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			} else { /* se la password è stata cambiata, torno al form di login */
				echo "<h3 class='avviso'>".Password_cambiata_correttamente."</h3>";
				echo "<form action='prenotazioni.php' method='post'>
						<input type='hidden' name='stato' value='accedi'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
			}
			break;
			
		case "disiscrizione": /* chiede conferma prima di eliminare l'account */
			echo "<h3 class='attenzione'>".confermare_eliminazione_account."?</h3>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='hidden' name='stato' value='delete_account'>"; // passo allo stato che elimina l'account
				echo "<input type='submit' class='bottone'  value='".Conferma."'>
				</form>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;

		case "delete_account": /* cancella l'account e i dati relativi (prenotazioni inserite) */
			delete_account();
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "cambia_email": /* form per cambiare indirizzo mail */
			echo "<h3>".cambio_mail."</h3>";
			echo "<form action='prenotazioni.php' method='post'>
					<label for='newmail'>".Nuovo_indirizzo.": </label>
					<input type='hidden' name='stato' value='cambio_mail'>"; // passo allo stato che aggiorna l'email nel db
				echo "<input type='mail' id='newmail' name='newmail' placeholder='".Nuovo_indirizzo."' required>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
		
		case "cambio_mail": /* aggiorna l'indirizzo mail con il nuovo e disconnette l'utente, che dovrà confermare il nuovo indirizzo da un link che riceverà */
			update_indirizzo_mail(htmlentities($_REQUEST["newmail"], ENT_QUOTES));
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			break;
			
		case "conferma_nuova_mail": /* l'utente ha premuto il link che conferma il nuovo indirizzo mail */
			confermata_nuova_mail(htmlentities($_REQUEST["hash"], ENT_QUOTES));
			break;
						
		case "logout": /* disconnessione utente */
			unset($_SESSION["mail"], $_SESSION["password"], $_SESSION["cod_utente"]);
			pagina();
			break;
			
		case "cambia_dati_personali": /* form aggiornamento dati personali (nome, telefono, ...) */
			echo "<h3>".cambia_dati_personali."</h3>";
			$cod_utente = htmlentities($_SESSION["cod_utente"], ENT_QUOTES);
			$dati = select_dati_personali($cod_utente);
			echo "<form action='prenotazioni.php' method='post'>";
			echo "<input type='hidden' name='stato' value='update_dati_personali'>"; /* passo allo stato per l'aggiornamento dei dati sul db */
			echo "<label for='nome'>".Nome.": </label>";
			echo "<input type='text' id='nome' name='nome' value='$dati[0]' required>";
			echo "<label for='telefono'>".Telefono.": </label>";
			echo "<input type='tel' id='telefono' name='telefono' value='$dati[1]' required>";
			echo "<input type='submit' class='bottone'  value='".Aggiorna."'>";
			echo "</form>";
			echo "<form method='post' action='prenotazioni.php'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "update_dati_personali": /* aggiorna dati personali utente sul db */
			$nome = htmlentities($_REQUEST["nome"], ENT_QUOTES);
			$telefono = htmlentities($_REQUEST["telefono"], ENT_QUOTES);
			update_dati_personali($nome, $telefono);
			echo "<form method='post' action='prenotazioni.php'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		default: /* se nessun stato è specificato: se ho un utente loggato mostro la sua pagina, altrimenti mostro quella di registrazione/login */
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
