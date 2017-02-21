$mt(function($){
    var body = $('body'),
        container = $('.mt-container'),
        wrapper = $('.mt-wrapper'),
        siteOverlay = $('.site-overlay'),
        pushyActiveClass = "mt-effect-slide mt-menu-open",
        menu = $('.mt-menu'),
        containerClassActive = "mt-container-active",
        menuWidth = menu.width() + "px",
        menuSpeed = 500,
        menuBtn = $('.navbar-toggle');
    function toggleMenu(){
        body.toggleClass(pushyActiveClass);
        wrapper.toggleClass(containerClassActive);
        menu.css('height', container.height());
    }

    function openMenuFallback(){
        body.addClass(pushyActiveClass);
        menu.animate({left: "0px", visibility: 'visible'}, menuSpeed);
        wrapper.animate({left: menuWidth}, menuSpeed); //css class to add pushy capability
    }

    function closeMenuFallback(){
        body.removeClass(pushyActiveClass);
        menu.animate({left: "-" + menuWidth, visibility: 'hidden'}, menuSpeed);
        wrapper.animate({left: "0px"}, menuSpeed); //css class to add pushy capability
    }

    $(window).resize(function(){
        var mainWidth = $(window).width();
        if(mainWidth > 767){
            body.removeClass(pushyActiveClass);
            if(!Modernizr.csstransforms3d){
                closeMenuFallback();
            }
        }
    });

    if(Modernizr.csstransforms3d){
        //toggle menu
        menuBtn.click(function() {
            toggleMenu();
        });
        //close menu when clicking site overlay
        siteOverlay.click(function(){
            toggleMenu();
        });
    }else{
        //jQuery fallback
        menu.css({left: "-" + menuWidth}); //hide menu by default
        container.css({"overflow-x": "hidden"}); //fixes IE scrollbar issue

        //keep track of menu state (open/close)
        var state = true;

        //toggle menu
        menuBtn.click(function() {
            if (state) {
                openMenuFallback();
                state = false;
            } else {
                closeMenuFallback();
                state = true;
            }
        });

        //close menu when clicking site overlay
        siteOverlay.click(function(){
            if (state) {
                openMenuFallback();
                state = false;
            } else {
                closeMenuFallback();
                state = true;
            }
        });
    }
});