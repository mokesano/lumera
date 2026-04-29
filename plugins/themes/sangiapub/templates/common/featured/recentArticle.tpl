{**
 * recentArticle.tpl
 *
 * Copyright (c) 2018-2025 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Recent Articles
 *
 *}
 <section class="area-wrapper u-mt-16 u-mb-24">
    <div class="row raw">
        <div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2>Popular Articles <span class="sub-title"></span></h2>
                <div class="insight-label u-font-sans">Based on the number of impressions and downloads.</div>
            </div>
  		</div>
<ul>
    {php}
        // Ambil instance dari database
        $journal =& Request::getJournal();
        
        if ($journal) {
            // Ambil artikel terbaru
            $publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
            $articleDao =& DAORegistry::getDAO('ArticleDAO');
                
            // Pastikan ID jurnal benar
            $journalId = $journal->getId();
                
            // Ambil issue terbaru
            $issueDao =& DAORegistry::getDAO('IssueDAO');
            $currentIssue =& $issueDao->getCurrentIssue($journalId);
                
            // Jika issue terbaru ditemukan, ambil artikel dari issue tersebut
            if ($currentIssue) {
                $recentArticles = $publishedArticleDao->getPublishedArticles($currentIssue->getId());
                
                // Urutkan artikel berdasarkan tanggal terbit, dari terbaru ke terlama
                usort($recentArticles, function($a, $b) {
                    return strtotime($b->getDatePublished()) - strtotime($a->getDatePublished());
                });
            } else {
                $recentArticles = array();
            }
        
            // Debugging: cetak artikel terbaru
            // echo '<pre>';
            // print_r($recentArticles);
            // echo '</pre>';
                
            // Batasi jumlah artikel yang ditampilkan
            $recentArticles = array_slice($recentArticles, 0, 1);
                        
            // Assign artikel ke template Smarty
            $this->assign('recentArticles', $recentArticles);
        } else {
            // Jika jurnal tidak ditemukan, tampilkan pesan error
            echo 'Jurnal tidak ditemukan.';
            $this->assign('recentArticles', array());
        }
    {/php}
    {if $recentArticles|@count > 0}
        {foreach from=$recentArticles item=article}
            <li>
                <a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank">{$article->getLocalizedTitle()}</a>
                <br/>
                <small>{$article->getDatePublished()|date_format:"%d %b %Y"}</small>
            </li>
        {/foreach}
    {else}
        <li>Tidak ada artikel terbaru.</li>
    {/if}
</ul>
<ul>
    {php}
        // Ambil instance dari database
        $journal =& Request::getJournal();
        
        if ($journal) {
            // Ambil artikel terbaru
            $publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
            $articleDao =& DAORegistry::getDAO('ArticleDAO');
                
            // Pastikan ID jurnal benar
            $journalId = $journal->getId();
                
            // Ambil issue terbaru
            $issueDao =& DAORegistry::getDAO('IssueDAO');
            $currentIssue =& $issueDao->getCurrentIssue($journalId);
                
            // Jika issue terbaru ditemukan, ambil artikel dari issue tersebut
            if ($currentIssue) {
                $recentArticles = $publishedArticleDao->getPublishedArticles($currentIssue->getId());
                
                // Urutkan artikel berdasarkan tanggal terbit, dari terbaru ke terlama
                usort($recentArticles, function($a, $b) {
                    return strtotime($b->getDatePublished()) - strtotime($a->getDatePublished());
                });
            } else {
                $recentArticles = array();
            }
        
            // Debugging: cetak artikel terbaru
            // echo '<pre>';
            // print_r($recentArticles);
            // echo '</pre>';
                
            // Batasi jumlah artikel yang ditampilkan
            $recentArticles = array_slice($recentArticles, 1, 4);
                        
            // Assign artikel ke template Smarty
            $this->assign('recentArticles', $recentArticles);
        } else {
            // Jika jurnal tidak ditemukan, tampilkan pesan error
            echo 'Jurnal tidak ditemukan.';
            $this->assign('recentArticles', array());
        }
    {/php}
    {if $recentArticles|@count > 0}
        {foreach from=$recentArticles item=article}
            <li>
                <a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank">{$article->getLocalizedTitle()}</a>
                <br/>
                <small>{$article->getDatePublished()|date_format:"%d %b %Y"}</small>
            </li>
        {/foreach}
    {else}
        <li>Tidak ada artikel terbaru.</li>
    {/if}
</ul>

    </div>
</section>