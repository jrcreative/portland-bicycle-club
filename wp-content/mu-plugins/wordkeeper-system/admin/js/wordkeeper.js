const wordkeeper = {};
wordkeeper.form = {};
wordkeeper.form.data = {};
wordkeeper.validate = {};
wordkeeper.api = {};
wordkeeper.dialog = {};
wordkeeper.dialog.submit = {};
wordkeeper.dialog.confirm = {};
wordkeeper.dialog.leave = {};
wordkeeper.notice = {};
wordkeeper.video = {};
wordkeeper.announcement = {};
wordkeeper.status = {};

// Validate form data
wordkeeper.form.validate = function(form){
	let errors = {};
	if(form != null){
		document.querySelectorAll('.error').forEach((elem) => {
			elem.classList.remove('error');
		});

		document.querySelectorAll('.errormsg').forEach((elem) => {
			elem.remove();
		});

		form.querySelectorAll('[data-checkboxgroup]').forEach(item => {
			let checked = form.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)').length;

			if(checked == 0){
				let disabled = form.querySelectorAll('input[type="checkbox"]:disabled').length;
				let allTotal = form.querySelectorAll('input[type="checkbox"]').length;

				if(disabled === allTotal){
					return; // if all the checkboxes inside the checkbox list are disabled, dont show error
				}
				errors['checkboxgroup'] = 'Field is required';
			}
		});
		
		let context = true;
		form.querySelectorAll('[data-checkboxlist]').forEach(list => {
			let checked = form.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)').length;
			if(checked == 0){
				let disabled = form.querySelectorAll('input[type="checkbox"]:disabled').length;
				let allTotal = form.querySelectorAll('input[type="checkbox"]').length;
				if(disabled === allTotal){
					return; // if all the checkboxes inside the checkbox list are disabled, dont show error
				}
				context = false;
			}
		});

		form.querySelectorAll('[data-inputlist]').forEach(list => {
			let count = 0;
			list.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="range"], textarea').forEach(input => {
				if(input.value != ''){
					count++;
				}
			});

			if(count == 0){
				context = false;
			}
		});

		if(context == false){
			wordkeeper.notice.fire('error', [{action: '', text: 'At least one value is required'}]);
			return false;
		}

		form.querySelectorAll('input[type="text"], input[type="hidden"], input[type="email"], input[type="number"], input[type="range"], textarea').forEach(input => {
			if(input.name && !input.hasAttribute('disabled')){
				if(input.required && input.value == ''){
					errors[input.name] = 'Field is required';
				}
				else if(input.hasAttribute('data-validate')){
					if(input.value && input.value != ''){
						let value = input.value.trim();
						if(input.tagName.toLowerCase() == 'textarea' && input.hasAttribute('data-multiple')){
							value = value.split(/\r?\n/);
						}
						switch(input.dataset.validate){
							case 'basic':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(!pattern.match(/^[0-9a-zA-Z\-\_\.\s]*$/)){
											errors[input.name] = 'Field must be alphanumeric.  Periods, spaces, underscores, and dashes allowed.';
										}
									});
								}
								else{
									if(!value.match(/^[0-9a-zA-Z\-\_\.\s]*$/)){
										errors[input.name] = 'Field must be alphanumeric.  Periods, spaces, underscores, and dashes allowed.';
									}
								}
								break;
							case 'alnum':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(!pattern.match(/^[0-9a-zA-Z]*$/)){
											errors[input.name] = 'Field must be alphanumeric';
										}
									});
								}
								else{
									if(!value.match(/^[0-9a-zA-Z]*$/)){
										errors[input.name] = 'Field must be alphanumeric';
									}
								}
								break;
							case 'number':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(!pattern.match(/^[0-9]*$/)){
											errors[input.name] = 'Field must be numeric';
										}
									});
								}
								else{
									if(!value.match(/^[0-9]*$/)){
										errors[input.name] = 'Field must be numeric';
									}
								}
								break;
							case 'machine':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(!pattern.match(/^[0-9a-zA-Z\-\_\.]*$/)){
											errors[input.name] = 'Field must be alphanumeric.  Periods, underscores, and dashes allowed.';
										}
									});
								}
								else{
									if(!value.match(/^[0-9a-zA-Z\-\_\.]*$/)){
										errors[input.name] = 'Field must be alphanumeric.  Periods, underscores, and dashes allowed.';
									}
								}
								break;
							case 'email':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(pattern.trim().length > 62){
											errors[input.name] = 'Email must be shorter than 62 characters';
										}

										if(!pattern.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
											errors[input.name] = 'Field must be a valid email';
										}
									});
								}
								else{
									if(value.trim().length > 62){
										errors[input.name] = 'Field must be shorter than 62 characters';
									}

									if(!value.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
										errors[input.name] = 'Field must be a valid email';
									}
								}
								break;
							case 'emails':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(pattern.indexOf(',') == -1){
											if(pattern.trim().length > 62){
												errors[input.name] = 'Each email must be shorter than 62 characters';
											}

											if(!pattern.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
												errors[input.name] = 'Field must be a comma separated email list';
											}
										}
										else{
											let emails = pattern.split(',');
											emails.forEach((email) => {
												if(email.trim().length > 62){
													errors[input.name] = 'Each email must be shorter than 62 characters';
												}

												if(!email.trim().match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
													errors[input.name] = 'Field must be a comma separated email list';
												}
											});
										}
									});
								}
								else{
									if(value.indexOf(',') == -1){
										if(!value.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
											errors[input.name] = 'Field must be a comma separated email list';
										}
									}
									else{
										let emails = value.split(',');
										emails.forEach((email) => {
											if(!email.trim().match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
												errors[input.name] = 'Field must be a comma separated email list';
											}
										});
									}
								}

								break;
							case 'regex':
								if(input.dataset.regex){
									let negate = input.hasAttribute('data-negate');
									if(value instanceof Array){
										// Need validation on a line by line basis.
										// We need to make sure that each line in the input passes the regex validation
										value.forEach(pattern => {
											if(negate){
												if(pattern.trim().match(input.dataset.regex.replaceAll('&quot;', '"'))){
													errors[input.name] = input.dataset.noValidate;
												}
											}
											else{
												if(!pattern.trim().match(input.dataset.regex.replaceAll('&quot;', '"'))){
													errors[input.name] = input.dataset.noValidate;
												}
											}
										});
									}
									else{
										// We need to make sure the input passes the regex validation
										if(negate){
											if(value.match(input.dataset.regex.replaceAll('&quot;', '"'))){
												errors[input.name] = input.dataset.noValidate;
											}
										}
										else{
											if(!value.match(input.dataset.regex.replaceAll('&quot;', '"'))){
												errors[input.name] = input.dataset.noValidate;
											}
										}
									}
								}
								break;
							case 'minimum':
								if(input.dataset.minimum){
									if(value instanceof Array){
										value.forEach(pattern => {
											if(parseInt(pattern.trim().replaceAll(/[^0-9]/g, '')) < parseInt(input.dataset.minimum.trim())){
												errors[input.name] = input.dataset.noValidate;
											}
										});
									}
									else {
										if(parseInt(value.replaceAll(/[^0-9]/g, '')) < parseInt(input.dataset.minimum.trim())){
											errors[input.name] = input.dataset.noValidate;
										}
									}
								}
								break;
							case 'maximum':
								if(input.dataset.maximum){
									if(value instanceof Array){
										value.forEach(pattern => {
											if(parseInt(pattern.trim().replaceAll(/[^0-9]/g, '')) > parseInt(input.dataset.maximum.trim())){
												errors[input.name] = input.dataset.noValidate;
											}
										});
									}
									else{
										if(parseInt(value.replaceAll(/[^0-9]/g, '')) > parseInt(input.dataset.maximum.trim())){
											errors[input.name] = input.dataset.noValidate;
										}
									}
								}
								break;
							case 'url':
								if(value instanceof Array){
									value.forEach(pattern => {
										if(!wordkeeper.validate.url(pattern)) {
											errors[input.name] = 'Field must be a valid URL';
										}
									});
								}
								else{
									if(!wordkeeper.validate.url(value)) {
										errors[input.name] = 'Field must be a valid URL';
									}
								}

								break;
							case 'host':
								if(value instanceof Array){
									value.forEach(pattern => {
										// we could probably remove this variable, but it makes the following code more readable
										let host = pattern;
										try{
											// Convert to URL format for further processing if the protocol is missing
											if(!(/^https?:\/\//i).test(host)){
												host = 'https://' + host;
											}

											// Parse as a URL
											host = new URL(host);

											if(host.hash != '' || host.pathname != '/' || host.port != '' || host.search != ''){
												errors[input.name] = 'Field must be a valid host name';
											}
										}
										catch(_){
											errors[input.name] = 'Field must be a valid host name';
										}
									});
								}
								else{
									// we could probably remove this variable, but it makes the following code more readable
									let host = value;
									try{
										// Convert to URL format for further processing if the protocol is missing
										if(!(/^https?:\/\//i).test(host)){
											host = 'https://' + host;
										}

										// Parse as a URL
										host = new URL(host);

										if(host.hash != '' || host.pathname != '/' || host.port != '' || host.search != ''){
											errors[input.name] = 'Field must be a valid host name';
										}
									}
									catch(_){
										errors[input.name] = 'Field must be a valid host name';
									}
								}

								break;
							default:
								break;
						}
					}
				}
			}
		});

		let radioNames = [];
		form.querySelectorAll('input[type="radio"]').forEach(input => {
			if(input.name && !input.hasAttribute('disabled')){
				radioNames.push(input.name);
			}
		});

		radioNames.forEach(name => {
			let checked = form.querySelectorAll('input[name="' + name + '"]:checked');
			if(checked.length == 1){
				checked.forEach(input => {
					if(input.name){

					}
				});
			}
			else{
				if(form.querySelectorAll('input[name="' + name + '"][required]').length > 0){
					errors[input.name] = 'Field is required';
				}
			}
		});

		form.querySelectorAll('input[type="checkbox"]').forEach(input => {
			if(input.name && !input.hasAttribute('disabled')){
				if(input.checked){

				}
				else{
					if(input.required){
						errors[input.name] = 'Field is required';
					}
				}
			}
		});

		form.querySelectorAll('select').forEach(input => {
			if(input.name && !input.hasAttribute('disabled')){
				if(input.required && input.selectedOptions.length == 0){
					errors[input.name] = 'Field is required';
				}
			}
		});

		// Show errors and return false
		let names = Object.keys(errors);
		if(names.length > 0){
			names.forEach((name) => {
				let elem = document.querySelector('[name="' + name + '"');
				let container = elem.closest('.holder-input, .col');
				let errormsg = elem.nextElementSibling;
				if(elem.classList.contains('multiple-select')){
					errormsg = elem.parentNode.parentNode.parentNode.querySelector('.text-error');
				}
				if(container){
					container.classList.add('error');
				}
				if(errormsg){
					errormsg.innerText = errors[name];
				}
			});

			return false;
		}
		else{
			return true;
		}
	}
}

