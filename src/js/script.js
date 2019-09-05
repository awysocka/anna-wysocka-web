
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('contact-form').onsubmit = function(e) {
	    e.preventDefault();
	    const form = e.target;
	    const data = new FormData(form);

	    const xhr = typeof XMLHttpRequest != 'undefined'
				? new XMLHttpRequest()
				: new ActiveXObject('Microsoft.XMLHTTP');

		xhr.open('POST', 'mail.php');
		xhr.responseType = 'json';
		xhr.setRequestHeader("ACCEPT","application/json");
		xhr.onload = function() {
            if (xhr.status == 200) {
                document.getElementById("form-container").classList.add("form__container--inactive");
                document.getElementById("form-confirmation").classList.add("form__confirmation--active");
            } else {
                alert('Błąd! ' + xhr.response.message);
            }
		};
		xhr.onerror = function() {
			alert('Wystąpił błąd wysyłania formularza. Spróbuj ponownie za chwilę.');
		}
		xhr.send(data);
	};
});
