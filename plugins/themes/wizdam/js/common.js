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
 * Warna teks pada header journal.
 */
document.addEventListener("DOMContentLoaded", function () {
    const sectionElement = document.querySelector('.sc-1oj9st5-1.kSjVSRM');
    const sectionStyle = window.getComputedStyle(sectionElement);
    const backgroundImageUrl = sectionStyle.backgroundImage.slice(5, -2); // Menghapus 'url("...")'

    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.src = backgroundImageUrl;

    img.onload = function () {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        context.drawImage(img, 0, 0, img.width, img.height);

        // Fungsi untuk menghitung luminance rata-rata dari sampel piksel
        function getAverageLuminance(x, y, width, height) {
            const imageData = context.getImageData(x, y, width, height);
            const data = imageData.data;
            let luminanceSum = 0;
            let count = 0;

            // Melompat setiap 10 piksel dalam sumbu x dan y untuk sampel
            for (let i = 0; i < data.length; i += 4 * 10) {
                const r = data[i] / 255;
                const g = data[i + 1] / 255;
                const b = data[i + 2] / 255;

                // Hitung luminance
                const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
                luminanceSum += luminance;
                count++;
            }

            return luminanceSum / count;
        }

        // Fungsi untuk menentukan warna teks yang kontras berdasarkan luminance
        function getContrastingTextColor(luminance) {
            return luminance > 0.5 ? 'rgb(0, 0, 0)' : 'rgb(255, 255, 255)';
        }

        // Fungsi untuk menambahkan bayangan teks untuk keterbacaan yang lebih baik
        function applyTextShadow(element, textColor) {
            if (textColor === 'rgb(255, 255, 255)') {
                element.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.7)';
            } else {
                element.style.textShadow = '2px 2px 4px rgba(255, 255, 255, 0.7)';
            }
        }

        // Tentukan area sekitar elemen teks untuk perhitungan luminance
        function adjustTextColor(element) {
            const rect = element.getBoundingClientRect();
            const scaleX = img.width / sectionElement.clientWidth;
            const scaleY = img.height / sectionElement.clientHeight;

            const luminance = getAverageLuminance(
                rect.left * scaleX,
                rect.top * scaleY,
                rect.width * scaleX,
                rect.height * scaleY
            );

            const textColor = getContrastingTextColor(luminance);
            element.style.color = textColor;

            applyTextShadow(element, textColor);
        }

        // Ubah warna teks berdasarkan luminance area
        const titleText = document.querySelector('.js-title-text');
        const openStatement = document.querySelector('.js-open-statement');
        const textElements = document.querySelectorAll('.sc-thb58v-8 span');

        if (titleText) {
            adjustTextColor(titleText);
        }

        if (openStatement) {
            adjustTextColor(openStatement);
        }

        textElements.forEach(function (element) {
            adjustTextColor(element);
        });
    };

    img.onerror = function () {
        console.error('Gagal memuat gambar latar belakang.');
    };
});

/**
 * Style menu members dan about journals 
 */
document.addEventListener('DOMContentLoaded', () => {
    const details1 = document.querySelector('details.Details-2932877531');
    const details2 = document.querySelector('details.Details-3185356244');
    const summary1 = details1 ? details1.querySelector('summary') : null;
    const summary2 = details2 ? details2.querySelector('summary') : null;

    function toggleDetails(detailsToOpen, detailsToClose) {
        if (detailsToOpen.hasAttribute('open')) {
            detailsToOpen.removeAttribute('open');
        } else {
            detailsToOpen.setAttribute('open', '');
            detailsToClose.removeAttribute('open');
        }
    }

    // [FIX] Listener moved from the whole <details> to its <summary> only.
    // Attaching it to <details> caught every click bubbling up from inside
    // (including the Subscribe/Pay/Learn more buttons in the <article> body)
    // and cancelled their default action via preventDefault() — that's why
    // those buttons stopped submitting/navigating. <summary> is also the
    // only element that natively toggles <details>, so behavior is unchanged.
    if (summary1) {
        summary1.addEventListener('click', (event) => {
            event.preventDefault();
            toggleDetails(details1, details2);
        });
    }

    if (summary2) {
        summary2.addEventListener('click', (event) => {
            event.preventDefault();
            toggleDetails(details2, details1);
        });
    }
});

/**
 * Style side menu about journals 
 */