// Validate an input URL
wordkeeper.validate.url = function (url) {
	let valid = false;
	if(url.startsWith('/')){
		url = document.location.protocol + '//' + document.location.host + url;
	}

	try{
		url = new URL(url);
		valid = true;
	}
	catch(_){
		valid = false;
	}

	return valid;
}

// Filter input fields against a regex
wordkeeper.form.filter = function(input){
	input.addEventListener('keypress', function(e){
		let input = e.target;
		let char = String.fromCharCode(e.keyCode);
		let regex = null;

		if(input.dataset.filter){
			switch(input.dataset.filter){
				case 'basic':
					regex = /[^0-9a-zA-Z\-\_\.\s]/;
					break;
				case 'alnum':
					regex = /[^0-9a-zA-Z]/;
					break;
				case 'number':
					regex = /[^0-9]/;
					break;
				case 'machine':
					regex = /[^0-9a-zA-Z\-\_\.]/;
					break;
				case 'host':
					regex = /[^0-9a-zA-Z\-\_\.\:\/]/;
					break;
				default:
					regex = new RegExp(input.dataset.filter.replaceAll('&quot;', '"'));
					break;
			}

			if(!regex || char.match(regex)){
				e.preventDefault();
			}
		}
	});
}

// Process form data into a JSON object
wordkeeper.form.process = function(form){
	let data = {};
	if(form != null){
		form.querySelectorAll('input[type="text"], input[type="hidden"], input[type="email"], input[type="number"], input[type="range"], textarea').forEach(input => {
			if(input.name && !input.hasAttribute('disabled') && typeof(input.value) !== 'undefined'){
				data[input.name] = input.value.trim();
			}
		});

		let radioNames = [];
		form.querySelectorAll('input[type="radio"]').forEach(input => {
			if(input.name && !input.hasAttribute('disabled')){
				radioNames.push(input.name);
			}
		});

		radioNames.forEach(name => {
			let checked = form.querySelectorAll('input[name="' + name + '"]:checked');
			if(checked.length == 1){
				checked.forEach(input => {
					if(input.name && input.value){
						data[name] = input.value.trim();
					}
				});
			}
			else{
				data[name] = false;
			}
		});

		form.querySelectorAll('input[type="checkbox"]').forEach(input => {
			if(input.name && !input.hasAttribute('disabled') && input.value && input.value != 'on' && input.checked){
				data[input.name] = input.value.trim();
			}
			else if(input.name && !input.hasAttribute('disabled')){
				if(input.checked){
					data[input.name] = true;
				}
				else{
					data[input.name] = false;
				}
			}
		});

		form.querySelectorAll('select').forEach(input => {
			if(input.dataset.dualListbox){
				const options = input.getElementsByTagName('option');
				const valueList = [];
				Array.from(options).forEach(function(option){
					if(option.selected){
						valueList.push(option.value);
					}
				});
				data[input.name] = valueList.join(',').trim();
			}
			else if(input.name && !input.hasAttribute('disabled') && input.value){
				if(input.multiple && input.multiple == true){
					const options = input.getElementsByTagName('option');
					const valueList = [];
					Array.from(options).forEach(function(option){
						if(option.selected){
							valueList.push(option.value);
						}
					});
					data[input.name] = valueList.join(',').trim();
				}
				else{
					data[input.name] = input.value.trim();
				}
			}
		});
	}

	return data;
}

