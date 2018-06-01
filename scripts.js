
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

