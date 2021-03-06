<?php

/**
 * Copyright 2006-2010 Jan Kanters
 * Copyright 2010-2014 Gert Ebersbach
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Realblog_XH.
 *
 * Realblog_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Realblog_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Realblog_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Realblog;

class FeedLinkController extends AbstractController
{
    public function defaultAction($target)
    {
        global $sn, $pth;

        return <<<HTML
<!-- realblog feed link -->
<a href="$sn?realblog_feed=rss" target="$target">
    <img src="{$pth['folder']['plugins']}realblog/images/rss.png"
         alt="{$this->text['rss_tooltip']}" title="{$this->text['rss_tooltip']}"
         style="border: 0">
</a>
HTML;
    }
}
