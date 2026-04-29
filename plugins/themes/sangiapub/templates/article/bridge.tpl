{assign var="revisedDate" value=""}
{assign var="acceptedDate" value=""}

<!-- Debugging Output Start -->
<p>Debugging Output of Article:</p>
<pre>
{$article|@print_r}
</pre>
<p>Debugging Output of Article Data Keys:</p>
<pre>
{foreach from=$article->_data item=data key=key}
    {$key} : {$data|@print_r}
{/foreach}
</pre>
<!-- Debugging Output End -->

<!-- Loop through related files to find editor decisions -->
{foreach from=['submissionFileId', 'revisedFileId', 'reviewFileId', 'editorFileId'] item=fileKey}
    {if isset($article->_data.$fileKey)}
        <p>Found {$fileKey}:</p>
        <pre>
        {$article->_data.$fileKey|@print_r}
        {foreach from=$article->_data.$fileKey.decisions item=decision}
            Decision: {$decision.decision}, Date Decided: {$decision.dateDecided}
            {if $decision.decision == 2 && !$revisedDate}
                {assign var="revisedDate" value=$decision.dateDecided|date_format:"%d %B %Y"}
            {/if}
            {if $decision.decision == 1 && !$acceptedDate}
                {assign var="acceptedDate" value=$decision.dateDecided|date_format:"%d %B %Y"}
            {/foreach}
        </pre>
    {/if}
{/foreach}

<!-- Display results if any date is found -->
{if $revisedDate || $acceptedDate}
    <p>
        {if $revisedDate}
            Revisions Required: {$revisedDate}<br>
        {/if}
        {if $acceptedDate}
            Accepted: {$acceptedDate}
        {/if}
    </p>
{else}
    <p>No revision or acceptance dates found.</p>
{/if}
