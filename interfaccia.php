<?php

	session_start();
	
	ini_set('session.use_only_cookies', 1);
	ini_set('session.cookie_httponly', 1);
	ini_set('session.cookie_secure', 1);

	include("libreria.php");
	
	function isDebug() {
		return false;
	}

	function getStato() {
		if (!isset($_REQUEST["stato"])) {
			$stato="";
		} else {
			$stato=$_REQUEST["stato"];
		}
		return $stato;
	}
	
	if (empty($_SESSION["lang"])) {
		$_SESSION["lang"] = "it"; // ita di default
	}
	
	if (!empty($_REQUEST["lang"])) { // se viene settata un'altra lingua...
		$lang = $_REQUEST["lang"]; // la salvo...
		if ($lang == "it" or $lang == "en") { // controllo sia una lingua di cui ho le traduzioni...
			$_SESSION["lang"] = $lang; // la salvo in SESSION
			include_once("lang/$lang.php"); // includo la traduzione nel sito
		}
	} else if(empty($_REQUEST["lang"])) { // in caso di lingua non specificata tengo quella pre-impostata
		$lang = $_SESSION["lang"];
		include_once("lang/$lang.php"); 
	}

	function head() {
		echo "<!DOCTYPE HTML>";
		echo "<html lang=\"it\">";
		echo "<head>";
		echo "<title>Corte Ada</title>";
		echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\"/>"; /*iso-8859-1*/
		echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">";
		echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"scripts.js\"></script>";
		//echo "<link href=\"https://fonts.googleapis.com/css?family=Cookie\" rel=\"stylesheet\"/>";
		echo "<link rel=\"icon\" href=\"media/logo.png\">";
		echo "</head>";
		echo "<body onload='resizeFooter();'>";
	}
	
	function tail() {
	
		if (isDebug()) {
			echo "<p style='font-family: monospace'>";
			print_r($_SESSION);
			echo "PHPSESSID: ".session_id();
			echo "</p>";
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

	function topbar($pagina_attiva) {
	
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
		
		/*
		echo "<a ";
		if ($pagina_attiva == "admin") {
			echo " class='active_link' ";
		}
		echo "href=\"admin.php?lang=".getLang()."\" style='padding-top: 11px; padding-bottom:5px'><img src='media/lock.png' alt='admin' width='32px' heigth='32px'></a>";
		*/
		
		//echo "<a href=\"?lang=it\"><img src='it.png' width='20px' heigth='20px' alt='it'></a>";
		
		//echo "<a href=\"?lang=en\"><img src='en.png' width='24px' heigth='24px' alt='en'></a>";
				
		echo "</div>";
		echo "</nav>";
		
		echo "<div id='content' class='content'>";
	}

?>
