{if ($pageDisplayed == "site") }
<div id="customblock-Large-Button"class="_largeButton">
	<a href="mailto:rochmady@stipwunaraha.ac.id">{translate key="about.contact.principalContact"}</a>
</div>
{else}
<div id="customblock-Large-Button" class="_largeButton">
	<a href="{url page="about" op="submissions" anchor="onlineSubmissions"}">{translate key="user.noRoles.submitArticle"}</a>
</div>
{/if}