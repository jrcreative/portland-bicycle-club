
window.addEventListener('DOMContentLoaded', function(){
	let imageResize = document.getElementById('images-resize');
	imageResize.addEventListener('change', function(e){
		let resizeSettings = document.getElementById('block-resize-settings').querySelectorAll('input');
		if(imageResize.checked){
			resizeSettings.forEach((input) => {
				input.setAttribute('required', true);
			});
		}
		else{
			resizeSettings.forEach((input) => {
				input.removeAttribute('required');
			});
		}
	});

	let imageWidthThreshold = document.getElementById('images-width-threshold');
	let imageWidthMax = document.getElementById('images-width-max');

	let imageHeightThreshold = document.getElementById('images-height-threshold');
	let imageHeightMax = document.getElementById('images-height-max');

	imageWidthThreshold.addEventListener('keyup', function(){
		imageWidthMax.innerText = imageWidthThreshold.value.replace('px', '') + 'px';
	})

	imageHeightThreshold.addEventListener('keyup', function(){
		imageHeightMax.innerText = imageHeightThreshold.value.replace('px', '') + 'px';
	})
});