// Submit data to the REST API
wordkeeper.api.submit = function(button, data){
	return new Promise((resolve, reject) => {
		button.setAttribute('disabled', 'disabled');
		if(!button.dataset.path){
			reject(false);
		}

		let path = button.dataset.path;
		if(!path.match(/^[0-9a-zA-Z]{1,}(?:\/[0-9a-zA-Z]{1,}(?:\/[0-9a-zA-Z]{1,})?)?$/)){
			reject(false);
		}
		else{
			wp.apiRequest({
				path: wordKeeperApiPath + button.dataset.path,
				method: 'POST',
				data: data,
			}).then((result) => {
				// Set the temporary status object
				wordkeeper.status = result;

				// Close dialog and clean up
				if(typeof result.status != 'undefined' && result.status === true){
					wordkeeper.dialog.success(button.dataset.success);
					button.removeAttribute('disabled');
					resolve(true);
				}
				else if(typeof result.status != 'undefined' && result.status === false){
					setTimeout(function(){
						Swal.close();
						button.removeAttribute('disabled');
						reject(result);

					}, 500);
				}
				// Otherwise display a generic error
				else{
					wordkeeper.dialog.error('Something Went Wrong');
					button.removeAttribute('disabled');
					reject(result);
				}
			}).catch(err => {
				wordkeeper.dialog.error('Something Went Wrong');
				button.removeAttribute('disabled');
				reject(err);
			});
		}
	});
}

