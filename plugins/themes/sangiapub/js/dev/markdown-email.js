/**
 * Ultra Simple Approach - Back to Basics
 * Tidak pakai contenteditable, tidak pakai complex logic
 * HANYA focus: ketik markdown → tampil formatted
 */

$(document).ready(function() {
    console.log('Ultra simple approach...');
    
    $('textarea').each(function() {
        var $textarea = $(this);
        
        // Skip jika sudah diproses
        if ($textarea.data('simple-processed')) return;
        $textarea.data('simple-processed', true);
        
        // Buat preview div yang simple
        var $preview = $('<div></div>').css({
            border: '1px solid #ccc',
            padding: '10px',
            minHeight: '100px',
            background: '#f9f9f9',
            fontFamily: 'Arial, sans-serif',
            fontSize: '14px',
            lineHeight: '1.6',
            marginTop: '5px',
            display: 'none'
        });
        
        // Buat tombol toggle simple
        var $toggle = $('<button type="button" style="margin: 5px 0; padding: 5px 10px; background: #007cbb; color: white; border: none;">Show Preview</button>');
        
        // Insert setelah textarea
        $textarea.after($toggle).after($preview);
        
        // Function convert markdown simple
        function convertToHtml(text) {
            return text
                .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                .replace(/\*([^*]+)\*/g, '<em>$1</em>')
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
                .replace(/\n/g, '<br>');
        }
        
        // Toggle preview
        $toggle.click(function() {
            if ($preview.is(':visible')) {
                $preview.hide();
                $toggle.text('Show Preview');
            } else {
                var content = $textarea.val();
                var html = convertToHtml(content);
                $preview.html(html);
                $preview.show();
                $toggle.text('Hide Preview');
            }
        });
        
        // Update preview saat mengetik (jika visible)
        $textarea.on('input', function() {
            if ($preview.is(':visible')) {
                var content = $textarea.val();
                var html = convertToHtml(content);
                $preview.html(html);
            }
        });
        
        console.log('Simple preview added');
    });
});