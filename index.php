<?php
	include("interfaccia.php");
	head();
	topbar("home");
	
	echo "
	<style>
		.slideshow_container {
			align-items: center;
			justify-content: center;
		}
		.slideshow_container img {
			width: 700px;
			height: 400px;
		}
		.slideshow_container * {
			vertical-align: middle;
		}
		button {
			margin: 0 10px;
		}
		@media screen and (min-width: 800px) {
			.slideshow_container {
				display: flex;
			}
			.slideshow_container_mobile {
				display: none;
			}
		}
		@media screen and (max-width: 800px) {
			.slideshow_container {
				display: none;
			}
			.slideshow_container_mobile {
				display: block;
			}
			.slideshow_container_mobile img {
				width: 100%;
				height: 240px;
			}
		}
	</style>";
	
	$files = glob("media/foto/*.*");
	$nomi = array();
	for ($i=0; $i<count($files); $i++) {
		$nomi[$i] = $files[$i];
	}
	$arr = json_encode(print_r($nomi, true));
	echo "<script>console.log('$arr');</script>";
	
	echo "<script>";
	$js_array = json_encode($nomi);
	echo "var javascript_array = ". $js_array . ";\n";
	echo "
	var i = 0;
	var len = ".(count($nomi)-1).";
	function gira(verso) {
		console.log('in '+i);
		if (verso == 'sx') {
			if (i>0) {
				i--;
			} else {
				i = len;
			}
			document.getElementById('immagine').src = javascript_array[i];
		}
		if (verso == 'dx') {
			if (i<len) {
				i++;
			} else {
				i=0;
			}
			document.getElementById('immagine').src = javascript_array[i];
		}
		console.log('out '+i);
	}
	function gira_mobile(verso) {
		if (verso == 'sx') {
			if (i>0) {
				i--;
			} else {
				i = len;
			}
			document.getElementById('immagine_mobile').src = javascript_array[i];
		}
		if (verso == 'dx') {
			if (i<len) {
				i++;
			} else {
				i=0;
			}
			document.getElementById('immagine_mobile').src = javascript_array[i];
		}
	}
	</script>";
	
	echo "
	<div class='slideshow_container'>
		<button class='bottone' onClick=\"gira('sx');\">&lt;</button>
		<img id='immagine' src='$nomi[0]'>
		<button class='bottone' onClick=\"gira('dx');\">&gt;</button>
	</div>
	
	<div class='slideshow_container_mobile'>
		<img id='immagine_mobile' src='$nomi[0]'>
		<br>
		<button class='bottoneAllineatoColorato' onClick=\"gira_mobile('sx');\">&lt;</button>
		<button class='bottoneAllineatoColorato' onClick=\"gira_mobile('dx');\">&gt;</button>
	</div>
	";
	
	tail();

?>
