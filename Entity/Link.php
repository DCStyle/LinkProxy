<?php

namespace DC\LinkProxy\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $link_id
 * @property int $post_id
 * @property string $encoded_url
 * @property string $decoded_url
 * @property string $status
 * @property int $generated_date
 *
 * RELATIONS
 * @property \XF\Entity\Post $Post
 */

class Link extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->table = 'xf_dcProxy_link';
        $structure->primaryKey = 'link_id';
        $structure->shortName = 'DC\LinkProxy:Link';
        $structure->columns = [
            'link_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'post_id' => ['type' => self::UINT, 'required' => true],
            'encoded_url' => ['type' => self::STR, 'required' => true],
            'dedcoded_url' => ['type' => self::STR, 'required' => true],
            'status' => ['type' => self::STR, 'allowedValues' => ['active', 'blocked'], 'default' => 'active'],
            'generated_date' => ['type' => self::UINT, 'default' => \XF::$time]
        ];
        $structure->relations = [
            'Post' => [
                'entity' => 'XF:Post',
                'type' => self::TO_ONE,
                'conditions' => 'post_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}