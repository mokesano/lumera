<?php /* Smarty version 2.6.26, created on 2026-04-14 11:23:16
         compiled from citation/citationEditor.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'citation/citationEditor.tpl', 102, false),array('function', 'translate', 'citation/citationEditor.tpl', 165, false),array('function', 'load_url_in_div', 'citation/citationEditor.tpl', 265, false),)), $this); ?>

<script type="text/javascript">
	$(function() {
		<?php if ($this->_tpl_vars['unprocessedCitations'] !== false): ?>
			// Activate "Refresh Citation List" button.
			$('#refreshCitationListButton').click(function() {
				var $citationGrid = $('#citationGridContainer');

				// Activate the throbber.
				actionThrobber('#citationGridContainer');

				// Trigger the throbber.
				$citationGrid.triggerHandler('actionStart');

				// Reload the citation list.
				$.getJSON('<?php echo $this->_tpl_vars['citationGridUrl']; ?>
&refresh=1', function(jsonData) {
					// Stop the throbber.
					$citationGrid.triggerHandler('actionStop');

					if (jsonData.status === true) {
						// Replace the grid.
						$citationGrid.html(jsonData.content);
					} else {
						// Display the error message.
						alert(jsonData.content);
					}

					// Check whether all missing citations
					// have been added.
					var unprocessedCitationIds = [<?php echo ''; ?><?php $_from = $this->_tpl_vars['unprocessedCitations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['unprocessedCitations'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['unprocessedCitations']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['unprocessedCitation']):
        $this->_foreach['unprocessedCitations']['iteration']++;
?><?php echo ''; ?><?php echo $this->_tpl_vars['unprocessedCitation']->getId(); ?><?php echo ''; ?><?php if (! ($this->_foreach['unprocessedCitations']['iteration'] == $this->_foreach['unprocessedCitations']['total'])): ?><?php echo ','; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?>
];
					var missingIds = false;
					for (var i in unprocessedCitationIds) {
						if ($('#component-grid-citation-citationgrid-row-'+unprocessedCitationIds[i]).length == 0) {
							missingIds = true;
							break;
						}
					}

					// Remove the refresh button if all originally
					// missing citations have been processed by now.
					if (!missingIds) {
						$('#refreshCitationListMessage').remove();
					}
				});
			});
		<?php endif; ?>

		// Vertical splitter.
		$('#citationEditorCanvas').splitter({
			splitVertical:true,
			A:$('#citationEditorNavPane'),
			minAsize:200,
			B:$('#citationEditorDetailPane'),
			minBsize:300
		});

		// Main tabs.
		$mainTabs = $('#citationEditorMainTabs').tabs({
			show: function(e, ui) {
				// Make sure the citation editor is correctly sized when
				// opened for the first time.
				if (ui.panel.id == 'citationEditorTabEdit') {
					$('#citationEditorCanvas').triggerHandler('splitterRecalc');
				}
				<?php if (! $this->_tpl_vars['citationEditorConfigurationError']): ?>
					if (ui.panel.id == 'citationEditorTabExport') {
						$('#citationEditorExportPane').html('<div id="citationEditorExportThrobber" class="throbber"></div>');
						$('#citationEditorExportThrobber').show();

						// Re-load export tab whenever it is shown.
						$.getJSON('<?php echo $this->_tpl_vars['citationExportUrl']; ?>
', function(jsonData) {
							if (jsonData.status === true) {
								$("#citationEditorExportCanvas").replaceWith(jsonData.content);
							} else {
								// Alert that loading failed
								alert(jsonData.content);
							}
						});
					}
				<?php endif; ?>
			}
		});

		<?php if (! $this->_tpl_vars['introductionHide']): ?>
			// Feature to disable introduction message.
			$('#introductionHide').click(function() {
				$.getJSON(
					'<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('router' => @ROUTE_COMPONENT,'component' => "api.user.UserApiHandler",'op' => 'updateUserMessageState'), $this);?>
?setting-name=citation-editor-hide-intro&setting-value='+($(this).attr('checked')===true ? 'true' : 'false'),
					function(jsonData) {
						if (jsonData.status !== true) {
							alert(jsonData.content);
						}
					}
				);
			});
		<?php endif; ?>

		<?php if ($this->_tpl_vars['citationEditorConfigurationError']): ?>
			// Disable editor when not properly configured.
			$mainTabs.tabs('option', 'disabled', [1, 2]);
		<?php endif; ?>

		// Throbber feature (binds to ajaxAction()'s 'actionStart' event).
		actionThrobber('#citationEditorDetailCanvas');

		//
		// Fullscreen feature.
		//
		/**
		 * Opera has an ugly bug in connection with
		 * vertical resize and absolute positioning.
		 * This function works around this bug with an
		 * equally ugly hack.
		 */
		var operaVerticalResizeBugWorkaround = function() {
			if ($.browser.opera) {
				// Opera needs to be remembered that the export
				// pane is positioned absolutely.
				// We do this by resizing, taking a breath so that
				// Opera repaints and then resizing again.
				$('#citationEditorExportPane>.scrollable').css('bottom', '29px');
				setTimeout(function() {
					$('#citationEditorExportPane>.scrollable').css('bottom', '30px');
				}, 250);
			}
		};

		var $citationEditor = $('#citationEditor');
		var beforeFullscreen;
		$('#fullScreenButton').click(function() {
			if ($citationEditor.hasClass('fullscreen')) {
				// Going back to normal:
				// 1) Unbind tab change event (for Opera compat).
				$('.composite-ui>.ui-tabs').unbind('tabsselect');

				// 2) Remove additional CSS.
				$citationEditor
					// Remove the fullscreen layout.
					.removeClass('fullscreen')
					// Remove IE7 width hack (see below, window resizing event handling for IE).
					.css('width', '');

				// 3) Restore original values.
				$('.composite-ui>.ui-tabs').css('margin-top', beforeFullscreen.topMargin);
				$('.composite-ui>.ui-tabs div.main-tabs').each(function() {
					$(this).height(beforeFullscreen.height);
				});
				$('.composite-ui div.two-pane>div.left-pane .scrollable').first().height(beforeFullscreen.height-30);
				$('body, html').css('overflow', 'auto'); // html for IE7, body for the rest
				window.scroll(beforeFullscreen.x, beforeFullscreen.y);
				$(this).text('<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fullscreen"), $this);?>
');
			} else {
				// Going fullscreen:
				// 1) Save current values.
				beforeFullscreen = {
					topMargin: $('.composite-ui>.ui-tabs').css('margin-top'),
					height: $('.composite-ui>.ui-tabs div.main-tabs').not('.ui-tabs-hide').height(),
					x: $(window).scrollLeft(),
					y: $(window).scrollTop()
				};

				// 2) Set values needed to go fullscreen.
				$('body, html').css('overflow', 'hidden'); // html for IE7, body for the rest
				$citationEditor.addClass('fullscreen');
				$('.composite-ui>.ui-tabs').css('margin-top', '0');
				canvasHeight=$(window).height()-$('ul.main-tabs').height();
				$('.composite-ui>.ui-tabs div.main-tabs').each(function() {
					$(this).height(canvasHeight);
				});
				$('.composite-ui div.two-pane>div.left-pane .scrollable').first().height(canvasHeight-30);
				window.scroll(0,0);
				$(this).text('<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fullscreenOff"), $this);?>
');

				// 3) Bind event to tab change (for Opera compat).
				$('.composite-ui>.ui-tabs').bind('tabsselect', function() {
					window.scroll(0,0);
				});
			}

			// Resize 2-pane layout.
			$('.two-pane').css('width', '100%').triggerHandler('splitterRecalc');

			// Opera vertical resize bug workaround.
			operaVerticalResizeBugWorkaround();
		});

		// Resize citation editor in fullscreen mode
		// when the browser window is being resized.
		$(window).resize(function() {
			// Adjust editor height to new window height when in fullscreen mode.
			if ($citationEditor.hasClass('fullscreen')) {
				// IE7 needs to be told explicitly that we
				// really want to maintain 100% width.
				$citationEditor.width($(window).width());

				// Correctly adapt the height of scrollable areas.
				canvasHeight=$(window).height()-$('ul.main-tabs').height();
				$('.composite-ui>.ui-tabs div.main-tabs').each(function() {
					$(this).height(canvasHeight);
				});
				$('.composite-ui div.two-pane>div.left-pane .scrollable').first().height(canvasHeight-30);
			}

			// Adjust 2-pane layout to new window width.
			$('.two-pane').css('width', '100%').triggerHandler('splitterRecalc');

			// Opera vertical resize bug workaround.
			operaVerticalResizeBugWorkaround();
		});
	});
