<?php
	include("interfaccia.php");
	
	if (getStato() == "gestione_prenotazioni") {
		echo "<meta http-equiv='refresh' content='60'>";
	}
	
	head();

	topbar("admin");
	
	getStato();
	
	function pagina() {
		echo "<form method='get' action='admin.php'>";		
		echo "<input type='hidden' name='stato' value='login'>";
		echo "<label for='mail'>Mail: </label>";
		echo "<input type='email' id='mail' name='mail' placeholder='Mail' required>";
		echo "<label for='password'>Password: </label>";
		echo "<input type='password' id='password' name='password' placeholder='Password' required>";
		echo "<input type='submit' class='bottone'  value='".Accedi."' class='bottone'>";
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
			if ($file["name"] != "") {
				$checkImg = getimagesize($file["tmp_name"]);
				$temp = explode(".",$file["name"]);
				$newFileName = md5($file["name"]).".".end($temp);
			}
			if($file['error'] == UPLOAD_ERR_OK and is_uploaded_file($file['tmp_name']))	{
				if($checkImg == true) {
					if (isDebug()) echo "File is an image - " . $checkImg["mime"] . "<br/>";
				} else if($checkImg == false) {
					echo non_immagine."<br/>";
					$checkUpload = true;
				}
				if (file_exists(UPLOAD_DIR.$newFileName)) {
					echo file_gia_esistente."<br/>";
					$checkUpload = true;
				}
				if ($_FILES['file']['size'] > 5000000) {
					echo file_troppo_grande."<br/>";
					$checkUpload = true;
				}
				if ($checkUpload==false) {
					if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$newFileName)) {
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
			$mail = $_REQUEST["mail"];
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
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='".Prenotazioni."' class='bottone'>
				</form>";
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='News' class='bottone'>
				</form>";
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='".Utenti."' class='bottone'>
				</form>";
			echo "<form method='get' action='admin.php' style='margin-top:20px'>
					<input type='hidden' name='stato' value='logout' >
					<input type='submit' class='bottone'  value='Logout' class='bottone'>
				</form>";
			if (isDebug()) {
				echo "<form action='admin.php' method='get' style='margin-top:20px'>
					<input type='hidden' name='stato' value='killer'>
					<input type='submit' class='bottone'  value='DISTRUGGI TUTTO'>
				</form>";
			}
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
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."' class='bottone'>
				</form>";
			break;
			
		case "gestione_news":
			echo "<h2>News</h2>";
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='inserisci_news'>
					<input type='submit' class='bottone'  value='".inserisci_news."' class='bottone'>
				</form>";
			crea_tab_news();
			visualizza_news();
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."' class='bottone'>
				</form>";
			break;
			
		case "gestione_utenti":
			echo "<h2>".Utenti."</h2>";
			crea_tab_utenti();
			visualizza_utenti();
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Torna_indietro."' class='bottone'>
				</form>";
			break;
			
		case "cancella_utente":
			echo "<form action='admin.php' method='get'>
					<h4 class='attenzione'>".confermare_eliminazione_account.":</h4>"
					."<label for='nome'>".Nome.":</label> <input type='text' id='nome' readonly value='".$_REQUEST["nome"]."'>
					<label for='mail'>Mail: </label><input type='text' id='mail' readonly value='".$_REQUEST["mail"]."'>
					<input type='hidden' name='stato' value='delete_account_admin'>
					<input type='hidden' name='codice' value='".$_REQUEST["codice"]."'>
					<input type='submit' class='bottone'  value='OK' class='bottone'>
				</form>";
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='".Annulla."' class='bottone'>
				</form>";
			break;
			
		case "delete_account_admin":
			delete_account_admin($_REQUEST["codice"]);
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_utenti'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "conferma_scelta":
			$codice_prenotazione = $_REQUEST["codice"];
			$dati = select_prenotazione_admin($codice_prenotazione);
			echo "<form action='admin.php' method='get' style='display:inline'>";
				if (isset($_REQUEST["accetta"])) {
					echo "<input type='hidden' name='stato' value='accetta_prenotazione'>";
				} else if (isset($_REQUEST["rifiuta"])) {
					echo "<input type='hidden' name='stato' value='rifiuta_prenotazione'>";
				};
				echo "<input type='hidden' name='codice' value='$codice_prenotazione'>";
				echo Data.": ";
				echo "<input type='date' name='data' value='$dati[0]' required readonly><br/>";
				echo Ora.": ";
				echo "<input type='time' name='ora' value='$dati[1]' required readonly><br/>";
				echo Nome_tavolo.": ";
				echo "<input type='text' name='nome' maxlength='250' placeholder='".placeholder."' value='$dati[2]' required readonly><br/>";
				echo Numero_persone.": ";
				echo "<input type='number' name='num_persone' min='1' value='$dati[3]' required readonly><br/>";
				echo Richieste_particolari."? ";
				echo "<textarea name='richieste' rows='6' cols='40' maxlength='250' readonly>$dati[4]</textarea><br/>";
				if (isset($_REQUEST["accetta"])) {
					echo "<h4>".Accetta."?</h4>";
				} else if (isset($_REQUEST["rifiuta"])) {
					echo "<h4>".Rifiuta."?</h4>";
				};
				echo "<input type='submit' class='bottone'  name='conferma' value='OK'>
				</form>";
			echo "<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  name='conferma' value='No'>
				</form>";
			break;
			
		case "accetta_prenotazione":
			$codice_prenotazione = $_REQUEST["codice"];
			conferma_prenotazione($codice_prenotazione, 1);
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "rifiuta_prenotazione":
			$codice_prenotazione = $_REQUEST["codice"];
			conferma_prenotazione($codice_prenotazione, 2);
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_prenotazioni'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "inserisci_news":
			echo "<h2>".inserisci_news."</h2>";
			$cod_admin = $_SESSION["cod_admin"];
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'><br/>
					<input type='hidden' name='stato' value='insert_news'>
					".Titolo.": <input type='text' name='titolo' maxlength='250' required><br/>
					".Titolo." (english): <input type='text' name='titolo_en' maxlength='250' required><br/>
					".Testo.": <textarea name='contenuto' rows='6' cols='40' maxlength='250' required></textarea><br/>
					".Testo." (english): <textarea name='contenuto_en' rows='6' cols='40' maxlength='250' required></textarea><br/>
					".immagine_facoltativa.": <input type='file' name='file'><br/>
					<input type='submit' class='bottone'  value='".Inserisci."'>
					<input type='hidden' name='action' value='upload'>
				</form>";
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
		
		case "modifica_news":
			$codice = $_REQUEST["codice"];
			$dati = select_news($codice);
			echo "<form action='admin.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='stato' value='update_news'>
					<input type='hidden' name='codice' value='$codice'>
					".Titolo.": <input type='text' name='titolo' maxlength='250' value='$dati[0]' required><br/>
					".Titolo." (english): <input type='text' name='titolo_en' maxlength='250' value='$dati[6]' required><br/>
					".Testo.": <textarea name='contenuto' rows='6' cols='40' maxlength='250' required>$dati[1]</textarea><br/>
					".Testo." (inglese): <textarea name='contenuto_en' rows='6' cols='40' maxlength='250' required>$dati[5]</textarea><br/>
					".immagine_facoltativa.": <input type='file' name='file'><br/>
					<input type='submit' class='bottone'  value='".Aggiorna."'>
					<input type='hidden' name='action' value='upload'>
				</form>";	
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "cancella_news":
			echo "<h3>".Cancella." news</h3>";
			$codice = $_REQUEST["codice"];
			$dati = select_news($codice);
			echo "<form action='admin.php' method='get'>";
			echo "<input type='hidden' name='stato' value='delete_news'>";
			
			echo "<input type='hidden' name='codice' value='$codice'>";
			echo Titolo.": <input type='text' name='titolo' maxlength='250' value='$dati[0]' required readonly><br/>
					".Titolo." (english): <input type='text' name='titolo_en' maxlength='250' value='$dati[6]' required readonly><br/>
					".Testo.": <textarea name='contenuto' rows='6' cols='40' maxlength='250' required readonly>$dati[1]</textarea><br/>
					".Testo." (inglese): <textarea name='contenuto_en' rows='6' cols='40' maxlength='250' required readonly>$dati[5]</textarea><br/>
					".immagine_facoltativa.": <input type='file' name='file' readonly><br/>";
			echo "<h4>".confermare."</h4>";
			echo "<input type='submit' class='bottone'  value='".Cancella."'>";
			echo "</form>";
			echo "<form method='get' action='admin.php'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "delete_news":
			$codice = $_REQUEST["codice"];
			delete_news($codice);
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "insert_news":
			$titolo = addslashes($_REQUEST["titolo"]);
			$titolo_en = addslashes($_REQUEST["titolo_en"]);
			$contenuto = addslashes($_REQUEST["contenuto"]);
			$contenuto_en = addslashes($_REQUEST["contenuto_en"]);
			$cod_admin = $_SESSION["cod_admin"];
					
			insert_news($cod_admin, $titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName);
			
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
		
		case "update_news":
			$codice = $_REQUEST["codice"];
			$titolo = addslashes($_REQUEST["titolo"]);
			$titolo_en = addslashes($_REQUEST["titolo_en"]);
			$contenuto = addslashes($_REQUEST["contenuto"]);
			$contenuto_en = addslashes($_REQUEST["contenuto_en"]);
			$filename = addslashes($file["name"]);
					
			update_news($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $newFileName);
			
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='gestione_news'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			break;
			
		case "killer":
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='drop'>
					<input type='submit' class='bottone'  name='conferma' value='DISTRUGGI TUTTE LE TABELLE'>
				</form>";
			echo "<form action='admin.php' method='get'>
					<input type='hidden' name='stato' value='login'>
					<input type='submit' class='bottone'  value='".Annulla."'>
				</form>";
			break;
			
		case "drop":
			if (isset($_REQUEST["conferma"]) and ($_REQUEST["conferma"] === "DISTRUGGI TUTTE LE TABELLE")) 
				drop();
			session_unset();
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
