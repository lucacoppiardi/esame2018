<?php
	include("interfaccia.php");
	
	if (getStato() == "gestione_prenotazioni") {
		echo "<meta http-equiv='refresh' content='60'>";
	}
	
	head();

	topbar("admin");
	
	function pagina() {
		echo "<form method='post' action='admin.php'>";		
		echo "<input type='hidden' name='stato' value='login'>";
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' required>";
		echo "<label for='password'>Password: </label>";
		echo "<input type='password' id='password' name='password' placeholder='Password' required>";
		echo "<input type='submit' class='bottone'  value='".Accedi."' >";
		echo "</form>";
	}
	
	echo "<h1 class='titolo_pagina'>".Amministrazione."</h1>";
		
	if(isset($_POST['action']) and $_POST['action'] == 'upload') {
		define("UPLOAD_DIR", "./uploads/");
		
		$checkUpload = false;
		
		$newFileName = "";
		$checkImg = 0;
		$temp = "";
		
		if(isset($_FILES['file'])) {
			$file = $_FILES['file'];
			if (!empty($file["name"])) {
				$checkImg = getimagesize($file["tmp_name"]);
				$temp = explode(".",$file["name"]);
				$newFileName = UPLOAD_DIR.md5($file["name"]).".".end($temp);
			}
			if($file['error'] == UPLOAD_ERR_OK and is_uploaded_file($file['tmp_name']))	{
				if($checkImg == true) {
					if (isDebug()) echo "File is an image - " . $checkImg["mime"] . "<br/>";
				} else if($checkImg == false) {
					echo non_immagine."<br/>";
					$checkUpload = true;
				}
				if (file_exists($newFileName)) {
					echo file_gia_esistente."<br/>";
					$checkUpload = true;
				}
				if ($_FILES['file']['size'] > 5000000) {
					echo file_troppo_grande."<br/>";
					$checkUpload = true;
				}
				if ($checkUpload==false) {
					if (move_uploaded_file($file['tmp_name'], $newFileName)) {
						echo "File ".caricato."<br/>";
					} else {
						echo "File ".non_caricato."<br/>";
					}
				} else {
					echo "File ".non_caricato."<br/>";
				}
			} else {
				if (isDebug()) echo $file["error"];
			}
		} else if (isDebug()) {
			echo "!isset";
		}
	} /*else if (isDebug()) {
		echo "!post";
	}*/
	
	function paginaLogged() {
		$mail = null;
		$pass = null;
		if(!empty($_REQUEST["mail"]) and !empty($_REQUEST["password"])) {
			$mail = htmlentities($_REQUEST["mail"], ENT_QUOTES);
			$pass = md5($_REQUEST["password"]);
		}
		if(empty($_REQUEST["mail"]) and empty($_REQUEST["password"]) and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"])) {
			$mail = $_SESSION["mail_admin"];
			$pass = $_SESSION["password_admin"];
		}
		if (empty($mail) and empty($password)) {
			pagina();
			tail();
			die();
		}
		$login = admin_login($mail, $pass);
		if (!$login) {
			echo "<h2 class='errore'>".Login_errato."</h2>";
			pagina();
		} else {
			$_SESSION["mail_admin"] = $login[1];
			$_SESSION["password_admin"] = $login[3];
			$_SESSION["cod_admin"] = $login[0];
			echo "<h3 class='avviso'>".Login_riuscito.": ".$login[1]."</h3>";
			echo "<h4 class='avviso'>".Ultimo_accesso.": ";
			if (!empty($login[2])) {
				echo $login[2];
			} else {
				echo Primo_accesso;
			}
			echo "</h4>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='".Prenotazioni."' >
				</form>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='News' >
				</form>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='".Utenti."' >
				</form>";
			echo "<form action='admin.php' method='post'>
				<input type='hidden' name='stato' value='gestione_piatti'>
				<input type='submit' class='bottone'  value='".Piatti."' >
			</form>";
			echo "<form method='post' action='admin.php' style='margin-top:40px'>
					<input type='hidden' name='stato' value='logout' >
					<input type='submit' class='bottone'  value='Logout' >
				</form>";
		}
	}

	switch(getStato()) {
		
		case "login":
			paginaLogged();
			break;
			
		case "gestione_prenotazioni":
			echo "<h2>".Prenotazioni_online."</h2>";
			crea_tab_utenti();
			crea_tab_prenotazioni();
			visualizza_prenotazioni();
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."'>
				</form>";
			break;
			
		case "gestione_news":
			echo "<h2>News</h2>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='inserisci_news'>
					<input type='submit' class='bottone'  value='".inserisci_news."'>
				</form>";
			crea_tab_news();
			visualizza_news();
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."'>
				</form>";
			break;
			
		case "gestione_piatti":
			echo "<h2>".Piatti."</h2>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='inserisci_piatto'>
					<input type='submit' class='bottone'  value='".inserisci_piatto."'>
				</form>";
			crea_tab_piatti();
			visualizza_piatti();
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."'>
				</form>";
			break;
			
		case "gestione_utenti":
			echo "<h2>".Utenti."</h2>";
			crea_tab_utenti();
			visualizza_utenti();
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."'>
				</form>";
			break;
			
		case "cancella_utente":
			echo "<form action='admin.php' method='post'>
					<h4 class='attenzione'>".confermare_eliminazione_account.":</h4>"
					."<label for='nome'>".Nome.":</label> <input type='text' id='nome' readonly value='".$_REQUEST["nome"]."'>
					<label for='mail'>Mail: </label><input type='text' id='mail' readonly value='".$_REQUEST["mail"]."'>
					<input type='hidden' name='stato' value='delete_account_admin'>
					<input type='hidden' name='codice' value='".htmlentities($_REQUEST["codice"], ENT_QUOTES)."'>
					<input type='submit' class='bottone'  value='OK' >
				</form>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='".Annulla."' >
				</form>";
			break;
			
		case "delete_account_admin":
			delete_account_admin(htmlentities(htmlentities($_REQUEST["codice"], ENT_QUOTES), ENT_QUOTES));
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "conferma_scelta":
			$codice_prenotazione = htmlentities(htmlentities($_REQUEST["codice"], ENT_QUOTES), ENT_QUOTES);
			$dati = select_prenotazione_admin($codice_prenotazione);
			echo "<form action='admin.php' method='post'>";
				if (isset($_REQUEST["accetta"]) and $_REQUEST["accetta"]=="Accetta") {
					echo "<input type='hidden' name='stato' value='accetta_prenotazione'>";
				} else if (isset($_REQUEST["rifiuta"]) and $_REQUEST["rifiuta"]=="Rifiuta") {
					echo "<input type='hidden' name='stato' value='rifiuta_prenotazione'>";
				};
				if (isset($_REQUEST["accetta"]) and $_REQUEST["accetta"]=="Accetta") {
					echo "<h4 class='attenzione'>".Conferma." \"".Accetta."\"?</h4>";
				} else if (isset($_REQUEST["rifiuta"]) and $_REQUEST["rifiuta"]=="Rifiuta") {
					echo "<h4 class='attenzione'>".Conferma." \"".Rifiuta."\"?</h4>";
				};
				echo "<label for='msg'>".msg_per_cliente.": </label>";
				echo "<textarea id='msg' name='msg' rows='6' cols='40' maxlength='250'></textarea>";
				echo "<h4 style='clear: both'>".Prenotazione.":</h4>";
				echo "<input type='hidden' name='codice' value='$codice_prenotazione'>";
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
				echo "<input type='submit' class='bottone'  name='conferma' value='OK'>
				</form>";
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  name='conferma' value='NO'>
				</form>";
			break;
			
		case "accetta_prenotazione":
			$codice_prenotazione = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$msg = htmlentities($_REQUEST["msg"], ENT_QUOTES);
			conferma_prenotazione($codice_prenotazione, 1, $msg);
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "rifiuta_prenotazione":
			$codice_prenotazione = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$msg = htmlentities($_REQUEST["msg"], ENT_QUOTES);
			conferma_prenotazione($codice_prenotazione, 2, $msg);
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "inserisci_news":
			echo "<h3>".inserisci_news."</h3>";
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='stato' value='insert_news'>
					<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' placeholder='".Titolo."' maxlength='250' required>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' placeholder='".Titolo." (english)' maxlength='250' required>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' placeholder='".Testo."' maxlength='250' required></textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' placeholder='".Testo." (english)' maxlength='250' required></textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file'>
					<input type='submit' class='bottone'  value='".Inserisci."'>
					<input type='hidden' name='action' value='upload'>
				</form>";
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
		
		case "modifica_news":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_news($codice);
			echo "<h3>".Aggiorna." news</h3>";
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='stato' value='update_news'>
					<input type='hidden' name='codice' value='$codice'>
					<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' maxlength='250' value='$dati[0]' required>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' maxlength='250' value='$dati[6]' required>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' maxlength='250' required>$dati[1]</textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' maxlength='250' required>$dati[5]</textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file'>
					<input type='submit' class='bottone'  value='".Aggiorna."'>
					<input type='hidden' name='action' value='upload'>
				</form>";	
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_news":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_news($codice);
			echo "<h3>".Cancella." news</h3>";
			echo "<form action='admin.php' method='post'>";
				echo "<input type='hidden' name='stato' value='delete_news'>";
				echo "<input type='hidden' name='codice' value='$codice'>";
				echo "<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' maxlength='250' value='$dati[0]' required readonly>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' maxlength='250' value='$dati[6]' required readonly>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' maxlength='250' required readonly>$dati[1]</textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' maxlength='250' required readonly>$dati[5]</textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file' disabled>";
				echo "<input type='submit' class='bottone'  value='".Cancella."'>";
			echo "</form>";
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "delete_news":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			delete_news($codice);
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "insert_news":
			$titolo = htmlentities($_REQUEST["titolo"], ENT_QUOTES);
			$titolo_en = htmlentities($_REQUEST["titolo_en"], ENT_QUOTES);
			$contenuto = htmlentities($_REQUEST["contenuto"], ENT_QUOTES);
			$contenuto_en = htmlentities($_REQUEST["contenuto_en"], ENT_QUOTES);
					
			insert_news($titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName);
			
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		case "update_news":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$titolo = htmlentities($_REQUEST["titolo"], ENT_QUOTES);
			$titolo_en = htmlentities($_REQUEST["titolo_en"], ENT_QUOTES);
			$contenuto = htmlentities($_REQUEST["contenuto"], ENT_QUOTES);
			$contenuto_en = htmlentities($_REQUEST["contenuto_en"], ENT_QUOTES);
					
			update_news($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName);
			
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "inserisci_piatto":
			echo "<h3>".inserisci_piatto."</h3>";
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='stato' value='insert_piatto'>
					<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' placeholder='".Titolo."' maxlength='250' required>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' placeholder='".Titolo." (english)' maxlength='250' required>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' placeholder='".Testo."' maxlength='250' required></textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' placeholder='".Testo." (english)' maxlength='250' required></textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file'>
					<label for='titolo'>".Titolo.": </label>
					<input type='number' id='prezzo' name='prezzo' placeholder='".Prezzo."' step='0.01' required>
					<label for='tipo'>".Tipo."</label>
					<select id='tipo' name='tipo'>
						<option value='1'>".Antipasti."</option>
						<option value='2'>".PrimoPiatto."</option>
						<option value='3'>".SecondoPiatto."</option>
						<option value='4'>".Contorno."</option>
						<option value='5'>".Dolce."</option>
						<option value='6'>".Altro."</option>
					</select>
					<input type='submit' class='bottone'  value='".Inserisci."'>
					<input type='hidden' name='action' value='upload'>
				</form>";
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_piatti'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
		
		case "modifica_piatto":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_piatto($codice);
			echo "<h3>".modifica_piatto."</h3>";
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='stato' value='update_piatto'>
					<input type='hidden' name='codice' value='$codice'>
					<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' maxlength='250' value='$dati[0]' required>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' maxlength='250' value='$dati[6]' required>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' maxlength='250' required>$dati[1]</textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' maxlength='250' required>$dati[5]</textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file'>
					<label for='prezzo'>".Prezzo."</label>
					<input type='number' id='prezzo' name='prezzo' placeholder='".Prezzo."' step='0.01' value='$dati[2]' required>
					<label for='tipo'>".Tipo."</label>
					<select id='tipo' name='tipo'>
						<option value='$dati[7]'>";
						if ($dati[7]==1) echo Antipasti;
						if ($dati[7]==2) echo PrimoPiatto;
						if ($dati[7]==3) echo SecondoPiatto;
						if ($dati[7]==4) echo Contorno;
						if ($dati[7]==5) echo Dolce;
						if ($dati[7]==6) echo Altro;
						echo "</option>
						<option disabled></option>
						<option value='1'>".PrimoPiatto."</option>
						<option value='2'>".SecondoPiatto."</option>
						<option value='3'>".Contorno."</option>
						<option value='4'>".Dolce."</option>
						<option value='5'>".Altro."</option>
					</select>
					<input type='submit' class='bottone'  value='".Aggiorna."'>
					<input type='hidden' name='action' value='upload'>
				</form>";	
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_piatti'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_piatto":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$dati = select_piatto($codice);
			echo "<h3>".cancella_piatto."</h3>";
			echo "<form action='admin.php' method='post'>";
				echo "<input type='hidden' name='stato' value='delete_piatto'>";
				echo "<input type='hidden' name='codice' value='$codice'>";
				echo "<label for='titolo'>".Titolo.": </label>
					<input type='text' id='titolo' name='titolo' maxlength='250' value='$dati[0]' required readonly>
					<label for='titolo_en'>".Titolo." (english): </label>
					<input type='text' id='titolo_en' name='titolo_en' maxlength='250' value='$dati[6]' required readonly>
					<label for='contenuto'>".Testo.": </label>
					<textarea id='contenuto' name='contenuto' rows='6' cols='40' maxlength='250' required readonly>$dati[1]</textarea>
					<label for='contenuto_en'>".Testo." (english): </label>
					<textarea id='contenuto_en' name='contenuto_en' rows='6' cols='40' maxlength='250' required readonly>$dati[5]</textarea>
					<label for='file'>".immagine_facoltativa.": </label>
					<input type='file' id='file' name='file' disabled>
					<label for='prezzo'>".Prezzo."</label>
					<input type='number' id='prezzo' name='prezzo' placeholder='".Prezzo."' step='0.01' value='$dati[2]' required readonly>
					<label for='tipo'>".Tipo."</label>
					<select id='tipo' name='tipo' readonly>
						<option value='$dati[7]'>";
						if ($dati[7]==1) echo Antipasti;
						if ($dati[7]==2) echo PrimoPiatto;
						if ($dati[7]==3) echo SecondoPiatto;
						if ($dati[7]==4) echo Contorno;
						if ($dati[7]==5) echo Dolce;
						if ($dati[7]==6) echo Altro;
						echo "</option>
					</select>
					";
				echo "<input type='submit' class='bottone'  value='".Cancella."'>";
			echo "</form>";
			echo "<form method='post' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_piatti'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "delete_piatto":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			delete_piatto($codice);
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_piatti'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "insert_piatto":
			$titolo = htmlentities($_REQUEST["titolo"], ENT_QUOTES);
			$titolo_en = htmlentities($_REQUEST["titolo_en"], ENT_QUOTES);
			$contenuto = htmlentities($_REQUEST["contenuto"], ENT_QUOTES);
			$contenuto_en = htmlentities($_REQUEST["contenuto_en"], ENT_QUOTES);
			$prezzo = htmlentities($_REQUEST["prezzo"], ENT_QUOTES);
			$tipo = htmlentities($_REQUEST["tipo"], ENT_QUOTES);
					
			insert_piatto($titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName, $prezzo, $tipo);
			
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_piatto'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		case "update_piatto":
			$codice = htmlentities($_REQUEST["codice"], ENT_QUOTES);
			$titolo = htmlentities($_REQUEST["titolo"], ENT_QUOTES);
			$titolo_en = htmlentities($_REQUEST["titolo_en"], ENT_QUOTES);
			$contenuto = htmlentities($_REQUEST["contenuto"], ENT_QUOTES);
			$contenuto_en = htmlentities($_REQUEST["contenuto_en"], ENT_QUOTES);
			$prezzo = htmlentities($_REQUEST["prezzo"], ENT_QUOTES);
			$tipo = htmlentities($_REQUEST["tipo"], ENT_QUOTES);
					
			update_piatto($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName, $prezzo, $tipo);
			
			echo "<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='gestione_piatto'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "logout":
			unset($_SESSION["mail_admin"], $_SESSION["password_admin"], $_SESSION["cod_admin"]);
			pagina();
			break;
		
		default:
			crea_tab_admin();
			if (!empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
				paginaLogged();
			} else {
				pagina();
			}
			break;
	}
	
	tail();
?>
