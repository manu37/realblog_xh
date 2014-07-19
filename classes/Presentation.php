<?php

/**
 * The presentation layer.
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
 * The article views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_ArticleView
{
    /**
     * The article ID.
     *
     * @var int
     */
    private $_id;

    /**
     * The article record.
     *
     * @var array
     */
    private $_article;

    /**
     * The article page. Most likely this is always 1.
     *
     * @var int
     */
    private $_page;

    /**
     * Initializes a new instance.
     *
     * @param int    $id      An article ID.
     * @param string $article An article record.
     * @param int    $page    An article page.
     *
     * @return void
     */
    public function __construct($id, $article, $page)
    {
        $this->_id = (int) $id;
        $this->_article = (array) $article;
        $this->_page = (int) $page;
    }

    /**
     * Renders the article.
     *
     * @return string (X)HTML.
     *
     * @global array The configuration of the plugins.
     */
    public function render()
    {
        global $plugin_cf;

        $html = '<div class="realblog_show_box">'
            . $this->_renderLinks() . $this->_renderHeading()
            . $this->_renderDate() . $this->_renderStory()
            . $this->_renderLinks() . '</div>';
        // output comments in RealBlog
        if ($this->_wantsComments() && $this->_article[REALBLOG_COMMENTS] == 'on') {
            $realblog_comments_id = 'comments' . $this->_id;
            if ($plugin_cf['realblog']['comments_form_protected'] == 'true') {
                $html .= comments($realblog_comments_id, 'protected');
            } else {
                $html .= comments($realblog_comments_id);
            }
        }
        return $html;
    }

    /**
     * Renders the links.
     *
     * @return string (X)HTML.
     *
     * @global bool Whether we're in admin mode.
     */
    private function _renderLinks()
    {
        global $adm;

        $html = '<div class="realblog_buttons">'
            . $this->_renderOverviewLink();
        if ($adm) {
            if ($this->_wantsComments()) {
                $html .= $this->_renderEditCommentsLink();
            }
            $html .= $this->_renderEditEntryLink();
        }
        $html .= '<div style="clear: both;"></div>'
            . '</div>';
        return $html;
    }

    /**
     * Renders the overview link.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global string The URL of the current page.
     * @global array  The localization of the plugins.
     */
    private function _renderOverviewLink()
    {
        global $sn, $su, $plugin_tx;

        if ($this->_article[REALBLOG_STATUS] == 2) {
            $url = $sn . '?' . $su . '&amp;realblogYear='
                . $_SESSION['realblogYear'];
            $text = $plugin_tx['realblog']['archiv_back'];
        } else {
            $url = $sn . '?' . $su . '&amp;page=' . $this->_page;
            $text = $plugin_tx['realblog']['blog_back'];
        }
        return '<span class="realblog_button">'
            . '<a href="' . $url . '">' . $text . '</a></span>';
    }

    /**
     * Renders the edit entry link.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderEditEntryLink()
    {
        global $sn, $plugin_tx;

        return '<span class="realblog_button">'
            . '<a href="' . $sn . '?&amp;realblog&amp;admin=plugin_main'
            . '&amp;action=modify_realblog&amp;realblogID='
            . $this->_id . '">'
            . $plugin_tx['realblog']['entry_edit'] . '</a></span>';
    }

    /**
     * Renders the edit comments link.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderEditCommentsLink()
    {
        global $sn, $plugin_tx;

        return '<span class="realblog_button">'
            . '<a href="' . $sn . '?&amp;comments&amp;admin=plugin_main'
            . '&amp;action=plugin_text&amp;selected=comments'
            . $this->_id . '.txt">'
            . $plugin_tx['realblog']['comment_edit'] . '</a></span>';
    }

    /**
     * Renders the article heading.
     *
     * @return string (X)HTML.
     *
     * @todo Heed $cf[menu][levels].
     */
    private function _renderHeading()
    {
        return '<h4>' . $this->_article[REALBLOG_TITLE] . '</h4>';
    }

    /**
     * Renders the article date.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderDate()
    {
        global $plugin_tx;

        $date = strftime(
            $plugin_tx['realblog']['display_date_format'],
            $this->_article[REALBLOG_DATE]
        );
        return '<div class="realblog_show_date">' . $date . '</div>';
    }

    /**
     * Renders the article story.
     *
     * @return string (X)HTML.
     */
    private function _renderStory()
    {
        $story = $this->_article[REALBLOG_STORY] != ''
            ? $this->_article[REALBLOG_STORY]
            : $this->_article[REALBLOG_HEADLINE];
        return '<div class="realblog_show_story_entry">'
            // FIXME: stripslashes() ?
            . stripslashes(evaluate_scripting($story))
            . '</div>';
    }

    /**
     * Returns whether comments are enabled.
     *
     * @return bool
     *
     * @global array The configuration of the plugins.
     */
    private function _wantsComments()
    {
        global $plugin_cf;

        return $plugin_cf['realblog']['comments_function'] == 'true'
            && function_exists('comments');
    }
}

