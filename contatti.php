<?php
	include("interfaccia.php");
	head();
	topbar("contatti");
	
	echo "<h1 class='titolo_pagina'>".Contatti."</h1>";
	
	echo "<div>";
	echo "<p><strong>".Indirizzo.": </strong>Cimbriolo (Mantova)</p>";
	echo "<p><strong>".Telefono.": </strong><a href='tel:+39-0376-123456'>+39 0376 123456</a></p>";
	echo "<p><strong>Mail: </strong><a href='mailto:lucacoppiardi@altervista.org'>lucacoppiardi@altervista.org</a></p>";
	echo "</div>";
	
	echo clicca_mappa;
	echo "<a href='https://www.google.com/maps/dir//Cimbriolo,+Provincia+di+Mantova/'>".Indicazioni."</a>";
	echo "<div id='scroll_tabella'>";
	echo "<a href='https://www.google.com/maps/place/Cimbriolo+MN/'>
		<img class='mappa' src='media/cartina.png' alt='google maps'>
		</a>";
	echo "<br/>";
	echo "</div>";
	
	echo "<h3>".Contattaci."</h3>";
	echo "<form action='contatti.php' method='post'>";
	echo "<input type='hidden' name='stato' value='form_contatti'>";
	echo "<label for='mail'>Mail: </label>";
	echo "<input type='email' name='mail' id='mail' placeholder='Mail' required>";
	echo "<label for='oggetto'>".Oggetto.": </label>";
	echo "<input type='text' name='oggetto' id='oggetto' placeholder='".Oggetto."' required>";
	echo "<label for='messaggio'>".Messaggio.": </label>";
	echo "<textarea id='messaggio' name='messaggio' rows='6' cols='40' maxlength='250' placeholder='".Messaggio."' required></textarea>";
	echo "<input type='submit' value='".Invia." mail' class='bottone'>";
	echo "</form>";
	
	switch (getStato()) {
		case "form_contatti":
			$mail = htmlentities($_REQUEST["mail"], ENT_QUOTES);
			$oggetto = htmlentities($_REQUEST["oggetto"], ENT_QUOTES);
			$messaggio = htmlentities($_REQUEST["messaggio"], ENT_QUOTES);
			mail_form_contatti($mail, $oggetto, $messaggio);
			break;
		
		default:
			break;
	}
	
	tail();
?>
