<?php
namespace MissionalDigerati\CommunityProfile\Stores;

use MissionalDigerati\CommunityProfile\Stores\QuestionStore;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/**
 * A class for handling the storage of the Answer in the database.
 */
class AnswerStore
{
    /**
     * The name of the Section table.
     *
     * @var string
     */
    public static $tableName = 'copr_answers';

    /**
     * The WordPress database
     *
     * @var object
     */
    protected $db = null;

    /**
     * The database table prefix
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Build the class
     *
     * @param object $db The WordPress database object
     */
    public function __construct($db, $prefix = '')
    {
        if (!$db) {
            throw new \InvalidArgumentException('the WordPress database object must be set.');
        }
        $this->db = $db;
        $this->prefix = $prefix;
    }

    /**
     * Create a new answer
     *
     * @param  integer  $userId         The current user's id
     * @param  integer  $groupId        The current group's id
     * @param  integer  $questionId     The current question's id
     * @param  string   $answer         The answer
     *
     * @return integer|false            It returns the id or false if it failed to create
     */
    public function create($userId, $groupId, $questionId, $answer)
    {
        $exists = $this->find($userId, $groupId, $questionId);
        if ($exists) {
            return $exists->id;
        }

        $created = $this->createAnswer($userId, $groupId, $questionId, $answer);
        return ($created) ? $this->db->insert_id : false;
    }

    /**
     * Find the answer
     *
     * @param  integer  $userId         The current user's id
     * @param  integer  $groupId        The current group's id
     * @param  integer  $questionId     The current question's id
     * @return object                   The answer object
     */
    public function find($userId, $groupId, $questionId)
    {
        $tableName = $this->prefix . self::$tableName;
        $prepare = $this->db->prepare("SELECT * FROM {$tableName}
            WHERE copr_question_id = %d AND user_id = %d AND group_id = %d",
            $questionId,
            $userId,
            $groupId
        );
        $answer = $this->db->get_row($prepare);
        if ($answer) {
            $answer->id = intval($answer->id);
            $answer->copr_question_id = intval($answer->copr_question_id);
            $answer->user_id = intval($answer->user_id);
            $answer->group_id = intval($answer->group_id);
        }
        return $answer;
    }

    /**
     * Set up the answers table.
     *
     * @param   string  $charsetCollate The character set and collation
     *
     * @return void
     */
    public function setUp($charsetCollate)
    {
        $tableName = $this->prefix . self::$tableName;
        $questionTableName = $this->prefix . QuestionStore::$tableName;
        $sql = "CREATE TABLE {$tableName} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            copr_question_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            group_id mediumint(9) NOT NULL,
            answer longtext DEFAULT '' NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY  (copr_question_id) REFERENCES {$questionTableName}(id)
        ) {$charsetCollate};";
        dbDelta($sql);
    }

    /**
     * Create a new answer
     *
     * @param  integer  $userId         The current user's id
     * @param  integer  $groupId        The current group's id
     * @param  integer  $questionId     The current question's id
     * @param  string   $answer         The answer
     * @return boolean                  Was it successfully created?
     *
     */
    protected function createAnswer($userId, $groupId, $questionId, $answer)
    {
        $tableName = $this->prefix . self::$tableName;
        $prepare = $this->db->prepare("INSERT INTO {$tableName}
                (copr_question_id, user_id, group_id, answer, created_at)
                VALUES(%d, %d, %d, %s, NOW())
            ",
            $questionId,
            $userId,
            $groupId,
            $answer
        );
        return $this->db->query($prepare);
    }
}
