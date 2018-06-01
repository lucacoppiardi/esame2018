<?php
function connessione() {
	include("connessione.php");
	$conn = mysqli_connect($db_host,$db_user,$db_password, $db_name);
	if (!$conn) {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
		if (isDebug()) {
			echo mysqli_connect_error();
		}
		tail();
		die();
	}
	return $conn;
}

function close($conn){
	if(!mysqli_close($conn)){
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
		tail();
		die();
	}
}

function query($s, $conn, $tab) {
	if (isDebug()) {
		echo "<p style='font-family: monospace'>".$tab."<br/>".$s."</p>";
	}
	if (!$request=mysqli_query($conn, $s)) {
		if (isDebug()) {
			echo "<h3>".mysqli_error($conn)." (".mysqli_errno($conn).")</h3>"; // se il debug è attivo mostro l'errore e il suo codice
		}
		if (mysqli_errno($conn) == 1062 and $tab == "insert utenti") {
			echo "<h3 class='errore'>".utente_esistente."</h3>"; // un utente inserito già in tabella
		}
		if (mysqli_errno($conn) == 1146 or mysqli_errno($conn) == 1051) {
			echo "<h3 class='errore'>".tabella_inesistente."</h3>"; // la tabella non esiste
		}
		if ($tab != "drop") { // pulsante per tornare indietro, in caso la query non fosse eseguita
			torna_indietro();
		}
	}
	return $request;
}

function torna_indietro() {
	echo "<h2 class='errore'>".Errore_query.". </h2>";
	echo "<form action='".basename($_SERVER['PHP_SELF'])."' method='get'>
			<input type='hidden' name='lang' value='".getLang()."'>
			<input type='submit' value='".Torna_indietro."'>
		</form>";
	tail();
	die();
}

function fetch_row($result) {
	$row=mysqli_fetch_row($result);
	return $row;
}

function crea_tab_utenti() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="CREATE TABLE IF NOT EXISTS utenti (
		codice INT(11) NOT NULL AUTO_INCREMENT,
		mail VARCHAR(255) UNIQUE NOT NULL,
		password VARCHAR(255) NOT NULL,
		nome VARCHAR(255) NOT NULL,
		telefono VARCHAR(15) NOT NULL,
		data_iscrizione DATE NOT NULL,
		hash VARCHAR(255) UNIQUE NOT NULL,
		confermato BIT,
		ultimo_accesso DATETIME,
		PRIMARY KEY (codice)
		);";
		query($s, $db_conn, "creata utenti");
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
}

function crea_tab_prenotazioni() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="CREATE TABLE IF NOT EXISTS prenotazioni (
		codice INT(11) NOT NULL AUTO_INCREMENT,
		cod_utente INT(11) NOT NULL,
		data DATE NOT NULL,
		ora TIME NOT NULL,
		nome VARCHAR(255) NOT NULL,
		partecipanti INT(5) NOT NULL,
		richieste VARCHAR(255),
		stato int(1),
		PRIMARY KEY (codice),
		FOREIGN KEY (cod_utente) REFERENCES utenti(codice)
		ON DELETE CASCADE
		);";
		query($s, $db_conn, "creata prenotazioni");
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
}

function crea_tab_news() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="CREATE TABLE IF NOT EXISTS news (
		codice INT(11) NOT NULL AUTO_INCREMENT,
		cod_admin INT(11) NOT NULL,
		data DATE NOT NULL,
		ora TIME NOT NULL,
		titolo VARCHAR(255) NOT NULL,
		titolo_en VARCHAR(255) NOT NULL,
		testo VARCHAR(255) NOT NULL,
		testo_en VARCHAR(255) NOT NULL,
		immagine VARCHAR(255),
		PRIMARY KEY (codice),
		FOREIGN KEY (cod_admin) REFERENCES amministratori(codice)
		ON DELETE CASCADE
		);";
		query($s, $db_conn, "creata news");
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
}

