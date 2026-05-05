document.addEventListener('DOMContentLoaded', function(){
	document.querySelectorAll('button.show-more').forEach((button) => {
		button.addEventListener('click', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();

			e.target.parentNode.querySelector('.list-downloads').classList.add('open');
			e.target.style.display = 'none';
		});
	});

	// Add click events to log downloads
	document.querySelectorAll('a.link-downloads').forEach((link) => {
		link.addEventListener('click', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();

			// Get the log to download
			let log = e.target.dataset.log;

			// Download the log, then convert it into a clickable link and trigger it to get the browser to return it as a download
			fetch(document.location.protocol + '//' + document.location.host + '/wp-json/' + wordKeeperApiPath + 'log/download', {
				method: 'POST',
				body: 'log=' + log,
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					'X-Wp-Nonce': wpApiSettings.nonce
				}
			}).then(function(response){
				// Download log if request succeeded
				if(response.status == 200){
					response.blob().then((blob) => {
						let url = window.URL.createObjectURL(blob);
						let a = document.createElement('a');
						a.href = url;
						a.download = log;
						document.body.appendChild(a);
						a.click();
						a.remove();
						URL.revokeObjectURL(url);
					});
				}
				// Show an error notice if the request failed
				else{
					wordkeeper.notice.fire('error', [{text: 'A problem occurred.  Contact support.', action: ''}]);
				}
			});
		});
	});
});