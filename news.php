<?php

	/* news.php
	 * 
	 * In questa pagina sono mostrate le notizie pubblicate dall'amministrazione
	 */
	
	include("interfaccia.php");
	head();
	topbar("news");

	echo "<h1 class='titolo_pagina'>News</h1>";
	
	$result = pagina_news();
	
	while ($row=fetch_row($result)) {
		echo "<div class='notizia'>";
		echo "<a href='#$row[0]'><h3 class='titolo_sezione' id='$row[0]'>";
		if ($_SESSION["lang"] == "en") {
			echo $row[9];
		} else {
			echo $row[2];
		}
		echo "</h3></a>";
		echo "<div class='contenuto_notizia'>";
		if ($row[4] != null) {
			echo "<a href='$row[4]'><img src='$row[4]' alt='$row[4]' class='align_news_img'></a>";
		}
		if ($_SESSION["lang"] == "en") {
			echo "<p class='align_news'>$row[8]</p>";
		} else {
			echo "<p class='align_news'>$row[3]</p>";
		}
		echo "</div>";
		echo "<address>";
		echo Pubblicata_il;
		echo " $row[1] $row[7] ";
		echo da;
		echo " <a href='mailto:$row[10]?subject=".subject_news." ".$row[0]."'>$row[6]</a></address>";
		echo "</div>";
		echo "<hr class='separatore'>";
	}

	tail();
?>