</script>

<?php if ($this->_tpl_vars['unprocessedCitations'] !== false): ?>
	<div id="refreshCitationListMessage" class="composite-ui">
		<p>
			<span class="formError"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.unprocessedCitations"), $this);?>
</span>
		</p>
		<button id="refreshCitationListButton" type="button" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.unprocessedCitationsButtonTitle"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.unprocessedCitationsButton"), $this);?>
</button>
	</div>
<?php endif; ?>
<div id="citationEditor" class="composite-ui block">
	<div id="citationEditorMainTabs">
		<button id="fullScreenButton" type="button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fullscreen"), $this);?>
</button>
		<ul class="main-tabs">
			<?php if (! $this->_tpl_vars['introductionHide']): ?><li><a href="#citationEditorTabIntroduction"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.introduction"), $this);?>
</a></li><?php endif; ?>
			<li><a href="#citationEditorTabEdit"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.edit"), $this);?>
</a></li>
			<li><a href="#citationEditorTabExport"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.export"), $this);?>
</a></li>
		</ul>
		<?php if (! $this->_tpl_vars['introductionHide']): ?>
			<div id="citationEditorTabIntroduction" class="main-tabs">
				<div id="citationEditorIntroductionCanvas" class="canvas">
					<div id="citationEditorIntroductionPane" class="pane text-pane scrollable">
						<div class="help-message">
							<?php ob_start(); ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'setup','path' => '3','anchor' => 'metaCitationEditing'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('citationSetupUrl', ob_get_contents());ob_end_clean(); ?>
							<?php if ($this->_tpl_vars['citationEditorConfigurationError']): ?>
								<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['citationEditorConfigurationError'],'citationSetupUrl' => $this->_tpl_vars['citationSetupUrl']), $this);?>

								<?php if ($this->_tpl_vars['showIntroductoryMessage']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.introduction.introductionMessage",'citationSetupUrl' => $this->_tpl_vars['citationSetupUrl']), $this);?>
<?php endif; ?>
							<?php else: ?>
								<?php if ($this->_tpl_vars['showIntroductoryMessage']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.introduction.introductionMessage",'citationSetupUrl' => $this->_tpl_vars['citationSetupUrl']), $this);?>
<?php endif; ?>
								<input id="introductionHide" type="checkbox" /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations.editor.details.dontShowMessageAgain"), $this);?>

							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div id="citationEditorTabEdit" class="main-tabs">
			<div id="citationEditorCanvas" class="canvas two-pane">
				<div id="citationEditorNavPane" class="pane left-pane">
					<?php if (! $this->_tpl_vars['citationEditorConfigurationError']): ?>
						<?php echo $this->_plugins['function']['load_url_in_div'][0][0]->smartyLoadUrlInDiv(array('id' => 'citationGridContainer','loadMessageId' => "submission.citations.editor.loadMessage",'url' => ($this->_tpl_vars['citationGridUrl'])), $this);?>

					<?php endif; ?>
				</div>
				<div id="citationEditorDetailPane" class="pane right-pane">
					<table class="pane_header"><thead><tr><th></th></tr></thead></table>
					<div id="citationEditorDetailCanvas" class="canvas">
						<div class="wrapper scrollable">
							<div class="help-message"><?php echo $this->_tpl_vars['initialHelpMessage']; ?>
</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="citationEditorTabExport" class="main-tabs">
			<div id="citationEditorExportCanvas" class="canvas">
				<div id="citationEditorExportPane" class="pane text-pane"></div>
			</div>
		</div>
	</div>
</div>