/**
 * The info views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_InfoView
{
    /**
     * Renders the plugin info.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        return '<h1>Realblog</h1>'
            . $this->_renderLogo()
            . '<p>Version: ' . REALBLOG_VERSION . '</p>'
            . $this->_renderCopyright() . $this->_renderLicense();
    }

    /**
     * Renders the plugin logo.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the plugins.
     */
    private function _renderLogo()
    {
        global $pth, $plugin_tx;

        return tag(
            'img src="' . $pth['folder']['plugins']
            . 'realblog/realblog.png" class="realblog_logo"'
            . ' alt="' . $plugin_tx['realblog']['alt_logo'] . '"'
        );
    }

    /**
     * Renders the copyright info.
     *
     * @return string (X)HTML.
     */
    private function _renderCopyright()
    {
        return '<p>Copyright &copy; 2006-2010 Jan Kanters' . tag('br')
            . 'Copyright &copy; 2010-2014 '
            . '<a href="http://www.ge-webdesign.de/" target="_blank">'
            . 'Gert Ebersbach</a>' . tag('br')
            . 'Copyright &copy; 2014 '
            . '<a href="http://3-magi.net/" target="_blank">'
            . 'Christoph M. Becker</a></p>';
    }

    /**
     * Renders the license info.
     *
     * @return string (X)HTML.
     */
    private function _renderLicense()
    {
        return <<<EOT
<p class="realblog_license">This program is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>
<p class="realblog_license">This program is distributed in the hope that it will
be useful, but <em>without any warranty</em>; without even the implied warranty
of <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
the GNU General Public License for more details.</p>
<p class="realblog_license">You should have received a copy of the GNU General
Public License along with this program. If not, see <a
href="http://www.gnu.org/licenses/"
target="_blank">http://www.gnu.org/licenses/</a>.</p>
EOT;
    }
}

