<?php
	include("interfaccia.php");
	head();
	topbar("home");
	
?>
	<style>
		.slideshow_container {
			background-color: grey;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.slideshow_container img {
			max-width: 80%;
		}
		.slideshow_container * {
			vertical-align: middle;
		}
		button {
			margin: 0 10px;
		}
	</style>
	
	<?php
		$files = glob("media/foto/*.*");
		$nomi = array();
		for ($i=1; $i<count($files); $i++) {
			$nomi[$i] = $files[$i];
		}
		$nomi[0] = "media/logo.png";
		if (isDebug()) print_r($nomi);
		echo "<script>";
		$js_array = json_encode($nomi);
		echo "var javascript_array = ". $js_array . ";\n";
		echo "
		var i = 0;
		function gira(verso) {
			if (verso == 'sx') {
				if (i>0) i--;
				document.getElementById('immagine').src = javascript_array[i];
			}
			if (verso == 'dx') {
				i++;
				document.getElementById('immagine').src = javascript_array[i];
			}
		}
		</script>";
	?>
	
	<div class='slideshow_container'>
		<button onClick="gira('sx');"><</button>
		<img id='immagine' src='media/logo.png'>
		<button onClick="gira('dx');">></button>
	</div>
	
	
<?php
	tail();
?>
