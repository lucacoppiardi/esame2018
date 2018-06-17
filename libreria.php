<?php

/* libreria.php
 * 
 * In questo file sono contenute tutte le funzioni SQL usate dal sito.
 */ 

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
		echo "<p style='font-family: monospace'>".$tab."<br/>".$s."</p>"; // se il debug è attivo mostro la sintassi della query eseguita
	}
	if (!$request=mysqli_query($conn, $s)) {
		if (isDebug()) {
			echo "<h3>".mysqli_error($conn)." (".mysqli_errno($conn).")</h3>"; // se il debug è attivo mostro l'errore e il suo codice
		}
		if (mysqli_errno($conn) == 1146 or mysqli_errno($conn) == 1051) {
			echo "<h3 class='errore'>".tabella_inesistente."</h3>"; // la tabella non esiste
		}
		torna_indietro(); // pulsante per tornare indietro, in caso la query non fosse eseguita
	}
	return $request;
}

function torna_indietro() { /* se la query non può essere eseguita, termina e mostra un messaggio di errore con un pulsante per tornare indietro */
	echo "<h2 class='errore'>".Errore_query.". </h2>";
	echo "<form action='".basename($_SERVER['PHP_SELF'])."' method='post'>
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
		stato INT(1),
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

function crea_tab_piatti() {
	$db_conn=connessione();
	if ($db_conn) {
		$s="CREATE TABLE IF NOT EXISTS piatti (
		codice INT(11) NOT NULL AUTO_INCREMENT,
		cod_admin INT(11) NOT NULL,
		titolo VARCHAR(255) NOT NULL UNIQUE,
		titolo_en VARCHAR(255) NOT NULL UNIQUE,
		testo VARCHAR(255) NOT NULL,
		testo_en VARCHAR(255) NOT NULL,
		tipo INT(11) NOT NULL,
		immagine VARCHAR(255),
		prezzo FLOAT(8,2),
		PRIMARY KEY (codice),
		FOREIGN KEY (cod_admin) REFERENCES amministratori(codice)
		ON DELETE CASCADE
		);";
		if (!query($s, $db_conn, "creata piatti")) {
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
		$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
		$s = "INSERT IGNORE INTO amministratori (codice,mail,password,nome,telefono,ultimo_accesso) VALUES (1,'lucacoppiardi@altervista.org', MD5('luca".$salt."'), 'Luca', '1234121212', NOW());"; /* per comodità creo automaticamente il mio utente */
		if (!query($s, $db_conn, "insert admin")) {
			torna_indietro();
		}
	} else {
		echo Errore_connessione_database."<br/>";
	}
	close($db_conn);
}

function crea_utente($mail,$password,$nome,$telefono) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($password) and !empty($nome) and !empty($telefono)) {
		$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
		$s="INSERT INTO utenti (mail,password,nome,telefono,data_iscrizione,hash) VALUES ('$mail','$password','$nome','$telefono',NOW(),'".md5($mail.$salt)."') ";
		$result = query($s, $db_conn, "insert utenti"); // inserisce l'utente nel db
		
		if ($result != false and mysqli_affected_rows($db_conn) == 1) { // se l'utente è inserito, mando la mail per confermare la sua iscrizione
			$to = $mail;
			$subject = grazie_iscrizione;
			$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_registrazione&lang=".$_SESSION["lang"]."&hash=".md5($mail.$salt)."\n\n".ringraziamenti_email;
			$headers = "From: lucacoppiardi@altervista.org";

			$esito_mail = mail($to,$subject,$message,$headers);
			
			if (!isDebug()) {
				echo $message; // mostro comunque il link perchè l'invio di mail da Altervista non sempre funziona correttamente
			}
			
			if (isDebug()) { // se il debug è attivo mostro la mail inviata
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
			torna_indietro(); // l'utente non è stato inserito, torno alla pagina di registrazione
		}
				
	} else {
		torna_indietro(); // se ci sono campi vuoti, torno alla pagina di registrazione
	}
	close($db_conn);
}

