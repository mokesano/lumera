{**
 * @file templates/common/submit.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * TO DO: CTA Submission Guide.
 *}
{if ($pageDisplayed == "site") }
	<div id="customblock-Large-Button"class="_largeButton">
		<a href="mailto:admin@sangia.org">{translate key="about.contact.principalContact"}</a>
	</div>
{else}
	<div id="customblock-Large-Button" class="_largeButton">
		<a href="{url page="submission" op="submit"}">{translate key="user.noRoles.submitArticle"}</a>
	</div>
{/if}