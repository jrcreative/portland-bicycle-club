window.addEventListener('DOMContentLoaded', function(){
	// let formLoginProtect = document.getElementById('form-login-protect');
	let loginRestrictedRadio = document.getElementById('login-protect');
	let loginUnrestrictedRadio = document.getElementById('login-open');
	let protectCountries = document.getElementById('protect-countries');
	let protectWhitelist = document.getElementById('protect-whitelist');

	let loginRestrict = document.getElementById('login-restrict');
	let loginRestrictState = null;
	let loginWhitelist = null;
	if(loginRestrict && loginRestrict.checked){
		loginRestrictState = true;
	}

	let resetRestrict = document.getElementById('reset-restrict');
	let resetRestrictState = null;
	let resetWhitelist = null;
	if(resetRestrict && resetRestrict.checked){
		resetRestrictState = true;
	}

	let registerRestrict = document.getElementById('register-restrict');
	let registerRestrictState = null;
	let registerWhitelist = null;
	if(registerRestrict && registerRestrict.checked){
		registerRestrictState = true;
	}

	let botRestrict = document.getElementById('bot-restrict');
	let botRegister = document.getElementById('bot-register');
	let botForms = document.getElementById('bot-forms');
	let botComments = document.getElementById('bot-comments');

	// let formGeoProtect = document.getElementById('form-geo-protect');
	let geoRestrictedRadio = document.getElementById('geo-restrict');
	let geoUnrestrictedRadio = document.getElementById('geo-unrestricted');
	let geoprotectCountries = document.getElementById('geo-restrict-whitelist-select');
	let geoCountryInput = document.getElementById('geo-restrict-whitelist-input');

	let commentRestrict = document.getElementById('comment-restrict');
	let commentRestrictState = null;
	let commentWhitelist = null;
	if(commentRestrict && commentRestrict.checked){
		commentRestrictState = true;
	}

	let formsRestrict = document.getElementById('forms-restrict');
	let formsRestrictState = null;
	let formsWhitelist = null;
	if(formsRestrict && formsRestrict.checked){
		formsRestrictState = true;
	}

	function addItem(target, val){
		let list = Array();
		if(target.value != ''){
			list = target.value.split(',');
		}

		if(list.indexOf(val) == -1){
			list.push(val);
		}

		if(list.length > 1){
			target.value = list.join(',');
		}
		else{
			target.value = list.join('');
		}
	}

	function removeItem(target, val){
		let list = Array();

		if(target.value == '') return;
		list = target.value.split(',');

		if(list.length == 0) return;
		if(list.indexOf(val) == -1) return;
		if(list.length == 1){
			list.pop();
			target.value = list.join('');
			return;
		}
		list.splice(list.indexOf(val), 1);

		if(list.length > 1){
			target.value = list.join(',');
		}
		else{
			target.value = list.join('');
		}
	}

	function syncLoginValues(){
		if(loginRestrict.checked){
			document.getElementById('login-whitelist').value = protectWhitelist.value;
		}

		if(resetRestrict.checked){
			document.getElementById('reset-whitelist').value = protectWhitelist.value;
		}

		if(registerRestrict.checked){
			document.getElementById('register-whitelist').value = protectWhitelist.value;
		}
	}

	function syncGeoRestrictValues(){
		if(commentRestrict.checked){
			document.getElementById('comment-whitelist').value = geoCountryInput.value;
		}

		if(formsRestrict.checked){
			document.getElementById('forms-whitelist').value = geoCountryInput.value = geoCountryInput.value;
		}
	}

	// if(formLoginProtect){
	//     formLoginProtect.addEventListener('submit', function(event){
	//         event.preventDefault();
	//         console.log('hit');

	//         if(loginRestrictedRadio.checked){
	//             if(loginRestrict.checked || resetRestrict.checked || registerRestrict.checked){
	//                 return true;
	//             }
	//         }
	//         return false;
	//     }, false);
	// }

	if(loginRestrictedRadio){
		loginRestrictedRadio.addEventListener('change', function(event){
			if(loginRestrictedRadio.checked){
				document.getElementById('login-apply-to-container').style.display = 'block';
				document.querySelector('.allow-from').style.paddingBottom = '0px';
				loginRestrict.setAttribute('data-checkboxgroup',true);
				resetRestrict.setAttribute('data-checkboxgroup',true);
				registerRestrict.setAttribute('data-checkboxgroup',true);

				if(loginRestrictState){
					loginRestrict.checked = true;
					if(!document.getElementById('login-whitelist')){
						loginWhitelist = document.createElement('input');
						loginWhitelist.setAttribute('type', 'hidden');
						loginWhitelist.setAttribute('name', 'login/whitelist');
						loginWhitelist.setAttribute('id', 'login-whitelist');
						loginWhitelist.setAttribute('value', protectWhitelist.value);
						document.getElementById('login-whitelist-wrapper').appendChild(loginWhitelist);
					}
				}

				if(resetRestrictState){
					resetRestrict.checked = true;
					if(!document.getElementById('reset-whitelist')){
						resetWhitelist = document.createElement('input');
						resetWhitelist.setAttribute('type', 'hidden');
						resetWhitelist.setAttribute('name', 'reset/whitelist');
						resetWhitelist.setAttribute('id', 'reset-whitelist');
						resetWhitelist.setAttribute('value', protectWhitelist.value);
						document.getElementById('reset-whitelist-wrapper').appendChild(resetWhitelist);
					}
				}

				if(registerRestrictState){
					registerRestrict.checked = true;
					if(!document.getElementById('register-whitelist')){
						registerWhitelist = document.createElement('input');
						registerWhitelist.setAttribute('type', 'hidden');
						registerWhitelist.setAttribute('name', 'register/whitelist');
						registerWhitelist.setAttribute('id', 'register-whitelist');
						registerWhitelist.setAttribute('value', protectWhitelist.value);
						document.getElementById('register-whitelist-wrapper').appendChild(registerWhitelist);
					}
				}
			}

		}, false);
	}

	if(loginUnrestrictedRadio){
		loginUnrestrictedRadio.addEventListener('change', function(event){
			document.getElementById('login-apply-to-container').style.display = 'none';
			document.querySelector('.allow-from').style.removeProperty('padding-bottom');
			loginRestrict.removeAttribute('data-checkboxgroup');
			resetRestrict.removeAttribute('data-checkboxgroup');
			registerRestrict.removeAttribute('data-checkboxgroup');

			loginRestrict.checked = false;
			if(document.getElementById('login-whitelist')){
				document.getElementById('login-whitelist').remove();
			}

			resetRestrict.checked = false;
			if(document.getElementById('reset-whitelist')){
				document.getElementById('reset-whitelist').remove();
			}

			registerRestrict.checked = false;
			if(document.getElementById('register-whitelist')){
				document.getElementById('register-whitelist').remove();
			}
		}, false);
	}

	if(protectCountries){
		protectCountries.addEventListener('addItem', function(event){
			addItem(protectWhitelist, event.detail.value);
			syncLoginValues();
		}, false);

		protectCountries.addEventListener('removeItem', function(event){
			removeItem(protectWhitelist, event.detail.value);
			syncLoginValues();
		}, false);
	}

	if(loginRestrict){
		loginRestrict.addEventListener('change', function(event){
			loginRestrictState = loginRestrict.checked;
			if(loginRestrict.checked){
				if(!loginRestrictedRadio.checked){
					loginRestrictedRadio.click();
				}

				if(!document.getElementById('login-whitelist')){
					loginWhitelist = document.createElement('input');
					loginWhitelist.setAttribute('type', 'hidden');
					loginWhitelist.setAttribute('name', 'login/whitelist');
					loginWhitelist.setAttribute('id', 'login-whitelist');
					loginWhitelist.setAttribute('value', protectWhitelist.value);
					document.getElementById('login-whitelist-wrapper').appendChild(loginWhitelist);
				}
			}
			else{
				document.getElementById('login-whitelist').remove();
			}
		}, false);
	}

	if(resetRestrict){
		resetRestrict.addEventListener('change', function(event){
			resetRestrictState = resetRestrict.checked;
			if(resetRestrict.checked){
				if(!loginRestrictedRadio.checked){
					loginRestrictedRadio.click();
				}

				if(!document.getElementById('reset-whitelist')){
					resetWhitelist = document.createElement('input');
					resetWhitelist.setAttribute('type', 'hidden');
					resetWhitelist.setAttribute('name', 'reset/whitelist');
					resetWhitelist.setAttribute('id', 'reset-whitelist');
					resetWhitelist.setAttribute('value', protectWhitelist.value);
					document.getElementById('reset-whitelist-wrapper').appendChild(resetWhitelist);
				}
			}
			else{
				document.getElementById('reset-whitelist').remove();
			}
		}, false);
	}

	if(registerRestrict){
		registerRestrict.addEventListener('change', function(event){
			registerRestrictState = registerRestrict.checked;
			if(registerRestrict.checked){
				if(!loginRestrictedRadio.checked){
					loginRestrictedRadio.click();
				}

				if(!document.getElementById('register-whitelist')){
					registerWhitelist = document.createElement('input');
					registerWhitelist.setAttribute('type', 'hidden');
					registerWhitelist.setAttribute('name', 'register/whitelist');
					registerWhitelist.setAttribute('id', 'register-whitelist');
					registerWhitelist.setAttribute('value', protectWhitelist.value);
					document.getElementById('register-whitelist-wrapper').appendChild(registerWhitelist);
				}
			}
			else{
				document.getElementById('register-whitelist').remove();
			}
		}, false);
	}

	if(botRestrict){
		botRestrict.addEventListener('change', function(event){
			if(botRestrict.checked){
				botRegister.checked = true;
				botForms.checked = true;
				botComments.checked = true;
			}
			else{
				botRegister.checked = false;
				botForms.checked = false;
				botComments.checked = false;
			}
		}, false);
	}

	if(botRegister){
		botRegister.addEventListener('change', function(event){
			if(botRegister.checked){
				botRestrict.checked = true;
			}
			else{
				if(!botForms.checked && !botComments.checked){
					botRestrict.checked = false;
				}
			}
		}, false);
	}

	if(botForms){
		botForms.addEventListener('change', function(event){
			if(botForms.checked){
				botRestrict.checked = true;
			}
			else{
				if(!botRegister.checked && !botComments.checked){
					botRestrict.checked = false;
				}
			}
		}, false);
	}

	if(botComments){
		botComments.addEventListener('change', function(event){
			if(botComments.checked){
				botRestrict.checked = true;
			}
			else{
				if(!botRegister.checked && !botForms.checked){
					botRestrict.checked = false;
				}
			}
		}, false);
	}

	if(geoRestrictedRadio){
		geoRestrictedRadio.addEventListener('change', function(event){
			if(geoRestrictedRadio.checked){
				document.querySelector('#geo-apply-to-container').style.display = 'block';
				document.querySelector('.allow-from').style.paddingBottom = '0px';
				commentRestrict.setAttribute('data-checkboxgroup',true);
				formsRestrict.setAttribute('data-checkboxgroup',true);

				if(commentRestrictState){
					commentRestrict.checked = true;
					if(!document.getElementById('comment-whitelist')){
						commentWhitelist = document.createElement('input');
						commentWhitelist.setAttribute('type', 'hidden');
						commentWhitelist.setAttribute('name', 'comment/whitelist');
						commentWhitelist.setAttribute('id', 'comment-whitelist');
						commentWhitelist.setAttribute('value', geoCountryInput.value);
						document.getElementById('comment-whitelist-wrapper').appendChild(commentWhitelist);
					}
				}

				if(formsRestrictState){
					formsRestrict.checked = true;
					if(!document.getElementById('forms-whitelist')){
						formsWhitelist = document.createElement('input');
						formsWhitelist.setAttribute('type', 'hidden');
						formsWhitelist.setAttribute('name', 'forms/whitelist');
						formsWhitelist.setAttribute('id', 'forms-whitelist');
						formsWhitelist.setAttribute('value', geoCountryInput.value);
						document.getElementById('forms-whitelist-wrapper').appendChild(formsWhitelist);
					}
				}
			}
		}, false);
	}

	if(geoUnrestrictedRadio){
		geoUnrestrictedRadio.addEventListener('change', function(event){
			document.querySelector('#geo-apply-to-container').style.display = 'none';
			document.querySelector('.allow-from').style.removeProperty('padding-bottom');
			commentRestrict.checked = false;
			formsRestrict.checked = false;
			commentRestrict.removeAttribute('data-checkboxgroup');
			formsRestrict.removeAttribute('data-checkboxgroup');
			if(document.getElementById('comment-whitelist')){
				document.getElementById('comment-whitelist').remove();
			}

			formsRestrict.checked = false;
			if(document.getElementById('forms-whitelist')){
				document.getElementById('forms-whitelist').remove();
			}
		}, false);
	}

	if(geoprotectCountries){
		geoprotectCountries.addEventListener('addItem', function(event){
			addItem(geoCountryInput, event.detail.value);
			syncGeoRestrictValues();
		}, false);

		geoprotectCountries.addEventListener('removeItem', function(event){
			removeItem(geoCountryInput, event.detail.value);
			syncGeoRestrictValues();
		}, false);
	}

	if(commentRestrict){
		commentRestrict.addEventListener('change', function(event){
			commentRestrictState = commentRestrict.checked;
			if(commentRestrict.checked){
				if(!geoRestrictedRadio.checked){
					geoRestrictedRadio.click();
				}

				if(!document.getElementById('comment-whitelist')){
					commentWhitelist = document.createElement('input');
					commentWhitelist.setAttribute('type', 'hidden');
					commentWhitelist.setAttribute('name', 'comment/whitelist');
					commentWhitelist.setAttribute('id', 'comment-whitelist');
					commentWhitelist.setAttribute('value', geoCountryInput.value);
					document.getElementById('comment-whitelist-wrapper').appendChild(commentWhitelist);
				}
			}
			else{
				document.getElementById('comment-whitelist').remove();
			}
		}, false);
	}

	if(formsRestrict){
		formsRestrict.addEventListener('change', function(event){
			formsRestrictState = formsRestrict.checked;
			if(formsRestrict.checked){
				if(!geoRestrictedRadio.checked){
					geoRestrictedRadio.click();
				}

				if(!document.getElementById('forms-whitelist')){
					formsWhitelist = document.createElement('input');
					formsWhitelist.setAttribute('type', 'hidden');
					formsWhitelist.setAttribute('name', 'forms/whitelist');
					formsWhitelist.setAttribute('id', 'forms-whitelist');
					formsWhitelist.setAttribute('value', geoCountryInput.value);
					document.getElementById('forms-whitelist-wrapper').appendChild(formsWhitelist);
				}
			}
			else{
				document.getElementById('forms-whitelist').remove();
			}
		}, false);
	}
});
