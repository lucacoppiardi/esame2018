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
	echo "<form action='".basename($_SERVER['PHP_SELF'])."' method='POST'>
			<input type='submit' class='bottone'  value='".Torna_indietro."'>
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
		confermato INT(1),
		ultimo_accesso DATETIME,
		PRIMARY KEY (codice)
		);";
		if (!query($s, $db_conn, "creata utenti")) {
			torna_indietro();
		}
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
		if (!query($s, $db_conn, "creata prenotazioni")) {
			torna_indietro();
		}
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
		titolo VARCHAR(255) NOT NULL UNIQUE,
		titolo_en VARCHAR(255) NOT NULL UNIQUE,
		testo VARCHAR(255) NOT NULL,
		testo_en VARCHAR(255) NOT NULL,
		immagine VARCHAR(255),
		PRIMARY KEY (codice),
		FOREIGN KEY (cod_admin) REFERENCES amministratori(codice)
		ON DELETE CASCADE
		);";
		if (!query($s, $db_conn, "creata news")) {
			torna_indietro();
		}
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
		if (!query($s, $db_conn, "creata amministratori")) {
			torna_indietro();
		}
		$s = "INSERT IGNORE INTO amministratori (codice,mail,password,nome,telefono,ultimo_accesso) VALUES (1,'lucacoppiardi@altervista.org', MD5('luca'), 'Luca', '1234121212', NOW());";
		if (!query($s, $db_conn, "insert admin")) {
			torna_indietro();
		}
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
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
		echo "<form action='admin.php' method='POST'>
				<input type='submit' class='bottone'  value='".Torna_indietro."'>
			</form>";
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function crea_utente($mail,$password,$nome,$telefono) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($password) and !empty($nome) and !empty($telefono)) {
		$s="INSERT INTO utenti (mail,password,nome,telefono,data_iscrizione,hash) VALUES ('".addslashes($mail)."','$password','$nome','$telefono',NOW(),'".md5($mail)."') ";
		$result = query($s, $db_conn, "insert utenti");
		
		if ($result != false and mysqli_affected_rows($db_conn) == 1) {
			$to = addslashes($mail);
			$subject = grazie_iscrizione;
			$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_registrazione&lang=".$_SESSION["lang"]."&hash=".md5($mail)."\n".ringraziamenti_email;
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
			torna_indietro();
		}
				
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function login($mail, $pass) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($pass)) {
		$s="SELECT confermato FROM utenti WHERE mail='".addslashes($mail)."' AND password='$pass'";
		$result=query($s, $db_conn, "select confermato login");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			if ($row[0] != 1) {
				echo "<h3 class='avviso'>".Conferma_iscrizione_cliccando_link."</h3>";
				
				$to = addslashes($mail);
				$subject = grazie_iscrizione;
				$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_registrazione&lang=".$_SESSION["lang"]."&hash=".md5($mail)."\n".ringraziamenti_email;
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
				
				echo "<form action='prenotazioni.php' method='POST'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
					
				tail();
				die();
				
			} else {
				$s="SELECT codice,mail,ultimo_accesso,password FROM utenti WHERE mail='".addslashes($mail)."' AND password='$pass'";
				$result=query($s, $db_conn, "select login");
				if (mysqli_num_rows($result) == 1) {
					$row = fetch_row($result);
					$sql = "UPDATE utenti SET ultimo_accesso = NOW() WHERE mail='".addslashes($mail)."' AND password='$pass'";
					query($sql, $db_conn, "update ultimo accesso");
					return $row;
				} else {
					torna_indietro();
				}
			}
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function admin_login($mail, $pass) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($pass)) {
		$s="SELECT codice,mail,ultimo_accesso,password FROM amministratori WHERE mail='".addslashes($mail)."' AND password='$pass'";
		$result=query($s, $db_conn, "select admin login");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			$sql = "UPDATE amministratori SET ultimo_accesso = NOW() WHERE mail='".addslashes($mail)."' AND password='$pass'";
			query($sql, $db_conn, "update ultimo accesso");
			return $row;
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function visualizza_utenti() {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$s="SELECT codice,nome,mail,telefono,data_iscrizione FROM utenti ORDER BY data_iscrizione";
		$result=query($s, $db_conn, "select utenti");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr class='prima_riga'><td>Cod.</td></td><td>".Nome."</td><td>Mail</td><td>".Telefono."</td><td>".Data_iscrizione."</td><td></td></tr>";
		while ($row=fetch_row($result)) {
			echo "<tr class='altre_righe'>";
			for ($i=0; $i<5; $i++) {
				if ($i != 2) {
					echo "<td>".$row[$i]."</td>";
				} else {
					echo "<td><a href='mailto:".$row[2]."'>".$row[2]."</a></td>";
				}
			}
			echo "<td>
				<form action='admin.php' method='POST'>
					<input type='hidden' name='stato' value='cancella_utente'>
					<input type='submit' class='bottonePiccolo'  value='".Cancella."'>
					<input type='hidden' name='codice' value='$row[0]'>
					<input type='hidden' name='nome' value='$row[1]'>
					<input type='hidden' name='mail' value='$row[2]'>
				</form>
				</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function delete_account_admin($id) {
	$db_conn=connessione();
	if ($db_conn and !empty($id) and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$s="DELETE FROM utenti WHERE codice=$id";
		query($s, $db_conn, "delete utente");
		if (mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Account_cancellato."</h3>";
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function visualizza_prenotazioni() {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$s="SELECT prenotazioni.codice, utenti.nome, utenti.mail, utenti.telefono, prenotazioni.data, prenotazioni.ora, prenotazioni.nome, prenotazioni.partecipanti, prenotazioni.richieste, prenotazioni.stato FROM prenotazioni,utenti WHERE prenotazioni.cod_utente = utenti.codice ORDER BY prenotazioni.data, prenotazioni.ora, prenotazioni.stato";
		$prenotazioni=query($s, $db_conn, "select visualizza_prenotazioni");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr class='prima_riga'><td>Cod.</td><td>".Nome."</td><td>".Contatti."</td><td>".Data."</td><td>".Ora."</td><td>".Nome_prenotazione."</td><td>".Persone."</td><td>Note</td><td>".Stato."</td></tr>";
		while ($row=fetch_row($prenotazioni)) {	
			echo "<tr class='altre_righe'>";
			echo "<td>$row[0]</td>";
			echo "<td>$row[1]</td>";
			echo "<td>$row[3]<br/><a href='mailto:'".$row[2]."'>".$row[2]."</a></td>";
			for ($i=4; $i<=7; $i++) {
				echo "<td>$row[$i]</td>";
			}
			echo "<td><div class='nota'>$row[8]</div></td>";
			if ($row[9] == 0) {
				echo "<td>
				<form action='admin.php' method='POST'>
					<input type='hidden' name='stato' value='conferma_scelta'>
					<input type='submit' class='bottonePiccolo'  name='accetta' value='".Accetta."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				<form action='admin.php' method='POST'>
					<input type='hidden' name='stato' value='conferma_scelta'>
					<input type='submit' class='bottonePiccolo'  name='rifiuta' value='".Rifiuta."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				</td>";
			} else if ($row[9] == 1) {
				echo "<td>".Accettata."</td>";
			} else if ($row[9] == 2) {
				echo "<td>".Rifiutata."</td>";
			}
				
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function visualizza_news() {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$cod_admin = $_SESSION["cod_admin"];
		$s="SELECT codice, data, ora, titolo, testo, titolo_en, testo_en FROM news WHERE cod_admin = $cod_admin ORDER BY data DESC, ora DESC";
		$result=query($s, $db_conn, "select visualizza_news");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr class='prima_riga'><td>".Data."/".Ora."</td><td>".Titolo."</td><td>".Testo."</td><td></td></tr>";
		while ($row=fetch_row($result)) {	
			echo "<tr class='altre_righe'>";
			echo "<td>$row[1]<br/>$row[2]</td>";
			echo "<td><a href='news.php#$row[0]'>";
			if ($_SESSION["lang"] == "en") {
				echo $row[5];
			} else {
				echo $row[3];
			}
			echo "</a></td>";
			echo "<td><div class='nota'>";
			if ($_SESSION["lang"] == "en") {
				/*echo substr($row[6],0,50);
				if (strlen($row[6])>=50) echo "...";*/
				echo $row[6];
			} else {
				/*echo substr($row[4],0,50);
				if (strlen($row[4])>=50) echo "...";*/
				echo $row[4];
			}
			echo "</div></td>";
			echo "<td>
				<form action='admin.php' method='POST'>
					<input type='hidden' name='stato' value='modifica_news'>
					<input type='submit' class='bottonePiccolo'  value='".Modifica."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				<form action='admin.php' method='POST'>
					<input type='hidden' name='stato' value='cancella_news'>
					<input type='submit' class='bottonePiccolo'  value='".Cancella."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function inserisci_prenotazione($data, $ora, $nome, $partecipanti, $richieste) {
	$db_conn=connessione();
	$cod = $_SESSION["cod_utente"];
	if ($db_conn and !empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($cod) and !empty($data) and !empty($ora) and !empty($nome) and !empty($partecipanti)) {
		if ($richieste == "") {
			$s="INSERT INTO prenotazioni (cod_utente,data,ora,nome,partecipanti,richieste,stato) VALUES ($cod, '$data','$ora','$nome','$partecipanti',null,0) ";
		} else {
			$s="INSERT INTO prenotazioni (cod_utente,data,ora,nome,partecipanti,richieste,stato) VALUES ($cod, '$data','$ora','$nome','$partecipanti','$richieste',0) ";
		}
		if (query($s, $db_conn, "insert prenotazioni") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Prenotazione_inserita."</h3>";
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function mail_riepilogo($data, $ora, $nome, $num_persone, $richieste) {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($_SESSION["cod_utente"]) and !empty($data) and !empty($ora) and !empty($nome) and !empty($num_persone) and $num_persone>0) {
		$id_utente = $_SESSION["cod_utente"];
		$s="SELECT mail FROM utenti WHERE codice=$id_utente";
		$result = query($s, $db_conn, "select mail");
		if (mysqli_num_rows($result) == 1) {
			$mail = fetch_row($result);
		} else {
			torna_indietro();
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
		torna_indietro();
	}
	close($db_conn);
}

function mail_riepilogo_cancella($data, $ora, $nome, $num_persone, $richieste) {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($_SESSION["cod_utente"]) and !empty($data) and !empty($ora) and !empty($nome) and !empty($num_persone) and $num_persone>0) {
		$id_utente = $_SESSION["cod_utente"];
		$s="SELECT mail FROM utenti WHERE codice=$id_utente";
		$result = query($s, $db_conn, "select mail");
		if (mysqli_num_rows($result) == 1) {
			$mail = fetch_row($result);
		} else {
			torna_indietro();
		}
		
		$to = $mail[0];
		$subject = Prenotazione_cancellata;
			$message = Prenotazione_cancellata."\n"
			.Data.": ".$data."\n".
			Ora.": ".$ora."\n".
			Nome_tavolo.": ".$nome."\n".
			Numero_persone.": ".$num_persone."\n".
			Richieste_particolari.": ".$richieste."\n\n".
			ringraziamenti_email
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
		torna_indietro();
	}
	close($db_conn);
}

function mail_riepilogo_modifica($data, $ora, $nome, $num_persone, $richieste) {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($_SESSION["cod_utente"]) and !empty($data) and !empty($ora) and !empty($nome) and !empty($num_persone) and $num_persone>0) {
		$id_utente = $_SESSION["cod_utente"];
		$s="SELECT mail FROM utenti WHERE codice=$id_utente";
		$result = query($s, $db_conn, "select mail");
		if (mysqli_num_rows($result) == 1) {
			$mail = fetch_row($result);
		} else {
			torna_indietro();
		}
		
		$to = $mail[0];
		$subject = Prenotazione_modificata;
			$message = Prenotazione_modificata."\n"
			.Data.": ".$data."\n".
			Ora.": ".$ora."\n".
			Nome_tavolo.": ".$nome."\n".
			Numero_persone.": ".$num_persone."\n".
			Richieste_particolari.": ".$richieste."\n\n".
			ringraziamenti_email
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
		torna_indietro();
	}
	close($db_conn);
}

function conferma_prenotazione($cod_prenotazione, $stato) {
	$db_conn=connessione();
	if ($db_conn and !empty($cod_prenotazione) and !empty($stato) and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$sql = "UPDATE prenotazioni SET stato=$stato WHERE codice=$cod_prenotazione";
		query($sql, $db_conn, "update stato confermato");
		if ($stato == 1 and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Prenotazione_confermata."</h3>";
		} else if ($stato == 2 and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Prenotazione_rifiutata."</h3>";
		} else {
			torna_indietro();
		}
		$s = "SELECT cod_utente FROM prenotazioni WHERE codice = $cod_prenotazione";
		$result = query($s, $db_conn, "select cod_utente");
		$row = fetch_row($result);
		$s = "SELECT mail FROM utenti WHERE codice = $row[0]";
		$result = query($s, $db_conn, "select cod_utente");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		$to = $row[0];
		
		$s = "SELECT data, ora, nome, partecipanti, richieste FROM prenotazioni WHERE codice = $cod_prenotazione";
		$result = query($s, $db_conn, "select prenotazione");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		
		if($stato == 1) {
			$subject = Prenotazione_inserita;
			$message = Prenotazione_confermata.":\n"
			.Data.": ".$row[0]."\n".
			Ora.": ".$row[1]."\n".
			Nome_tavolo.": ".$row[2]."\n".
			Numero_persone.": ".$row[3]."\n".
			Richieste_particolari.": ".$row[4]."\n\n".
			ringraziamenti_email
			;
		}
		else if ($stato == 2) {
			$subject = Prenotazione_non_inserita;
			$message = Prenotazione_rifiutata.":\n"
			.Data.": ".$row[0]."\n".
			Ora.": ".$row[1]."\n".
			Nome_tavolo.": ".$row[2]."\n".
			Numero_persone.": ".$row[3]."\n".
			Richieste_particolari.": ".$row[4]."\n\n".
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
		torna_indietro();
	}
	close($db_conn);
}

function prenotazioni_utente() {
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail"]) and !empty($_SESSION["password"]) and !empty($_SESSION["cod_utente"])) {
		$cod = $_SESSION["cod_utente"];
		$s="SELECT prenotazioni.codice, prenotazioni.data, prenotazioni.ora, prenotazioni.nome, prenotazioni.partecipanti, prenotazioni.richieste, prenotazioni.stato FROM prenotazioni WHERE prenotazioni.cod_utente = $cod ORDER BY prenotazioni.data, prenotazioni.ora, prenotazioni.stato";
		$pr = query($s, $db_conn, "select visualizza_prenotazioni");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr class='prima_riga'><td>".Data."</td><td>".Ora."</td><td>".Nome_prenotazione."</td><td>".Persone."</td><td>Note</td><td>".Stato."</td></tr>";
		while ($row = fetch_row($pr)) {
			echo "<tr class='altre_righe'>";
			for ($i=1; $i<=4; $i++) {
				echo "<td>$row[$i]</td>";
			}
			echo "<td><div class='nota'>$row[5]</div></td>";
			if ($row[6] == 0) {
				echo "<td>
					<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottonePiccolo'  value='".Modifica."'>
					<input type='hidden' name='stato' value='modifica_prenotazione'>
					<input type='hidden' name='codice' value='$row[0]'>
					</form>
					<form action='prenotazioni.php' method='POST'>
					<input type='submit' class='bottonePiccolo'  value='".Cancella."'>
					<input type='hidden' name='stato' value='cancella_prenotazione'>
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
		torna_indietro();
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
		torna_indietro();
	}
	close($db_conn);
}	

function select_prenotazione_admin($codice) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($_SESSION["cod_admin"])) {
		$s="SELECT data,ora,nome,partecipanti,richieste,cod_utente FROM prenotazioni WHERE codice = $codice";
		$result = query($s, $db_conn, "select prenotazione");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
			return $row;
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
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
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
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
			$s="UPDATE prenotazioni SET data='$data', ora='$ora', nome='$nome', partecipanti=$num_persone, richieste='$richieste' WHERE codice=$codice AND cod_utente=$cod_utente";
			if (query($s, $db_conn, "update prenotazione") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".Prenotazione_modificata."</h3>";
			} else {
				torna_indietro();
			}
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function update_news($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $filename) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($titolo) and !empty($contenuto) and !empty($contenuto_en) and !empty($titolo_en)) {
		$cod_admin = $_SESSION["cod_admin"];
		$s = "SELECT cod_admin FROM news WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo update news");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_admin == $row[0]) {
			if ($filename == "") {
				$s="UPDATE news SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine=null, data=NOW(), ora=NOW() WHERE codice = $codice AND cod_admin = $cod_admin";
			} else {
				$s="UPDATE news SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine='$filename', data=NOW(), ora=NOW() WHERE codice = $codice AND cod_admin = $cod_admin";
			}
			if (query($s, $db_conn, "update news") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".News_aggiornata."</h3>";
			} else {
				torna_indietro();
			}
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
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
			if (query($s, $db_conn, "delete prenotazione") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".Prenotazione_cancellata."</h3>";
			} else {
				torna_indietro();
			}
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function delete_news($codice) {
	$db_conn=connessione();
	if ($db_conn and !empty($codice)) {
		$cod_admin = $_SESSION["cod_admin"];
		$s = "SELECT cod_admin FROM news WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo delete news");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_admin == $row[0]) {
			$s="DELETE FROM news WHERE codice = $codice AND cod_admin = $cod_admin";
			if (query($s, $db_conn, "delete news") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".News_cancellata."</h3>";
			} else {
				torna_indietro();
			}
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
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
		torna_indietro();
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
		if (query($s, $db_conn, "insert news") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".News_inserita."</h3>";
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function pagina_news() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="SELECT news.codice, news.data, news.titolo, news.testo, news.immagine, news.cod_admin, amministratori.nome, news.ora, news.testo_en, news.titolo_en FROM news,amministratori WHERE amministratori.codice = news.cod_admin ORDER BY news.data DESC, news.ora DESC";
		$result=query($s, $db_conn, "select pagina news");
		if (mysqli_num_rows($result) == 0) {
			echo "<h3 class='errore'>".No_notizie."</h3>";
			tail();
			die();
		}
		return $result;
	} else {
		torna_indietro();
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
		
		$s = "SELECT confermato FROM utenti WHERE mail='".addslashes($mail)."'";
		$result = query($s, $db_conn, "select confermato per recupero password");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		}
		
		if ($row[0] == 1) {
			$s = "UPDATE utenti SET confermato=0, password='".md5($new_pw)."' WHERE mail='".addslashes($mail)."'";
			if (query($s, $db_conn, "update password temp recupero") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".Password_inviata_mail."</h3>";
			} else {
				torna_indietro();
			}
			$to = addslashes($mail);
			$subject = Recupero_password;
			$message = la_tua_nuova_password.": $new_pw".ringraziamenti_email;
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
				echo $message."<br/>";
			}
		} else {
			torna_indietro();
		}						
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function delete_account() {
	$db_conn=connessione();
	$codice = $_SESSION["cod_utente"];
	$mail = $_SESSION["mail"];
	$password = $_SESSION["password"];
	if ($db_conn and !empty($codice) and !empty($mail) and !empty($password)) {
		$s = "DELETE FROM utenti WHERE codice=$codice AND mail='$mail' AND password='$password'";
		if (query($s, $db_conn, "delete user") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Account_cancellato."</h3>";
			$to = $mail;
			$subject = Account_cancellato;
			$message = Account_cancellato."\n".ringraziamenti_email;
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
				echo $message."<br/>";
			}
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function update_indirizzo_mail($newmail) {
	$db_conn=connessione();
	$mail = $_SESSION["mail"];
	if ($db_conn and !empty($mail) and !empty($newmail)) {
		$s = "UPDATE utenti SET mail='".addslashes($newmail)."', hash='".md5($newmail)."', confermato=0 WHERE mail='".addslashes($mail)."'";
		if (query($s, $db_conn, "cambio mail") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Indirizzo_aggiornato."<br/>".Conferma_iscrizione_cliccando_link."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
			$to = addslashes($newmail);
			$subject = cambio_mail;
			$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_nuova_mail&lang=".$_SESSION["lang"]."&hash=".md5($newmail)."\n".ringraziamenti_email;
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
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function registrazione_confermata($hash) {
	$db_conn=connessione();
	if ($db_conn and !empty($hash)) {
		$s = "UPDATE utenti SET confermato=1 WHERE hash='$hash'";
		if (query($s, $db_conn, "conferma hash") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Registrazione_riuscita."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function confermata_nuova_mail($hash) {
	$db_conn=connessione();
	if ($db_conn and !empty($hash)) {
		$s = "UPDATE utenti SET confermato=1 WHERE hash='$hash'";
		if (query($s, $db_conn, "conferma hash") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Indirizzo_aggiornato."</h3>";
			echo "<form action='prenotazioni.php' method='POST'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

?>
