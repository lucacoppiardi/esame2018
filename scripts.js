
/*
window.onscroll = function() {cambiaLogo()};
function cambiaLogo () {
	if (document.documentElement.scrollTop != 0) {
		document.getElementById("logo_home").innerHTML = "<p id=\"logo_txt\">Corte Ada</p>";
	} else {
		document.getElementById("logo_home").innerHTML = "<img id=\"logo_img\" src=\"logo.png\" alt=\"logo\"/>";
	}
}
*/

function menu() {
    if (document.getElementById("barra").className == "menu") {
        document.getElementById("barra").className += " responsive";
    } else {
        document.getElementById("barra").className = "menu";
    }
}

function link_attivo(link) {
	var lista = document.getElementsByClassName("active_link");
    for (var i = 0; i < lista.lenght; i++) {
		lista[i].className = "";
	}
	link.className = "active_link";
}

function checkPw() {
	if (document.getElementById("new_pw").value != document.getElementById("new_pw_conferma").value) {
		alert("IT: Ricontrollare la password inserita\nEN: Please check the password you typed");
		return false;
	}
	return true;
}

function checkMail() {
	if (document.getElementById("mail").value != document.getElementById("conferma_mail").value) {
		alert("IT: Ricontrollare la mail\nEN: Please check the email address");
		return false;
	}
	return true;
}

function spoilerNuovaPrenotazione() {
	if (document.getElementById('nuova_prenotazione').style.display=='none') {
		document.getElementById('nuova_prenotazione').style.display='';
		document.getElementById('btn_nuova_prenotazione').style.display='none';
	} else {
		document.getElementById('nuova_prenotazione').style.display='none';
	}
	resizeFooter();
}

function resizeFooter() {
	/*if (window.innerWidth < 700) {
		var body = document.body,
		html = document.documentElement;
		var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
		document.getElementById('pagina').style.height = height+"px";
		document.getElementById('content').style.height = (height-120)+"px";
		//document.getElementById('footer').style.marginTop = ((document.getElementById('content').style.height)-120)+"px";
	}*/
}