/**
 * The articles administration views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_ArticlesAdminView
{
    /**
     * The path of the plugin image folder.
     *
     * @var string
     */
    private $_imageFolder;

    /**
     * Initializes a new instance.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     */
    public function __construct()
    {
        global $pth;

        $this->_imageFolder =  $pth['folder']['plugins'] . 'realblog/images/';
    }

    /**
     * Renders the view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global string The current page number.
     * @global int    The number of articles per page.
     * @global int    The start index of the first article on the page.
     * @global int    The article count.
     * @global array  The article records.
     */
    public function render()
    {
        global $sn, $page, $page_record_limit, $start_index, $db_total_records,
            $records;

        $o = $this->_renderFilterForm();
        // Display table header
        $o .= "\n" . '<div>' . "\n"
            . '<form method="post" action="' . $sn . '?&amp;' . 'realblog'
            . '&amp;admin=plugin_main&amp;action=plugin_text">' . "\n"
            . '<table class="realblog_table" width="100%" cellpadding="0"'
            . ' cellspacing="0">';
        $o .= $this->_renderTableHead();

        $end_index = $page * $page_record_limit - 1;

        // Display table lines
        for ($i = $start_index; $i <= $end_index; $i++) {
            if ($i > $db_total_records - 1) {
                $o .= $this->_renderEmptyRow();
            } else {
                $field = $records[$i];
                $o .= $this->_renderRow($field);
            }
        }

        $o .= '</table></div>';
        $o .= tag('input type="hidden" name="page" value="' . $page . '"')
            . '</form><div>&nbsp;</div>';
        $o .= $this->_renderNavigation();
        return $o;
    }

    /**
     * Renders the filter form.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string Whether filter 1 is enabled.
     * @global string Whether filter 2 is enabled.
     * @global string Whether filter 3 is enabled.
     */
    private function _renderFilterForm()
    {
        global $sn, $plugin_tx, $filter1, $filter2, $filter3;

        $tstfilter1 = ($filter1 == 'on') ? ' checked="checked"' : '';
        $tstfilter2 = ($filter2 == 'on') ? ' checked="checked"' : '';
        $tstfilter3 = ($filter3 == 'on') ? ' checked="checked"' : '';
        return '<div>'
            . '<form name="selectstatus" method="post" action="' . $sn
            . '?&amp;realblog&amp;admin=plugin_main&amp;action=plugin_text">'
            . '<table width="100%">' . '<tr>'
            . '<td width="35%">'
            . tag('input type="checkbox" name="filter1" ' . $tstfilter1 . '"')
            . '&nbsp;' . $plugin_tx['realblog']['readyforpublishing'] . '</td>'
            . '<td width="30%">'
            . tag('input type="checkbox" name="filter2"' . $tstfilter2 . '"')
            . '&nbsp;' . $plugin_tx['realblog']['published'] . '</td>'
            . '<td width="30%">'
            . tag('input type="checkbox" name="filter3"' . $tstfilter3 . '"')
            . '&nbsp;' . $plugin_tx['realblog']['archived'] . '</td>'
            . '<td width="5%">'
            . tag(
                'input type="image" align="middle" src="'
                . $this->_imageFolder . 'filter.png" name="send"'
                . ' value="Apply filter" title="'
                . $plugin_tx['realblog']['btn_search']
                . '"'
            )
            . '</td>'
            . '</tr>' . '</table>'
            . tag('input type="hidden" name="filter" value="true"')
            . '</form>' . "\n" . '</div>';
    }

    /**
     * Renders the head of the table.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderTableHead()
    {
        global $sn, $plugin_tx;

        return '<tr>'
            . '<td class="realblog_table_header" align="center">'
            . tag(
                'input type="image" align="middle" src="'
                . $this->_imageFolder . 'delete.png" name="batchdelete"'
                . ' value="true" title="'
                . $plugin_tx['realblog']['tooltip_deleteall'] . '"'
            )
            . '</td>'
            . '<td class="realblog_table_header" align="center">'
            . tag(
                'input type="image" align="middle" src="' . $this->_imageFolder
                . 'change-status.png" name="changestatus" value="true"'
                . ' title="' . $plugin_tx['realblog']['tooltip_changestatus']
                . '"'
            )
            . '</td>'
            . '<td class="realblog_table_header" align="center">'
            . '<a href="' . $sn . '?&amp;' . 'realblog'
            . '&amp;admin=plugin_main&amp;action=add_realblog">'
            . tag(
                'img src="' . $this->_imageFolder . 'add.png"'
                . ' align="middle" title="'
                . $plugin_tx['realblog']['tooltip_add'] . '" alt=""'
            )
            . '</a></td>' . "\n"
            . '<td class="realblog_table_header" align="center">'
            . $plugin_tx['realblog']['id_label'] . '</td>' . "\n"
            . '<td class="realblog_table_header" align="center">'
            . $plugin_tx['realblog']['date_label'] . '</td>' . "\n"
            . '<td class="realblog_table_header" align="center">'
            . 'Status' . '</td>' . "\n"
            . '<td class="realblog_table_header" align="center">RSS Feed'
            . '</td>' . "\n"
            . '<td class="realblog_table_header" align="center">'
            . $plugin_tx['realblog']['comments_onoff'] . '</td>' . "\n"
            . '</tr>';
    }

    /**
     * Renders the pagination navigation.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string Whether the filter form has been submitted.
     * @global string Whether filter 1 is enabled.
     * @global string Whether filter 2 is enabled.
     * @global string Whether filter 3 is enabled.
     * @global string The current page number.
     * @global int    The number of pages.
     * @global int    The number of articles.
     */
    private function _renderNavigation()
    {
        global $sn, $plugin_tx, $filter, $filter1, $filter2, $filter3, $page,
            $page_total, $db_total_records;

        $tmp = ($db_total_records > 0)
            ? $plugin_tx['realblog']['page_label'] . ' : ' . $page .  ' / '
                . $page_total
            : '';
        $o = '<div class="realblog_paging_block">'
            . '<div class="realblog_db_info">'
            . $plugin_tx['realblog']['record_count'] . ' : '
            . $db_total_records . '</div>'
            . '<div class="realblog_page_info">&nbsp;&nbsp;&nbsp;' . $tmp
            . '</div>';

        if ($db_total_records > 0 && $page_total > 1) {
            if ($page_total > $page) {
                $next = $page + 1;
                $back = ($page > 1) ? ($next - 2) : '1';
            } else {
                $next = $page_total;
                $back = $page_total - 1;
            }
            $o .= '<div class="realblog_table_paging">'
                . '<a href="' . $sn . '?&amp;' . 'realblog'
                . '&amp;admin=plugin_main&amp;action=plugin_text&amp;page='
                . $back . '&amp;filter1=' . $filter1 . '&amp;filter2='
                . $filter2 . '&amp;filter3=' . $filter3 . '&amp;filter='
                . $filter . '" title="'
                . $plugin_tx['realblog']['tooltip_previous'] . '">'
                . '&#9664;</a>&nbsp;&nbsp;';
            for ($i = 1; $i <= $page_total; $i++) {
                $separator = ($i < $page_total) ? ' ' : '';
                $o .= '<a href="' . $sn . '?&amp;' . 'realblog'
                    . '&amp;admin=plugin_main&amp;action=plugin_text&amp;page='
                    . $i . '&amp;filter1=' . $filter1 . '&amp;filter2='
                    . $filter2 . '&amp;filter3=' . $filter3 . '&amp;filter='
                    . $filter . '" title="' . $plugin_tx['realblog']['page_label']
                    . ' ' . $i . '">[' . $i . ']</a>' . $separator;
            }
            $o .= '&nbsp;&nbsp;<a href="' . $sn . '?&amp;' . 'realblog'
                . '&amp;admin=plugin_main&amp;action=plugin_text&amp;page='
                . $next . '&amp;filter1=' . $filter1 . '&amp;filter2='
                . $filter2 . '&amp;filter3=' . $filter3 . '&amp;filter='
                . $filter . '" title="' . $plugin_tx['realblog']['tooltip_next']
                . '">'
                . '&#9654;</a>';
            $o .= '</div>';
        }
        $o .= '</div>';
        return $o;
    }

    /**
     * Renders a row.
     *
     * @param array $field An article record.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The current page number.
     */
    private function _renderRow($field)
    {
        global $sn, $plugin_tx, $page;

        return '<tr>'
            . '<td class="realblog_table_line" align="center">'
            . tag(
                'input type="checkbox" name="realblogtopics[]"'
                . ' value="' . $field[REALBLOG_ID] . '"'
            )
            . '</td>'
            . '<td class="realblog_table_line" valign="top"'
            . ' align="center">'
            . '<a href="' . $sn. '?&amp;' . 'realblog'
            . '&amp;admin=plugin_main&amp;action=delete_realblog'
            . '&amp;realblogID=' . $field[REALBLOG_ID] . '&amp;page='
            . $page . '">'
            . tag(
                'img src="' . $this->_imageFolder . 'delete.png"'
                . ' align="center" title="'
                . $plugin_tx['realblog']['tooltip_delete'] . '" alt=""'
            )
            . '</a></td>'
            . '<td class="realblog_table_line" valign="top"'
            . ' align="center">'
            . '<a href="' . $sn . '?&amp;' . 'realblog'
            . '&amp;admin=plugin_main&amp;action=modify_realblog'
            . '&amp;realblogID=' . $field[REALBLOG_ID] . '&amp;page='
            . $page . '">'
            . tag(
                'img src="' . $this->_imageFolder . 'edit.png"'
                . ' align="center" title="'
                . $plugin_tx['realblog']['tooltip_modify'] . '" alt=""'
            )
            . '</a></td>'
            . '<td class="realblog_table_line" valign="top"'
            . ' align="center"><b>' . $field[REALBLOG_ID] . '</b></td>'
            . '<td valign="top" style="text-align: center;"'
            . ' class="realblog_table_line">'
            . date(
                $plugin_tx['realblog']['date_format'], $field[REALBLOG_DATE]
            )
            . '</td>' . "\n"
            . '<td class="realblog_table_line" valign="top"'
            . ' style="text-align: center;"><b>'
            . $field[REALBLOG_STATUS] . '</b></td>' . "\n"
            . '<td class="realblog_table_line realblog_onoff"'
            . ' valign="top" style="text-align: center;">'
            . $field[REALBLOG_RSSFEED] . '</td>' . "\n"
            . '<td class="realblog_table_line realblog_onoff"'
            . ' valign="top" style="text-align: center;">'
            . $field[REALBLOG_COMMENTS] . '</td>' . "\n"
            . '</tr>' . "\n" . '<tr>' . "\n"
            . '<td colspan="8" valign="top"'
            . ' class="realblog_table_title"><span>'
            . $field[REALBLOG_TITLE] . '</span></td></tr>';
    }

    /**
     * Renders an empty row.
     *
     * @return string (X)HTML.
     *
     * @todo Simply remove this?
     */
    private function _renderEmptyRow()
    {
        $html = '<tr>';
        for ($i = 0; $i < 5; $i++) {
            $html .= '<td class="realblog_table_line" align="center">&nbsp;</td>';
        }
        for ($i = 0; $i < 3; $i++) {
            $html .= '<td class="realblog_table_line">&nbsp;</td>';
        }
        $html .= '</tr>';
        return $html;
    }
}