// Show submit dialog
wordkeeper.dialog.submit.fire = function(dataset){
	Swal.fire({
		showConfirmButton: false,
		allowOutsideClick: false,
		template: '#template-saving',
		showClass: {
			popup: 'animate__animated animate__zoomIn'
		},
		hideClass: {
			popup: 'animate__animated animate__fadeOut'
		},
		didRender: function(){
			let container = Swal.getHtmlContainer();
			for(var name in dataset){
				let selector = 'placeholder[name="' + name.replaceAll('"', '\\"') + '"]';;
				let placeholder = container.querySelectorAll(selector);
				if(placeholder){
					placeholder.forEach(function(elem, index){
						elem.outerHTML = dataset[name].trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:\/]/ig, '').replaceAll("\n", '<br />')
					});
				}
			}
		},
		didClose: function(){
			// Fire an info notice
			if(typeof wordkeeper.status.status != 'undefined' && wordkeeper.status.status === true){
				if(wordkeeper.status.hasOwnProperty('messages') && Array.isArray(wordkeeper.status.messages) && wordkeeper.status.messages.length > 0){
					wordkeeper.notice.fire('info', wordkeeper.status.messages);
				}
			}
			// Fire an error notice
			else if(typeof wordkeeper.status.status != 'undefined' && wordkeeper.status.status === false){
				if(wordkeeper.status.hasOwnProperty('messages') && Array.isArray(wordkeeper.status.messages) && wordkeeper.status.messages.length > 0){
					wordkeeper.notice.fire('error', wordkeeper.status.messages);
				}
				else{
					wordkeeper.notice.fire('error', [{action: '', text: 'A problem occurred.  Contact support.'}]);
				}
			}

			// Reset the temporary status object
			wordkeeper.status = {};
		},
		didDestroy: function(){
			const errorEl = Array.from(document.querySelectorAll('.text-error')).filter(s =>
			   window.getComputedStyle(s).getPropertyValue('display') != 'none'
			).at(0);

			// scroll to the first error
			if(errorEl && typeof errorEl != 'undefined'){
				window.scroll({
					behavior: 'smooth',
					left: 0,
					top: errorEl.offsetTop - 300
				  });
			}
		}
	});
}

