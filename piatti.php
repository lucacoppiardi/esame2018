<?php
	include("interfaccia.php");
	head();
	topbar("piatti");
	
	echo "<h1 class='titolo_pagina'>".Piatti."</h1>";
	
	echo "<h3 class='avviso'>".testo_piatti."</h3>";
	
	$result = pagina_piatti();
	
	/*  0codice, 1titolo, 2titolo_en, 3testo, 4testo_en, 5prezzo, 6tipo, 7immagine */
	
	$piatti = array();
	$i = 0;
	
	while ($row=fetch_row($result)) {
		$piatti[$i] = $row;
		$i++;
	}
	
	$cont = count($piatti);

	//echo "<pre>";print_r($piatti);echo "</pre>";
	
	echo "<div id='scroll_tabella'>";
	echo "<table style='width: 100%; border-collapse: collapse; padding: 5px; text-align:center;'>";
	
	for ($i=0; $i<$cont; $i++) {
		
		if ($i==0) {
			echo "<tr>";
			echo "<td colspan='4'>";
			echo "<h3 class='titolo_sezione'>";
			if ($piatti[$i][6]==1) echo Antipasti;
			if ($piatti[$i][6]==2) echo PrimoPiatto;
			if ($piatti[$i][6]==3) echo SecondoPiatto;
			if ($piatti[$i][6]==4) echo Contorno;
			if ($piatti[$i][6]==5) echo Dolce;
			if ($piatti[$i][6]==6) echo Altro;
			echo "</h3>";
			echo "</td>";
			echo "</tr>";
		} else if (array_key_exists($i+1, $piatti)) {
			if ($piatti[$i][6] != $piatti[$i-1][6]) {
				echo "<tr>";
				echo "<td colspan='4'>";
				echo "<h3 class='titolo_sezione'>";
				if ($piatti[$i][6]==1) echo Antipasti;
				if ($piatti[$i][6]==2) echo PrimoPiatto;
				if ($piatti[$i][6]==3) echo SecondoPiatto;
				if ($piatti[$i][6]==4) echo Contorno;
				if ($piatti[$i][6]==5) echo Dolce;
				if ($piatti[$i][6]==6) echo Altro;
				echo "</h3>";
				echo "</td>";
				echo "</tr>";
			}
		}
		
		echo "<tr class='riga_piatto'>";
		
		echo "<td width='200px'>";
		if (!empty($piatti[$i][7])) {
			echo "<a href='".$piatti[$i][7]."'><img class='foto_piatto_tab' src='".$piatti[$i][7]."' alt='".$piatti[$i][1]." - ".$piatti[$i][2]."'></a>";
		}
		echo "</td>";
		
		if ($_SESSION["lang"] == "en") {
			echo "<td><p class='margine_tab_piatti'><strong>".$piatti[$i][2]."</strong></p></td>";
			echo "<td><p class='margine_tab_piatti'>".$piatti[$i][4]."</p></td>";
		} else {
			echo "<td><p class='margine_tab_piatti'><strong>".$piatti[$i][1]."</strong></p></td>";
			echo "<td><p class='margine_tab_piatti'>".$piatti[$i][3]."</p></td>";
		}
		echo "<td><p class='margine_tab_piatti'>".$piatti[$i][5]." &euro;"."</p></td>";
		
		echo "</tr>";
		
	}
	echo "</table>";
	echo "</div>";
	
	tail();
?>
