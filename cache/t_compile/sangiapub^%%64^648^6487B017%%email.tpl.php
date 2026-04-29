<?php /* Smarty version 2.6.26, created on 2026-04-28 00:39:46
         compiled from email/email.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'email/email.tpl', 369, false),array('modifier', 'assign', 'email/email.tpl', 394, false),array('modifier', 'substr', 'email/email.tpl', 501, false),array('function', 'translate', 'email/email.tpl', 390, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "email.compose"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "email.email"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
function deleteAttachment(fileId) {
	var emailForm = document.getElementById(\'emailForm\');
	emailForm.deleteAttachment.value = fileId;
	emailForm.submit();
}

function showCc() {
	document.getElementById(\'ccField\').style.display = \'flex\';
	document.getElementById(\'ccToggle\').style.display = \'none\';
}

function hideCc() {
	document.getElementById(\'ccField\').style.display = \'none\';
	document.getElementById(\'ccToggle\').style.display = \'inline-block\';
}

function showBcc() {
	document.getElementById(\'bccField\').style.display = \'flex\';
	document.getElementById(\'bccToggle\').style.display = \'none\';
}

function hideBcc() {
	document.getElementById(\'bccField\').style.display = \'none\';
	document.getElementById(\'bccToggle\').style.display = \'inline-block\';
}

// Remove recipient functionality
function removeRecipient(element) {
	var recipientChip = element.parentNode;
	recipientChip.remove();
}

// File preview functionality with drag & drop
function initFilePreview() {
	var fileInput = document.getElementById(\'fileInput\');
	var filePreviewList = document.getElementById(\'filePreviewList\');
	var uploadBtn = document.querySelector(\'.upload-btn\');
	var uploadBox = document.getElementById(\'uploadBox\');
	
	// Prevent default drag behaviors
	[\'dragenter\', \'dragover\', \'dragleave\', \'drop\'].forEach(eventName => {
		uploadBox.addEventListener(eventName, preventDefaults, false);
		document.body.addEventListener(eventName, preventDefaults, false);
	});
	
	// Highlight drop area when item is dragged over it
	[\'dragenter\', \'dragover\'].forEach(eventName => {
		uploadBox.addEventListener(eventName, highlight, false);
	});
	
	[\'dragleave\', \'drop\'].forEach(eventName => {
		uploadBox.addEventListener(eventName, unhighlight, false);
	});
	
	// Handle dropped files
	uploadBox.addEventListener(\'drop\', handleDrop, false);
	
	function preventDefaults(e) {
		e.preventDefault();
		e.stopPropagation();
	}
	
	function highlight(e) {
		uploadBox.classList.add(\'drag-over\');
	}
	
	function unhighlight(e) {
		uploadBox.classList.remove(\'drag-over\');
	}
	
	function handleDrop(e) {
		var dt = e.dataTransfer;
		var files = dt.files;
		handleFiles(files);
	}
	
	// File input change
	if (fileInput && filePreviewList) {
		fileInput.addEventListener(\'change\', function(e) {
			var files = e.target.files;
			if (files.length > 0) {
				handleFiles(files);
			}
		});
	}
	
	function handleFiles(files) {
		filePreviewList.innerHTML = \'\';
		
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			createFilePreview(file);
		}
		
		if (uploadBtn) {
			uploadBtn.style.display = \'inline-block\';
		}
		if (uploadBox) {
			uploadBox.classList.add(\'file-selected\');
		}
	}
	
	function createFilePreview(file) {
		var fileName = file.name;
		var fileSize = formatFileSize(file.size);
		var fileType = getFileType(fileName);
		
		var previewDiv = document.createElement(\'div\');
		previewDiv.className = \'file-preview-item\';
		
		var iconDiv = document.createElement(\'div\');
		iconDiv.className = \'file-preview-icon\';
		
		// Create preview based on file type
		if (fileType === \'image\') {
			createImagePreview(file, iconDiv);
		} else if (fileType === \'pdf\') {
			createPDFPreview(file, iconDiv);
		} else if (fileType === \'text\') {
			createTextPreview(file, iconDiv);
		} else {
			iconDiv.innerHTML = getFileIcon(fileType);
		}
		
		var infoDiv = document.createElement(\'div\');
		infoDiv.className = \'file-preview-info\';
		infoDiv.innerHTML = \'<div class="preview-name">\' + fileName + \'</div><div class="preview-size">\' + fileSize + \'</div>\';
		
		var removeBtn = document.createElement(\'span\');
		removeBtn.className = \'preview-remove\';
		removeBtn.innerHTML = \'×\';
		removeBtn.onclick = function() {
			previewDiv.remove();
			checkPreviewList();
		};
		
		previewDiv.appendChild(iconDiv);
		previewDiv.appendChild(infoDiv);
		previewDiv.appendChild(removeBtn);
		
		filePreviewList.appendChild(previewDiv);
	}
	
	function createImagePreview(file, container) {
		var img = document.createElement(\'img\');
		img.className = \'preview-image\';
		var reader = new FileReader();
		reader.onload = function(e) {
			img.src = e.target.result;
		};
		reader.readAsDataURL(file);
		container.appendChild(img);
	}
	
	function createPDFPreview(file, container) {
		var canvas = document.createElement(\'canvas\');
		canvas.className = \'preview-canvas\';
		canvas.width = 48;
		canvas.height = 48;
		container.appendChild(canvas);
		
		var reader = new FileReader();
		reader.onload = function(e) {
			try {
				// Try to use PDF.js if available, otherwise fallback to icon
				if (typeof pdfjsLib !== \'undefined\') {
					var typedarray = new Uint8Array(e.target.result);
					pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
						pdf.getPage(1).then(function(page) {
							var viewport = page.getViewport({scale: 0.2});
							var context = canvas.getContext(\'2d\');
							canvas.height = viewport.height;
							canvas.width = viewport.width;
							
							var renderContext = {
								canvasContext: context,
								viewport: viewport
							};
							page.render(renderContext);
						});
					}).catch(function() {
						// Fallback to icon if PDF.js fails
						container.innerHTML = getFileIcon(\'pdf\');
					});
				} else {
					// Fallback to icon if PDF.js not available
					container.innerHTML = getFileIcon(\'pdf\');
				}
			} catch (error) {
				container.innerHTML = getFileIcon(\'pdf\');
			}
		};
		reader.readAsArrayBuffer(file);
	}
	
	function createTextPreview(file, container) {
		var previewDiv = document.createElement(\'div\');
		previewDiv.className = \'text-preview\';
		container.appendChild(previewDiv);
		
		var reader = new FileReader();
		reader.onload = function(e) {
			var content = e.target.result;
			var lines = content.split(\'\\n\').slice(0, 4); // First 4 lines
			var preview = lines.join(\'\\n\');
			if (preview.length > 100) {
				preview = preview.substring(0, 100) + \'...\';
			}
			previewDiv.textContent = preview;
		};
		reader.readAsText(file);
	}
	
	function checkPreviewList() {
		if (filePreviewList.children.length === 0) {
			if (uploadBtn) {
				uploadBtn.style.display = \'none\';
			}
			if (uploadBox) {
				uploadBox.classList.remove(\'file-selected\');
			}
		}
	}
	
	function getFileType(fileName) {
		var ext = fileName.split(\'.\').pop().toLowerCase();
		if ([\'jpg\', \'jpeg\', \'png\', \'gif\', \'bmp\', \'webp\', \'svg\'].includes(ext)) {
			return \'image\';
		} else if ([\'pdf\'].includes(ext)) {
			return \'pdf\';
		} else if ([\'doc\', \'docx\'].includes(ext)) {
			return \'word\';
		} else if ([\'xls\', \'xlsx\'].includes(ext)) {
			return \'excel\';
		} else if ([\'ppt\', \'pptx\'].includes(ext)) {
			return \'powerpoint\';
		} else if ([\'txt\', \'md\', \'log\'].includes(ext)) {
			return \'text\';
		} else if ([\'zip\', \'rar\', \'7z\'].includes(ext)) {
			return \'archive\';
		} else if ([\'mp4\', \'avi\', \'mov\', \'wmv\', \'flv\'].includes(ext)) {
			return \'video\';
		} else if ([\'mp3\', \'wav\', \'ogg\', \'flac\'].includes(ext)) {
			return \'audio\';
		}
		return \'file\';
	}
	
	function getFileIcon(fileType) {
		var icons = {
			\'pdf\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#ea4335"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>\',
			\'word\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#2b579a"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
			\'excel\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#107c41"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
			\'powerpoint\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#d24726"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
			\'archive\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#ff9800"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
			\'video\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#f44336"><path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"/></svg>\',
			\'audio\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#9c27b0"><path d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z"/></svg>\',
			\'file\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#5f6368"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\'
		};
		return icons[fileType] || icons[\'file\'];
	}
}

function formatFileSize(bytes) {
	if (bytes === 0) return \'0 Bytes\';
	var k = 1024;
	var sizes = [\'Bytes\', \'KB\', \'MB\', \'GB\'];
	var i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + \' \' + sizes[i];
}

// Initialize when page loads
window.addEventListener(\'load\', function() {
	initFilePreview();
	loadExistingAttachments();
});

// Load existing attachments with proper previews
function loadExistingAttachments() {
	var attachments = document.querySelectorAll(\'.attachment-preview\');
	attachments.forEach(function(attachment) {
		var fileName = attachment.getAttribute(\'data-file-name\');
		var fileId = attachment.getAttribute(\'data-file-id\');
		var thumbnail = attachment.querySelector(\'.attachment-thumbnail\');
		
		if (fileName && thumbnail) {
			var fileType = getFileTypeFromName(fileName);
			
			if (fileType === \'image\') {
				// For images, try to load the actual file if URL is available
				// In real implementation, you\'d get the file URL from the server
				var img = document.createElement(\'img\');
				img.className = \'attachment-image\';
				img.src = \'/path/to/uploaded/files/\' + fileId; // Replace with actual file URL
				img.onerror = function() {
					// Fallback to icon if image can\'t be loaded
					thumbnail.innerHTML = getFileIconForAttachment(fileType);
				};
				thumbnail.innerHTML = \'\';
				thumbnail.appendChild(img);
			} else {
				thumbnail.innerHTML = getFileIconForAttachment(fileType);
			}
		}
	});
}

function getFileTypeFromName(fileName) {
	var ext = fileName.split(\'.\').pop().toLowerCase();
	if ([\'jpg\', \'jpeg\', \'png\', \'gif\', \'bmp\', \'webp\', \'svg\'].includes(ext)) {
		return \'image\';
	} else if ([\'pdf\'].includes(ext)) {
		return \'pdf\';
	} else if ([\'doc\', \'docx\'].includes(ext)) {
		return \'word\';
	} else if ([\'xls\', \'xlsx\'].includes(ext)) {
		return \'excel\';
	} else if ([\'ppt\', \'pptx\'].includes(ext)) {
		return \'powerpoint\';
	} else if ([\'txt\', \'md\', \'log\'].includes(ext)) {
		return \'text\';
	} else if ([\'zip\', \'rar\', \'7z\'].includes(ext)) {
		return \'archive\';
	} else if ([\'mp4\', \'avi\', \'mov\', \'wmv\', \'flv\'].includes(ext)) {
		return \'video\';
	} else if ([\'mp3\', \'wav\', \'ogg\', \'flac\'].includes(ext)) {
		return \'audio\';
	}
	return \'file\';
}

function getFileIconForAttachment(fileType) {
	var icons = {
		\'pdf\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#ea4335"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>\',
		\'word\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#2b579a"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
		\'excel\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#107c41"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
		\'powerpoint\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#d24726"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
		\'archive\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#ff9800"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\',
		\'video\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#f44336"><path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"/></svg>\',
		\'audio\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#9c27b0"><path d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z"/></svg>\',
		\'file\': \'<svg width="48" height="48" viewBox="0 0 24 24" fill="#5f6368"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>\'
	};
	return icons[fileType] || icons[\'file\'];
}
// -->
'; ?>

</script>

<form method="post" id="emailForm" action="<?php echo $this->_tpl_vars['formActionUrl']; ?>
"<?php if ($this->_tpl_vars['attachmentsEnabled']): ?> enctype="multipart/form-data"<?php endif; ?> class="email-compose">
    <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<input type="hidden" name="continued" value="1"/>
	<?php if ($this->_tpl_vars['hiddenFormParams']): ?>
		<?php $_from = $this->_tpl_vars['hiddenFormParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['hiddenFormParam']):
?>
			<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['hiddenFormParam'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
		<?php endforeach; endif; unset($_from); ?>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['attachmentsEnabled']): ?>
		<input type="hidden" name="deleteAttachment" value="" />
		<?php $_from = $this->_tpl_vars['persistAttachments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['temporaryFile']):
?>
			<?php if (is_object ( $this->_tpl_vars['temporaryFile'] )): ?><input type="hidden" name="persistAttachments[]" value="<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
" /><?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
	<?php endif; ?>

	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

	<?php $_from = $this->_tpl_vars['errorMessages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['message']):
?>
		<?php if (! $this->_tpl_vars['notFirstMessage']): ?>
			<?php $this->assign('notFirstMessage', 1); ?>
			<div class="error-messages">
				<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
</strong>
				<ul>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['message']['type'] == MAIL_ERROR_INVALID_EMAIL): ?>
			<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.invalid",'email' => $this->_tpl_vars['message']['address']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'message') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'message'));?>

			<li><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>

	<?php if ($this->_tpl_vars['notFirstMessage']): ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="recipient-row to-row">
		<span class="field-label">To</span>
		<div class="field-content">
			<?php $_from = $this->_tpl_vars['to']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['toAddress']):
?>
				<div class="recipient-chip">
					<input type="text" name="to[]" value="<?php if ($this->_tpl_vars['toAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?>class="recipient-input" />
					<?php if ($this->_tpl_vars['addressFieldsEnabled']): ?><span class="remove-recipient" onclick="removeRecipient(this)">×</span><?php endif; ?>
				</div>
			<?php endforeach; else: ?>
				<input type="text" name="to[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Recipients" />
			<?php endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['blankTo']): ?>
				<input type="text" name="to[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Recipients" />
			<?php endif; ?>
		</div>
		<div class="field-actions">
			<?php if ($this->_tpl_vars['addressFieldsEnabled']): ?>
				<input type="submit" name="blankTo" class="add-link" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addToRecipient"), $this);?>
"/>
			<?php endif; ?>
			<div class="field-toggles">
				<span id="ccToggle" class="toggle-link" onclick="showCc()">Cc</span>
				<span id="bccToggle" class="toggle-link" onclick="showBcc()">Bcc</span>
			</div>
		</div>
	</div>

	<div id="ccField" class="recipient-row cc-row" style="display: none;">
		<span class="field-label">Cc</span>
		<div class="field-content">
			<?php $_from = $this->_tpl_vars['cc']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ccAddress']):
?>
				<input type="text" name="cc[]" value="<?php if ($this->_tpl_vars['ccAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> />
			<?php endforeach; else: ?>
				<input type="text" name="cc[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Cc recipients" />
			<?php endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['blankCc']): ?>
				<input type="text" name="cc[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Cc recipients" />
			<?php endif; ?>
		</div>
		<div class="field-actions">
			<div class="action-buttons">
				<?php if ($this->_tpl_vars['addressFieldsEnabled']): ?>
					<input type="submit" name="blankCc" class="add-link" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addCcRecipient"), $this);?>
"/>
				<?php endif; ?>
				<span class="close-btn" onclick="hideCc()">×</span>
			</div>
		</div>
	</div>

	<div id="bccField" class="recipient-row bcc-row" style="display: none;">
		<span class="field-label">Bcc</span>
		<div class="field-content">
			<?php $_from = $this->_tpl_vars['bcc']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bccAddress']):
?>
				<input type="text" name="bcc[]" value="<?php if ($this->_tpl_vars['bccAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> />
			<?php endforeach; else: ?>
				<input type="text" name="bcc[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Bcc recipients" />
			<?php endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['blankBcc']): ?>
				<input type="text" name="bcc[]" class="recipient-input" <?php if (! $this->_tpl_vars['addressFieldsEnabled']): ?>disabled="disabled" <?php endif; ?> placeholder="Bcc recipients" />
			<?php endif; ?>
		</div>
		<div class="field-actions">
			<div class="action-buttons">
				<?php if ($this->_tpl_vars['addressFieldsEnabled']): ?>
					<input type="submit" name="blankBcc" class="add-link" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addBccRecipient"), $this);?>
"/>
				<?php endif; ?>
				<span class="close-btn" onclick="hideBcc()">×</span>
			</div>
		</div>
	</div>

	<?php if ($this->_tpl_vars['addressFieldsEnabled'] && $this->_tpl_vars['senderEmail']): ?>
	<div class="options-row">
		<label class="checkbox-option">
			<input type="checkbox" name="bccSender" value="1"<?php if ($this->_tpl_vars['bccSender']): ?> checked<?php endif; ?> />
			<span class="checkbox-custom"></span>
			Send a copy of this message to my address (<?php echo ((is_array($_tmp=$this->_tpl_vars['senderEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)
		</label>
	</div>
	<?php endif; ?>

	<div class="subject-row">
		<input type="text" name="subject" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['subject'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="subject-input" placeholder="Subject" />
	</div>

	<div class="message-row">
		<textarea id="emailBody" name="body" class="message-input markdown-editor richContent" data-markdown-editor="true" placeholder="Compose your message..." style="white-space: normal;"><?php echo ((is_array($_tmp=$this->_tpl_vars['body'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
	</div>

	<?php if ($this->_tpl_vars['attachmentsEnabled']): ?>
	<div class="attachments-row">
		<div class="attachments-list" id="attachmentsList">
			<?php $this->assign('attachmentNum', 1); ?>
			<?php $_from = $this->_tpl_vars['persistAttachments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['temporaryFile']):
?>
				<?php if (is_object ( $this->_tpl_vars['temporaryFile'] )): ?>
					<div class="attachment-preview" data-file-id="<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
" data-file-name="<?php echo ((is_array($_tmp=$this->_tpl_vars['temporaryFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
						<div class="attachment-content">
							<div class="attachment-thumbnail" id="thumb-<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
">
								<div class="file-icon"><?php echo ((is_array($_tmp=$this->_tpl_vars['temporaryFile']->getOriginalFileName())) ? $this->_run_mod_handler('substr', true, $_tmp, -4) : substr($_tmp, -4)); ?>
</div>
							</div>
							<div class="attachment-info">
								<div class="attachment-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['temporaryFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
								<div class="attachment-size">(<?php echo $this->_tpl_vars['temporaryFile']->getNiceFileSize(); ?>
)</div>
							</div>
						</div>
						<a href="javascript:deleteAttachment(<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
)" class="remove-btn">×</a>
					</div>
					<?php $this->assign('attachmentNum', $this->_tpl_vars['attachmentNum']+1); ?>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</div>
		
		<div class="file-upload-section">
			<div class="file-upload-box" id="uploadBox" onclick="document.getElementById('fileInput').click()">
				<div class="upload-icon">📁</div>
				<div class="upload-content">
					<div class="upload-title">Choose file</div>
					<div class="upload-subtitle">JPG, PNG, PDF, DOC recommended</div>
					<div class="drag-text">or drag and drop files here</div>
				</div>
				<input type="file" name="newAttachment" id="fileInput" class="file-hidden" multiple />
				<button type="button" class="browse-btn" onclick="event.stopPropagation(); document.getElementById('fileInput').click()">Browse</button>
			</div>
			<div class="file-preview-list" id="filePreviewList"></div>
			<input name="addAttachment" type="submit" class="upload-btn" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" style="display: none;" />
		</div>
	</div>
	<?php endif; ?>

	<div class="actions-row">
		<input name="send" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.send"), $this);?>
" class="send-btn" />
		<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="cancel-btn" onclick="history.go(-1)" />
		<?php if (! $this->_tpl_vars['disableSkipButton']): ?>
			<input name="send[skip]" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.skip"), $this);?>
" class="skip-btn" />
		<?php endif; ?>
	</div>
</form>

<script type="text/javascript">
<?php echo '
    // Ganti fungsi initTextarea yang bermasalah dengan:
    OJSEmailComposer.initTextarea = function() {
    	// TIDAK MELAKUKAN APA-APA - biarkan textarea berperilaku normal
    	// Textarea sudah memiliki cols="60" rows="15" dari template
    };
    
    // Atau lebih sederhana lagi, hapus panggilan initTextarea dari init():
    OJSEmailComposer.init = function() {
    	// OJSEmailComposer.initTextarea(); // <- HAPUS BARIS INI
    	OJSEmailComposer.initFilePreview();
    	OJSEmailComposer.processEmailContent();
    };
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>