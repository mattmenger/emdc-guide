<?php
namespace MissionalDigerati\CommunityProfile\Repositories;

use MissionalDigerati\CommunityProfile\Stores\AnswerStore;
use MissionalDigerati\CommunityProfile\Stores\QuestionStore;
use MissionalDigerati\CommunityProfile\Stores\SectionStore;

/**
 * This is an abstraction layer to make it easier to work with answers.
 */
class AnswerRepository
{
    /**
     * The last inserted answer id.
     *
     * @var integer
     */
    public $lastId = -1;

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
     * The Answer store
     *
     * @var AnswerStore
     */
    protected $answerStore = null;

    /**
     * The Question store
     *
     * @var QuestionStore
     */
    protected $questionStore = null;

    /**
     * The Section store
     *
     * @var SectionStore
     */
    protected $sectionStore = null;

    /**
     * Build the class
     *
     * @param   object    $db        The WordPress database object
     * @param   string    $prefix    The prefix for the database table (default: '')
     */
    public function __construct($db, $prefix = '')
    {
        if (!$db) {
            throw new \InvalidArgumentException('the WordPress database object must be set.');
        }
        $this->db = $db;
        $this->prefix = $prefix;
        $this->answerStore = new AnswerStore($this->db, $this->prefix);
        $this->sectionStore = new SectionStore($this->db, $this->prefix);
        $this->questionStore = new QuestionStore($this->db, $this->prefix);
    }

    /**
     * Create or Update a given answer
     *
     * @param  integer  $groupId            The group id
     * @param  integer  $userId             The user id
     * @param  string   $sectionTitle       The title of the section
     * @param  string   $sectionTag         The section tag
     * @param  string   $sectionUrl         The section url
     * @param  string   $questionChoices    The choices for the question
     * @param  integer  $questionNumber     The question's number
     * @param  string   $question           The question
     * @param  string   $questionType       The type of question
     * @param  string   $answer             The answer
     * @return boolean                      Was it successful?
     */
    public function createOrUpdate(
        $groupId,
        $userId,
        $sectionTitle,
        $sectionTag,
        $sectionUrl,
        $questionChoices,
        $questionNumber,
        $question,
        $questionType,
        $answer
    ) {
        $success = false;
        $sectionId = $this->sectionStore->createOrUpdate($sectionTitle, $sectionTag, $sectionUrl);
        if ($sectionId) {
            $questionId = $this->questionStore->createOrUpdate(
                $sectionId,
                $questionChoices,
                intval($questionNumber),
                $question,
                $questionType
            );
            if ($questionId) {
                $this->lastId = $this->answerStore->createOrUpdate(intval($userId), intval($groupId), $questionId, $answer);
                if ($this->lastId) {
                    $success = true;
                }
            }
        }
        return $success;
    }

    /**
     * Find by id including the section and question.  If you only need the answer, use the AnswerStore.
     *
     * @param  integer  $id     The answer id
     * @return object           The answer object
     */
    public function findById($id)
    {
        $answerTableName = $this->prefix . AnswerStore::$tableName;
        $questionTableName = $this->prefix . QuestionStore::$tableName;
        $sectionTableName = $this->prefix . SectionStore::$tableName;
        $prepare = $this->db->prepare(
            "SELECT s.title as section_title, s.tag as section_tag, s.url as section_url, q.question,
            q.unique_hash as question_hash, q.question_number, q.question_type, q.question_choices,
            a.id as answer_id, a.answer, a.user_id, a.group_id, a.created_at, a.updated_at
            FROM {$sectionTableName} as s JOIN {$questionTableName} as q ON s.id = q.copr_section_id JOIN
            {$answerTableName} as a ON q.id = a.copr_question_id WHERE
            a.id = %d",
            $id
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
     * Find all the questions for a specific group
     *
     * @param  integer  $groupId    The id of the group to search
     * @return array                An array of standard objects
     */
    public function findAllForGroup($groupId)
    {
        $answerTableName = $this->prefix . AnswerStore::$tableName;
        $questionTableName = $this->prefix . QuestionStore::$tableName;
        $sectionTableName = $this->prefix . SectionStore::$tableName;
        $prepare = $this->db->prepare(
            "SELECT s.title as section_title, s.tag as section_tag, s.url as section_url, q.question,
            q.unique_hash as question_hash, q.question_number, q.question_type, q.question_choices,
            a.id as answer_id, a.answer, a.user_id, a.group_id, a.created_at, a.updated_at
            FROM {$sectionTableName} as s JOIN {$questionTableName} as q ON s.id = q.copr_section_id JOIN
            {$answerTableName} as a ON q.id = a.copr_question_id WHERE
            a.group_id = %d ORDER BY s.tag ASC, q.question_number ASC, a.created_at ASC",
            $groupId
        );
        return $this->db->get_results($prepare);
    }

    /**
     * Find all the answers for a specific section.  Tag is used to find the section.
     *
     * @param  string   $sectionTag     The section tag
     * @param  integer  $groupId        The group id
     * @param  integer  $userId         The user id
     * @return array                    An array of standard objects
     */
    public function findAllBySectionTag($sectionTag, $groupId, $userId)
    {
        $answerTableName = $this->prefix . AnswerStore::$tableName;
        $questionTableName = $this->prefix . QuestionStore::$tableName;
        $sectionTableName = $this->prefix . SectionStore::$tableName;
        $prepare = $this->db->prepare(
            "SELECT s.title as section_title, s.tag as section_tag, q.unique_hash, q.question,
            q.question_number, a.answer FROM {$sectionTableName} as s JOIN
            {$questionTableName} as q ON s.id = q.copr_section_id JOIN
            {$answerTableName} as a ON q.id = a.copr_question_id WHERE
            s.tag = %s AND a.group_id = %d AND a.user_id = %d",
            strtolower($sectionTag),
            $groupId,
            $userId
        );
        return $this->db->get_results($prepare);
    }
}