// Show confirm dialog
wordkeeper.dialog.confirm.fire = function(containerType, dialogType, dataset){

	containerType = containerType.trim().replaceAll(/[^0-9a-zA-Z\-_\s#]/g, '');
	dialogType = dialogType.trim().replaceAll(/[^0-9a-zA-Z\-_\s#]/g, '');

	// Confirm dialogs for backups, staging, etc
	Swal.fire({
		template: dialogType,
		showConfirmButton: false,
		showCancelButton: false,
		allowOutsideClick: false,
		customClass: {
			container: containerType
		},
		showClass: {
			popup: 'animate__animated animate__zoomIn'
		},
		hideClass: {
			popup: 'animate__animated animate__fadeOut'
		},
		didRender: function(){
			let container = Swal.getHtmlContainer();

			// Iterate over dataset values to populate confirm dialog
			for(var name in dataset){
				// Populate placeholders
				let selector = '[name="' + name.replaceAll('"', '\\"') + '"]';;
				let placeholder = container.querySelectorAll('placeholder' + selector);
				if(placeholder){
					placeholder.forEach(function(elem, index){
						elem.outerHTML = dataset[name].trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:\/]/ig, '').replaceAll("\n", '<br />');
					});
				}

				// Populate form values
				let input = container.querySelectorAll('input' + selector);
				if(input){
					input.forEach(function(elem, index){
						elem.value = dataset[name].trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:\/]/ig, '');
					});
				}
			}

			// Render any custom control formats
			formControls();

			// Close
			let closeButton = container.querySelector('.close-button');
			closeButton.addEventListener('click', function(){
				Swal.close();
			});

			// Filter typeable inputs that have char filters
			document.querySelectorAll('input[data-filter],textarea[data-filter]').forEach((input) => {
				wordkeeper.form.filter(input);
			});

			// Add emails
			const links = container.querySelectorAll('.open-btn');
			links.forEach(link => {
				link.addEventListener('click', function(event){
					event.preventDefault();
					let parent = event.target.closest('.holder-email-info');
					if(parent){
						parent.classList.add('active');
					}
				});
			});

			// Confirm
			let confirmButton = container.querySelector('[data-submit]');
			confirmButton.addEventListener('click', function(e){
				e.preventDefault();
				e.stopImmediatePropagation();

				// Combine confirm form data with main form data
				let form = container.querySelector('form');
				if(form != null){
					let valid = wordkeeper.form.validate(form);

					if(valid){
						let data = wordkeeper.form.process(form);
						Object.keys(data).forEach((key) => {
							wordkeeper.form.data[key] = data[key];
						});

						// Close existing alert
						Swal.close();

						// Fire submit dialog
						wordkeeper.dialog.submit.fire(confirmButton.dataset);

						// Submit to REST API
						wordkeeper.api.submit(confirmButton, wordkeeper.form.data).then(() => {
							wordkeeper.form.data = {};
						}).catch(err => {
							console.log(err);
							wordkeeper.form.data = {};
						});
					}
				}
			});
		}
	});
}

// Show dialog success message
wordkeeper.dialog.success = function(message){
	setTimeout(function(){
		document.getElementById('waiting-holder').style.display = 'none';
		document.getElementById('success-icon').style.display = 'flex';
		document.getElementById('waiting-message').innerHTML = message.trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:]/ig, '').replaceAll("\n", '<br />');
		setTimeout(function(){
			Swal.close();
		}, 700);
	}, 700);
}

