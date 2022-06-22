var lazyImageObserver;

function documentReady(fn) {
  if (document.readyState != 'loading'){ fn(); }
  else { document.addEventListener('DOMContentLoaded', fn); }
}

documentReady(function() {
	
	// Lazy load
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
	
	// On filter change
	document.querySelectorAll('.posts-filter').forEach(e =>
		e.addEventListener('change', (event) => {
			sendAJAX();
	}));

	// On initial load
	sendAJAX();
});

function loadMore() {
	const loadMore = document.getElementById('load_more');
	const data = {
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
					disableLoadMore(false);
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
	const lazyImages = [].slice.call(document.querySelectorAll('.post-container'));
	lazyImages.forEach(function(lazyImage) { lazyImageObserver.observe(lazyImage); });
} 

function disableButtons() {
	const buttons = document.querySelectorAll('.radio-toolbar label');
	buttons.forEach(function(e) { e.classList.add('disabled'); });

	const filters = document.querySelectorAll('.posts-filter');
	filters.forEach(function(e) { e.disabled = true; });
}

function enableButtons() {
	const buttons = document.querySelectorAll('.radio-toolbar label');
	buttons.forEach(function(e) { e.classList.remove('disabled'); });

	const filters = document.querySelectorAll('.posts-filter');
	filters.forEach(function(e) { e.disabled = false; });
}

function incrementLoadMore() {
	const loadMore = document.querySelector('#load_more');
	loadMore.dataset.currentPage++;
	loadMore.dataset.nextPage++;
	
	if (loadMore.dataset.currentPage == loadMore.dataset.maxPage) {
		loadMore.style.visibility = 'hidden';
	}
}

documentReady(function() {
	let last_known_scroll_position = 0;
	let ticking = false;

	document.addEventListener('scroll', function(e) {
	  last_known_scroll_position = window.scrollY;

	  if (!ticking) {
		window.requestAnimationFrame(function() {
		  scrollInfinite();
		  ticking = false;
		});

		ticking = true;
	  }
	});
});

function scrollInfinite() {
	const isInfinite = document.querySelector('input[name=infinite_scroll]').value === 'true';

	if (isInfinite) {

		var loadMoreButton = document.getElementById("load_more");

		const isDisabled = loadMoreButton.disabled;
		const isHidden = loadMoreButton.style.visibility === 'hidden';

		const offsetTop = (loadMoreButton.getBoundingClientRect().top + document.documentElement.scrollTop);

		if( (jQuery(this).scrollTop() + jQuery(window).height() ) >= offsetTop && !isDisabled && !isHidden){
			disableLoadMore(true);
			var event = document.createEvent('HTMLEvents');
			event.initEvent('click', true, false);
			loadMoreButton.dispatchEvent(event);
		}
	}
}

function disableLoadMore(disabled) {
	document.getElementById("load_more").disabled = disabled;
}
