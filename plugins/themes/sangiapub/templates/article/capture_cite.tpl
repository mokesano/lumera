{** Modal citation — VERSI UJI (all-in-one) **}
<div id="sangia-citation-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2em; max-width:640px; width:90%; max-height:80vh; overflow-y:auto; border-radius:7px; position:relative; box-shadow:0 4px 24px rgba(0,0,0,0.2);">
        <h2 style="font-weight:600; margin:0 0 1em 0; color:#1a1a1a;">Export Citation</h2>
        <button id="sangia-modal-close-btn" aria-label="Close dialog" style="position:absolute; top:.75em; right:.75em; background:none; border:none; cursor:pointer; padding:.25em; display:flex; align-items:center; color:#555; border-radius:2px;">
            <svg focusable="false" viewBox="0 0 128 128" width="20" height="20" aria-hidden="true">
                <path d="M113 21L107 15 64 58 21 15 15 21l43 43L15 107l6 6 43-43 43 43 6-6-43-43z"/>
            </svg>
        </button>
        <div style="display:flex; align-items:center; gap:.75em; padding-bottom:1em; border-bottom:1px solid #e0e0e0;">
            <label for="sangia-citation-format" style="font-size:initial; font-weight:600; color:#555; white-space:nowrap;">Format</label>
            <select id="sangia-citation-format" style="flex:1; height:2.25em; padding:0 .75em; border:1px solid #ccc; border-radius:3px; color:#1a1a1a; background:#fff; cursor:pointer;"></select>
        </div>
        <div id="sangia-citation-content"></div>
    </div>
</div>
{literal}
<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var modal   = document.getElementById('sangia-citation-modal');
        var content = document.getElementById('sangia-citation-content');
        var select  = document.getElementById('sangia-citation-format');

        if (!modal || !content || !select) {
            console.error('sangia-citation: elemen modal tidak ditemukan');
            return;
        }

        var baseUrl     = null; // URL captureCite tanpa nama plugin
        var formatsData = [];   // cache hasil getFormats

        function openModal()  { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        function loadCitation(plugin) {
            content.innerHTML = '<div style="display:flex;justify-content:center;padding:2.5em 0;"><svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"><circle cx="18" cy="18" r="14" fill="none" stroke="#e0e0e0" stroke-width="3"/><circle cx="18" cy="18" r="14" fill="none" stroke="#0070c0" stroke-width="3" stroke-linecap="round" stroke-dasharray="22 66"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.8s" repeatCount="indefinite"/></circle></svg></div>';
            fetch(baseUrl + '/' + plugin + '?isModal=1')
                .then(function (r) { return r.text(); })
                .then(function (html) { content.innerHTML = html; })
                .catch(function () {
                    content.innerHTML = '<p style="color:red;">Gagal memuat sitasi.</p>';
                });
        }

        function populateDropdown(formats) {
            select.innerHTML = '';
            formats.forEach(function (f) {
                var opt   = document.createElement('option');
                opt.value = f.plugin;
                opt.text  = f.label;
                select.appendChild(opt);
            });
        }

        function openWithPlugin(plugin) {
            if (formatsData.length > 0) {
                select.value = plugin;
                openModal();
                loadCitation(plugin);
                return;
            }
        
            // Tampilkan spinner saat fetch formats
            content.innerHTML = '<div style="display:flex;justify-content:center;padding:2.5em 0;"><svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"><circle cx="18" cy="18" r="14" fill="none" stroke="#e0e0e0" stroke-width="3"/><circle cx="18" cy="18" r="14" fill="none" stroke="#0070c0" stroke-width="3" stroke-linecap="round" stroke-dasharray="22 66"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.8s" repeatCount="indefinite"/></circle></svg></div>';
            openModal();
        
            fetch(baseUrl + '?getFormats=1')
                .then(function (r) { return r.json(); })
                .then(function (formats) {
                    formatsData = formats;
                    populateDropdown(formats);
                    select.value = plugin;
                    loadCitation(plugin);
                })
                .catch(function () {
                    content.innerHTML = '<p style="color:red;">Gagal memuat daftar format.</p>';
                });
        }

        // Ganti format via dropdown
        select.addEventListener('change', function () {
            loadCitation(this.value);
        });

        // Intercept semua form di popover
        document.querySelectorAll('#export-citation-popover form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var action  = form.getAttribute('action') || '';
                // Ambil base URL dan nama plugin dari action
                // action = ".../rt/captureCite/articleId/galleyId/NamaPlugin"
                var parts   = action.split('/');
                var plugin  = parts.pop();
                baseUrl     = parts.join('/');
                openWithPlugin(plugin);
            });
        });

        // Tutup
        document.getElementById('sangia-modal-close-btn')
            .addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });
    });
}());
</script>
{/literal}