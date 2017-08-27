<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class ArticleType implements ValidatorInterface
{
    const ARTICLE_OURS = 'article_ours';
    const ARTICLE_THIRD_PARTY = 'article_third_party';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::ARTICLE_OURS => _META_('mp.article_type.article_ours'),
                self::ARTICLE_THIRD_PARTY => _META_('mp.article_type.article_third_party'),
            );
        }

        return self::$_data;
    }

    public static function getValues()
    {
        $data = self::getDictionary();
        return array_keys($data);
    }

    public static function getDisplayName($value)
    {
        $data = self::getDictionary();
        return $data[$value];
    }

    public static function getDefaultValue()
    {
        return self::ARTICLE_OURS;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}