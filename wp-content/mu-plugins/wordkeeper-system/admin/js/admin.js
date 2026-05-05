const wordKeeperApiPath = 'wordkeeper-system/v1/';
document.addEventListener('DOMContentLoaded', function(event){
	formTooltips();
	formDisplayToggles();
	formControls();
	bindSweetAlert();
});

function formDisplayToggles(){
	// Checkbox toggles
	const checkboxes = document.querySelectorAll('.checkbox-openclose');
	const blocks = document.querySelectorAll('.content-open-close');
	checkboxes.forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			const blockId = checkbox.dataset.show;
			const block = document.getElementById(blockId);
			if(checkbox.checked){
				block.style.display = 'block';
				block.querySelectorAll('input, textarea, select').forEach((input) => {
					if(input.disabled && !input.hasAttribute('data-unavailable')){
						input.removeAttribute('disabled');
					}
				});
			}
			else{
				block.style.display = 'none';
				block.querySelectorAll('input, textarea, select').forEach((input) => {
					if(!input.disabled){
						input.setAttribute('disabled', true);
					}
				});
			}
		});
	});

	// Radio toggles
	if(document.querySelectorAll('.radio-holder').length){
		document.querySelectorAll('.openclose input[type="radio"][data-type="toggle"]').forEach((radio) => {
			radio.addEventListener('change', (event) => {
				const blockId = radio.dataset.show;
				const block = document.getElementById(blockId);
				if(!block){return;}
				if(event.target.dataset.showtype === 'open'){
					block.style.display = 'block';
					block.querySelectorAll('input, textarea, select').forEach((input) => {
						if(input.disabled && input.offsetParent !== null && !input.hasAttribute('data-unavailable')){
							// input.offsetParent !== null accounts for the edge case where element might be in a hidden div
							input.removeAttribute('disabled');
						}
					});
				}
				else if(event.target.dataset.showtype === 'close'){
					block.style.display = 'none';
					block.querySelectorAll('input, textarea, select').forEach((input) => {
						if(!input.disabled){
							input.setAttribute('disabled', true);
						}
					});
				}
			});
		});
	}
}

function formTooltips(){
	if(document.querySelectorAll('.tooltips').length){
		tippy('.tooltips:not(.disabled .tooltips)', {
			trigger: 'click',
			animation: 'fade',
			interactive: true,
			placement: 'right',
			allowHTML: true,
			content(reference){
				const id = reference.getAttribute('data-template');
				const template = document.getElementById(id);
				return template.innerHTML;
			},
			onMount: function(instance){
				let closeButton = instance.popper.querySelector('.close');
				closeButton.addEventListener('click', function(event){
					event.preventDefault();
					instance.hide();
				});
			}
		});
	}
}

