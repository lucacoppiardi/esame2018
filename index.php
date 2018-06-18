<?php

	/* index.php
	 * 
	 * La pagina iniziale contiene un breve testo introduttivo
	 * ed uno slideshow di immagini
	 */

	include("interfaccia.php");
	head();
	topbar("home");
	
	echo "<div id='titolo_home'>";
	echo "<h1 class='titolo_big'>Corte Ada</h1>";
	echo "<h2 class='testo_titolo'>".testo_benvenuto."</h2>";
	echo "<p class='paragrafo_home'>".testo_home."</p>";
	echo "</div>";
	
	$files = glob("media/foto/*.*");
	$nomi = array();
	for ($i=0; $i<count($files); $i++) {
		$nomi[$i] = $files[$i];
	}
	shuffle($nomi);
	
	if (isDebug()) {
		$arr = json_encode(print_r($nomi, true));
		echo "<script>console.log('$arr');</script>";
	}
	
	echo "<script>";
	$js_array = json_encode($nomi);
	echo "var javascript_array = ". $js_array . ";\n";
	echo "
	var i = 0;
	var girando = true;
	var len = ".count($nomi).";
	function gira(verso) {
		if (girando == true) {
			i--;
			girando = false;
		}
		clearInterval(timer);
		var j = 0;
		for (j=0; j<len; j++) {
			document.getElementById('img_'+j).style.backgroundColor = '';
			document.getElementById('img_'+j).style.padding = '';
		}
		if (verso == 'sx') {
			if (i>0) {
				i--;
			} else {
				i = len-1;
			}
			document.getElementById('immagine').src = javascript_array[i];
			document.getElementById('immagine_mobile').src = javascript_array[i];
			document.getElementById('img_'+i).style.backgroundColor = 'green';
			document.getElementById('img_'+i).style.padding = '10px';
		}
		if (verso == 'dx') {
			if (i<(len-1)) {
				i++;
			} else {
				i=0;
			}
			document.getElementById('immagine').src = javascript_array[i];
			document.getElementById('immagine').src = javascript_array[i];
			document.getElementById('immagine_mobile').src = javascript_array[i];
			document.getElementById('img_'+i).style.backgroundColor = 'green';
			document.getElementById('img_'+i).style.padding = '10px';
		}
	}";
	echo "
	function cambia_immagine(num) {
		i = num;
		clearInterval(timer);
		for (j=0; j<len; j++) {
			if (j!=i) {
				document.getElementById('img_'+j).style.backgroundColor = '';
				document.getElementById('img_'+j).style.padding = '';
			}
		}
		document.getElementById('img_'+i).style.backgroundColor = 'green';
		document.getElementById('img_'+i).style.padding = '10px';
		document.getElementById('immagine_mobile').src = javascript_array[i];
		document.getElementById('immagine').src = javascript_array[i];
	}
	function gira_automatico() {
		var girando = true;
		if (i==len) {
			i=0;
			document.getElementById('img_'+(len-1)).style.backgroundColor = '';
			document.getElementById('img_'+(len-1)).style.padding = '';
		}
		document.getElementById('immagine_mobile').src = javascript_array[i];
		document.getElementById('immagine').src = javascript_array[i];
		document.getElementById('link_immagine').href = javascript_array[i];
		document.getElementById('img_'+i).style.backgroundColor = 'green';
		document.getElementById('img_'+i).style.padding = '10px';
		if (i!=0) {
			document.getElementById('img_'+(i-1)).style.backgroundColor = '';
			document.getElementById('img_'+(i-1)).style.padding = '';
		}
		i++;
	}
	var timer = setInterval(gira_automatico, 10000);
	</script>";
	
	echo "
	<div class='slideshow_container'>
		<button class='bottone' style='margin: 0 10px;' onClick=\"gira('sx');\">&lt;</button>
		<a id='link_immagine' href='$nomi[0]'><img id='immagine' src='$nomi[0]'></a>
		<button class='bottone' style='margin: 0 10px;' onClick=\"gira('dx');\">&gt;</button>
	</div>
	
	<div class='slideshow_container_mobile'>
		<a href='$nomi[0]'><img id='immagine_mobile' src='$nomi[0]'></a>
		<br>
		<button class='bottoneAllineatoColorato' onClick=\"gira('sx');\">&lt;</button>
		<button class='bottoneAllineatoColorato' onClick=\"gira('dx');\">&gt;</button>
	</div>
	
	<div class='mini_immagini'>
	";
	for ($i=0; $i<count($nomi); $i++) {
		echo "<img src='$nomi[$i]' id='img_$i' width='50px' height='50px' style='margin-right: 10px' onClick='cambia_immagine($i)'>";
	}
	echo "</div>";
	
	tail();

?>
