{**
 * templates/email/email.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Generic email template form
 *
 *}
{strip}
{assign var="pageTitle" value="email.compose"}
{assign var="pageCrumbTitle" value="email.email"}
{include file="common/header-parts/header-user.tpl"}
{/strip}

<script type="text/javascript">
{literal}
<!--
function deleteAttachment(fileId) {
	var emailForm = document.getElementById('emailForm');
	emailForm.deleteAttachment.value = fileId;
	emailForm.submit();
}

function showCc() {
	document.getElementById('ccField').style.display = 'flex';
	document.getElementById('ccToggle').style.display = 'none';
}

function hideCc() {
	document.getElementById('ccField').style.display = 'none';
	document.getElementById('ccToggle').style.display = 'inline-block';
}

function showBcc() {
	document.getElementById('bccField').style.display = 'flex';
	document.getElementById('bccToggle').style.display = 'none';
}

function hideBcc() {
	document.getElementById('bccField').style.display = 'none';
	document.getElementById('bccToggle').style.display = 'inline-block';
}

// Remove recipient functionality
function removeRecipient(element) {
	var recipientChip = element.parentNode;
	recipientChip.remove();
}

// File preview functionality with drag & drop
function initFilePreview() {
	var fileInput = document.getElementById('fileInput');
	var filePreviewList = document.getElementById('filePreviewList');
	var uploadBtn = document.querySelector('.upload-btn');
	var uploadBox = document.getElementById('uploadBox');
	
	// Prevent default drag behaviors
	['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
		uploadBox.addEventListener(eventName, preventDefaults, false);
		document.body.addEventListener(eventName, preventDefaults, false);
	});
	
	// Highlight drop area when item is dragged over it
	['dragenter', 'dragover'].forEach(eventName => {
		uploadBox.addEventListener(eventName, highlight, false);
	});
	
	['dragleave', 'drop'].forEach(eventName => {
		uploadBox.addEventListener(eventName, unhighlight, false);
	});
	
	// Handle dropped files
	uploadBox.addEventListener('drop', handleDrop, false);
	
	function preventDefaults(e) {
		e.preventDefault();
		e.stopPropagation();
	}
	
	function highlight(e) {
		uploadBox.classList.add('drag-over');
	}
	
	function unhighlight(e) {
		uploadBox.classList.remove('drag-over');
	}
	
	function handleDrop(e) {
		var dt = e.dataTransfer;
		var files = dt.files;
		handleFiles(files);
	}
	
	// File input change
	if (fileInput && filePreviewList) {
		fileInput.addEventListener('change', function(e) {
			var files = e.target.files;
			if (files.length > 0) {
				handleFiles(files);
			}
		});
	}
	
	function handleFiles(files) {
		filePreviewList.innerHTML = '';
		
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			createFilePreview(file);
		}
		
		if (uploadBtn) {
			uploadBtn.style.display = 'inline-block';
		}
		if (uploadBox) {
			uploadBox.classList.add('file-selected');
		}
	}
	
	function createFilePreview(file) {
		var fileName = file.name;
		var fileSize = formatFileSize(file.size);
		var fileType = getFileType(fileName);
		
		var previewDiv = document.createElement('div');
		previewDiv.className = 'file-preview-item';
		
		var iconDiv = document.createElement('div');
		iconDiv.className = 'file-preview-icon';
		
		// Create preview based on file type
		if (fileType === 'image') {
			createImagePreview(file, iconDiv);
		} else if (fileType === 'pdf') {
			createPDFPreview(file, iconDiv);
		} else if (fileType === 'text') {
			createTextPreview(file, iconDiv);
		} else {
			iconDiv.innerHTML = getFileIcon(fileType);
		}
		
		var infoDiv = document.createElement('div');
		infoDiv.className = 'file-preview-info';
		infoDiv.innerHTML = '<div class="preview-name">' + fileName + '</div><div class="preview-size">' + fileSize + '</div>';
		
		var removeBtn = document.createElement('span');
		removeBtn.className = 'preview-remove';
		removeBtn.innerHTML = '×';
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
		var img = document.createElement('img');
		img.className = 'preview-image';
		var reader = new FileReader();
		reader.onload = function(e) {
			img.src = e.target.result;
		};
		reader.readAsDataURL(file);
		container.appendChild(img);
	}
	
	function createPDFPreview(file, container) {
		var canvas = document.createElement('canvas');
		canvas.className = 'preview-canvas';
		canvas.width = 48;
		canvas.height = 48;
		container.appendChild(canvas);
		
		var reader = new FileReader();
		reader.onload = function(e) {
			try {
				// Try to use PDF.js if available, otherwise fallback to icon
				if (typeof pdfjsLib !== 'undefined') {
					var typedarray = new Uint8Array(e.target.result);
					pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
						pdf.getPage(1).then(function(page) {
							var viewport = page.getViewport({scale: 0.2});
							var context = canvas.getContext('2d');
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
						container.innerHTML = getFileIcon('pdf');
					});
				} else {
					// Fallback to icon if PDF.js not available
					container.innerHTML = getFileIcon('pdf');
				}
			} catch (error) {
				container.innerHTML = getFileIcon('pdf');
			}
		};
		reader.readAsArrayBuffer(file);
	}
	
	function createTextPreview(file, container) {
		var previewDiv = document.createElement('div');
		previewDiv.className = 'text-preview';
		container.appendChild(previewDiv);
		
		var reader = new FileReader();
		reader.onload = function(e) {
			var content = e.target.result;
			var lines = content.split('\n').slice(0, 4); // First 4 lines
			var preview = lines.join('\n');
			if (preview.length > 100) {
				preview = preview.substring(0, 100) + '...';
			}
			previewDiv.textContent = preview;
		};
		reader.readAsText(file);
	}
	
	function checkPreviewList() {
		if (filePreviewList.children.length === 0) {
			if (uploadBtn) {
				uploadBtn.style.display = 'none';
			}
			if (uploadBox) {
				uploadBox.classList.remove('file-selected');
			}
		}
	}
	
	function getFileType(fileName) {
		var ext = fileName.split('.').pop().toLowerCase();
		if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) {
			return 'image';
		} else if (['pdf'].includes(ext)) {
			return 'pdf';
		} else if (['doc', 'docx'].includes(ext)) {
			return 'word';
		} else if (['xls', 'xlsx'].includes(ext)) {
			return 'excel';
		} else if (['ppt', 'pptx'].includes(ext)) {
			return 'powerpoint';
		} else if (['txt', 'md', 'log'].includes(ext)) {
			return 'text';
		} else if (['zip', 'rar', '7z'].includes(ext)) {
			return 'archive';
		} else if (['mp4', 'avi', 'mov', 'wmv', 'flv'].includes(ext)) {
			return 'video';
		} else if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) {
			return 'audio';
		}
		return 'file';
	}
	
	function getFileIcon(fileType) {
		var icons = {
			'pdf': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#ea4335"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>',
			'word': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#2b579a"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
			'excel': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#107c41"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
			'powerpoint': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#d24726"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
			'archive': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#ff9800"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
			'video': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#f44336"><path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"/></svg>',
			'audio': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#9c27b0"><path d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z"/></svg>',
			'file': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#5f6368"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>'
		};
		return icons[fileType] || icons['file'];
	}
}

