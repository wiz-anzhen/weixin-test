<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\WeixinMessageType;
use MP\Model\Mp\MpRule;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\WxSubMenu;

class MpRuleBusiness
{
    /*
     *  return ['content_type' => string , 'content' => string]
     */
    public static function matchMpRule($mpUserID, $userKeyword)
    {
        $res    = [];
        $rows   = MpRule::fetchRows([MpRule::KEYWORD, MpRule::MP_RULE_ID], [MpRule::MP_USER_ID => $mpUserID]);
        $ruleID = 0;

        $userKeyword = strtolower($userKeyword);

        foreach ($rows as $row)
        {
            $dbKeyword = $row[MpRule::KEYWORD];

            // 可能匹配上
            if (strpos($dbKeyword, $userKeyword) !== false)
            {
                // 数据库中的多个关键词以英文逗号分割
                $keywordArr = explode(',', $dbKeyword);
                if (in_array($userKeyword, $keywordArr))
                {
                    $ruleID = $row[MpRule::MP_RULE_ID];
                    break;
                }
            }
        }

        if (!empty($ruleID))
        {
            $rule = new MpRule([MpRule::MP_RULE_ID => $ruleID]);
            if ($rule->isEmpty())
            {
                log_warn("[ruleID:$ruleID]");

                return $res;
            }

            $contentType = $rule->getContentType();

            if ($contentType == WeixinMessageType::TEXT)
            {
                $res['content_type'] = $rule->getContentType();
                $res['content']      = $rule->getContent();
            }
            elseif ($contentType == WeixinMessageType::NEWS)
            {
                $newsIDArray = explode(',', $rule->getContent());
                $rows        = MpRuleNewsItem::fetchRows(['*'], [MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $newsIDArray]);

                if (!empty($rows))
                {
                    $res['content_type'] = $rule->getContentType();
                    $res['content']      = $rows;
                }
            }
        }

        return $res;
    }

    public static function getMpRuleList(array $condition, array &$paging = NULL, $ranking, array &$outputColumns = NULL)
    {

        return MpRule::fetchRowsWithCount(['*'], $condition, NULL, $ranking, $paging, $outputColumns);
    }

    //编辑
    public static function update($mpUserId, $mpRuleID, $ruleName, $keyword, $contentType, $content)
    {
        $mpRule = new MpRule([MpRule::MP_USER_ID => $mpUserId, MpRule::MP_RULE_ID => $mpRuleID]);

        if ($mpRule->isEmpty())
        {
            log_warn("Could not find MpRule($mpRuleID)");

            return false;
        }
        if (!in_array($contentType, WeixinMessageType::getValues()))
        {
            $contentType = WeixinMessageType::getDefaultValue();
        }

        $mpRule->setName($ruleName);
        $mpRule->setKeyword($keyword);
        $mpRule->setContentType($contentType);
        if (!is_null($content))
            $mpRule->setContent($content);
        $mpRule->save();

        return TRUE;
    }

    public static function insert($mpUserId, $ruleName, $keyword, $contentType, $content)
    {
        $mpRule = new MpRule();

        $ruleName = trim($ruleName);
        $keyword  = str_to_semiangle(trim($keyword));
        $keyword  = strtr($keyword, array('，' => ','));
        $keyword  = preg_replace('/\s+/', '', $keyword);
        if (!in_array($contentType, WeixinMessageType::getValues()))
        {
            $contentType = WeixinMessageType::getDefaultValue();
        }

        return $mpRule->setMpUserID($mpUserId)->setName($ruleName)->setKeyword($keyword)->setContent($content)->setContentType($contentType)->insert();
    }

    public static function remove($mpUserId, $mpRuleId)
    {
        $mpRule = new MpRule([MpRule::MP_USER_ID => $mpUserId, MpRule::MP_RULE_ID => $mpRuleId]);
        if ($mpRule->isEmpty())
        {
            log_warn("Could not find MpRule($mpRuleId)");

            return false;
        }
        $mpRule->delete();

        return true;
    }

    public static function addRuleNewsItem($mpRuleId, $mpRuleNewsItemId)
    {
        $mpRule = new MpRule([MpRule::MP_RULE_ID => $mpRuleId]);
        if ($mpRule->isEmpty())
        {
            log_warn("Could not find MpRule($mpRuleId)");

            return false;
        }
        $content = explode(',', $mpRule->getContent());
        array_push_unique($content, $mpRuleNewsItemId);
        foreach ($content as $k => $item)
        {
            if (!filter_var($item, FILTER_VALIDATE_INT))
            {
                unset($content[$k]);
            }
        }
        $mpRule->setContent(implode(',', $content));

        return $mpRule->update();
    }

    public static function addRuleNewsItemForWx($wxSubMenuID, $mpRuleNewsItemId)
    {
        $wxSubMenu = new WxSubMenu([WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID]);
        if ($wxSubMenu->isEmpty())
        {
            log_warn("Could not find WxSubMenu($wxSubMenu)");

            return false;
        }
        $content = explode(',', $wxSubMenu->getContentValue());
        array_push_unique($content, $mpRuleNewsItemId);
        foreach ($content as $k => $item)
        {
            if (!filter_var($item, FILTER_VALIDATE_INT))
            {
                unset($content[$k]);
            }
        }
        $wxSubMenu->setContentValue(implode(',', $content));

        return $wxSubMenu->update();
    }

    public static function removeRuleNewsItem($mpUserId, $mpRuleId, $mpRuleNewsItemId)
    {
        $mpRule = new MpRule([MpRule::MP_USER_ID => $mpUserId, MpRule::MP_RULE_ID => $mpRuleId]);
        if ($mpRule->isEmpty())
        {
            log_warn("Could not find MpRule($mpRuleId)");

            return false;
        }
        $content = explode(',', $mpRule->getContent());
        array_erase($content, $mpRuleNewsItemId);
        $mpRule->setContent(implode(',', $content));

        return $mpRule->update();
    }

    public static function removeRuleNewsItemForWx($mpUserId, $wxSubMenuID, $mpRuleNewsItemId)
    {
        $wxSubMenu = new WxSubMenu([WxSubMenu::MP_USER_ID => $mpUserId, WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID]);
        if ($wxSubMenu->isEmpty())
        {
            log_warn("Could not find WxSubMenu($wxSubMenu)");

            return false;
        }
        $content = explode(',', $wxSubMenu->getContentValue());
        array_erase($content, $mpRuleNewsItemId);
        $wxSubMenu->setContentValue(implode(',', $content));

        return $wxSubMenu->update();
    }
}