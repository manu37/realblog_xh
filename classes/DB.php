<?php

/**
 * The DB.
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
 * @copyright 2014-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Realblog_XH
 */

namespace Realblog;

/**
 * The DB.
 *
 * @category CMSimple_XH
 * @package  Realblog
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Realblog_XH
 */
class DB
{
    /**
     * The unique instance.
     *
     * @var DB
     */
    protected static $instance;

    /**
     * The connection.
     *
     * @var \SQLite3
     */
    protected $connection;

    /**
     * Returns the connection.
     *
     * @return \SQLite3
     */
    public static function getConnection()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    /**
     * Initializes a new instance.
     *
     * @global array The paths of system files and folders.
     */
    protected function __construct()
    {
        global $pth;

        $filename = "{$pth['folder']['content']}realblog/realblog.db";
        try {
            $this->connection = new \Sqlite3($filename, SQLITE3_OPEN_READWRITE);
        } catch (\Exception $ex) {
            $dirname = dirname($filename);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777);
                chmod($dirname, 0777);
            }
            $this->connection = new \Sqlite3($filename);
            $this->createDatabase();
        }
    }

    private function createDatabase()
    {
        $sql = <<<'EOS'
CREATE TABLE articles (
	id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	date INTEGER,
	publishing_date INTEGER,
	archiving_date INTEGER,
	status INTEGER,
	title TEXT,
	teaser TEXT,
	body TEXT,
	feedable INTEGER,
	commentable INTEGER
);
CREATE INDEX status ON articles (status, date, id);
CREATE INDEX feedable ON articles (feedable, date, id);
EOS;
        $this->connection->exec($sql);
        $this->importFlatfile();
    }

    private function importFlatfile()
    {
        global $pth;

        $types = array(SQLITE3_INTEGER, SQLITE3_INTEGER, SQLITE3_INTEGER,
                       SQLITE3_INTEGER, SQLITE3_INTEGER, SQLITE3_TEXT,
                       SQLITE3_TEXT, SQLITE3_TEXT, SQLITE3_INTEGER,
                       SQLITE3_INTEGER);
        $filename = "{$pth['folder']['content']}realblog/realblog.txt";
        if (file_exists($filename)) {
            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $this->connection->exec("BEGIN TRANSACTION");
            $sql = "INSERT INTO articles VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $statement = $this->connection->prepare($sql);
            foreach ($lines as $line) {
                $record = explode("\t", $line);
                unset($record[5]);
                $record = array_values($record);
                foreach ($record as $i => $field) {
                    $statement->bindValue($i + 1, $record[$i], $types[$i]);
                }
                $statement->execute();
            }
            $this->connection->exec("COMMIT TRANSACTION");
        }
    }

    /**
     * Finds and returns all articles with a certain status ordered by date and ID.
     *
     * @param int $status A status.
     * @param int $order  Order 1 (ascending) or -1 (descending).
     *
     * @return array<stdClass>
     */
    public static function findArticles($status, $limit, $offset = 0, $order = -1, $category = 'all', $search = null)
    {
        $db = self::getConnection();
        if ($order === -1) {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
        $categoryClause = ($category !== 'all')
            ? 'AND (teaser LIKE :category OR body LIKE :category)'
            : '';
        $searchClause = isset($search)
            ? 'AND (title LIKE :search OR body LIKE :search)'
            : '';
        $sql = <<<EOS
SELECT id, date, title, teaser, commentable, length(body) AS body_length
	FROM articles
    WHERE status = :status $categoryClause $searchClause
    ORDER BY date $order, id $order
	LIMIT $limit OFFSET $offset
EOS;
        $statement = $db->prepare($sql);
        $statement->bindValue(':status', $status, SQLITE3_INTEGER);
        $statement->bindValue(':category', "%|$category|%", SQLITE3_TEXT);
        $statement->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $result = $statement->execute();
        $records = array();
        while (($record = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $records[] = (object) $record;
        }
        return $records;
    }

    /**
     * Counts the number of archived articles within a certain period.
     *
     * @param int $start A start timestamp.
     * @param int $end   An end timestamp.
     *
     * @return int
     */
    public static function countArchivedArticlesInPeriod($start, $end)
    {
        $db = self::getConnection();
        $sql = <<<'EOS'
SELECT COUNT(*) AS count FROM articles
    WHERE status = :status AND date >= :start AND date < :end
    ORDER BY date DESC, id DESC
EOS;
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':status', 2, SQLITE3_INTEGER);
        $stmt->bindValue(':start', $start, SQLITE3_INTEGER);
        $stmt->bindValue(':end', $end, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $record = $result->fetchArray(SQLITE3_ASSOC);
        return $record['count'];
    }

    /**
     * Selects all archived articles within a certain period.
     *
     * @param int $start A start timestamp.
     * @param int $end   An end timestamp.
     *
     * @return array<stdClass>
     */
    public static function findArchivedArticlesInPeriod($start, $end)
    {
        $db = self::getConnection();
        $sql = <<<'EOS'
SELECT id, date, title FROM articles
    WHERE status = :status AND date >= :start AND date < :end
    ORDER BY date DESC, id DESC
EOS;
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':status', 2, SQLITE3_INTEGER);
        $stmt->bindValue(':start', $start, SQLITE3_INTEGER);
        $stmt->bindValue(':end', $end, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $records = array();
        while (($record = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $records[] = (object) $record;
        }
        return $records;
    }

	/**
	 * @param string $search
	 * @return array<stdClass>
	 */
	public static function findArchivedArticlesContaining($search)
	{
		$sql = <<<'EOS'
SELECT id, date, title FROM articles
    WHERE (title LIKE :text OR body LIKE :text) AND status = 2
    ORDER BY date DESC, id DESC
EOS;
		$db = self::getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':text', '%' . $search . '%', SQLITE3_TEXT);
		$result = $stmt->execute();
		$records = array();
		while (($record = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
			$records[] = (object) $record;
		}
		return $records;
	}

    /**
     * Counts the number of articles with one of the statuses.
     *
     * @param array $statuses An array of statuses.
     *
     * @return int
     */
    public static function countArticlesWithStatus($statuses, $category = 'all', $search = null)
    {
        $db = self::getConnection();
        if (empty($statuses)) {
            $whereClause = 'WHERE 1 = 1';
        } else {
            $whereClause = sprintf('WHERE status IN (%s)', implode(', ', $statuses));
        }
        $categoryClause = ($category !== 'all')
            ? 'AND (teaser LIKE :category OR body LIKE :category)'
            : '';
        $searchClause = isset($search)
            ? 'AND (title LIKE :search OR body LIKE :search)'
            : '';
        $sql = <<<SQL
SELECT COUNT(*) AS count FROM articles $whereClause $categoryClause $searchClause
SQL;
        $statement = $db->prepare($sql);
        $statement->bindValue(':category', "%|$category|%", SQLITE3_TEXT);
        $statement->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $result = $statement->execute();
        $record = $result->fetchArray(SQLITE3_ASSOC);
        return $record['count'];
    }

    /**
     * Finds all articles with one of the statuses.
     *
     * @param array $statuses An array of statuses.
     * @param int   $limit    The maximum number of articles.
     * @param int   $offset   The offset of the first article.
     *
     * @return array<stdClass>
     */
    public static function findArticlesWithStatus($statuses, $limit, $offset)
    {
        $db = self::getConnection();
        if (empty($statuses)) {
            $whereClause = '';
        } else {
            $whereClause = sprintf('WHERE status IN (%s)', implode(', ', $statuses));
        }
        $sql = <<<EOS
SELECT id, date, status, title, feedable, commentable
    FROM articles $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset
EOS;
        $result = $db->query($sql);
        $records = array();
        while (($record = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $records[] = (object) $record;
        }
        return $records;
    }

    /**
     * Finds and returns all feedable articles ordered by date and ID.
     *
     * @return array<stdClass>
     */
    public static function findFeedableArticles()
    {
        $db = self::getConnection();
        $sql = <<<'EOS'
SELECT id, date, title, teaser
    FROM articles WHERE feedable = :feedable ORDER BY date DESC, id DESC
EOS;
        $statement = $db->prepare($sql);
        $statement->bindValue(':feedable', 1, SQLITE3_INTEGER);
        $result = $statement->execute();
        $records = array();
        while (($record = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $records[] = (object) $record;
        }
        return $records;
    }

    /**
     * Finds an article by ID.
     *
     * @param int $id An ID.
     *
     * @return stdClass
     */
    public static function findById($id)
    {
        $db = self::getConnection();
        $statement = $db->prepare('SELECT * FROM articles WHERE id = :id');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $statement->execute();
        $record = $result->fetchArray(SQLITE3_ASSOC);
        if ($record !== false) {
            return (object) $record;
        } else {
            return null;
        }
    }

    public static function insertArticle(\stdClass $article)
    {
        $db = self::getConnection();
        $sql = <<<'EOS'
INSERT INTO articles
    VALUES (
        :id, :date, :publishing_date, :archiving_date, :status, :title, :teaser,
        :body, :feedable, :commentable
    )
EOS;
        $statement = $db->prepare($sql);
        $statement->bindValue(':id', null, SQLITE3_NULL);
        $statement->bindValue(':date', $article->date, SQLITE3_INTEGER);
        $statement->bindValue(':publishing_date', $article->publishing_date, SQLITE3_INTEGER);
        $statement->bindValue(':archiving_date', $article->archiving_date, SQLITE3_INTEGER);
        $statement->bindValue(':status', $article->status, SQLITE3_INTEGER);
        $statement->bindValue(':title', $article->title, SQLITE3_TEXT);
        $statement->bindValue(':teaser', $article->teaser, SQLITE3_TEXT);
        $statement->bindValue(':body', $article->body, SQLITE3_TEXT);
        $statement->bindValue(':feedable', $article->feedable, SQLITE3_INTEGER);
        $statement->bindValue(':commentable', $article->commentable, SQLITE3_INTEGER);
        $statement->execute();
    }

    /**
     * Updates an article in the database.
     *
     * @return void
     */
    public static function updateArticle(\stdClass $article)
    {
        $db = self::getConnection();
        $sql = <<<'EOS'
UPDATE articles
    SET date = :date, publishing_date = :publishing_date,
    	archiving_date = :archiving_date, status = :status, title = :title,
        teaser = :teaser, body = :body, feedable = :feedable,
        commentable = :commentable
    WHERE id = :id
EOS;
        $statement = $db->prepare($sql);
        $statement->bindValue(':id', $article->id, SQLITE3_INTEGER);
        $statement->bindValue(':date', $article->date, SQLITE3_INTEGER);
        $statement->bindValue(':publishing_date', $article->publishing_date, SQLITE3_INTEGER);
        $statement->bindValue(':archiving_date', $article->archiving_date, SQLITE3_INTEGER);
        $statement->bindValue(':status', $article->status, SQLITE3_INTEGER);
        $statement->bindValue(':title', $article->title, SQLITE3_TEXT);
        $statement->bindValue(':teaser', $article->teaser, SQLITE3_TEXT);
        $statement->bindValue(':body', $article->body, SQLITE3_TEXT);
        $statement->bindValue(':feedable', $article->feedable, SQLITE3_INTEGER);
        $statement->bindValue(':commentable', $article->commentable, SQLITE3_INTEGER);
        $statement->execute();
    }

    /**
     * @param string $field  A field name.
     * @param int    $status A status.
     *
     * @return void
     */
    public static function autoChangeStatus($field, $status)
    {
        $db = self::getConnection();
        $sql = "UPDATE articles SET status = :status WHERE status < :status AND $field <= :date";
        $statement = $db->prepare($sql);
        $statement->bindValue(':status', $status, SQLITE3_INTEGER);
        $statement->bindValue(':date', strtotime('midnight'), SQLITE3_INTEGER);
        $statement->execute();
        $records = array();
    }

	/**
	 * @param array<int> $ids
	 * @param int        $status
	 */
	public static function updateStatusOfArticlesWithIds($ids, $status)
	{
		$sql = sprintf('UPDATE articles SET status = :status WHERE id in (%s)',
					   implode(',', $ids));
		$db = self::getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':status', $status, SQLITE3_INTEGER);
		$stmt->execute();
	}

    public static function deleteArticleWithId($id)
    {
        $sql = 'DELETE FROM articles WHERE id = :id';
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

	/**
	 * @param array<int> $ids
	 */
	public static function deleteArticlesWithIds($ids)
	{
        $sql = sprintf('DELETE FROM articles WHERE id in (%s)',
                       implode(',', $ids));
        $db = self::getConnection();
        $db->exec($sql);
	}
}

?>