function crea_tab_admin() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="CREATE TABLE IF NOT EXISTS amministratori (
		codice INT(11) NOT NULL,
		mail VARCHAR(255) UNIQUE NOT NULL,
		password VARCHAR(255) NOT NULL,
		nome VARCHAR(255) NOT NULL,
		telefono VARCHAR(15) NOT NULL,
		ultimo_accesso DATETIME,
		PRIMARY KEY (codice)
		);";
		query($s, $db_conn, "creata amministratori");
		file_insert_admin();
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
}

function file_insert_admin() {
	$db_conn = connessione();
	$file = fopen("insert_amministratori.sql", "r") or die("Unable to open file!");
	$i = 1;
	while(!feof($file)) {
		if ($db_conn) {
			$s = fgets($file);
			if ($s!="") {
				query($s, $db_conn, "import $i");
			}
		} else {
			echo("errore di connessione");
		}
		$i++;
	}
	fclose($file);
}

function drop() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="DROP TABLE prenotazioni";
		if (query($s, $db_conn, "drop")) echo "prenotazioni cancellata<br/>";
		$s="DROP TABLE news";
		if (query($s, $db_conn, "drop")) echo "news cancellata<br/>";
		$s="DROP TABLE utenti";
		if (query($s, $db_conn, "drop")) echo "utenti cancellata<br/>";
		$s="DROP TABLE amministratori";
		if (query($s, $db_conn, "drop")) echo "amministratori cancellata<br/>";
		echo "<form action='admin.php' method='get'>
				<input type='hidden' name='lang' value='".getLang()."'>
				<input type='submit' value='".Torna_indietro."'>
			</form>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function crea_utente($mail,$password,$nome,$telefono) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($password) and !empty($nome) and !empty($telefono)) {
		$s="INSERT INTO utenti (mail,password,nome,telefono,data_iscrizione,hash) VALUES ('$mail','$password','$nome','$telefono',NOW(),'".md5($mail)."') ";
		$result = query($s, $db_conn, "insert utenti");
		
		if ($result != false) {
			$to = $mail;
			$subject = grazie_iscrizione;
			$message = conferma_a_questo_link.": <a href='prenotazioni.php?stato=conferma_registrazione&lang=".getLang()."&hash=".md5($mail)."'>".Conferma."</a>";
			$headers = "From: lucacoppiardi@altervista.org";

			$esito_mail = mail($to,$subject,$message,$headers);
			
			if (!isDebug()) {
				echo $message;
			}
			
			if (isDebug()) {
				echo $esito_mail;
				if (!$esito_mail) {
					echo " EMAIL ERROR"."<br/>";
				}			
				else {
					echo " EMAIL OK"."<br/>";
				}
				echo $to."<br/>";
				echo $subject."<br/>";
				echo $message."<br/>";
				echo $headers."<br/>";
			}
		} else {
			echo "<form action='prenotazioni.php' method='get'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' value='OK'>
				</form>";
			tail();
			die();
		}
				
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function login($mail, $pass) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($pass)) {
		$s="SELECT confermato FROM utenti WHERE mail='$mail' AND password='$pass'";
		$result=query($s, $db_conn, "select confermato login");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			if ($row[0] != 1) {
				echo "<h3 class='avviso'>".Conferma_iscrizione_cliccando_link."</h3>";
				
				$to = $mail;
				$subject = grazie_iscrizione;
				$message = conferma_a_questo_link.": <a href='prenotazioni.php?stato=conferma_registrazione&lang=".getLang()."&hash=".md5($mail)."'>".Conferma."</a>";
				$headers = "From: lucacoppiardi@altervista.org";
				
				$esito_mail = mail($to,$subject,$message,$headers);
								
				if (isDebug()) {
					echo $esito_mail;
					if (!$esito_mail) {
						echo " EMAIL ERROR"."<br/>";
					}			
					else {
						echo " EMAIL OK"."<br/>";
					}
					echo $to."<br/>";
					echo $subject."<br/>";
					echo $message."<br/>";
					echo $headers."<br/>";
				}
				
				if (!isDebug()) {
					echo $message;
				}
				
				echo "<form action='prenotazioni.php' method='get'>
						<input type='hidden' name='lang' value='".getLang()."'>
						<input type='submit' value='OK'>
					</form>";
					
				tail();
				die();
				
			} else {
				$s="SELECT codice,mail,ultimo_accesso,password FROM utenti WHERE mail='$mail' AND password='$pass'";
				$result=query($s, $db_conn, "select login");
				if (mysqli_num_rows($result) == 1) {
					$row = fetch_row($result);
					$sql = "UPDATE utenti SET ultimo_accesso = NOW() WHERE mail='$mail' AND password='$pass'";
					query($sql, $db_conn, "update ultimo accesso");
					return $row;
				}
			}
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function admin_login($mail, $pass) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($pass)) {
		$s="SELECT codice,mail,ultimo_accesso,password FROM amministratori WHERE mail='$mail' AND password='$pass'";
		$result=query($s, $db_conn, "select admin login");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			$sql = "UPDATE amministratori SET ultimo_accesso = NOW() WHERE mail='$mail' AND password='$pass'";
			query($sql, $db_conn, "update ultimo accesso");
			return $row;
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function visualizza_utenti() { /*SEI QUI*/
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$s="SELECT codice,nome,mail,telefono,data_iscrizione FROM utenti ORDER BY data_iscrizione";
		$result=query($s, $db_conn, "select utenti");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr><td>".Codice."</td></td><td>".Nome."</td><td>Mail</td><td>".Telefono."</td><td>".Data_iscrizione."</td></tr>";
		while ($row=fetch_row($result)) {
			echo "<tr>";
			foreach ($row as $campo) {
				echo "<td>$campo</td>";
			}
			echo "<td>
				<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='cancella_utente'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' value='".Cancella."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function delete_account_admin($id) {
	$db_conn=connessione();
	if ($db_conn and !empty($id)) {
		$s="DELETE FROM utenti WHERE codice=$id";
		if (query($s, $db_conn, "delete utente")) {
			echo "<h3 class='avviso'>".Account_cancellato."</h3>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function visualizza_prenotazioni() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="SELECT prenotazioni.codice, utenti.nome, utenti.mail, utenti.telefono, prenotazioni.data, prenotazioni.ora, prenotazioni.nome, prenotazioni.partecipanti, prenotazioni.richieste, prenotazioni.stato FROM prenotazioni,utenti WHERE prenotazioni.cod_utente = utenti.codice ORDER BY prenotazioni.data, prenotazioni.ora, prenotazioni.stato";
		$prenotazioni=query($s, $db_conn, "select visualizza_prenotazioni");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>\n";
		echo "<tr><td>".Codice."</td><td>".Nome."</td><td>".Contatti."</td><td>".Data."</td><td>".Ora."</td><td>".Nome_prenotazione."</td><td>".Persone."</td><td>Note</td><td>".Stato."</td></tr>\n";
		while ($row=fetch_row($prenotazioni)) {	
			echo "<tr>\n";
			echo "<td style='width:10px'>$row[0]</td>";
			echo "<td>$row[1]</td>\n";
			echo "<td>$row[2]<br/>$row[3]</td>\n";
			for ($i=4; $i<=8; $i++) {
				echo "<td>$row[$i]</td>\n";
			}
			if ($row[9] == 0) {
				echo "<td>
				<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='conferma_scelta'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' name='accetta' value='".Accetta."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='conferma_scelta'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' name='rifiuta' value='".Rifiuta."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				</td>";
			} else if ($row[9] == 1) {
				echo "<td>".Accettata."</td>";
			} else if ($row[9] == 2) {
				echo "<td>".Rifiutata."</td>";
			}
				
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "</div>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function visualizza_news() {
	$db_conn=connessione();
	if ($db_conn) {
		$cod_admin = $_SESSION["cod_admin"];
		$s="SELECT codice, data, ora, titolo, testo, titolo_en, testo_en FROM news WHERE cod_admin = $cod_admin ORDER BY data";
		$result=query($s, $db_conn, "select visualizza_news");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr><td>".Data."/".Ora."</td><td>".Titolo."</td><td>".Testo."</td></tr>";
		while ($row=fetch_row($result)) {	
			echo "<tr>";
			echo "<td>$row[1]<br/>$row[2]</td>";
			echo "<td><a href='news.php?lang=".getLang()."#$row[0]'>";
			if (getLang() == "en") {
				echo $row[5];
			} else {
				echo $row[3];
			}
			echo "</a></td>";
			echo "<td>";
			if (getLang() == "en") {
				echo substr($row[6],0,50);
				if (strlen($row[6])>=50) echo "...";
			} else {
				echo substr($row[4],0,50);
				if (strlen($row[4])>=50) echo "...";
			}
			echo "</td>";
			echo "<td>
				<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='modifica_news'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' value='".Modifica."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<form action='admin.php' method='get' style='display:inline'>
					<input type='hidden' name='stato' value='cancella_news'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='submit' value='".Cancella."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function inserisci_prenotazione($data, $ora, $nome, $partecipanti, $richieste) {
	$db_conn=connessione();
	$cod = $_SESSION["cod_utente"];
	if ($db_conn and !empty($cod) and !empty($data) and !empty($ora) and !empty($nome) and !empty($partecipanti)) {
		if ($richieste == "") {
			$s="INSERT INTO prenotazioni (cod_utente,data,ora,nome,partecipanti,richieste,stato) VALUES ($cod, '$data','$ora','$nome','$partecipanti',null,0) ";
		} else {
			$s="INSERT INTO prenotazioni (cod_utente,data,ora,nome,partecipanti,richieste,stato) VALUES ($cod, '$data','$ora','$nome','$partecipanti','$richieste',0) ";
		}
		if (query($s, $db_conn, "insert prenotazioni")) {
			echo "<h3 class='avviso'>".Prenotazione_inserita."</h3>";
		} else {
			echo "<h2 class='errore'>".Errore_query."</h2>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function mail_riepilogo($data, $ora, $nome, $num_persone, $richieste) {
	$db_conn=connessione();
	if ($db_conn and !empty($data) and !empty($ora) and !empty($nome) and !empty($num_persone) and $num_persone>0) {
		$id_utente = $_SESSION["cod_utente"];
		$s="SELECT mail FROM utenti WHERE codice=$id_utente";
		$result = query($s, $db_conn, "select mail");
		if (mysqli_num_rows($result) == 1) {
			$mail = fetch_row($result);
		}
		
		/*
		if(mail('lucacoppiardi@altervista.org','oggetto','messaggio','From: lucacoppiardi@altervista.org')) {
			echo 'email inviata correttamente';
		}
		else {
			echo 'Errore!';
		}
		*/
		
		$to = $mail[0];
		$subject = subject_email;
		$message = 
			riepilogo_email."\n".
			Data.": ".$data."\n".
			Ora.": ".$ora."\n".
			Nome_tavolo.": ".$nome."\n".
			Numero_persone.": ".$num_persone."\n".
			Richieste_particolari.": ".$richieste."\n\n".
			ringraziamenti_email."\n"
			;
		$headers = "From: lucacoppiardi@altervista.org";
		
		$esito_mail = mail($to,$subject,$message,$headers);
		
		if (isDebug()) {
			echo $esito_mail;
			if (!$esito_mail) {
				echo "EMAIL ERROR"."<br/>";
			}			
			else {
				echo "EMAIL OK"."<br/>";
			}
			echo $to."<br/>";
			echo $subject."<br/>";
			echo $message."<br/>";
			echo $headers."<br/>";
		}		
		
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function conferma_prenotazione($cod_prenotazione, $stato) {
	$db_conn=connessione();
	if ($db_conn and !empty($cod_prenotazione) and !empty($stato)) {
		$s="SELECT cod_utente FROM prenotazioni WHERE codice=$cod_prenotazione";
		$result=query($s, $db_conn, "select cod_utente prenotazioni");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		}
		
		$s="SELECT mail FROM utenti WHERE codice=$row[0]";
		$result=query($s, $db_conn, "select mail utente");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		}
		
		$sql = "UPDATE prenotazioni SET stato=$stato WHERE codice=$cod_prenotazione";
		if (query($sql, $db_conn, "update stato confermato") and $stato == 1) {
			echo "<h3 class='avviso'>".Prenotazione_confermata."</h3>";
		} else if (query($sql, $db_conn, "update stato confermato") and $stato == 2) {
			echo "<h3 class='avviso'>".Prenotazione_rifiutata."</h3>";
		}
		
		$to = $row[0];
		if($stato == 1) {
			$subject = Prenotazione_inserita;
			$message = Prenotazione_confermata."\n".
			ringraziamenti_email
			;
		}
		else if ($stato == 2) {
			$subject = Prenotazione_non_inserita;
			$message = Prenotazione_rifiutata."\n".
			ringraziamenti_email
			;
		}
		$headers = "From: lucacoppiardi@altervista.org";
		
		$esito_mail = mail($to,$subject,$message,$headers);
		
		if (isDebug()) {
			echo $esito_mail;
			if ($esito_mail == false) {
				echo "EMAIL ERROR"."<br/>";
			}			
			else if($esito_mail == true) {
				echo "EMAIL OK"."<br/>";
			}
			echo $to."<br/>";
			echo $subject."<br/>";
			echo $message."<br/>";
			echo $headers."<br/>";
		}
		
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function prenotazioni_utente($result) {
	$db_conn=connessione();
	if ($db_conn and !empty($result)) {
		$s="SELECT prenotazioni.codice, prenotazioni.data, prenotazioni.ora, prenotazioni.nome, prenotazioni.partecipanti, prenotazioni.richieste, prenotazioni.stato FROM prenotazioni WHERE prenotazioni.cod_utente = $result[0] ORDER BY prenotazioni.data, prenotazioni.ora, prenotazioni.stato";
		$pr = query($s, $db_conn, "select visualizza_prenotazioni");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr><td>".Data."</td><td>".Ora."</td><td>".Nome_prenotazione."</td><td>".Persone."</td><td>Note</td><td>".Stato."</td></tr>";
		while ($row = fetch_row($pr)) {
			echo "<tr>";
			for ($i=1; $i<=5; $i++) {
				echo "<td>$row[$i]</td>";
			}
			if ($row[6] == 0) {
				echo "<td>
					<form action='prenotazioni.php' method='get' style='display:inline'>
					<input type='submit' value='".Modifica."'>
					<input type='hidden' name='stato' value='modifica_prenotazione'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='hidden' name='codice' value='$row[0]'>
					</form>
					<form action='prenotazioni.php' method='get' style='display:inline'>
					<input type='submit' value='".Cancella."'>
					<input type='hidden' name='stato' value='cancella_prenotazione'>
					<input type='hidden' name='lang' value='".getLang()."'>
					<input type='hidden' name='codice' value='$row[0]'>
					</form>
					</td>";
			} else if($row[6] == 1){
				echo "<td>".Accettata."</td>";
			} else if($row[6] == 2){
				echo "<td>".Rifiutata."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function select_prenotazione($codice) {
	$db_conn=connessione();
	$cod = $_SESSION["cod_utente"];
	if ($db_conn and !empty($codice) and !empty($cod)) {
		$s="SELECT data,ora,nome,partecipanti,richieste,cod_utente FROM prenotazioni WHERE codice = $codice AND cod_utente = $cod";
		$result = query($s, $db_conn, "select prenotazione");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			return $row;
		} else {
			torna_indietro();
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}	

function select_news($codice) {
	$db_conn=connessione();
	$cod_admin = $_SESSION["cod_admin"];
	if ($db_conn and !empty($codice) and !empty($cod_admin)) {
		$s="SELECT titolo,testo,data,immagine,cod_admin,testo_en,titolo_en FROM news WHERE codice = $codice AND cod_admin = $cod_admin";
		$result = query($s, $db_conn, "select news");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			return $row;
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function update_prenotazione($codice, $data, $ora, $nome, $num_persone, $richieste) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($data) and !empty($ora) and !empty($nome) and !empty($num_persone) and $num_persone>0) {
		$cod_utente = $_SESSION["cod_utente"];
		$s="SELECT cod_utente FROM prenotazioni WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo update prenotazione");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_utente == $row[0]) {
			$s="UPDATE prenotazioni SET data='$data', ora='$ora', nome='$nome', partecipanti=$num_persone, richieste='$richieste' WHERE codice = $codice AND cod_utente=$cod_utente";
			if (query($s, $db_conn, "update prenotazione")) {
				echo "<h3 class='avviso'>".Prenotazione_modificata."</h3>";
			} else {
				torna_indietro();
			}
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function update_news($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $filename) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($titolo) and !empty($contenuto) and !empty($contenuto_en) and !empty($titolo_en)) {
		if ($filename == "") {
			$s="UPDATE news SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine=null WHERE codice = $codice";
		} else {
			$s="UPDATE news SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine='$filename' WHERE codice = $codice";
		}
		if (query($s, $db_conn, "update news")) {
			echo "<h3 class='avviso'>".News_aggiornata."</h3>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function delete_prenotazione($codice) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice)) {
		$cod_utente = $_SESSION["cod_utente"];
		$s="SELECT cod_utente FROM prenotazioni WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo mail e password delete prenotazione");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_utente == $row[0]) {
			$s="DELETE FROM prenotazioni WHERE codice = $codice";
			if (query($s, $db_conn, "delete prenotazione")) {
				echo "<h3 class='avviso'>".Prenotazione_cancellata."</h3>";
			} else {
				torna_indietro();
			}
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function delete_news($codice) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice)) {
		$s="DELETE FROM news WHERE codice = $codice";
		if (query($s, $db_conn, "delete news")) {
			echo "<h3 class='avviso'>".News_cancellata."</h3>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function reset_password($old_pw, $new_pw) {
	$db_conn=connessione();
	if ($db_conn and !empty($old_pw) and !empty($new_pw)) {
		$id_utente = $_SESSION["cod_utente"];
		$s = "SELECT password FROM utenti WHERE codice=$id_utente";
		$result = query($s, $db_conn, "select old password per check cambio");
		$row = fetch_row($result);
		if ($row[0] != $old_pw) {
			return false;
		} else {
			$s="UPDATE utenti SET password='$new_pw' WHERE codice=$id_utente AND password='$old_pw'";
			if (query($s, $db_conn, "update password")) {
				return true;
			} else {
				return false;
			}
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function insert_news($cod_admin, $titolo, $contenuto, $contenuto_en, $titolo_en, $filename) {
	$db_conn=connessione();
	if ($db_conn and !empty($cod_admin) and !empty($titolo) and !empty($contenuto) and !empty($contenuto_en) and !empty($titolo_en)) {
		if ($filename == "") {
			$s="INSERT INTO news (cod_admin, titolo, testo, testo_en, titolo_en, immagine, data, ora) VALUES ($cod_admin, '$titolo', '$contenuto', '$contenuto_en', '$titolo_en', null, NOW(), NOW())";
		} else {
			$s="INSERT INTO news (cod_admin, titolo, testo, testo_en, titolo_en, immagine, data, ora) VALUES ($cod_admin, '$titolo', '$contenuto', '$contenuto_en', '$titolo_en', '$filename', NOW(), NOW())";
		}
		if (query($s, $db_conn, "insert news")) {
			echo "<h3 class='avviso'>".News_inserita."</h3>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function pagina_news() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="SELECT news.codice, news.data, news.titolo, news.testo, news.immagine, news.cod_admin, amministratori.nome, news.ora, news.testo_en, news.titolo_en FROM news,amministratori WHERE amministratori.codice = news.cod_admin ORDER BY data";
		$result=query($s, $db_conn, "select pagina news");
		while ($row=fetch_row($result)) {
			echo "<h2 id='$row[0]'>";
			if (getLang() == "en") {
				echo $row[9];
			} else {
				echo $row[2];
			}
			echo "</h2>";
			if (getLang() == "en") {
				echo "<p>$row[8]</p>";
			} else {
				echo "<p>$row[3]</p>";
			}
			if ($row[4] != null) {
				echo "<img src='uploads/$row[4]' width='200px' heigth='200px' alt='$row[4]'>";
			}
			echo "<p style='font-style: italic;'>";
			echo Pubblicata_il;
			echo " $row[1] $row[7] ";
			echo da;
			echo " $row[6]</p>";
			echo "<hr width='90%'>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function mail_recupero_password($mail) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail)) {
		$alfabeto = "1234567890QWERTYUIOPASDFGHJKLZXCVBNM1234567890";
		$len = strlen($alfabeto);
		$new_pw = "";
		for ($i=0; $i<10; $i++) {
			$new_pw .= $alfabeto[rand(0,$len-1)];
		}
	
		$s = "UPDATE utenti SET password='".md5($new_pw)."' WHERE mail='$mail'";
		query($s, $db_conn, "update password temp recupero");
		
		echo "<h3 class='avviso'>".Password_inviata_mail."</h3>";
		
		if (isDebug()) echo $new_pw;
						
		$to = $mail;
		$subject = Recupero_password;
		$message = la_tua_nuova_password.": $new_pw";
		$headers = "From: lucacoppiardi@altervista.org";
		
		$esito_mail = mail($to,$subject,$message,$headers);
		
		if (isDebug()) {
			echo $esito_mail;
			if (!$esito_mail) {
				echo "EMAIL ERROR"."<br/>";
			}			
			else {
				echo "EMAIL OK"."<br/>";
			}
			echo $to."<br/>";
			echo $subject."<br/>";
			echo $message."<br/>";
			echo $headers."<br/>";
		}
		
		if (!isDebug()) {
			echo $message;
		}
		
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function delete_account($codice, $mail, $password) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($mail) and !empty($password)) {
		$s = "DELETE FROM utenti WHERE codice=$codice AND mail='$mail' AND password='$password'";
		if (query($s, $db_conn, "delete user")) {
			echo "<h3 class='avviso'>".Account_cancellato."</h3>";
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function update_indirizzo_mail($mail, $newmail) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($newmail)) {
		$s = "UPDATE utenti SET mail='$newmail' WHERE mail='$mail'";
		if (query($s, $db_conn, "cambio mail")) {
			echo "<h3 class='avviso'>".Indirizzo_aggiornato."</h3>";
		} else {
			torna_indietro();
		}
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}

function registrazione_confermata($hash) {
	$db_conn=connessione();
	if ($db_conn and !empty($hash)) {
		$s = "UPDATE utenti SET confermato=1 WHERE hash='$hash'";
		query($s, $db_conn, "conferma hash");
		echo "<h3 class='avviso'>".Registrazione_riuscita."</h3>";
		echo "<form action='prenotazioni.php' method='get'>
				<input type='hidden' name='lang' value='".getLang()."'>
				<input type='hidden' name='stato' value='accedi'>
				<input type='submit' value='OK'>
			</form>";
	} else {
		echo "<h2 class='errore'>".Errore_connessione_database."</h2>";
	}
	close($db_conn);
}


?>
