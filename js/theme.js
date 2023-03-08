// Show Mobile Menu
document.getElementById("hamburger").addEventListener("click", function (event) {
    var menu_nav = document.getElementById("mobile-menu-nav");
    var header_logo = document.getElementById("header-logo");

    menu_nav.classList.toggle("dg-mobile-menu-active");
    header_logo.classList.toggle("header-logo-active");
});

jQuery(function($) {
  $(".menu-item-has-children").click(function (event) {
    let mobileMenu = document.getElementById("mobile-menu-nav");
    mobileMenu.classList.toggle("expand-sub-menu")
  });
});


// Show Searchbar
/* document.getElementById("show_searchbar").addEventListener("click", function (event) {
  var searchbar = document.getElementById("dg-searchbar");

  searchbar.classList.toggle("search-active");
});

document.getElementById("show_searchbar2").addEventListener("click", function (event) {
  var searchbar = document.getElementById("dg-searchbar");

  searchbar.classList.toggle("search-active");
});

// Close Searchbar
document.getElementById("close-searchbar").addEventListener("click", function (event) {
  var searchbar = document.getElementById("dg-searchbar");

  searchbar.classList.toggle("search-active");
}); */


// Scroll-Event Header-Bar
window.addEventListener("scroll", function (event) {
	var homenav = document.getElementById('home-header');
	if (window.scrollY > 100) {
    	homenav.classList.remove('home-header');
  	} else {
    	homenav.classList.add('home-header');
  	}
});