function formatFileSize(bytes) {
	if (bytes === 0) return '0 Bytes';
	var k = 1024;
	var sizes = ['Bytes', 'KB', 'MB', 'GB'];
	var i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Initialize when page loads
window.addEventListener('load', function() {
	initFilePreview();
	loadExistingAttachments();
});

// Load existing attachments with proper previews
function loadExistingAttachments() {
	var attachments = document.querySelectorAll('.attachment-preview');
	attachments.forEach(function(attachment) {
		var fileName = attachment.getAttribute('data-file-name');
		var fileId = attachment.getAttribute('data-file-id');
		var thumbnail = attachment.querySelector('.attachment-thumbnail');
		
		if (fileName && thumbnail) {
			var fileType = getFileTypeFromName(fileName);
			
			if (fileType === 'image') {
				// For images, try to load the actual file if URL is available
				// In real implementation, you'd get the file URL from the server
				var img = document.createElement('img');
				img.className = 'attachment-image';
				img.src = '/path/to/uploaded/files/' + fileId; // Replace with actual file URL
				img.onerror = function() {
					// Fallback to icon if image can't be loaded
					thumbnail.innerHTML = getFileIconForAttachment(fileType);
				};
				thumbnail.innerHTML = '';
				thumbnail.appendChild(img);
			} else {
				thumbnail.innerHTML = getFileIconForAttachment(fileType);
			}
		}
	});
}

function getFileTypeFromName(fileName) {
	var ext = fileName.split('.').pop().toLowerCase();
	if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) {
		return 'image';
	} else if (['pdf'].includes(ext)) {
		return 'pdf';
	} else if (['doc', 'docx'].includes(ext)) {
		return 'word';
	} else if (['xls', 'xlsx'].includes(ext)) {
		return 'excel';
	} else if (['ppt', 'pptx'].includes(ext)) {
		return 'powerpoint';
	} else if (['txt', 'md', 'log'].includes(ext)) {
		return 'text';
	} else if (['zip', 'rar', '7z'].includes(ext)) {
		return 'archive';
	} else if (['mp4', 'avi', 'mov', 'wmv', 'flv'].includes(ext)) {
		return 'video';
	} else if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) {
		return 'audio';
	}
	return 'file';
}

