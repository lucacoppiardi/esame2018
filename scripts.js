/* Cambio logo da testo a immagine e viceversa
window.onscroll = function() {cambiaLogo()};
window.onload = function() {cambiaLogo()};
function cambiaLogo () {
	console.log("scroll "+document.documentElement.scrollTop);
	if (document.documentElement.scrollTop != 0) {
		document.getElementById("logo_home").innerHTML = "<p id=\"logo_txt\">Corte Ada</p>";
	} else {
		document.getElementById("logo_home").innerHTML = "<img id=\"logo_img\" width='64px' height='64px' src=\"media/logo.png\" alt=\"logo\"/>";
	}
}*/

function menu() { /* attiva il menù per cellulari alla pressione del pulsante */
    if (document.getElementById("barra").className == "menu") {
        document.getElementById("barra").className += " responsive";
    } else {
        document.getElementById("barra").className = "menu";
    }
}

function link_attivo(link) { /* colora nella topbar il link della pagina corrente */
	var lista = document.getElementsByClassName("active_link");
    for (var i = 0; i < lista.lenght; i++) {
		lista[i].className = "";
	}
	link.className = "active_link";
}

function checkPw() { /* controlla la concordanza delle nuove password prima di inviare i form */
	if (document.getElementById("new_pw").value != document.getElementById("new_pw_conferma").value) {
		alert("IT: Ricontrollare la password inserita\nEN: Please check the password you typed");
		return false;
	}
	return true;
}

function checkMail() { /* controlla la concordanza delle nuove email prima di inviare i form */
	if (document.getElementById("mail").value != document.getElementById("conferma_mail").value) {
		alert("IT: Ricontrollare la mail\nEN: Please check the email address");
		return false;
	}
	return true;
}

function spoilerNuovaPrenotazione() { /* dopo aver premuto il bottone 'nuova prenotazione',svela il form per inserire una nuova prenotazione */
	if (document.getElementById('nuova_prenotazione').style.display=='none') {
		document.getElementById('nuova_prenotazione').style.display='';
		document.getElementById('btn_nuova_prenotazione').style.display='none';
	} else {
		document.getElementById('nuova_prenotazione').style.display='none';
	}
}
