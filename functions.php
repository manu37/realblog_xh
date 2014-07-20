<?php

/**
 * Utility functions.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Realblog
 * @author    Jan Kanters <jan.kanters@telenet.be>
 * @author    Gert Ebersbach <mail@ge-webdesign.de>
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2006-2010 Jan Kanters
 * @copyright 2010-2014 Gert Ebersbach <http://ge-webdesign.de/>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Realblog_XH
 */

/**
 * Returns the search clause.
 *
 * @return CompositeWhereClause
 *
 * @todo realblog_from_date and realblog_to_date are unused!
 */
function Realblog_searchClause()
{
    if (!empty($_REQUEST['realblog_from_date'])) {
        $compClauseDate1 = new SimpleWhereClause(
            REALBLOG_DATE, $_REQUEST['date_operator_1'],
            Realblog_makeTimestampDates1($_REQUEST['realblog_from_date'])
        );
    }
    if (!empty($_REQUEST['realblog_to_date'])) {
        $compClauseDate2 = new SimpleWhereClause(
            REALBLOG_DATE, $_REQUEST['date_operator_2'],
            Realblog_makeTimestampDates1($_REQUEST['realblog_to_date'])
        );
    }
    if (!empty($_REQUEST['realblog_title'])) {
        $compClauseTitle = new LikeWhereClause(
            REALBLOG_TITLE, $_REQUEST['realblog_title'],
            $_REQUEST['title_operator']
        );
    }
    if (!empty($_REQUEST['realblog_story'])) {
        $compClauseStory = new LikeWhereClause(
            REALBLOG_STORY, $_REQUEST['realblog_story'],
            $_REQUEST['story_operator']
        );
    }

    $code = (int) !empty($compClauseDate1) << 3
        | (int) !empty($compClauseDate2) << 2
        | (int) !empty($compClauseTitle) << 1
        | (int) !empty($compClauseStory);
    switch ($code) {
    case 0:
        $compClause = null;
        break;
    case 1:
        $compClause = $compClauseStory;
        break;
    case 2:
        $compClause = $compClauseTitle;
        break;
    case 3:
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClauseTitle, $compClauseStory
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClauseTitle, $compClauseStory
            );
            break;
        }
        break;
    case 4:
        $compClause = $compClauseDate2;
        break;
    case 5:
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClauseDate2, $compClauseStory
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClauseDate2, $compClauseStory
            );
            break;
        }
        break;
    case 6:
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClauseDate2, $compClauseTitle
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClauseDate2, $compClauseTitle
            );
            break;
        }
        break;
    case 7:
        $compClause = $compClauseDate2;
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause($compClause, $compClauseTitle);
            break;
        case 'OR':
            $compClause = new OrWhereClause($compClause, $compClauseTitle);
            break;
        }
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause($compClause, $compClauseStory);
            break;
        case 'OR':
            $compClause = new OrWhereClause($compClause, $compClauseStory);
            break;
        }
        break;
    case 8:
        $compClause = $compClauseDate1;
        break;
    case 9:
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClauseDate1, $compClauseStory
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClauseDate1, $compClauseStory
            );
            break;
        }
        break;
    case 10:
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClauseDate1, $compClauseTitle
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClauseDate1, $compClauseTitle
            );
            break;
        }
        break;
    case 11:
        $compClause = $compClauseDate1;
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause(
                $compClause, $compClauseTitle
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                $compClause, $compClauseTitle
            );
            break;
        }
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause($compClause, $compClauseStory);
            break;
        case 'OR':
            $compClause = new OrWhereClause($compClause, $compClauseStory);
            break;
        }
        break;
    case 12:
        $compClause = new AndWhereClause($compClauseDate1, $compClauseDate2);
        break;
    case 13:
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause(
                new AndWhereClause($compClauseDate1, $compClauseDate2),
                $compClauseStory
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                new AndWhereClause($compClauseDate1, $compClauseDate2),
                $compClauseStory
            );
            break;
        }
        break;
    case 14:
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause(
                new AndWhereClause($compClauseDate1, $compClauseDate2),
                $compClauseTitle
            );
            break;
        case 'OR':
            $compClause = new OrWhereClause(
                new AndWhereClause($compClauseDate1, $compClauseDate2),
                $compClauseTitle
            );
            break;
        }
        break;
    case 15:
        $compClause = new AndWhereClause($compClauseDate1, $compClauseDate2);
        switch ($_REQUEST['operator_1']) {
        case 'AND':
            $compClause = new AndWhereClause($compClause, $compClauseTitle);
            break;
        case 'OR':
            $compClause = new OrWhereClause($compClause, $compClauseTitle);
            break;
        }
        switch ($_REQUEST['operator_2']) {
        case 'AND':
            $compClause = new AndWhereClause($compClause, $compClauseStory);
            break;
        case 'OR':
            $compClause = new OrWhereClause($compClause, $compClauseStory);
            break;
        }
        break;
    }
    return $compClause;
}

/**
 * Renders the search results.
 *
 * @param string $what  Which search results ('blog' or 'archive').
 * @param string $count The number of hits.
 *
 * @return string (X)HTML.
 *
 * @global string The script name.
 * @global string The URL of the current page.
 * @global array  The localization of the plugins.
 */
function Realblog_renderSearchResults($what, $count)
{
    global $sn, $su, $plugin_tx;

    $key = ($what == 'archive') ? 'back_to_archive' : 'search_show_all';
    $title = Realblog_getPgParameter('realblog_title');
    $story = Realblog_getPgParameter('realblog_story');
    $words = array();
    if ($title != '') {
        $words[] = $title;
    }
    if ($story != '') {
        $words[] = $story;
    }
    $words = implode(',', $words);
    return '<p>' . $plugin_tx['realblog']['search_searched_for'] . ' <b>"'
        . $words . '"</b></p>'
        . '<p>' . $plugin_tx['realblog']['search_result'] . '<b> '
        . $count . '</b></p>'
        . '<p><a href="' . $sn . '?' . $su . '"><b>'
        . $plugin_tx['realblog'][$key] . '</b></a></p>';
}

?>