/**
 * The article administration views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_ArticleAdminView
{
    /**
     * The id of the current article.
     *
     * @var string
     */
    private $_realblogId;

    /**
     * The date of the article.
     *
     * @var FIXME
     */
    private $_realblogDate;

    /**
     * The publishing date of the article.
     *
     * @var FIXME
     */
    private $_startDate;

    /**
     * The archiving date of the article.
     *
     * @var FIXME
     */
    private $_endDate;

    /**
     * The status of the article.
     *
     * @var int
     */
    private $_status;

    /**
     * FIXME
     *
     * @var FIXME
     */
    private $_commentsChecked;

    /**
     * FIXME
     *
     * @var FIXME
     */
    private $_rssChecked;

    /**
     * The title of the article.
     *
     * @var string
     */
    private $_realBlogTitle;

    /**
     * The headline (teaser) of the article.
     *
     * @var string
     */
    private $_headline;

    /**
     * The story (body) of the article.
     *
     * @var string
     */
    private $_story;

    /**
     * The requested action.
     *
     * @var string
     */
    private $_action;

    /**
     * FIXME
     *
     * @var FIXME
     */
    private $_retPage;

    /**
     * The paths of the plugin image folder.
     *
     * @var string
     */
    private $_imageFolder;

    /**
     * Initializes a new instance.
     *
     * @param string $realblogId      The id of the current article.
     * @param FIXME  $realblogDate    The date of the article.
     * @param FIXME  $startDate       The publishing date of the article.
     * @param FIXME  $endDate         The archiving date of the article.
     * @param int    $status          The status of the article.
     * @param FIXME  $commentsChecked FIXME.
     * @param FIXME  $rssChecked      FIXME.
     * @param string $realBlogTitle   The title of the article.
     * @param string $headline        The teaser of the article.
     * @param string $story           The body of the article.
     * @param string $action          The requested action.
     * @param FIXME  $ret_page        FIXME.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     */
    public function __construct(
        $realblogId, $realblogDate, $startDate, $endDate, $status,
        $commentsChecked, $rssChecked, $realBlogTitle, $headline, $story,
        $action, $ret_page
    ) {
        global $pth;

        $this->_realblogId = $realblogId;
        $this->_realblogDate = $realblogDate;
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->_status = $status;
        $this->_commentsChecked = $commentsChecked;
        $this->_rssChecked = $rssChecked;
        $this->_realblogTitle = $realBlogTitle;
        $this->_headline = $headline;
        $this->_story = $story;
        $this->_action = $action;
        $this->_retPage = $ret_page;
        $this->_imageFolder = $pth['folder']['plugins'] . 'realblog/images/';
    }

    /**
     * Renders the article administration view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The title of the page.     *
     */
    public function render()
    {
        global $sn, $plugin_tx, $title;

        $t = '<div class="realblog_fields_block"><h1>Realblog &ndash; '
            . $title . '</h1>';
        $t .= '<form name="realblog" method="post" action="' . $sn . '?&amp;'
            . 'realblog' . '&amp;admin=plugin_main">'
            . $this->_renderHiddenFields();
        $t .= '<table width="100%">';
        $t .= '<tr><td width="30%"><span class="realblog_date_label">'
            . $plugin_tx['realblog']['date_label'] . '</span></td>'
            . '<td width="5%">&nbsp;</td><td width="30%">'
            . '<span class="realblog_date_label">'
            . $plugin_tx['realblog']['startdate_label'] . '</span></td>'
            . '<td width="5%">&nbsp;</td><td width="30%">'
            . '<span class="realblog_date_label">'
            . $plugin_tx['realblog']['enddate_label'] . '</span></td></tr><tr>';
        $t .= '<td width="30%" valign="top">'
            . $this->_renderDate()
            . '</td><td width="5%">&nbsp;</td>';
        $t .= '<td width="30%" valign="top">' . $this->_renderPublishingDate();
        $t .= '</td><td width="5%">&nbsp;</td>';
        $t .= '<td width="30%" valign="top">' . $this->_renderArchiveDate()
            . '</td></tr><tr>';

        $t .= $this->_renderCalendarScript();

        $t .= '<td width="30%"><span class="realblog_date_label">'
            . $plugin_tx['realblog']['status_label'] . '</span></td>'
            . '<td width="5%">&nbsp;</td>'
            . '<td width="30%">&nbsp;</span></td>'
            . '<td width="5%">&nbsp;</td>'
            . '<td width="30%"><span>&nbsp;</span></td></tr>'
            . '<tr>';
        $t .= '<td width="30%" valign="top">' . $this->_renderStatusSelect()
            . '</td>';
        $t .= '<td width="5%">&nbsp;</td><td width="30%" valign="top">'
            . $this->_renderCommentsCheckbox() . '</td>';
        $t .= '<td width="5%">&nbsp;</td><td width="30%" valign="top">'
            . $this->_renderFeedCheckbox() . '</td></tr>';
        $t .= '</table>';
        $t .= '<h4>' . $plugin_tx['realblog']['title_label'] . '</h4>';
        $t .= tag(
            'input type="text" value="' . @$this->_realblogTitle
            . '" name="realblog_title" size="70"'
        );
        $t .= $this->_renderHeadline() . $this->_renderStory()
            . $this->_renderSubmitButtons() . '</form>' . '</div>';
        return $t;
    }

    /**
     * Renders the hidden fields.
     *
     * @return string (X)HTML.
     */
    private function _renderHiddenFields()
    {
        $html = '';
        $fields = array(
            'page' => $this->_retPage,
            'realblog_id' => $this->_realblogId,
            'do' => $this->_getVerb()
        );
        foreach ($fields as $name => $value) {
            $html .= $this->_renderHiddenField($name, $value);
        }
        return $html;
    }

    /**
     * Renders a hidden field.
     *
     * @param string $name  A field name.
     * @param string $value A field value.
     *
     * @return string (X)HTML.
     */
    private function _renderHiddenField($name, $value)
    {
        return tag(
            'input type="hidden" name="' . $name . '" value="' . $value . '"'
        );
    }

    /**
     * Renders the date input.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderDate()
    {
        global $plugin_tx;

        $html = tag(
            'input type="text" name="realblog_date" id="date1" value="'
            . $this->_realblogDate . '" size="10" maxlength="10"'
            . ' onfocus="this.blur()"'
        );
        $html .= '&nbsp;'
            . tag(
                'img src="' . $this->_imageFolder . 'calendar.png"'
                . ' style="margin-left:1px;margin-bottom:-3px;"'
                . ' id="trig_date1" title="'
                . $plugin_tx['realblog']['tooltip_datepicker'] . '" alt=""'
            );
        return $html;
    }

    /**
     * Renders the publishing date input.
     *
     * @return string (X)HTML.
     *
     * @global array The configuration of the plugins.
     * @global array The localization of the plugins.
     */
    private function _renderPublishingDate()
    {
        global $plugin_cf, $plugin_tx;

        if ($plugin_cf['realblog']['auto_publish'] == 'true') {
            $html = tag(
                'input type="text" name="realblog_startdate" id="date2"'
                . ' value="' . $this->_startDate . '" size="10" maxlength="10"'
                . ' onfocus="this.blur()"'
            );
            $html .= '&nbsp;'
                . tag(
                    'img src="' . $this->_imageFolder . 'calendar.png"'
                    . ' style="margin-left:1px;margin-bottom:-3px;"'
                    . ' id="trig_date2" title="'
                    . $plugin_tx['realblog']['tooltip_datepicker'] . '" alt=""'
                );
        } else {
            $html = $plugin_tx['realblog']['startdate_hint'];
        }
        return $html;
    }

    /**
     * Renders the archiving date input.
     *
     * @return string (X)HTML.
     *
     * @global array The configuration of the plugins.
     * @global array The localization of the plugins.
     */
    private function _renderArchiveDate()
    {
        global $plugin_cf, $plugin_tx;

        if ($plugin_cf['realblog']['auto_archive'] == 'true') {
            $html = tag(
                'input type="text" name="realblog_enddate" id="date3"'
                . ' value="' . $this->_endDate . '" size="10" maxlength="10"'
                . ' onfocus="this.blur()"'
            );
            $html .= '&nbsp;'
                . tag(
                    'img src="' . $this->_imageFolder . 'calendar.png"'
                    . ' style="margin-left:1px;margin-bottom:-3px;"'
                    . ' id="trig_date3" title="'
                    . $plugin_tx['realblog']['tooltip_datepicker'] . '" alt=""'
                );
        } else {
            $html = $plugin_tx['realblog']['enddate_hint'];
        }
        return $html;
    }

    /**
     * Renders the calendar script.
     *
     * @return string (X)HTML.
     *
     * @return array The configuration of the plugins.
     */
    private function _renderCalendarScript()
    {
        global $plugin_cf;

        $html = '<script type="text/javascript">/* <![CDATA[ */'
            . $this->_renderCalendarInitialization(1);
        if ($plugin_cf['realblog']['auto_publish'] == 'true') {
            $html .= $this->_renderCalendarInitialization(2);
        }
        if ($plugin_cf['realblog']['auto_archive'] == 'true') {
            $html .= $this->_renderCalendarInitialization(3);
        }
        $html .= '/* ]]> */</script>';
        return $html;
    }

    /**
     * Renders a calendar initialization.
     *
     * @param string $num A date input number.
     *
     * @return string (X)HTML.
     *
     * @global string The date format.
     */
    private function _renderCalendarInitialization($num)
    {
        global $cal_format;

        return <<<EOT
Calendar.setup({
    inputField: "date$num",
    ifFormat: "$cal_format",
    button: "trig_date$num",
    align: "Br",
    singleClick: true,
    firstDay: 1,
    weekNumbers: false,
    electric: false,
    showsTime: false,
    timeFormat: "24"
});
EOT;
    }

    /**
     * Renders the status select.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderStatusSelect()
    {
        global $plugin_tx;

        $states = array('readyforpublishing', 'published', 'archived', 'backuped');
        $html = '<select name="realblog_status">';
        foreach ($states as $i => $state) {
            $html .= '<option value="' . $i . '" ' . @$this->_status[$i] . '>'
                . $plugin_tx['realblog'][$state] . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Renders the comments checkbox.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderCommentsCheckbox()
    {
        global $plugin_tx;

        return '<label>'
            . tag(
                'input type="checkbox" name="realblog_comments" '
                . @$this->_commentsChecked
            )
            . '&nbsp;<span>' . $plugin_tx['realblog']['comment_label']
            . '</span></label>';
    }

    /**
     * Renders the feed checkbox.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderFeedCheckbox()
    {
        global $plugin_tx;

        return '<label>'
            . tag(
                'input type="checkbox" name="realblog_rssfeed" '
                . @$this->_rssChecked
            )
            . '&nbsp;<span>' . $plugin_tx['realblog']['rss_label']
            . '</span></label>';
    }

    /**
     * Renders the headline (teaser).
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderHeadline()
    {
        global $plugin_tx;

        return '<h4>' . $plugin_tx['realblog']['headline_label'] . '</h4>'
            . '<p><b>Script for copy &amp; paste:</b></p>'
            . '{{{PLUGIN:rbCat(\'|the_category|\');}}}'
            . '<textarea class="realblog_headline_field" name="realblog_headline"'
            . ' id="realblog_headline" rows="6" cols="60">'
            . XH_hsc(@$this->_headline) . '</textarea>';
    }

    /**
     * Renders the story (body).
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderStory()
    {
        global $plugin_tx;

        return '<h4>' . $plugin_tx['realblog']['story_label'] . '</h4>'
            . '<p><b>Script for copy &amp; paste:</b></p>'
            . '{{{PLUGIN:CommentsMembersOnly();}}}'
            . '<textarea class="realblog_story_field"'
             . ' name="realblog_story" id="realblog_story" rows="30" cols="80">'
             . XH_hsc(@$this->_story) . '</textarea>';
    }

    /**
     * Renders the submit buttons.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderSubmitButtons()
    {
        global $sn, $plugin_tx;

        return '<p style="text-align: center">'
            . tag(
                'input type="submit" name="save" value="'
                . $plugin_tx['realblog']['btn_' . $this->_getVerb()] . '"'
            )
            . '&nbsp;&nbsp;&nbsp;'
            . tag(
                'input type="button" name="cancel" value="'
                . $plugin_tx['realblog']['btn_cancel'] . '" onclick="'
                . 'location.href=&quot;' . $sn . '?&amp;realblog&amp;'
                . 'admin=plugin_main&amp;action=plugin_text&page='
                . $this->_retPage . '&quot;"'
            )
            . '</p>';
    }

    /**
     * Gets the current verb.
     *
     * @return string
     */
    private function _getVerb()
    {
        switch ($this->_action) {
        case 'add_realblog':
            return 'add';
        case 'modify_realblog':
            return 'modify';
        case 'delete_realblog':
            return 'delete';
        }
    }
}

