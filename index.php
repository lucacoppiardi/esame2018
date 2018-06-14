<?php
	include("interfaccia.php");
	head();
	topbar("home");
	
?>
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
			.prima_img {
				width: 300px !important;
				height: 300px !important;
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
			.prima_img_mobile {
				width: 100px !important;
				height: 100px !important;
			}
		}
	</style>
	
	<?php
		$files = glob("media/foto/*.*");
		$nomi = array();
		for ($i=1; $i<count($files); $i++) {
			$nomi[$i] = $files[$i];
		}
		$nomi[0] = "media/logo.png";
		//print_r($nomi);
		echo "<script>";
		$js_array = json_encode($nomi);
		echo "var javascript_array = ". $js_array . ";\n";
		echo "
		var i = 0;
		var len = ".(count($nomi)-1).";
		function gira(verso) {
			if (verso == 'sx') {
				if (i>0) i--;
				document.getElementById('immagine').src = javascript_array[i];
			}
			if (verso == 'dx') {
				if (i<len) {
					i++;
				} else {
					i=0;
					document.getElementById('immagine').className = 'prima_img';
				}
				document.getElementById('immagine').src = javascript_array[i];
			}
			document.getElementById('immagine').className = '';
			if (i==0) {
				document.getElementById('immagine').className = 'prima_img';
			}
		}
		function gira_mobile(verso) {
			if (verso == 'sx') {
				if (i>0) i--;
				document.getElementById('immagine_mobile').src = javascript_array[i];
			}
			if (verso == 'dx') {
				if (i<len) {
					i++;
				} else {
					i=0;
					document.getElementById('immagine_mobile').className = 'prima_img';
				}
				document.getElementById('immagine_mobile').src = javascript_array[i];
			}
			document.getElementById('immagine_mobile').className = '';
			if (i==0) {
				document.getElementById('immagine_mobile').className = 'prima_img_mobile';
			}
		}
		</script>";
	?>
	
	<div class='slideshow_container'>
		<button class='bottone' onClick="gira('sx');">&lt;</button>
		<img id='immagine' class='prima_img' src='media/logo.png'>
		<button class='bottone' onClick="gira('dx');">&gt;</button>
	</div>
	
	<div class='slideshow_container_mobile'>
		<img id='immagine_mobile' class='prima_img_mobile' src='media/logo.png'>
		<br>
		<button class='bottoneAllineatoColorato' onClick="gira_mobile('sx');">&lt;</button>
		<button class='bottoneAllineatoColorato' onClick="gira_mobile('dx');">&gt;</button>
	</div>
	
	
<?php
	tail();
?>
