<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class TopDirectoryType implements ValidatorInterface
{
    const WU_YE_FU_FU = 'wu_ye_fu_fu';
    const YU_LE_PIN_DAO = 'yu_le_pin_dao';
    const WO_DE_WU_YE = 'wo_de_wu_ye';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::WU_YE_FU_FU => _META_('mp.top_directory_type.wu_ye_fu_fu'),
                self::YU_LE_PIN_DAO => _META_('mp.top_directory_type.yu_le_pin_dao'),
                self::WO_DE_WU_YE => _META_('mp.top_directory_type.wo_de_wu_ye'),
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
        return self::WU_YE_FU_FU;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}