/**
 * The delete views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_DeleteView
{
    /**
     * The title of the page.
     *
     * @var string
     */
    private $_title;

    /**
     * The topics (articles).
     *
     * @var array
     */
    private $_articles;

    /**
     * Initializes a new instance.
     *
     * @return void
     *
     * @global string The title of the page.
     * @global array  The localization of the plugins.
     */
    public function __construct()
    {
        global $title, $plugin_tx;

        $title = $this->_title = $plugin_tx['realblog']['tooltip_deleteall'];
        $this->_articles = Realblog_getPgParameter('realblogtopics');
    }

    /**
     * Renders the view.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        if (count($this->_articles) > 0) {
            $html = $this->_renderConfirmation();
        } else {
            $html = $this->_renderNoSelectionInfo();
        }
        return $html;
    }

    /**
     * Renders the delete confirmation.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderConfirmation()
    {
        global $sn, $plugin_tx;

        $o = '<h1>Realblog &ndash; ' . $this->_title . '</h1>';
        $o .= '<form name="confirm" method="post" action="' . $sn
            . '?&amp;realblog&amp;admin=plugin_main">'
            . $this->_renderHiddenFields();
        $o .= '<table width="100%">';
        $o .= '<tr><td class="reablog_confirm_info" align="center">'
            . $plugin_tx['realblog']['confirm_deleteall']
            . '</td></tr><tr><td>&nbsp;</td></tr>';
        $o .= '<tr><td class="reablog_confirm_button" align="center">'
            . $this->_renderConfirmationButtons()
            . '</td></tr>';
        $o .= '</table></form>';
        return $o;
    }

    /**
     * Renders the hidden fields.
     *
     * @return string (X)HTML.
     *
     * @global string The number of the current page.
     */
    private function _renderHiddenFields()
    {
        global $page;

        $html = '';
        foreach ($this->_articles as $value) {
            $html .= $this->_renderHiddenField('realblogtopics[]', $value);
        }
        $html .= $this->_renderHiddenField('page', $page)
            . $this->_renderHiddenField('do', 'delselected');
        return $html;
    }

    /**
     * Renders a hidden field.
     *
     * @param string $name  A field name.
     * @param string $value A field value.
     *
     * @return string (X)HTML.
     */
    private function _renderHiddenField($name, $value)
    {
        return tag(
            'input type="hidden" name="' . $name . '" value="' . $value . '"'
        );
    }

    /**
     * Renders the confirmation buttons
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The number of the current page.
     */
    private function _renderConfirmationButtons()
    {
        global $sn, $plugin_tx, $page;

        $html = tag(
            'input type="submit" name="submit" value="'
            . $plugin_tx['realblog']['btn_delete'] . '"'
        );
        $html .= '&nbsp;&nbsp;';
        $url = $sn . '?&amp;realblog&amp;admin=plugin_main&amp;action=plugin_text'
            . '&amp;page=' . $page;
        $html .= tag(
            'input type="button" name="cancel" value="'
            . $plugin_tx['realblog']['btn_cancel'] . '" onclick="'
            . 'location.href=&quot;' . $url . '&quot;"'
        );
        return $html;
    }

    /**
     * Renders the no selection information.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The number of the current page.
     */
    private function _renderNoSelectionInfo()
    {
        global $sn, $plugin_tx, $page;

        $o = '<h1>Realblog &ndash; ' . $this->_title . '</h1>';
        $o .= '<form name="confirm" method="post" action="' . $sn
            . '?&amp;realblog&amp;admin=plugin_main&amp;action=plugin_text">';
        $o .= '<table width="100%">';
        $o .= '<tr><td class="reablog_confirm_info" align="center">'
            . $plugin_tx['realblog']['nothing_selected']
            . '</td></tr>';
        $o .= '<tr><td class="reablog_confirm_button" align="center">'
            . tag(
                'input type="button" name="cancel" value="'
                . $plugin_tx['realblog']['btn_ok'] . '" onclick=\''
                . 'location.href="' . $sn . '?&amp;' . 'realblog'
                . '&amp;admin=plugin_main&amp;action=plugin_text'
                . '&amp;page=' . $page . '"\''
            )
            . '</td></tr>';
        $o .= '</table></form>';
        return $o;
    }
}