// Show dialog error message
wordkeeper.dialog.error = function(message){
	setTimeout(function(){
		document.getElementById('waiting-holder').style.display = 'none';
		document.getElementById('error-icon').style.display = 'flex';
		document.getElementById('waiting-message').innerHTML = message.trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:]/ig, '').replaceAll("\n", '<br />');
		setTimeout(function(){
			Swal.close();
		}, 700);
	}, 700);
}

wordkeeper.dialog.leave.fire = function(){
	// Leave page
	Swal.fire({
		showConfirmButton: false,
		showCancelButton: false,
		template: '#template-leave',
		customClass: {
			container: 'popups-wide-template popups-leave-page-template'
		},
		showClass: {
			popup: 'animate__animated animate__zoomIn'
		},
		hideClass: {
			popup: 'animate__animated animate__fadeOut'
		},
		didRender: function(){
			let container = Swal.getHtmlContainer();

			// Close button
			let closeButton = container.querySelector('.close-button');
			closeButton.addEventListener('click', function(){
				Swal.close();
			});

			// Add emails
			const links = container.querySelectorAll('.open-btn');
			links.forEach(link => {
				link.addEventListener('click', function(event){
					event.preventDefault();
					let parent = event.target.closest('.holder-email-info');
					if(parent){
						parent.classList.add('active');
					}
				});
			});
		}
	});
}

// Fire admin notification
wordkeeper.notice.fire = function(type, messages){
	// Consolidate multiple messages into one
	let content = [];
	messages.forEach(function(message){
		let html = '';
		if(message.hasOwnProperty('text') && typeof message.text == 'string' && message.text != ''){
			html = html + message.text.trim().replaceAll(/[^0-9a-zA-Z\s\n\.\-_:]/ig, '').replaceAll("\n", '<br />');
		}

		if(message.hasOwnProperty('action') && typeof message.action == 'string' && message.action != ''){
			// Support root relative links
			if(message.action.indexOf('/') === 0){
				html = html + '<a href="' + message.action.replaceAll(/[<>"\n\(\)]/gi, '') + '" id="notice-action" target="_blank">Action Required</a>';
			}
			// Otherwise for full URLs, check domain
			else{
				let url = false;

				// Validate that action is a URL
				try{
					url = new URL(message.action);
				}
				catch(_){
					url = false;
				}

				// If the URL is valid, add a follow up action
				if(url !== false){
					switch(url.host){
						case document.location.host:
						case 'wordkeeper.helpscoutdocs.com':
						case 'wordkeeper.com':
						case 'www.wordkeeper.com':
							html = html + '<a href="' + message.action.replaceAll(/[<>"\n\(\)]/gi, '') + '" id="notice-action" target="_blank">Action Required</a>';
							break;
						default:
							break;
					}
				}
			}
		}

		// Add message to content array
		content.push(html);
	});

	switch(type){
		case 'error':
		case 'info':
		case 'success':
		case 'warning':
			Swal.fire({
				allowOutsideClick: false,
				position: 'top-end',
				showCloseButton: true,
				showConfirmButton: false,
				template: '#template-' + type.trim().replaceAll(/[^0-9a-zA-Z\-_]/g, ''),
				customClass: {
					container: 'popups-notice-template template-system'
				},
				toast: true,
				//timerProgressBar: true,
				timer: 60000,
				width: 600,
				didRender: function(){
					Swal.getHtmlContainer().querySelector('#notice-message').innerHTML = content.join("\n\n").trim().replaceAll("\n", '<br />');
				},
			});
			break;
		default:
			break;
	}
}

// Fire video lightbox
wordkeeper.video.fire = function(){
	Swal.fire({
		showConfirmButton: false,
		showCancelButton: false,
		showCloseButton: true,
		template: '#template-video',
		customClass: {
			container: 'popups-video-template template-system'
		},
		showClass: {
			popup: 'animate__animated animate__zoomIn'
		},
		hideClass: {
			popup: 'animate__animated animate__fadeOut'
		}
	});
}

// Fire announcement
wordkeeper.announcement.fire = function(){
	// Announcements
	Swal.fire({
		showConfirmButton: false,
		showCancelButton: false,
		showCloseButton: true,
		template: '#template-announcements',
		customClass: {
			container: 'popups-announcements-template template-system'
		},
		showClass: {
			popup: 'animate__animated animate__zoomIn'
		},
		hideClass: {
			popup: 'animate__animated animate__fadeOut'
		}
	});
}