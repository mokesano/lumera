        document.addEventListener("DOMContentLoaded", function () {
            const scssUrls = {$scssUrls|@json_encode};

            // Fungsi untuk mengompilasi SCSS ke CSS
            function compileScssToCss(scssUrl) {
                fetch(scssUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Gagal memuat file SCSS (${scssUrl}): ${response.statusText}`);
                        }
                        return response.text();
                    })
                    .then(scssContent => {
                        try {
                            // Kompilasi SCSS ke CSS menggunakan SCSS.js
                            const result = Sass.compile(scssContent);
                            const cssContent = result.css;

                            // Buat elemen <style> dan tambahkan CSS ke DOM
                            const styleElement = document.createElement("style");
                            styleElement.textContent = cssContent;
                            document.head.appendChild(styleElement);

                            console.log(`SCSS (${scssUrl}) berhasil dikompilasi dan diterapkan.`);
                        } catch (error) {
                            console.error(`Gagal mengompilasi SCSS (${scssUrl}):`, error);
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }

            // Kompilasi semua file SCSS
            scssUrls.forEach(scssUrl => compileScssToCss(scssUrl));
        });