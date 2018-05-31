<?php
	include("interfaccia.php");
	head();
	topbar("news");
	
	getStato();
		
	echo "<h1 class='titolo_pagina'>News</h1>";
	
	pagina_news();

	tail();
?>