function login($mail, $pass) {
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($pass)) {
		$s="SELECT confermato FROM utenti WHERE mail='$mail' AND password='$pass'";
		$result=query($s, $db_conn, "select confermato login"); // controllo se l'utente ha confermato l'iscrizione
		if (mysqli_num_rows($result) == 1) { // controllo che un utente solo voglia loggarsi (per evitare SQL injection)
			$row = fetch_row($result);
			if ($row[0] != 1) { // se l'utente non è confermato, rimando la mail con il link per confermare l'iscrizione
				echo "<h3 class='avviso'>".Conferma_iscrizione_cliccando_link."</h3>";
				
				$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
				
				$to = $mail;
				$subject = grazie_iscrizione;
				$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_registrazione&lang=".$_SESSION["lang"]."&hash=".md5($mail.$salt)."\n".ringraziamenti_email;
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
				
				echo "<form action='prenotazioni.php' method='post'>
						<input type='submit' class='bottone'  value='OK'>
					</form>";
					
				tail();
				die();
				
			} else {
				$s="SELECT codice,mail,ultimo_accesso,password FROM utenti WHERE mail='$mail' AND password='$pass'";
				$result=query($s, $db_conn, "select login");
				if (mysqli_num_rows($result) == 1) { // l'utente è stato selezionato
					$row = fetch_row($result);
					$sql = "UPDATE utenti SET ultimo_accesso = NOW() WHERE mail='$mail' AND password='$pass'"; // aggiorno il suo ultimo accesso
					query($sql, $db_conn, "update ultimo accesso");
					return $row; // restituisco i dati selezionati alla pagina delle prenotazioni
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

function admin_login($mail, $pass) { /* consente il login dell'amministrazione */
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
		torna_indietro();
	}
	close($db_conn);
}

function visualizza_utenti() { /* mostra all'amministrazione i dati degli utenti registrati */
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
				<form action='admin.php' method='post'>
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

function delete_account_admin($id) { /* consente all'amministrazione di cancellare un account */
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

function visualizza_prenotazioni() { /* mostra all'amministrazione le prenotazioni inserite, con la possibilità di approvarle/respingerle */
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
				<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='conferma_scelta'>
					<input type='submit' class='bottonePiccolo'  name='accetta' value='".Accetta."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				<form action='admin.php' method='post'>
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

function visualizza_news() { /* mostra all'amministrazione le notizie pubblicate, consentendone la modifica */
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
			echo "<td><div class='nota'><a href='news.php#$row[0]'>";
			if ($_SESSION["lang"] == "en") {
				echo $row[5];
			} else {
				echo $row[3];
			}
			echo "</a></div></td>";
			echo "<td><div class='nota'>";
			if ($_SESSION["lang"] == "en") {
				echo $row[6];
			} else {
				echo $row[4];
			}
			echo "</div></td>";
			echo "<td>
				<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='modifica_news'>
					<input type='submit' class='bottonePiccolo'  value='".Modifica."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				<form action='admin.php' method='post'>
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

function visualizza_piatti() { /* mostra all'amministrazione i piatti pubblicati, permettendone la modifica */
	$db_conn=connessione();
	if ($db_conn and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		$cod_admin = $_SESSION["cod_admin"];
		$s="SELECT codice, titolo, testo, titolo_en, testo_en, prezzo, tipo FROM piatti WHERE cod_admin = $cod_admin ORDER BY tipo ASC";
		$result=query($s, $db_conn, "select visualizza_piatti");
		echo "<div id='scroll_tabella'>";
		echo "<table class='dati_stampati'>";
		echo "<tr class='prima_riga'><td>".Titolo."</td><td>".Testo."</td><td>".Prezzo."</td><td>".Tipo."</td><td></td></tr>";
		while ($row=fetch_row($result)) {	
			echo "<tr class='altre_righe'>";
			echo "<td><a href='piatti.php#$row[0]'>";
			if ($_SESSION["lang"] == "en") {
				echo $row[3];
			} else {
				echo $row[1];
			}
			echo "</a></td>";
			echo "<td><div class='nota'>";
			if ($_SESSION["lang"] == "en") {
				echo $row[4];
			} else {
				echo $row[2];
			}
			echo "</div></td>";
			echo "<td>$row[5] &euro;</td>";
			echo "<td>";
				if ($row[6]==1) echo Antipasti;
				if ($row[6]==2) echo PrimoPiatto;
				if ($row[6]==3) echo SecondoPiatto;
				if ($row[6]==4) echo Contorno;
				if ($row[6]==5) echo Dolce;
				if ($row[6]==6) echo Altro;
			echo "</td>";
			echo "<td>
				<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='modifica_piatto'>
					<input type='submit' class='bottonePiccolo'  value='".Modifica."'>
					<input type='hidden' name='codice' value='$row[0]'>
				</form>
				<form action='admin.php' method='post'>
					<input type='hidden' name='stato' value='cancella_piatto'>
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

function inserisci_prenotazione($data, $ora, $nome, $partecipanti, $richieste) { /* inserisce le prenotazioni nel db */
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

function mail_riepilogo($data, $ora, $nome, $num_persone, $richieste) { /* invia un promemoria al cliente coi dati della prenotazione appena inserita */
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
			riepilogo_email."\n\n".
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

function mail_riepilogo_cancella($data, $ora, $nome, $num_persone, $richieste) { /* invia un promemoria al cliente coi dati della prenotazione che ha appena cancellato */
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
			$message = Prenotazione_cancellata."\n\n"
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

function mail_riepilogo_modifica($data, $ora, $nome, $num_persone, $richieste) { /* invia un promemoria al cliente coi dati della prenotazione che ha appena modificato */
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
			$message = Prenotazione_modificata."\n\n"
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

function conferma_prenotazione($cod_prenotazione, $stato, $msg) { /* l'amministrazione ha approvato/respinto una prenotazione, il cliente riceverà una mail di notifica e l'amministrazione può allegare un messaggio */
	$db_conn=connessione();
	if ($db_conn and !empty($cod_prenotazione) and !empty($stato) and !empty($_SESSION["mail_admin"]) and !empty($_SESSION["password_admin"]) and !empty($_SESSION["cod_admin"])) {
		
		$sql = "UPDATE prenotazioni SET stato=$stato WHERE codice=$cod_prenotazione"; // aggiorno lo stato della prenotazione sul db
		query($sql, $db_conn, "update stato confermato");
		if ($stato == 1 and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Prenotazione_confermata."</h3>";
		} else if ($stato == 2 and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Prenotazione_rifiutata."</h3>";
		} else {
			torna_indietro();
		}
		
		$s = "SELECT cod_utente FROM prenotazioni WHERE codice = $cod_prenotazione"; // seleziono il codice dell'utente dalla prenotazione per ottenere la sua mail
		$result = query($s, $db_conn, "select cod_utente");
		$row = fetch_row($result);
		$s = "SELECT mail FROM utenti WHERE codice = $row[0]"; // con il codice selezionato prima, seleziono la mail del cliente
		$result = query($s, $db_conn, "select cod_utente");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		$to = $row[0];
		
		$s = "SELECT data, ora, nome, partecipanti, richieste FROM prenotazioni WHERE codice = $cod_prenotazione"; // seleziono i dati della prenotazione accettata/rifiutata
		$result = query($s, $db_conn, "select prenotazione");
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		
		if ($stato == 1) { // invio una mail di notifica al cliente
			$subject = Prenotazione_inserita;
			$message = Prenotazione_confermata.":\n\n"
			.Data.": ".$row[0]."\n".
			Ora.": ".$row[1]."\n".
			Nome_tavolo.": ".$row[2]."\n".
			Numero_persone.": ".$row[3]."\n".
			Richieste_particolari.": ".$row[4]."\n\n".
			msg_per_cliente.": ".$msg."\n\n".
			ringraziamenti_email
			;
		} else if ($stato == 2) {
			$subject = Prenotazione_non_inserita;
			$message = Prenotazione_rifiutata.":\n\n"
			.Data.": ".$row[0]."\n".
			Ora.": ".$row[1]."\n".
			Nome_tavolo.": ".$row[2]."\n".
			Numero_persone.": ".$row[3]."\n".
			Richieste_particolari.": ".$row[4]."\n\n".
			msg_per_cliente.": ".$msg."\n\n".
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

function prenotazioni_utente() { /* mostra all'utente le sue prenotazioni e ne permette la gestione */
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
					<form action='prenotazioni.php' method='post'>
					<input type='submit' class='bottonePiccolo'  value='".Modifica."'>
					<input type='hidden' name='stato' value='modifica_prenotazione'>
					<input type='hidden' name='codice' value='$row[0]'>
					</form>
					<form action='prenotazioni.php' method='post'>
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

function select_prenotazione($codice) { // seleziona i dati di una prenotazione in base al codice e li ritorna allo stato (modifica, cancella, ...) che li ha richiesti
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

function select_prenotazione_admin($codice) { // seleziona i dati di una prenotazione in base al codice per permettere all'amministrazione di accettarla/respingerla
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

function select_news($codice) { // seleziona i dati di una news in base al codice per la sua gestione
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

function select_piatto($codice) { // seleziona i dati di un piatto in base al codice per la sua gestione
	$db_conn=connessione();
	$cod_admin = $_SESSION["cod_admin"];
	if ($db_conn and !empty($codice) and !empty($cod_admin)) {
		$s="SELECT titolo,testo,prezzo,immagine,cod_admin,testo_en,titolo_en,tipo FROM piatti WHERE codice = $codice AND cod_admin = $cod_admin";
		$result = query($s, $db_conn, "select piatto");
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

function update_prenotazione($codice, $data, $ora, $nome, $num_persone, $richieste) { // aggiorna i dati di una prenotazione  (controllando il codice utente)
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

function update_news($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $filename) { // aggiorna una notizia (controllando il codice amministratore)
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

function update_piatto($codice, $titolo, $contenuto, $contenuto_en, $titolo_en, $filename, $prezzo, $tipo) { // aggiorna un piatto (controllando il codice amministratore)
	$db_conn=connessione();
	if ($db_conn and !empty($codice) and !empty($titolo) and !empty($contenuto) and !empty($contenuto_en) and !empty($titolo_en) and !empty($prezzo) and !empty($tipo)) {
		$cod_admin = $_SESSION["cod_admin"];
		$s = "SELECT cod_admin FROM piatti WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo update piatti");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_admin == $row[0]) {
			if ($filename == "") {
				$s="UPDATE piatti SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine=null, prezzo=$prezzo, tipo=$tipo WHERE codice = $codice AND cod_admin = $cod_admin";
			} else {
				$s="UPDATE piatti SET titolo='$titolo', testo='$contenuto', testo_en='$contenuto_en', titolo_en='$titolo_en', immagine='$filename', prezzo=$prezzo, tipo=$tipo WHERE codice = $codice AND cod_admin = $cod_admin";
			}
			if (query($s, $db_conn, "update piatto") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".piatto_modificato."</h3>";
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

function delete_prenotazione($codice) { // cancella una prenotazione (controllando il codice utente)
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

function delete_news($codice) { // cancella una notizia (controllando il codice amministratore)
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

function delete_piatto($codice) { // cancella un piatto (controllando il codice amministratore)
	$db_conn=connessione();
	if ($db_conn and !empty($codice)) {
		$cod_admin = $_SESSION["cod_admin"];
		$s = "SELECT cod_admin FROM piatti WHERE codice=$codice";
		$result=query($s, $db_conn, "select per controllo delete piatto");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		if ($cod_admin == $row[0]) {
			$s="DELETE FROM piatti WHERE codice = $codice AND cod_admin = $cod_admin";
			if (query($s, $db_conn, "delete piatti") and mysqli_affected_rows($db_conn)==1) {
				echo "<h3 class='avviso'>".piatto_cancellato."</h3>";
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

function reset_password($old_pw, $new_pw) { // cambia la password di un utente (controllando quella attuale)
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
			if (query($s, $db_conn, "update password") and mysqli_affected_rows($db_conn)==1) {
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

function insert_news($titolo, $contenuto, $contenuto_en, $titolo_en, $filename) { // inserisce una notizia nel db
	$db_conn=connessione();
	$cod_admin = $_SESSION["cod_admin"];
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

function insert_piatto($titolo, $contenuto, $contenuto_en, $titolo_en, $filename, $prezzo, $tipo) { // inserisce un piatto nel db
	$db_conn=connessione();
	$cod_admin = $_SESSION["cod_admin"];
	if ($db_conn and !empty($cod_admin) and !empty($titolo) and !empty($contenuto) and !empty($contenuto_en) and !empty($titolo_en) and !empty($prezzo) and !empty($tipo)) {
		if ($filename == "") {
			$s="INSERT INTO piatti (cod_admin, titolo, testo, testo_en, titolo_en, immagine, prezzo, tipo) VALUES ($cod_admin, '$titolo', '$contenuto', '$contenuto_en', '$titolo_en', null, $prezzo, $tipo)";
		} else {
			$s="INSERT INTO piatti (cod_admin, titolo, testo, testo_en, titolo_en, immagine, prezzo, tipo) VALUES ($cod_admin, '$titolo', '$contenuto', '$contenuto_en', '$titolo_en', '$filename', $prezzo, $tipo)";
		}
		if (query($s, $db_conn, "insert piatto") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".piatto_inserito."</h3>";
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function pagina_news() { // ritorna alla pagina news.php le notizie inserite nel database, o avvisa se non ci sono notizie pubblicate
	$db_conn=connessione();
	if ($db_conn) {
		$s="SELECT news.codice, news.data, news.titolo, news.testo, news.immagine, news.cod_admin, amministratori.nome, news.ora, news.testo_en, news.titolo_en, amministratori.mail FROM news,amministratori WHERE amministratori.codice = news.cod_admin ORDER BY news.data DESC, news.ora DESC";
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

function pagina_piatti() { // ritorna alla pagina piatti.php i piatti inseriti nel database, o avvisa se non ci sono piatti pubblicati
	$db_conn=connessione();
	if ($db_conn) {
		$s="SELECT codice, titolo, titolo_en, testo, testo_en, prezzo, tipo, immagine FROM piatti ORDER BY tipo ASC";
		$result=query($s, $db_conn, "select pagina piatti");
		if (mysqli_num_rows($result) == 0) {
			echo "<h3 class='errore'>".No_piatti."</h3>";
			tail();
			die();
		}
		return $result;
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function mail_recupero_password($mail) { // invia all'utente un link dove può recuperare la password se l'ha dimenticata
	$db_conn=connessione();
	if ($db_conn and !empty($mail)) {
		
		$s = "SELECT hash FROM utenti WHERE mail='$mail'"; // controllo l'hash se corrisponde a quello nel db
		$result = query($s, $db_conn, "select hash per recupero password");
		$row = null;
		if (mysqli_num_rows($result) == 1) {
			$row = fetch_row($result);
		} else {
			torna_indietro();
		}
		
		$to = $mail;
		$subject = Recupero_password;
		$message = la_tua_nuova_password.": ".link_al_sito."prenotazioni.php?stato=reset_password_link&mail=$mail&hash=$row[0]".ringraziamenti_email;
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
	close($db_conn);
}

function reset_password_link($mail, $hash, $new_pw) { // controlla mail ed hash ed esegue il cambio password
	$db_conn=connessione();
	if ($db_conn and !empty($mail) and !empty($hash) and !empty($new_pw)) {
		$s="UPDATE utenti SET password='$new_pw' WHERE mail='$mail' AND hash='$hash'";
		if (query($s, $db_conn, "update password") and mysqli_affected_rows($db_conn)==1) {
			return true;
		} else {
			return false;
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

function delete_account() { // permette al cliente di disiscriversi
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

function update_indirizzo_mail($newmail) { // permette all'utente di aggiornare la propria mail, dopo il cambio deve confermare il nuovo indirizzo cliccando un link ricevuto
	$db_conn=connessione();
	$mail = $_SESSION["mail"];
	if ($db_conn and !empty($mail) and !empty($newmail)) {
		$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
		$s = "UPDATE utenti SET mail='$newmail', hash='".md5($newmail.$salt)."', confermato=0 WHERE mail='$mail'";
		if (query($s, $db_conn, "cambio mail") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Indirizzo_aggiornato."<br/>".Conferma_iscrizione_cliccando_link."</h3>";
			echo "<form action='prenotazioni.php' method='post'>
					<input type='hidden' name='stato' value='accedi'>
					<input type='submit' class='bottone'  value='OK'>
				</form>";
				
			$salt = "3yRqgiTjp0ftpePtFLN5qWZtAHjx6S";
			
			$to = $newmail;
			$subject = cambio_mail;
			$message = conferma_a_questo_link.": ".link_al_sito."prenotazioni.php?stato=conferma_nuova_mail&lang=".$_SESSION["lang"]."&hash=".md5($newmail.$salt)."\n".ringraziamenti_email;
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

function registrazione_confermata($hash) { // conferma l'indirizzo dell'utente e gli permette di accedere al sito
	$db_conn=connessione();
	if ($db_conn and !empty($hash)) {
		$s = "UPDATE utenti SET confermato=1 WHERE hash='$hash'";
		if (query($s, $db_conn, "conferma hash") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Registrazione_riuscita."</h3>";
			echo "<form action='prenotazioni.php' method='post'>
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

function confermata_nuova_mail($hash) { // conferma il nuovo indirizzo dell'utente e gli permette di accedere al sito
	$db_conn=connessione();
	if ($db_conn and !empty($hash)) {
		$s = "UPDATE utenti SET confermato=1 WHERE hash='$hash'";
		if (query($s, $db_conn, "conferma hash") and mysqli_affected_rows($db_conn)==1) {
			echo "<h3 class='avviso'>".Indirizzo_aggiornato."</h3>";
			echo "<form action='prenotazioni.php' method='post'>
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

function mail_form_contatti($mail, $oggetto, $messaggio) { // invia all'amministrazione i messaggi inviati dal form Contattaci della pagina Info
	if (!empty($mail) and !empty($oggetto) and !empty($messaggio)) {
		
		$to = "lucacoppiardi@altervista.org";
		$subject = "[".Contattaci."]: ".$oggetto;
		$message = $messaggio;
		$headers = "From: ".$mail;
		
		$esito_mail = mail($to,$subject,$message,$headers);
		
		if ($esito_mail) {
			echo "<h3 class='avviso'>Mail ".inviata."</h3>";
		} else {
			torna_indietro();
		}
		
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
}


function select_dati_personali() { /* seleziona i dati personali dell'utente per la modifica */
	$db_conn=connessione();
	$cod = $_SESSION["cod_utente"];
	if ($db_conn and !empty($cod)) {
		$s="SELECT nome, telefono FROM utenti WHERE codice = $cod";
		$result = query($s, $db_conn, "select dati utente");
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

function update_dati_personali($nome, $telefono) { /* aggiorna i dati personali dell'utente */
	$db_conn=connessione();
	$cod = $_SESSION["cod_utente"];
	if ($db_conn and !empty($cod)) {
		$s="UPDATE utenti SET nome='$nome', telefono='$telefono' WHERE codice = $cod";
		if (query($s, $db_conn, "update dati utente") and mysqli_affected_rows($db_conn) == 1) {
			echo "<h3 class='avviso'>".Dati_aggiornati."</h3>";
		} else {
			torna_indietro();
		}
	} else {
		torna_indietro();
	}
	close($db_conn);
}

?>
