var lazyImageObserver;
		
jQuery( document ).ready(function($) {
	
	lazyImageObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				
				if (!entry.target.classList.contains('fadeIn')) {
					let lazyImage = entry.target.getElementsByTagName('img')[0];

					lazyImage.src = lazyImage.dataset.src;
					lazyImage.srcset = lazyImage.dataset.srcset;

					lazyImage.removeAttribute('data-src');
					lazyImage.removeAttribute('data-srcset');

					entry.target.classList.remove('lazy');
					lazyImageObserver.unobserve(entry.target);

					entry.target.classList.add('fadeIn');
					entry.target.classList.remove('loading');
				}

			}
		});
	});
	
	// On button change
	$('.posts-filter').change(function(){
		sendAJAX();
		return false;
	});

	// On load
	sendAJAX();
	return false;
});

function load_more() {
	const loadMore = document.querySelector('#load_more');
	
	data = {
		paged: loadMore.dataset.nextPage
	}
	
	sendAJAX(data);
}
		
function sendAJAX(loadMoreData) {
	var filter = jQuery('#filter');
	var data = filter.serialize();
	
	if (loadMoreData) {
		var data = data + '&' +  jQuery.param(loadMoreData);
	}
	
	jQuery.ajax({
		url:filter.attr('action'),
		data:data,
		type:filter.attr('method'),
		startTime:new Date().getTime(),
		beforeSend:function(xhr){
			disableButtons();
			
			if (!loadMoreData) {
				jQuery('.post-container').addClass('fadeOut');
			}
		},
		success:function(data){
			var endTime = new Date().getTime();
			var totalTime = endTime - this.startTime;
			var timeToWait = 0;
			
			if (totalTime >= 400) { 
				timeToWait = 0; 
			} else {
				timeToWait = 400 - totalTime; 
			}

			setTimeout(function(){
				
				if (loadMoreData) {
					jQuery('.posts').append(data);
					incrementLoadMore();
				} else {
					jQuery('#response').html(data);
				}
				
				updateObserver();
				
				setTimeout(function(){
					enableButtons();
				}, 800);
				
			}, timeToWait);
			
		}
	});
}

function updateObserver() {
	let lazyImages = [].slice.call(document.querySelectorAll('.post-container'));
	lazyImages.forEach(function(lazyImage) {
		lazyImageObserver.observe(lazyImage);
	});
} 

function disableButtons() {
	jQuery('.radio-toolbar label').css('opacity', '0.5');
	jQuery('.radio-toolbar label').css('cursor', 'default');
	jQuery('.posts-filter').attr('disabled', true);
}

function enableButtons() {
	jQuery('.radio-toolbar label').css('opacity', '1.0');
	jQuery('.radio-toolbar label').css('cursor', 'pointer');
	jQuery('.posts-filter').attr('disabled', false);
}

function incrementLoadMore() {
	const loadMore = document.querySelector('#load_more');
	loadMore.dataset.currentPage++;
	loadMore.dataset.nextPage++;
	
	if (loadMore.dataset.currentPage == loadMore.dataset.maxPage) {
		jQuery('#load_more').css('visibility', 'hidden');
	}
}
