<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/jqueryScripts.tpl */ ?>
<meta name="referrerpolicy" content="strict-origin-when-cross-origin">
<!-- Cookies CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<!-- Lazyloads -->
<script>
<?php echo '
  if (\'loading\' in HTMLImageElement.prototype) {
    const images = document.querySelectorAll(\'img[loading="lazy"]\');
    images.forEach(img => {
      img.src = img.dataset.src;
    });
  } else {
    // Dynamically import the LazySizes library
    const script = document.createElement(\'script\');
    script.src =
      \'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.1.2/lazysizes.min.js\';
    document.body.appendChild(script);
  }
'; ?>

</script>
<!-- End Lazyloads -->

<!-- OneTrust Cookies Consent Notice -->
<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="ef8d6a4d-3871-4684-91c9-80259f6aacfe-test" referrerpolicy="strict-origin-when-cross-origin"></script>
<!-- End OneTrust Cookies Consent Notice -->