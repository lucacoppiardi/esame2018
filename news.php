<?php
	include("interfaccia.php");
	head();
	topbar("news");
	
	getStato();
		
	echo "<h1 class='titolo_pagina'>News</h1>";
	
	$result = pagina_news();
	
	while ($row=fetch_row($result)) {
		echo "<div class='notizia'>";
		echo "<h3 class='titolo_notizia' id='$row[0]'>";
		if ($_SESSION["lang"] == "en") {
			echo $row[9];
		} else {
			echo $row[2];
		}
		echo "</h3>";
		echo "<div class='contenuto_notizia'>";
		if ($row[4] != null) {
			echo "<img src='uploads/$row[4]' alt='$row[4]' class='align_news_img'>";
		}
		if ($_SESSION["lang"] == "en") {
			echo "<p class='align_news'>$row[8]</p>";
		} else {
			echo "<p class='align_news'>$row[3]</p>";
		}
		echo "</div>";
		echo "<p style='font-style: italic;'>";
		echo Pubblicata_il;
		echo " $row[1] $row[7] ";
		echo da;
		echo " $row[6]</p>";
		echo "</div>";
		echo "<hr class='separatore'>";
	}

	tail();
?>
