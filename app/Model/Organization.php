<?php
namespace Welcomelafayette\Model;

use Aura\SqlQuery\QueryFactory;
use Aura\Sql\ExtendedPdo;
use Welcomelafayette\Lib\DBAL;
use Welcomelafayette\Lib\Config;

/**
 * this is really a Database abstraction layer, not a "model" per se.
 */
class Organization extends DBAL
{
    public $DB_TYPE = 'sqlite';
    public $DB_TABLE = 'organization';

    /**
     * returns a single record by ID
     * @param  int   $id
     * @return array
     */
    public function getById($id)
    {
        $select = $this->makeQueryFactory()->newSelect();
        $select->from($this->DB_TABLE)
            ->cols(array(
                'id',
                'name',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'phone',
                'email',
                'description',
                'img_url',
                'twitter',
                'facebook_url',
                'website_url',
                'date_created',
                'approved',
            ))
            ->where('id = :id')
            ->bindValue('id', $id);
        $sth = $this->sendQuery($select);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * returns a single record by ID, if approved
     * @param  int   $id
     * @return array
     */
    public function getApprovedById($id)
    {
        $select = $this->makeQueryFactory()->newSelect();
        $select->from($this->DB_TABLE)
            ->cols(array(
                'id',
                'name',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'phone',
                'email',
                'description',
                'img_url',
                'twitter',
                'facebook_url',
                'website_url',
                'date_created',
                'approved',
            ))
            ->where('id = :id')
            ->where('approved = 1')
            ->bindValue('id', $id);
        $sth = $this->sendQuery($select);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getAllApproved($limit = 10, $skip = 0, array $order = [])
    {
        return $this->getAll($limit, $skip, ['approved' => 1]);
    }

    /**
     * @param int $limit
     * @param int $skip
     * @param array $where
     * @param array $order
     * @return array
     */
    public function getAll($limit = 10, $skip = 0, array $where = [], array $order = [])
    {
        $select = $this->makeQueryFactory()->newSelect();
        $select->from($this->DB_TABLE)
            ->cols(array(
                'id',
                'name',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'phone',
                'email',
                'description',
                'img_url',
                'twitter',
                'facebook_url',
                'website_url',
                'date_created',
                'approved',
            ))
            ->limit($limit)
            ->offset($skip);

        foreach ($where as $col => $val) {
            $select->where("$col = ?", $val);
        }

        foreach ($order as $col => $direction) {
            $select->orderBy(array("$col $direction"));
        }

        $sth = $this->sendQuery($select);

        $rows = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * saving not allowed
     * @param  array  $record_data
     * @return void
     * @throws Exception
     */
    public function save(array $record_data)
    {
        $insert = $this->makeQueryFactory()->newInsert();
        $insert->into($this->DB_TABLE)
            ->cols(array(
                'name' => $record_data['name'],
                'address1' => $record_data['address1'],
                'address2' => $record_data['address2'],
                'city' => $record_data['city'],
                'state' => $record_data['state'],
                'zip' => $record_data['zip'],
                'phone' => $record_data['phone'],
                'email' => $record_data['email'],
                'description' => $record_data['description'],
                'img_url' => $record_data['img_url'],
                'twitter' => $record_data['twitter'],
                'facebook_url' => $record_data['facebook_url'],
                'website_url' => $record_data['website_url'],
                'approved' => (int)(bool)$record_data['approved'],
            ))
            ->set('date_created', 'datetime(\'now\')');

        $sth = $this->sendQuery($insert);

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $this->extendedPdo->lastInsertId($name);

        return $id;
    }

    /**
     * @param $id
     */
    public function approve($id)
    {
        $update = $this->makeQueryFactory()->newUpdate();
        $update->table($this->DB_TABLE)
            ->set('approved', 1);
        $sth = $this->sendQuery($update);
    }
}
