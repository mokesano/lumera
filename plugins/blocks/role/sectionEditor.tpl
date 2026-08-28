{**
 * plugins/blocks/role/sectionEditor.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Section Editor navigation sidebar.
 *
 *}
<div class="block" id="sidebarSectionEditor">
	<span class="blockTitle">{translate key="user.role.sectionEditor"}</span>
	<span class="blockSubtitle">{translate key="article.submissions"}</span>
	<ul>
		<li><a href="{url op="index" path="submissionsInReview"}">{translate key="common.queue.short.submissionsInReview"}</a>&nbsp;({if $submissionsCount[0]}{$submissionsCount[0]}{else}0{/if})</li>
		<li><a href="{url op="index" path="submissionsInEditing"}">{translate key="common.queue.short.submissionsInEditing"}</a>&nbsp;({if $submissionsCount[1]}{$submissionsCount[1]}{else}0{/if})</li>
		<li><a href="{url op="index" path="submissionsArchives"}">{translate key="common.queue.short.submissionsArchives"}</a></li>
		{* [WIZDAM] Tautan ke pengaturan tipe artikel per-Section --
		   lihat SectionEditorArticleTypeHandler.inc.php. Tanpa tautan
		   ini, halaman yang sudah dibuat handler-nya tidak bisa
		   dijangkau siapa pun lewat UI. *}
		<li><a href="{url page="sectionEditor" op="mySections"}">{translate key="article.type.mySections"}</a></li>
	</ul>
</div>