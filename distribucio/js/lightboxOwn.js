/****************************************
	Barebones Lightbox Template
	by Kyle Schaeffer
	http://www.kyleschaeffer.com
	* requires jQuery
****************************************/

// display the lightbox
function lightbox(insertContent, ajaxContentUrl){

	// jQuery wrapper (optional, for compatibility only)
	(function($) {
	
		// add lightbox/shadow <div/>'s if not previously added
		if($('#lightboxOwn').size() == 0){
			var theLightbox = $('<div id="lightboxOwn"/>');
			var theShadow = $('<div id="lightboxOwn-shadow"/>');
                        var theClose = $('<div id="lightboxOwn-close"><img src="/img/closelabel.gif"></div>');
			$(theShadow).click(function(e){
				closeLightbox();
			});
                        $(theClose).click(function(e){
				closeLightbox();
			});
                        
                        
			$('body').append(theShadow);
			$('body').append(theLightbox);
                        $('body').append(theClose);
		}
		
                
                $(window).scroll(function () {  

                    // move the lightbox to the current window top + 100px
                    $('#lightboxOwn').css('top', $(window).scrollTop() + 100 + 'px');
                    $('#lightboxOwn-close').css('top', $(window).scrollTop() + 100 + 'px');
                    // move the lightbox to the current window top + 100px
                    $('#lightboxOwn-shadow').css('top', $(window).scrollTop() + 'px');
                });
                
                
		// remove any previously added content
		$('#lightboxOwn').empty();
		
		// insert HTML content
		if(insertContent != null){
			$('#lightboxOwn').append(insertContent);
		}
		
		// insert AJAX content
		if(ajaxContentUrl != null){
			// temporarily add a "Loading..." message in the lightbox
			$('#lightboxOwn').append('<p class="loading">Loading...</p>');
			
			// request AJAX content
			$.ajax({
				type: 'GET',
				url: ajaxContentUrl,
				success:function(data){
					// remove "Loading..." message and append AJAX content
					$('#lightboxOwn').empty();
					$('#lightboxOwn').append(data);
				},
				error:function(){
					alert('AJAX Failure!');
				}
			});
		}
		
		// move the lightbox to the current window top + 100px
		$('#lightboxOwn').css('top', $(window).scrollTop() + 100 + 'px');
                $('#lightboxOwn-close').css('top', $(window).scrollTop() + 100 + 'px');
                // move the lightbox to the current window top + 100px
		$('#lightboxOwn-shadow').css('top', $(window).scrollTop() + 'px');
                
		
		// display the lightbox
		$('#lightboxOwn').show();
		$('#lightboxOwn-shadow').show();
                $('#lightboxOwn-close').show()
	
	})(jQuery); // end jQuery wrapper
	
}

// close the lightbox
function closeLightbox(){
	
	// jQuery wrapper (optional, for compatibility only)
	(function($) {
		
		// hide lightbox/shadow <div/>'s
		$('#lightboxOwn').hide();
		$('#lightboxOwn-shadow').hide();
                $('#lightboxOwn-close').hide()
		
		// remove contents of lightbox in case a video or other content is actively playing
		$('#lightboxOwn').empty();
                
	
	})(jQuery); // end jQuery wrapper
	
}