// skip-to-content
jQuery(document).ready(function($){
	$( ".mtoggle" ).click(function() {
		$( ".menu" ).slideToggle(500);
	});
    $("#skip-to-content").click(function() {
        $("#content").focus()
    });
});

// buttontop
var btn = $('.buttontop');

$(window).scroll(function() {
  if ($(window).scrollTop() > 300) {
    btn.addClass('show');
  } else {
    btn.removeClass('show');
  }
});

btn.on('click', function(e) {
  e.preventDefault();
  $('html, body').animate({scrollTop:0}, '300');
});

/**
 * Inisialisasi IP Address user.
 */
async function getIPAddress() {
  try {
    const response = await fetch('https://api.ipify.org?format=json');
    const data = await response.json();
    document.getElementById('diagnostic-ip').textContent = `IP Address: ${data.ip}`;
  } catch (error) {
    console.error('Error fetching IP address:', error);
  }
}

document.addEventListener('DOMContentLoaded', getIPAddress);

/**
 * Kode lainnya.
 */

