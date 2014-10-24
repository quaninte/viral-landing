/*
*
* Custom js snippets for Startuply v1.1
* by Vivaco 
*
*/
(function(){
	"use strict";
	// Init global DOM elements, functions and arrays
    window.app 			         = {el : {}, fn : {}};
	app.el['window']         = $(window);
	app.el['document']       = $(document);
    app.el['loader']         = $('#loader');
    app.el['mask']           = $('#mask');
	
	app.fn.screenSize = function() {
		var size, width = app.el['window'].width();
		if(width < 320) size = "Not supported";
		else if(width < 480) size = "Mobile portrait";
		else if(width < 768) size = "Mobile landscape";
		else if(width < 960) size = "Tablet";
		else size = "Desktop";
		// $('#screen').html( size + ' - ' + width );
		// console.log( size, width );
	};	
	
    //Preloader
    app.el['loader'].delay(700).fadeOut();
    app.el['mask'].delay(1200).fadeOut("slow");    
      
		// Resized based on screen size
		app.el['window'].resize(function() {
			app.fn.screenSize();
		});		
      

    // Animated Appear Element
	if (app.el['window'].width() > 1024){
		
		$('.animated').appear(function() {
		  var element = $(this);
		  var animation = element.data('animation');
		  var animationDelay = element.data('delay');
		  if(animationDelay) {
			  setTimeout(function(){
				  element.addClass( animation + " visible" );
				  element.removeClass('hiding');
			  }, animationDelay);
		  } else {
			  element.addClass( animation + " visible" );
			  element.removeClass('hiding');
		  }               

		}, {accY: -150});
    
	} else {
	
		$('.animated').css('opacity', 1);
		
	}
	
    // fade in .back-to-top
    $(window).scroll(function () {
        if ($(this).scrollTop() > 500) {
            $('.back-to-top').fadeIn();
        } else {
            $('.back-to-top').fadeOut();
        }
    });

    // scroll body to 0px on click
    $('.back-to-top').click(function () {
        $('html, body').animate({
            scrollTop: 0,
            easing: 'linear'
        }, 750);
        return false;
    });

    $('.to-sign-up-form').click(function(e) {
        $('html, body').animate({
            scrollTop: $("#sign-up").offset().top,
            easing: 'linear'
        }, 750, function() {
            $('#email-input').focus();
        });

        e.preventDefault();
    });

    $('.arrow2').click(function(e) {
        $('html, body').animate({
            scrollTop: $(".arrow3").offset().top,
            easing: 'linear'
        }, 1000, function() {
            $('.arrow3').hover();
        });

        e.preventDefault();
    });
    $('.arrow3').click(function(e) {
        $('html, body').animate({
            scrollTop: $(".arrow4").offset().top,
            easing: 'linear'
        }, 1000, function() {
            $('.arrow4').hover();
        });

        e.preventDefault();
    });
    $('.arrow4').click(function(e) {
        $('html, body').animate({
            scrollTop: $(".arrow5").offset().top,
            easing: 'linear'
        }, 1000, function() {
            $('.arrow5').hover();
        });

        e.preventDefault();
    });
    $('.arrow5').click(function(e) {
        $('html, body').animate({
            scrollTop: $("#sign-up-form-2").offset().top,
            easing: 'linear'
        }, 1000, function() {
            $('#sign-up-form-2').focus();
        });

        e.preventDefault();
    });

    $(window).ready(function() {
        var wi = $(window).width();
        if (wi <= 767){
            $('.arrow1').click(function(e) {
                $('html, body').animate({
                    scrollTop: $(".arrow2").offset().top,
                    easing: 'linear'
                }, 1000, function() {
                    $('.arrow2').hover();
                });

                e.preventDefault();
            });
        }
        else if (wi > 767){
            $('.arrow1').click(function(e) {
                $('html, body').animate({
                    scrollTop: $("#join-form .word-hard").offset().top,
                    easing: 'linear'
                }, 1000, function() {
                    $('.arrow3').hover();
                });

                e.preventDefault();
            });
        }
    });

})();