document.addEventListener('DOMContentLoaded', () => {
    const menuList = document.querySelector('.s-sidemenu');
    const menuItems = menuList.querySelectorAll('li.c-sidemenu');

    /**
     * Menetapkan item menu saat ini berdasarkan URL
     */
    const setCurrentMenuItem = () => {
        const currentUrl = window.location.href;
        menuItems.forEach(item => {
            const link = item.querySelector('a');
            if (link && link.href === currentUrl) {
                item.classList.add('menu-item--current');
            } else {
                item.classList.remove('menu-item--current');
            }
        });
    };

    setCurrentMenuItem();

    menuList.addEventListener('click', (event) => {
        const clickedItem = event.target.closest('li.c-sidemenu');
        if (clickedItem) {
            menuItems.forEach(item => item.classList.remove('menu-item--current'));
            clickedItem.classList.add('menu-item--current');

            const link = clickedItem.querySelector('a');
            if (link) {
                window.location.href = link.href;
            }
        }
    });
});

/**
 * Style side menu submission c-bar--menu 
 */
document.addEventListener('DOMContentLoaded', () => {
    const menuList = document.querySelector('.c-sidemenu');
    const menuItems = Array.from(menuList.querySelectorAll('.c-bar--menu'));

    /**
     * Menetapkan item menu saat ini berdasarkan URL dan anchor.
     * Menambahkan class 'c-menu--current' pada elemen utama yang sesuai,
     * dan menambahkan class 'is-active' pada elemen anchor yang sesuai.
     * Menampilkan atau menyembunyikan submenu berdasarkan status 'c-menu--current'.
     */
    const setCurrentMenuItem = () => {
        const currentUrl = window.location.href;
        const currentAnchor = window.location.hash;

        menuItems.forEach(item => {
            const link = item.querySelector('a');
            const subMenu = item.querySelector('.c-menu--anchor.c-nav.c-nav--stacked');

            if (link) {
                const linkUrl = new URL(link.href);
                const linkAnchor = linkUrl.hash;

                if (linkUrl.origin + linkUrl.pathname === window.location.origin + window.location.pathname) {
                    if (!linkAnchor) {
                        item.classList.add('c-menu--current');
                        if (subMenu && !item.contains(subMenu)) {
                            item.appendChild(subMenu);
                        }
                    } else {
                        item.classList.remove('c-menu--current');
                    }

                    if (linkAnchor && linkAnchor === currentAnchor) {
                        item.classList.add('is-active');
                        if (subMenu && !item.contains(subMenu)) {
                            item.appendChild(subMenu);
                        }
                    } else {
                        item.classList.remove('is-active');
                    }
                } else {
                    item.classList.remove('is-active');
                    item.classList.remove('c-menu--current');
                    if (subMenu && item.contains(subMenu)) {
                        item.removeChild(subMenu);
                    }
                }
            }
        });
    };

    setCurrentMenuItem();

    /**
     * Menangani event klik pada menu.
     */
    menuList.addEventListener('click', (event) => {
        const clickedItem = event.target.closest('.c-bar--menu');
        if (clickedItem) {
            const link = clickedItem.querySelector('a');
            const subMenu = clickedItem.querySelector('.c-menu--anchor.c-nav.c-nav--stacked');
            if (link && subMenu) {
                const clickedAnchor = new URL(link.href).hash;

                menuItems.forEach(item => {
                    item.classList.remove('c-menu--current');
                    item.classList.remove('is-active');
                    const subMenuItem = item.querySelector('.c-menu--anchor.c-nav.c-nav--stacked');
                    if (subMenuItem && item.contains(subMenuItem) && item !== clickedItem) {
                        item.removeChild(subMenuItem);
                    }
                });

                if (!clickedAnchor) {
                    clickedItem.classList.add('c-menu--current');
                    if (!clickedItem.contains(subMenu)) {
                        clickedItem.appendChild(subMenu);
                    }
                }

                if (clickedAnchor) {
                    clickedItem.classList.add('is-active');
                    if (!clickedItem.contains(subMenu)) {
                        clickedItem.appendChild(subMenu);
                    }
                }

                window.location.href = link.href;
            }
        }
    });

    window.addEventListener('hashchange', setCurrentMenuItem);
});

// Pastikan kode-kode tambahan diletakkan di sini.