// Wire up buttons
function formControls(){
	// Disabled checkboxes
	let checkboxList = document.querySelectorAll('.checkbox-toggle-disabled');
	for(var i = 0; i < checkboxList.length; i++){
		checkboxList[i].addEventListener('change', function(){
			let parent = this.parentNode.closest('.item');
			if(this.checked){
				parent.classList.remove('elements-disabled');
			}
			else{
				parent.classList.add('elements-disabled');
			}
		});
	}

	// Single select boxes
	if(document.querySelectorAll('.holder-single-select').length){
		const containerSingle = document.querySelectorAll('.holder-single-select');
		containerSingle.forEach((holder) => {
			const selectSingle = holder.querySelector('.single-select');
			const choicesSingle = new Choices(selectSingle, {
				searchEnabled: false,
				shouldSort: false,
				shouldSortItems: false,
				itemSelectText: ''
			});
		})
	}

	// Multiselect boxes
	if(document.querySelectorAll('.holder-multiple-select').length){
		const containerMultiple = document.querySelectorAll('.holder-multiple-select');
		containerMultiple.forEach((holder) => {
			const selectMultiple = holder.querySelector('.multiple-select');
			const choicesMultiple = new Choices(selectMultiple, {
				removeItemButton: true,
				placeholderValue: 'Select items',
				itemSelectText: '',
				shouldSort: false,
				shouldSortItems: false,
				allowHTML: true
			});
		})
	}

	// Custom select boxes
	if(document.querySelectorAll('.custom-select-block').length){
		const container = document.querySelectorAll('.custom-select-block');
		container.forEach((holder) => {
			const selectedOptionsContainer = document.createElement('div');
			const toggleArrow = document.createElement('span');
			toggleArrow.className = 'toggle-arrow';
			toggleArrow.innerHTML = 'Open/Hide options list';
			selectedOptionsContainer.className = 'selected-options-container';
			const select = holder.querySelector('.my-select');
			const choices = new Choices(select, {
				removeItems: true,
				removeItemButton: true,
				searchEnabled: true,
				searchChoices: true,
				shouldSort: false,
				shouldSortItems: false,
				itemSelectText: '',
				placeholder: true,
				placeholderValue: 'Start typing...',
				searchPlaceholderValue: 'Start typing...'
			});
			const selectedChoices = holder.querySelector('.choices__list--multiple');
			const selectHolder = holder.querySelector('.choices');
			const choicesInner = holder.querySelector('.choices__inner');
			const choicesDropdown = holder.querySelector('.choices__list--dropdown');
			selectHolder.appendChild(selectedOptionsContainer);
			choicesInner.appendChild(toggleArrow);
			choices
				.passedElement
				.element
				.addEventListener('change', function(event){
					if(selectedChoices !== null && selectedChoices !== ''){
						selectedOptionsContainer.appendChild(selectedChoices);
					}
				});

			toggleArrow.addEventListener('click', () => {
				choicesDropdown.classList.toggle('is-active');
			});

			document.addEventListener('click', () => {
				if(choicesDropdown.classList.contains('is-active')){
					toggleArrow.classList.add('is-active');
				}
				else{
					toggleArrow.classList.remove('is-active');
				}
			});
			select.dispatchEvent(new Event('change'));
		});
	}

	// Checkbox list toggles
	if(document.querySelectorAll('#ch-all').length){
		const selectAllCheckbox = document.querySelector('#ch-all');
		const checkboxes = document.querySelectorAll('.checkbox');
		function selectAll(){
			for(let i = 0; i < checkboxes.length; i++){
				checkboxes[i].checked = selectAllCheckbox.checked;
			}
		}
		selectAllCheckbox.addEventListener('change', selectAll);
	}

	// Filter typeable inputs that have char filters
	document.querySelectorAll('input[data-filter]').forEach((input) => {
		wordkeeper.form.filter(input);
	});

	// Basic save dialog
	document.querySelectorAll('[data-submit]').forEach(submit => {
		submit.addEventListener('click', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();

			let form = e.target.closest('form');
			let valid = false;
			if(form != null){
				valid = wordkeeper.form.validate(form);
			}
			else{
				valid = true;
			}

			if(valid){
				wordkeeper.dialog.submit.fire(submit.dataset);

				// Get data from form
				wordkeeper.form.data = wordkeeper.form.process(form);
				wordkeeper.api.submit(e.target, wordkeeper.form.data).then(() => {
					wordkeeper.form.data = {};
				}).catch(err => {

					// Display error messages
					if(
						typeof err !== 'undefined'
						&& typeof err.responseJSON !== 'undefined'
						&& typeof err.responseJSON.data !== 'undefined'
						&& typeof err.responseJSON.data.params !== 'undefined'
					){
						Object.keys(err.responseJSON.data.params).forEach(function(key, i){
							const el = document.querySelector('#' + key);

							if(el){
								let message;
								if(typeof el !== 'undefined' && typeof el.dataset !== 'undefined' && typeof el.dataset.novalidate !== 'undefined'){
									message = el.dataset.novalidate;
								}

								if(typeof message === 'undefined'){
									message = 'Invalid field';
								}

								if(el.parentElement){
									const errorSpan = el.parentElement.querySelector('.text-error');
									if(errorSpan){
										errorSpan.innerText = message;
										const holder = el.parentElement;
										if(holder.classList.contains('holder-input', 'holder-textarea-block')){
											holder.classList.add('error');
										}
									}
								}
							}
						});
					}

					wordkeeper.dialog.error('Something Went Wrong');
					wordkeeper.form.data = {};
				});
			}
		});
	});

	// Confirmation dialogs
	document.querySelectorAll('[data-confirm]').forEach(confirm => {
		confirm.addEventListener('click', function(e){
			let form = e.target.closest('form');

			let valid = false;
			if(form != null){
				valid = wordkeeper.form.validate(form);
			}
			else{
				valid = true;
			}

			if(valid){
				let containerType = '';
				if(e.target.hasAttribute('data-container')){
					containerType = e.target.dataset.container;
					containerType = 'template-system ' + containerType;
				}
				else{
					containerType = 'template-system popups-wide-template';
				}
				wordkeeper.dialog.confirm.fire(containerType, e.target.dataset.dialogTemplate, e.target.dataset);
			}
		});
	});

	// Display announcement
	document.querySelectorAll('[data-announcement]').forEach((announcement) => {
		announcement.addEventListener('click', function(e){
			wordkeeper.announcement.fire();
		});
	});

	// Display video
	document.querySelectorAll('[data-video]').forEach((video) => {
		video.addEventListener('click', function(e){
			wordkeeper.video.fire();
		});
	});

	// Leave page notice
	// wordkeeper.dialog.leave.fire();
}

function bindSweetAlert(){
	if(document.querySelectorAll('.sweetalert2-popups').length){
		//Video
		Swal.mixin({
			position: 'center',
			padding: '5em',
			showConfirmButton: false,
			showCancelButton: false,
			showCloseButton: true,
			customClass: {
				// for styling in the Speed plugin use the container class template-speed'
				container: 'popups-video-template template-speed',
			},
		}).bindClickHandler('data-swal-template-video-popups')
	}
}