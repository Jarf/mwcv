let widgetId;
const contactForm = document.getElementById('contactform');
const contactFormInputs = contactForm.querySelectorAll('input,textarea,button');
const contactFormSubmitBtn = document.getElementById('contactformsubmit');
const contactFormToggle = document.getElementById('contactformtogglecheck');
let turnstileInit = 0;

function initTurnstile(){
	if(typeof turnstile !== 'undefined'){
		widgetId = turnstile.render('#turnstile-container', {
			siteKey: document.getElementById('turnstile-container').dataset.sitekey,
			theme: 'light',
			size: 'normal',
			callback: function(token){
				sendMessage(token);
			}
		});
		turnstileInit = 1;
	}else{
		setTimeout(initTurnstile, 100);
	}
}

function submitContact(){
	contactFormToggle.setAttribute('disabled', 'disabled');
	contactFormInputs.forEach(function(el){
		el.setAttribute('disabled', 'disabled');
	});
	contactFormSubmitBtn.textContent = 'Sending...';
	if(validateContactForm() === true){
		if(turnstileInit === 0){
			initTurnstile();
		}else{
			turnstile.reset(widgetId);
		}
	}else{
		submitError(1);
	}
}

function sendMessage(token){
	let xhr = new XMLHttpRequest();
	var params = {
		token: token
	};
	var data = new FormData();
	contactFormInputs.forEach(function(el){
		if(el.name !== ''){
			params[el.name] = el.value;
		}
	});
	xhr.open('POST', '/ajax/contact.php', true);
	xhr.onreadystatechange = function(){
		if(this.readyState === 4 && this.status === 200){
			if (parseInt(this.responseText) === 1) {
				submitError(3);
			}else{
				submitError(2);
			}
		}else if(this.readyState === 4 && this.status !== 200){
			submitError(2);
		}
	}
	for(name in params){
		data.append(name, params[name]);
	}
	xhr.send(data);
}

function resetContact(){
	contactForm.reset();
	contactFormInputs.forEach(function(el){
		el.removeAttribute('disabled');
	});
}

function submitError(messageNo){
	var message;
	if(messageNo === 1){
		message = 'Looks like you missed some things, try again';
	}else if(messageNo === 2){
		message = 'Oops something went wrong, please try again';
	}else if(messageNo === 3){
		message = 'Sent, thankyou!';
	}
	contactFormSubmitBtn.textContent = message;
	setTimeout(function(){
		contactFormInputs.forEach(function(el){
			el.removeAttribute('disabled');
		});
		contactFormToggle.removeAttribute('disabled');
		contactFormSubmitBtn.textContent = 'Send';
		if(messageNo === 3){
			contactForm.reset();
			contactFormToggle.checked = false;
			document.getElementById('professional').scrollTo(0, 0);
		}
	}, 1500);
}

function validateContactForm(){
	let m;
	const mailregex = /^[\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/;
	var mail = document.getElementById('contactformemail').value;
	var valid = true;
	if(document.getElementById('contactformname').value === ''){
		valid = false;
	}
	if(mail === '' || mail.match(mailregex) === null){
		valid = false;
	}
	if(document.getElementById('contactformmessage').value === ''){
		valid = false;
	}
	return valid;
}

contactFormSubmitBtn.addEventListener('click', function(){
	submitContact();
});