function getFileIconForAttachment(fileType) {
	var icons = {
		'pdf': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#ea4335"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>',
		'word': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#2b579a"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
		'excel': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#107c41"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
		'powerpoint': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#d24726"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
		'archive': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#ff9800"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
		'video': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#f44336"><path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"/></svg>',
		'audio': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#9c27b0"><path d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z"/></svg>',
		'file': '<svg width="48" height="48" viewBox="0 0 24 24" fill="#5f6368"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>'
	};
	return icons[fileType] || icons['file'];
}
// -->
{/literal}
</script>

<form method="post" id="emailForm" action="{$formActionUrl}"{if $attachmentsEnabled} enctype="multipart/form-data"{/if} class="email-compose">
    <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
	<input type="hidden" name="continued" value="1"/>
	{if $hiddenFormParams}
		{foreach from=$hiddenFormParams item=hiddenFormParam key=key}
			<input type="hidden" name="{$key|escape}" value="{$hiddenFormParam|escape}" />
		{/foreach}
	{/if}

	{if $attachmentsEnabled}
		<input type="hidden" name="deleteAttachment" value="" />
		{foreach from=$persistAttachments item=temporaryFile}
			{if is_object($temporaryFile)}<input type="hidden" name="persistAttachments[]" value="{$temporaryFile->getId()}" />{/if}
		{/foreach}
	{/if}

	{include file="common/formErrors.tpl"}

	{foreach from=$errorMessages item=message}
		{if !$notFirstMessage}
			{assign var=notFirstMessage value=1}
			<div class="error-messages">
				<strong>{translate key="form.errorsOccurred"}</strong>
				<ul>
		{/if}
		{if $message.type == MAIL_ERROR_INVALID_EMAIL}
			{translate|assign:"message" key="email.invalid" email=$message.address}
			<li>{$message|escape}</li>
		{/if}
	{/foreach}

	{if $notFirstMessage}
			</ul>
		</div>
	{/if}

	<div class="recipient-row to-row">
		<span class="field-label">To</span>
		<div class="field-content">
			{foreach from=$to item=toAddress}
				<div class="recipient-chip">
					<input type="text" name="to[]" value="{if $toAddress.name != ''}{$toAddress.name|escape} &lt;{$toAddress.email|escape}&gt;{else}{$toAddress.email|escape}{/if}" {if !$addressFieldsEnabled}disabled="disabled" {/if}class="recipient-input" />
					{if $addressFieldsEnabled}<span class="remove-recipient" onclick="removeRecipient(this)">×</span>{/if}
				</div>
			{foreachelse}
				<input type="text" name="to[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Recipients" />
			{/foreach}
			{if $blankTo}
				<input type="text" name="to[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Recipients" />
			{/if}
		</div>
		<div class="field-actions">
			{if $addressFieldsEnabled}
				<input type="submit" name="blankTo" class="add-link" value="{translate key="email.addToRecipient"}"/>
			{/if}
			<div class="field-toggles">
				<span id="ccToggle" class="toggle-link" onclick="showCc()">Cc</span>
				<span id="bccToggle" class="toggle-link" onclick="showBcc()">Bcc</span>
			</div>
		</div>
	</div>

	<div id="ccField" class="recipient-row cc-row" style="display: none;">
		<span class="field-label">Cc</span>
		<div class="field-content">
			{foreach from=$cc item=ccAddress}
				<input type="text" name="cc[]" value="{if $ccAddress.name != ''}{$ccAddress.name|escape} &lt;{$ccAddress.email|escape}&gt;{else}{$ccAddress.email|escape}{/if}" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} />
			{foreachelse}
				<input type="text" name="cc[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Cc recipients" />
			{/foreach}
			{if $blankCc}
				<input type="text" name="cc[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Cc recipients" />
			{/if}
		</div>
		<div class="field-actions">
			<div class="action-buttons">
				{if $addressFieldsEnabled}
					<input type="submit" name="blankCc" class="add-link" value="{translate key="email.addCcRecipient"}"/>
				{/if}
				<span class="close-btn" onclick="hideCc()">×</span>
			</div>
		</div>
	</div>

	<div id="bccField" class="recipient-row bcc-row" style="display: none;">
		<span class="field-label">Bcc</span>
		<div class="field-content">
			{foreach from=$bcc item=bccAddress}
				<input type="text" name="bcc[]" value="{if $bccAddress.name != ''}{$bccAddress.name|escape} &lt;{$bccAddress.email|escape}&gt;{else}{$bccAddress.email|escape}{/if}" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} />
			{foreachelse}
				<input type="text" name="bcc[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Bcc recipients" />
			{/foreach}
			{if $blankBcc}
				<input type="text" name="bcc[]" class="recipient-input" {if !$addressFieldsEnabled}disabled="disabled" {/if} placeholder="Bcc recipients" />
			{/if}
		</div>
		<div class="field-actions">
			<div class="action-buttons">
				{if $addressFieldsEnabled}
					<input type="submit" name="blankBcc" class="add-link" value="{translate key="email.addBccRecipient"}"/>
				{/if}
				<span class="close-btn" onclick="hideBcc()">×</span>
			</div>
		</div>
	</div>

	{if $addressFieldsEnabled && $senderEmail}
	<div class="options-row">
		<label class="checkbox-option">
			<input type="checkbox" name="bccSender" value="1"{if $bccSender} checked{/if} />
			<span class="checkbox-custom"></span>
			Send a copy of this message to my address ({$senderEmail|escape})
		</label>
	</div>
	{/if}

	<div class="subject-row">
		<input type="text" name="subject" value="{$subject|escape}" class="subject-input" placeholder="Subject" />
	</div>

	<div class="message-row">
		<textarea id="emailBody" name="body" class="message-input markdown-editor richContent" data-markdown-editor="true" placeholder="Compose your message..." style="white-space: normal;">{$body|escape}</textarea>
	</div>

	{if $attachmentsEnabled}
	<div class="attachments-row">
		<div class="attachments-list" id="attachmentsList">
			{assign var=attachmentNum value=1}
			{foreach from=$persistAttachments item=temporaryFile}
				{if is_object($temporaryFile)}
					<div class="attachment-preview" data-file-id="{$temporaryFile->getId()}" data-file-name="{$temporaryFile->getOriginalFileName()|escape}">
						<div class="attachment-content">
							<div class="attachment-thumbnail" id="thumb-{$temporaryFile->getId()}">
								<div class="file-icon">{$temporaryFile->getOriginalFileName()|substr:-4}</div>
							</div>
							<div class="attachment-info">
								<div class="attachment-name">{$temporaryFile->getOriginalFileName()|escape}</div>
								<div class="attachment-size">({$temporaryFile->getNiceFileSize()})</div>
							</div>
						</div>
						<a href="javascript:deleteAttachment({$temporaryFile->getId()})" class="remove-btn">×</a>
					</div>
					{assign var=attachmentNum value=$attachmentNum+1}
				{/if}
			{/foreach}
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
			<input name="addAttachment" type="submit" class="upload-btn" value="{translate key="common.upload"}" style="display: none;" />
		</div>
	</div>
	{/if}

	<div class="actions-row">
		<input name="send" type="submit" value="{translate key="email.send"}" class="send-btn" />
		<input type="button" value="{translate key="common.cancel"}" class="cancel-btn" onclick="history.go(-1)" />
		{if !$disableSkipButton}
			<input name="send[skip]" type="submit" value="{translate key="email.skip"}" class="skip-btn" />
		{/if}
	</div>
</form>

<script type="text/javascript">
{literal}
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
{/literal}
</script>

{include file="common/footer-parts/footer-welcome.tpl"}
