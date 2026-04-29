<?php
require '/home/sangiaor/journals/lib/pkp/lib/smarty/Smarty.class.php';
include '/home/sangiaor/php_sangia/db_config.php';

function getArticleIdFromUrl() {
    $url = $_SERVER['REQUEST_URI'];
    preg_match('/\/article\/view\/(\d+)/', $url, $matches);
    if (isset($matches[1])) {
        return intval($matches[1]);
    }
    return 0;
}

function getAuthorAffiliations($articleId) {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $authors = array();
    $affiliationsIndex = array();
    $affiliationCounter = 1;

    $query = "
        SELECT aa.author_id, aa.affiliation, a.author_order, a.first_name, a.middle_name, a.last_name
        FROM author_affiliations aa
        JOIN authors a ON aa.author_id = a.author_id
        WHERE a.article_id = $articleId
        ORDER BY a.author_order ASC";

    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $authorOrder = $row['author_order'];
            $affiliation = $row['affiliation'];

            if (!isset($authors[$authorOrder])) {
                $authors[$authorOrder] = array(
                    'author_id' => $row['author_id'],
                    'first_name' => $row['first_name'],
                    'middle_name' => $row['middle_name'],
                    'last_name' => $row['last_name'],
                    'affiliations' => array()
                );
            }

            if (!isset($affiliationsIndex[$affiliation])) {
                $affiliationsIndex[$affiliation] = $affiliationCounter++;
            }

            $authors[$authorOrder]['affiliations'][] = array(
                'affiliation' => $affiliation,
                'index' => $affiliationsIndex[$affiliation]
            );
        }
    }

    $mysqli->close();
    return array('authors' => $authors, 'affiliationsIndex' => $affiliationsIndex);
}

$currentArticleId = getArticleIdFromUrl();
$data = getAuthorAffiliations($currentArticleId);

$smarty = new Smarty;
$smarty->setTemplateDir('/home/sangiaor/journals/templates');
$smarty->setCompileDir('/home/sangiaor/journals/cache/t_cache/templates_c');
$smarty->setCacheDir('/home/sangiaor/journals/cache/t_cache/cache');
$smarty->setConfigDir('/home/sangiaor/journals/cache/t_cache/configs');

$smarty->assign('authors', $data['authors']);
$smarty->assign('affiliationsIndex', $data['affiliationsIndex']);
$smarty->assign('baseURL', 'https://www.journals.sangia.org');
$smarty->display('article.tpl');
?>
