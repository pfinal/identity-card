<?php

namespace PFinal\IdentityCard;

//如果需要获取地区信息，并且开启了pdo sqlite扩展，可以使用另一个组件库 https://github.com/douyasi/identity-card
class IDCard
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * 验证身份证是否有效
     *
     * @param string $IDCard
     * @return bool
     */
    public static function validate($IDCard)
    {
        return static::check18IDCard($IDCard);
    }

    /**
     * 18位身份证校验码有效性检查
     *
     * @param $IDCard
     * @return bool
     */
    protected static function check18IDCard($IDCard)
    {
        if (strlen($IDCard) != 18) {
            return false;
        }

        if (!static::birthday($IDCard)) {
            return false;
        }

        $IDCardBody = substr($IDCard, 0, 17); //身份证主体
        $IDCardCode = strtoupper(substr($IDCard, 17, 1)); //身份证最后一位的验证码

        return static::calcIDCardCode($IDCardBody) === $IDCardCode;
    }

    /**
     * 计算身份证的最后一位验证码, 根据国家标准GB 11643-1999
     *
     * @param $IDCardBody
     * @return bool|string
     */
    protected static function calcIDCardCode($IDCardBody)
    {
        if (strlen($IDCardBody) != 17) {
            return false;
        }

        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        //校验码对应值
        $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        for ($i = 0; $i < strlen($IDCardBody); $i++) {
            $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
        }

        return $code[$checksum % 11];
    }

    /**
     * 将15位身份证升级到18位
     *
     * @param $IDCard
     * @return bool|string
     */
    public static function convertIDCard15to18($IDCard)
    {
        if (strlen($IDCard) != 15) {
            return false;
        }

        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($IDCard, 12, 3), array('996', '997', '998', '999')) !== false) {
            $IDCard = substr($IDCard, 0, 6) . '18' . substr($IDCard, 6, 9);
        } else {
            $IDCard = substr($IDCard, 0, 6) . '19' . substr($IDCard, 6, 9);
        }

        return $IDCard . static::calcIDCardCode($IDCard);
    }

    /**
     * 获取生日
     *
     * @param $IDCard
     * @return bool|string 例如 "1990-01-30" 失败返回false
     */
    public static function birthday($IDCard)
    {
        if (strlen($IDCard) != 18) {
            return false;
        }

        $year = substr($IDCard, 6, 4);
        $month = substr($IDCard, 10, 2);
        $day = substr($IDCard, 12, 2);

        $birthday = $year . '-' . $month . '-' . $day;

        $data = \DateTime::createFromFormat('Y-m-d', $birthday);

        if ($data == false) {
            return false;
        }

        if ($data->format('Y-m-d') === $birthday) {
            return $birthday;
        }

        return false;
    }

    /**
     * 获取性别
     *
     * @return int IDCard::GENDER_FEMALE 男(1)  IDCard::GENDER_FEMALE 女 (2)
     */
    public static function gender($IDCard)
    {
        if (strlen($IDCard) == 18) {
            $gender = $IDCard[16];
        } else {
            $gender = $IDCard[14];
        }

        return $gender % 2 == 0 ? self::GENDER_FEMALE : self::GENDER_MALE;
    }

    /**
     * 根据身份证判断,是否满足年龄条件
     *
     * @param string $IDCard 身份证
     * @param int $minAge 最小年龄
     * @return bool
     */
    public static function isMeetAgeByIDCard($IDCard, $minAge)
    {
        if (!static::validate($IDCard)) {
            return false;
        }

        $year = date('Y') - substr($IDCard, 6, 4);
        $monthDay = date('md') - substr($IDCard, 10, 4);

        return $year > $minAge || $year == $minAge && $monthDay > 0;
    }
}