/**
 * The change status views.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class Realblog_ChangeStatusView
{
    /**
     * The title of the page.
     *
     * @var string
     */
    private $_title;

    /**
     * The topics (articles).
     *
     * @var array
     */
    private $_topics;

    /**
     * Initializes a new instance.
     *
     * @return void
     *
     * @global string The title of the page.
     * @global array  The localization of the plugins.
     */
    public function __construct()
    {
        global $title, $plugin_tx;

        $title = $this->_title = $plugin_tx['realblog']['tooltip_changestatus'];
        $this->_topics = Realblog_getPgParameter('realblogtopics');
    }

    /**
     * Renders the change status view.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        if (count($this->_topics) > 0) {
            $o = $this->_renderConfirmation();
        } else {
            $o = $this->_renderNoSelectionInfo();
        }
        return $o;
    }

    /**
     * Renders the change status confirmation.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    private function _renderConfirmation()
    {
        global $sn, $plugin_tx;

        $html = '<h1>Realblog &ndash; ' . $this->_title . '</h1>'
            . '<form name="confirm" method="post" action="' . $sn
            . '?&amp;' . 'realblog' . '&amp;admin=plugin_main">'
            . $this->_renderHiddenFields()
            . '<table width="100%">'
            . '<tr><td width="100%" align="center">'
            . $this->_renderStatusSelect() . '</td></tr>'
            . '<tr><td class="realblog_confirm_info" align="center">'
            . $plugin_tx['realblog']['confirm_changestatus']
            . '</td></tr>'
            . '<tr><td>&nbsp;</td></tr>'
            . '<tr><td class="realblog_confirm_button" align="center">'
            . $this->_renderConfirmationButtons() . '</td></tr>'
            . '</table></form>';
        return $html;
    }

    /**
     * Renders the hidden fields.
     *
     * @return string (X)HTML.
     *
     * @global string The number of the current page.
     */
    private function _renderHiddenFields()
    {
        global $page;

        $html = '';
        foreach ($this->_topics as $value) {
            $html .= $this->_renderHiddenField('realblogtopics[]', $value);
        }
        $html .= $this->_renderHiddenField('page', $page)
            . $this->_renderHiddenField('do', 'batchchangestatus');
        return $html;
    }

    /**
     * Renders a hidden field.
     *
     * @param string $name  A field name.
     * @param string $value A field value.
     *
     * @return string (X)HTML.
     */
    private function _renderHiddenField($name, $value)
    {
        return tag(
            'input type="hidden" name="' . $name . '" value="' . $value . '"'
        );
    }

    /**
     * Renders the status select.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    private function _renderStatusSelect()
    {
        global $plugin_tx;

        $states = array(
            'entry_status', 'readyforpublishing', 'published', 'archived'
        );
        $html = '<select name="new_realblogstatus">';
        foreach ($states as $i => $state) {
            $value = $i == 0 ? '' : $i - 1;
            $html .= '<option value="' . $value . '" ' . @$this->_status[$i] . '>'
                . $plugin_tx['realblog'][$state] . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Renders the confirmation buttons
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The number of the current page.
     */
    private function _renderConfirmationButtons()
    {
        global $sn, $plugin_tx, $page;

        $html = tag(
            'input type="submit" name="submit" value="'
            . $plugin_tx['realblog']['btn_ok'] . '"'
        );
        $html .= '&nbsp;&nbsp;';
        $url = $sn . '?&amp;realblog&amp;admin=plugin_main&amp;action=plugin_text'
            . '&amp;page=' . $page;
        $html .= tag(
            'input type="button" name="cancel" value="'
            . $plugin_tx['realblog']['btn_cancel'] . '" onclick="'
            . 'location.href=&quot;' . $url . '&quot;"'
        );
        return $html;
    }

    /**
     * Renders the no selection information.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     * @global string The number of the current page.
     */
    private function _renderNoSelectionInfo()
    {
        global $sn, $plugin_tx, $page;

        return '<h1>Realblog &ndash; ' . $this->_title . '</h1>'
            . '<form name="confirm" method="post" action="' . $sn
            . '?&amp;' . 'realblog' . '&amp;admin=plugin_main">'
            . '<table width="100%">'
            . '<tr><td class="realblog_confirm_info" align="center">'
            . $plugin_tx['realblog']['nothing_selected']
            . '</td></tr>'
            . '<tr><td class="realblog_confirm_button" align="center">'
            . tag(
                'input type="button" name="cancel" value="'
                . $plugin_tx['realblog']['btn_ok'] . '" onclick=\''
                . 'location.href="' . $sn . '?&amp;' . 'realblog'
                . '&amp;admin=plugin_main&amp;action=plugin_text'
                . '&amp;page=' . $page . '"\''
            )
            . '</td></tr>'
            . '</table></form>';
    }
}

?>
