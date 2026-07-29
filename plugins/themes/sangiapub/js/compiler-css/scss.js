document.addEventListener('DOMContentLoaded', () => {
  // Template should render an array, e.g. ["assets/style1.scss","assets/style2.scss"]
  const scssUrls = {$scssUrls|@json_encode};

  if (!Array.isArray(scssUrls) || scssUrls.length === 0) {
    console.debug('No SCSS files to compile.');
    return;
  }

  // Optional helper to dynamically load a script (useful if Sass isn't already loaded)
  function loadScript(url) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = url;
      s.onload = () => resolve();
      s.onerror = () => reject(new Error('Failed to load script: ' + url));
      document.head.appendChild(s);
    });
  }

  // Wrap Sass.compile to support both sync and callback-style APIs
  function compileWithSass(scss) {
    return new Promise((resolve, reject) => {
      if (typeof Sass === 'undefined') {
        return reject(new Error('Sass runtime not available'));
      }

      try {
        // Some builds expose a synchronous compile that returns { css }
        const maybeResult = Sass.compile && Sass.compile.length === 1 ? Sass.compile(scss) : null;
        if (maybeResult && (maybeResult.css || maybeResult.text)) {
          resolve(maybeResult.css || maybeResult.text);
          return;
        }
      } catch (e) {
        // fallthrough to callback branch
      }

      // Many builds use callback-style: Sass.compile(scss, callbackOrOptions)
      try {
        Sass.compile(scss, result => {
          if (!result) return reject(new Error('Empty result from Sass.compile'));
          // result.status === 0 usually means success in sass.js
          if (result.status === 0) {
            // result.text or result.css depending on build
            resolve(result.text || result.css || '');
          } else {
            reject(new Error(result.formatted || result.message || 'Sass compilation error'));
          }
        });
      } catch (err) {
        reject(err);
      }
    });
  }

  // Compile one SCSS URL -> returns compiled CSS string or throws
  async function compileScssUrl(url) {
    const res = await fetch(url, { cache: 'no-cache' });
    if (!res.ok) throw new Error(`Failed to fetch ${url}: ${res.status} ${res.statusText}`);
    const scssContent = await res.text();
    const css = await compileWithSass(scssContent);
    return css;
  }

  (async () => {
    // If you want to dynamically load a Sass runtime, do it here.
    // Example (uncomment and set correct URL if needed):
    // if (typeof Sass === 'undefined') {
    //   await loadScript('https://cdn.jsdelivr.net/npm/sass.js@0.11.1/dist/sass.sync.js');
    // }

    // Kick off all fetch+compile tasks concurrently
    const tasks = scssUrls.map(url => (
      compileScssUrl(url)
        .then(css => ({ url, css, ok: true }))
        .catch(error => ({ url, error, ok: false }))
    ));

    const results = await Promise.all(tasks);

    // Insert styles in original order; replace previous style tags if present
    for (const r of results) {
      if (!r.ok) {
        console.error(`SCSS compile failed for ${r.url}:`, r.error);
        continue; // continue with others
      }

      // Remove previous compiled style for this source if present
      const existing = document.head.querySelector(`style[data-scss-src="${CSS.escape(r.url)}"]`);
      if (existing) existing.remove();

      const style = document.createElement('style');
      style.setAttribute('data-scss-src', r.url);
      style.textContent = r.css;
      document.head.appendChild(style);

      console.info(`Compiled and applied SCSS: ${r.url}`);
    }
  })().catch(err => {
    console.error('SCSS compilation process failed:', err);
  });
});
