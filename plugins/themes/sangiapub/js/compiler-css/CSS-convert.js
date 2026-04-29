// File: js/CSS-convert.js
const fs = require('fs');
const path = require('path');
const sass = require('node-sass'); // Menggunakan node-sass [[3]]

// Fungsi untuk membaca semua file CSS dari folder
function processCssFiles(cssDir, scssOutputDir, cssOutputDir) {
    try {
        // Baca semua file CSS dari folder
        const cssFiles = fs.readdirSync(cssDir).filter(file => file.endsWith('.css'));

        // Proses setiap file CSS
        cssFiles.forEach(cssFile => {
            const cssFilePath = path.join(cssDir, cssFile);
            const scssFileName = cssFile.replace('.css', '.scss'); // Contoh: layout.css -> layout.scss
            const scssOutputPath = path.join(scssOutputDir, scssFileName);

            // Konversi CSS ke SCSS
            convertCssToScss(cssFilePath, scssOutputPath);

            // Kompilasi SCSS ke CSS dengan nama berbasis timestamp
            const timestamp = Date.now(); // Waktu saat ini dalam milidetik
            const cssFileName = `enhanced-sangia-${path.basename(cssFile, '.css')}-${timestamp}.css`;
            const cssOutputPath = path.join(cssOutputDir, cssFileName);

            compileScssToCss(scssOutputPath, cssOutputPath);
        });

        console.log(`Proses konversi dan kompilasi selesai.`);
    } catch (error) {
        console.error('Terjadi kesalahan:', error);
    }
}

// Fungsi untuk mengonversi CSS ke SCSS
function convertCssToScss(cssFilePath, scssOutputPath) {
    try {
        const cssContent = fs.readFileSync(cssFilePath, 'utf8');
        let scssContent = cssContent
            .replace(/([^{]+)\{/g, (match, selector) => `${selector.trim()} {\n`)
            .replace(/;/g, ';\n')
            .replace(/\}/g, '\n}\n');

        fs.writeFileSync(scssOutputPath, scssContent, 'utf8');
        console.log(`Konversi berhasil. File SCSS disimpan di: ${scssOutputPath}`);
    } catch (error) {
        console.error('Gagal mengonversi CSS ke SCSS:', error);
    }
}

// Fungsi untuk mengompilasi SCSS ke CSS
function compileScssToCss(scssFilePath, cssOutputPath) {
    sass.render(
        {
            file: scssFilePath,
            outputStyle: 'compressed',
        },
        (err, result) => {
            if (err) {
                console.error('Gagal mengompilasi SCSS:', err);
            } else {
                fs.writeFileSync(cssOutputPath, result.css, 'utf8');
                console.log(`SCSS berhasil dikompilasi. File CSS disimpan di: ${cssOutputPath}`);
            }
        }
    );
}

// Path folder sumber dan output
const cssDir = path.join(__dirname, '../css'); // Folder CSS sumber
const scssOutputDir = path.join(__dirname, '../css/sass'); // Folder output SCSS
const cssOutputDir = path.join(__dirname, '../css'); // Folder output CSS

// Jalankan proses konversi dan kompilasi
processCssFiles(cssDir, scssOutputDir, cssOutputDir);