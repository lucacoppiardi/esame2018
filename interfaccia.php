<?php

	/* interfaccia.php
	 * 
	 * si occupa di includere i file con le stringhe per la traduzione del sito,
	 * stampa i tag di intestazione, la barra superiore ed il footer,
	 * include il file con le query SQL (libreria.php) in modo da non doverlo includere nelle altre pagine,
	 * inizia la sessione PHP,
	 * infine contiene la funzione isDebug: quando è true, sulle pagine sono mostrati dati come le query eseguite o i valori di alcuni variabili.
	 */

	/* per evitare session hijacking */
	ini_set('session.use_trans_sid', 0);
	ini_set('session.use_only_cookies', 1);
	ini_set('session.cookie_httponly', 1);
	ini_set('session.cookie_secure', 1);
	
	session_start(); // apre la sessione
	
	include("libreria.php"); // funzioni SQL che serviranno in altre pagine
	
	function isDebug() { /* se è true, le pagine stampano ad esempio le query eseguite o altre informazioni */
		return true;
	}

	function getStato() { // per controllare lo stato in cui sono (es.: inserisci prenotazione, modifica, cancella, ... )
		if (!isset($_REQUEST["stato"])) {
			$stato="";
		} else {
			$stato=$_REQUEST["stato"];
		}
		return $stato;
	}
	
	if (empty($_SESSION["lang"])) { // se nessuna lingua è stata impostata,
		$_SESSION["lang"] = "it"; // imposto nella sessione l'italiano come lingua predefinita.
	}
	if (!empty($_REQUEST["lang"])) { // se viene settata un'altra lingua,
		if ($_REQUEST["lang"] == "it" or $_REQUEST["lang"] == "en") { // controllo sia una lingua di cui ho le traduzioni,
			$_SESSION["lang"] = $_REQUEST["lang"]; // la salvo in SESSION.
			include("lang/".$_REQUEST["lang"].".php"); // includo la traduzione nel sito.
		} else { // se la lingua non ha traduzione includo quella predefinita.
			include("lang/".$_SESSION["lang"].".php");
		}
	}
	if (empty($_REQUEST["lang"])) { // in caso di lingua non specificata tengo quella predefinita.
		include("lang/".$_SESSION["lang"].".php"); 
	}
	
	function head() { // stampa i tag di intestazione
		echo "<!DOCTYPE HTML>";
		echo "<html lang=\"".$_SESSION["lang"]."\">";
		echo "<head>";
		echo "<title>Corte Ada</title>";
		echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>"; /*iso-8859-1*/
		echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
		
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">";
		echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"scripts.js\"></script>";
		echo "<link rel=\"icon\" href=\"media/logo.png\">";
		
		echo "</head>";
		echo "<body>";
	}
	
	function tail() { // stampa il footer
	
		echo "<br style='clear:both'>";
		
		if (isDebug()) {
			echo "<pre>";
			print_r($_SESSION);
			echo "PHPSESSID: ".session_id();
			echo "</pre>";
		}

		echo "</div>";

		echo "<footer id='footer'>";
		echo "<p class='footer_p'>".realizzato_da."</p>";
		echo "<p class='footer_p'><a href='admin.php' class='footer_a'>".Amministrazione."</a></p>";
		echo "</footer>";
		
		echo "</div>";
		echo "</body>";
		echo "</html>";
		
	}

	function topbar($pagina_attiva) { // stampa la barra di navigazione superiore, evidenziando la pagina aperta
	
		echo "<div id='pagina'>";
		echo "<nav class=\"menu\" id=\"barra\">";
		echo "<div id=\"logo\">";
		echo "<a id=\"logo_home\" href=\"index.php\">";
		echo "<p id=\"logo_txt\">Corte Ada</p>";
		echo "</a>";
		echo "</div>";
		echo "<div class=\"links\">";
		echo "<a href=\"javascript:void(0);\" class=\"icon\" onclick=\"menu()\">&#9776;</a>";
		
		echo "<a ";
		if ($pagina_attiva == "contatti") {
			echo " class='active_link' ";
		}
		echo "href=\"contatti.php\">".Contatti."</a>";
		
		echo "<a ";
		if ($pagina_attiva == "piatti") {
			echo " class='active_link' ";
		}
		echo "href=\"piatti.php\">".Piatti."</a>";
		
		echo "<a ";
		if ($pagina_attiva == "news") {
			echo " class='active_link' ";
		}
		echo "href=\"news.php\">News</a>";
		
		echo "<a ";
		if ($pagina_attiva == "prenotazioni") {
			echo " class='active_link' ";
		}
		echo "href=\"prenotazioni.php\">".Prenota."</a>";
				
		echo "<a>";
		echo "<form action='index.php' method='post' class='buttonLingua'>";
		echo "<input type='hidden' name='lang' value='it'/>";
		echo "<input type='submit' value='' style=\"background:url('media/it.png'); background-size:cover; width:30px; height:18px; border:none;\">";
		echo "</form>";
		echo "</a>";
		
		echo "<a>";
		echo "<form action='index.php' method='post' class='buttonLingua'>";
		echo "<input type='hidden' name='lang' value='en'/>";
		echo "<input type='submit' value='' style=\"background:url('media/en.png'); background-size:cover; width:40px; height:18px; border:none;\">";
		echo "</form>";
		echo "</a>";
						
		echo "</div>";
		echo "</nav>";
		
		echo "<div id='content' class='content'>";
	}

?>
