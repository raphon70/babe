<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Currency Class.
 * Get general settings
 * @class 		BABE_Currency
 * @version		1.4.19
 * @author 		Booking Algorithms
 */

class BABE_Currency {

    ////////////////////
    /**
     * Get Base Currency Code.
     *
     * @return string
     */
    public static function get_currency() {
        return apply_filters( 'babe_currency', BABE_Settings::get_option('currency') );
    }

    ////////////////////

    /**
     * Get currency precision
     *
     * @param string $currency
     * @return string
     */
    public static function get_currency_precision( $currency = '' ) {
        return apply_filters( 'babe_currency_precision', (int)BABE_Settings::get_option('price_decimals'), $currency );
    }

    ////////////////////

    /**
     * Get the currency place.
     *
     * @param string $currency
     * @return string
     */
    public static function get_currency_place( $currency = '' ) {
        return apply_filters( 'babe_currency_place', BABE_Settings::get_option('currency_place'), $currency );
    }

    ////////////////////

    /**
     * Get the currency decimal separator
     *
     * @param string $currency
     * @return string
     */
    public static function get_currency_decimal_separator( $currency = '' ) {
        return apply_filters( 'babe_currency_decimal_separator', BABE_Settings::get_option('price_decimal_separator'), $currency );
    }

    ////////////////////

    /**
     * Get the currency thousand separator
     *
     * @param string $currency
     * @return string
     */
    public static function get_currency_thousand_separator( $currency = '' ) {
        return apply_filters( 'babe_currency_thousand_separator', BABE_Settings::get_option('price_thousand_separator'), $currency );
    }

    ////////////////////
    /**
     * Get the price format depending on the currency place.
     *
     * @param string $currency_place
     * @return string
     */
    public static function get_price_format( $currency_place = '' ) {

        $currency_place = $currency_place ? $currency_place : self::get_currency_place();

        switch ( $currency_place ) {

            case 'right':
                $format = '%2$s%1$s';
                break;
            case 'left_space':
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space':
                $format = '%2$s&nbsp;%1$s';
                break;
            case 'left':
            default:
                $format = '%1$s%2$s';
                break;
        }

        return apply_filters( 'babe_price_format', $format, $currency_place );
    }

    ////////////////////////////////
    /**
     * Format the price with a currency symbol.
     *
     * @param float $price
     * @param string $currency
     * @param array $args
     * @return string
     */
    public static function get_currency_price( $price, $currency = '', $args = array() ) {

        $currency = $currency ?: self::get_currency();

        $args = apply_filters( 'babe_price_args', wp_parse_args( $args, array(
            'decimal_separator'  => self::get_currency_decimal_separator($currency),
            'thousand_separator' => self::get_currency_thousand_separator($currency),
            'decimals'           => self::get_currency_precision($currency),
        ) ) );

        $price = (float)$price;
        $data_amount = $price;

        $negative = $price < 0;
        $price = apply_filters( 'babe_raw_price', ($negative ? $price * -1 : $price ) );
        $price = apply_filters( 'babe_formatted_price',
            number_format(
                $price,
                $args['decimals'],
                $args['decimal_separator'],
                $args['thousand_separator']
            ),
            $price,
            $args['decimals'],
            $args['decimal_separator'],
            $args['thousand_separator']
        );

        if ( apply_filters( 'babe_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
            $price = (float)self::price_trim_zeros( $price );
        }

        $formatted_price = ( $negative ? '-' : '' ) . sprintf( self::get_price_format( self::get_currency_place($currency) ), '<span class="currency_symbol">' . self::get_currency_symbol( $currency ) . '</span>', $price );
        $return = '<span class="currency_amount" data-amount="'.$data_amount.'">' . $formatted_price . '</span>';

        // deprecated
        $return = apply_filters( 'babe_price', $return, $price, $args );

        return apply_filters( 'babe_currency_price', $return, $price, $currency, $args );
    }

    public static function get_zero_price_display_value( $currency = '' ){

        $option_zero_price_display_value = BABE_Settings::get_option('zero_price_display_value');
        if ( in_array($option_zero_price_display_value, ["0", "0.00", "0,00"]) ){
            return self::get_currency_price($option_zero_price_display_value, $currency);
        }

        return $option_zero_price_display_value;
    }

    /////////////////////////////
    /**
     * Trim trailing zeros off prices.
     *
     * @param mixed $price
     * @return string
     */
    public static function price_trim_zeros( $price ) {
        return preg_replace( '/' . preg_quote( stripslashes( BABE_Settings::$settings['price_decimal_separator'] ), '/' ) . '0++$/', '', $price );
    }

    ////////////////
    /**
     * Get currency symbol.
     * @param string $currency (default: '')
     * @return string
     */
    public static function get_currency_symbol($currency = '') {

        if ( ! $currency ) {
            $currency = self::get_currency();
        }

        $symbols = apply_filters( 'babe_currency_symbols', array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&fnof;',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        ) );

        $currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

        return apply_filters( 'babe_currency_symbol', $currency_symbol, $currency );
    }

    ////////////////////
    /**
     * Get full list of currency codes.
     *
     * @return array
     */
    public static function get_currencies() {
        return array_unique(
            apply_filters( 'babe_currencies',
                array(
                    'AED' => __( 'United Arab Emirates dirham', 'ba-book-everything' ),
                    'AFN' => __( 'Afghan afghani', 'ba-book-everything' ),
                    'ALL' => __( 'Albanian lek', 'ba-book-everything' ),
                    'AMD' => __( 'Armenian dram', 'ba-book-everything' ),
                    'ANG' => __( 'Netherlands Antillean guilder', 'ba-book-everything' ),
                    'AOA' => __( 'Angolan kwanza', 'ba-book-everything' ),
                    'ARS' => __( 'Argentine peso', 'ba-book-everything' ),
                    'AUD' => __( 'Australian dollar', 'ba-book-everything' ),
                    'AWG' => __( 'Aruban florin', 'ba-book-everything' ),
                    'AZN' => __( 'Azerbaijani manat', 'ba-book-everything' ),
                    'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'ba-book-everything' ),
                    'BBD' => __( 'Barbadian dollar', 'ba-book-everything' ),
                    'BDT' => __( 'Bangladeshi taka', 'ba-book-everything' ),
                    'BGN' => __( 'Bulgarian lev', 'ba-book-everything' ),
                    'BHD' => __( 'Bahraini dinar', 'ba-book-everything' ),
                    'BIF' => __( 'Burundian franc', 'ba-book-everything' ),
                    'BMD' => __( 'Bermudian dollar', 'ba-book-everything' ),
                    'BND' => __( 'Brunei dollar', 'ba-book-everything' ),
                    'BOB' => __( 'Bolivian boliviano', 'ba-book-everything' ),
                    'BRL' => __( 'Brazilian real', 'ba-book-everything' ),
                    'BSD' => __( 'Bahamian dollar', 'ba-book-everything' ),
                    'BTC' => __( 'Bitcoin', 'ba-book-everything' ),
                    'BTN' => __( 'Bhutanese ngultrum', 'ba-book-everything' ),
                    'BWP' => __( 'Botswana pula', 'ba-book-everything' ),
                    'BYR' => __( 'Belarusian ruble', 'ba-book-everything' ),
                    'BZD' => __( 'Belize dollar', 'ba-book-everything' ),
                    'CAD' => __( 'Canadian dollar', 'ba-book-everything' ),
                    'CDF' => __( 'Congolese franc', 'ba-book-everything' ),
                    'CHF' => __( 'Swiss franc', 'ba-book-everything' ),
                    'CLP' => __( 'Chilean peso', 'ba-book-everything' ),
                    'CNY' => __( 'Chinese yuan', 'ba-book-everything' ),
                    'COP' => __( 'Colombian peso', 'ba-book-everything' ),
                    'CRC' => __( 'Costa Rican col&oacute;n', 'ba-book-everything' ),
                    'CUC' => __( 'Cuban convertible peso', 'ba-book-everything' ),
                    'CUP' => __( 'Cuban peso', 'ba-book-everything' ),
                    'CVE' => __( 'Cape Verdean escudo', 'ba-book-everything' ),
                    'CZK' => __( 'Czech koruna', 'ba-book-everything' ),
                    'DJF' => __( 'Djiboutian franc', 'ba-book-everything' ),
                    'DKK' => __( 'Danish krone', 'ba-book-everything' ),
                    'DOP' => __( 'Dominican peso', 'ba-book-everything' ),
                    'DZD' => __( 'Algerian dinar', 'ba-book-everything' ),
                    'EGP' => __( 'Egyptian pound', 'ba-book-everything' ),
                    'ERN' => __( 'Eritrean nakfa', 'ba-book-everything' ),
                    'ETB' => __( 'Ethiopian birr', 'ba-book-everything' ),
                    'EUR' => __( 'Euro', 'ba-book-everything' ),
                    'FJD' => __( 'Fijian dollar', 'ba-book-everything' ),
                    'FKP' => __( 'Falkland Islands pound', 'ba-book-everything' ),
                    'GBP' => __( 'Pound sterling', 'ba-book-everything' ),
                    'GEL' => __( 'Georgian lari', 'ba-book-everything' ),
                    'GGP' => __( 'Guernsey pound', 'ba-book-everything' ),
                    'GHS' => __( 'Ghana cedi', 'ba-book-everything' ),
                    'GIP' => __( 'Gibraltar pound', 'ba-book-everything' ),
                    'GMD' => __( 'Gambian dalasi', 'ba-book-everything' ),
                    'GNF' => __( 'Guinean franc', 'ba-book-everything' ),
                    'GTQ' => __( 'Guatemalan quetzal', 'ba-book-everything' ),
                    'GYD' => __( 'Guyanese dollar', 'ba-book-everything' ),
                    'HKD' => __( 'Hong Kong dollar', 'ba-book-everything' ),
                    'HNL' => __( 'Honduran lempira', 'ba-book-everything' ),
                    'HRK' => __( 'Croatian kuna', 'ba-book-everything' ),
                    'HTG' => __( 'Haitian gourde', 'ba-book-everything' ),
                    'HUF' => __( 'Hungarian forint', 'ba-book-everything' ),
                    'IDR' => __( 'Indonesian rupiah', 'ba-book-everything' ),
                    'ILS' => __( 'Israeli new shekel', 'ba-book-everything' ),
                    'IMP' => __( 'Manx pound', 'ba-book-everything' ),
                    'INR' => __( 'Indian rupee', 'ba-book-everything' ),
                    'IQD' => __( 'Iraqi dinar', 'ba-book-everything' ),
                    'IRR' => __( 'Iranian rial', 'ba-book-everything' ),
                    'ISK' => __( 'Icelandic kr&oacute;na', 'ba-book-everything' ),
                    'JEP' => __( 'Jersey pound', 'ba-book-everything' ),
                    'JMD' => __( 'Jamaican dollar', 'ba-book-everything' ),
                    'JOD' => __( 'Jordanian dinar', 'ba-book-everything' ),
                    'JPY' => __( 'Japanese yen', 'ba-book-everything' ),
                    'KES' => __( 'Kenyan shilling', 'ba-book-everything' ),
                    'KGS' => __( 'Kyrgyzstani som', 'ba-book-everything' ),
                    'KHR' => __( 'Cambodian riel', 'ba-book-everything' ),
                    'KMF' => __( 'Comorian franc', 'ba-book-everything' ),
                    'KPW' => __( 'North Korean won', 'ba-book-everything' ),
                    'KRW' => __( 'South Korean won', 'ba-book-everything' ),
                    'KWD' => __( 'Kuwaiti dinar', 'ba-book-everything' ),
                    'KYD' => __( 'Cayman Islands dollar', 'ba-book-everything' ),
                    'KZT' => __( 'Kazakhstani tenge', 'ba-book-everything' ),
                    'LAK' => __( 'Lao kip', 'ba-book-everything' ),
                    'LBP' => __( 'Lebanese pound', 'ba-book-everything' ),
                    'LKR' => __( 'Sri Lankan rupee', 'ba-book-everything' ),
                    'LRD' => __( 'Liberian dollar', 'ba-book-everything' ),
                    'LSL' => __( 'Lesotho loti', 'ba-book-everything' ),
                    'LYD' => __( 'Libyan dinar', 'ba-book-everything' ),
                    'MAD' => __( 'Moroccan dirham', 'ba-book-everything' ),
                    'MDL' => __( 'Moldovan leu', 'ba-book-everything' ),
                    'MGA' => __( 'Malagasy ariary', 'ba-book-everything' ),
                    'MKD' => __( 'Macedonian denar', 'ba-book-everything' ),
                    'MMK' => __( 'Burmese kyat', 'ba-book-everything' ),
                    'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'ba-book-everything' ),
                    'MOP' => __( 'Macanese pataca', 'ba-book-everything' ),
                    'MRO' => __( 'Mauritanian ouguiya', 'ba-book-everything' ),
                    'MUR' => __( 'Mauritian rupee', 'ba-book-everything' ),
                    'MVR' => __( 'Maldivian rufiyaa', 'ba-book-everything' ),
                    'MWK' => __( 'Malawian kwacha', 'ba-book-everything' ),
                    'MXN' => __( 'Mexican peso', 'ba-book-everything' ),
                    'MYR' => __( 'Malaysian ringgit', 'ba-book-everything' ),
                    'MZN' => __( 'Mozambican metical', 'ba-book-everything' ),
                    'NAD' => __( 'Namibian dollar', 'ba-book-everything' ),
                    'NGN' => __( 'Nigerian naira', 'ba-book-everything' ),
                    'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'ba-book-everything' ),
                    'NOK' => __( 'Norwegian krone', 'ba-book-everything' ),
                    'NPR' => __( 'Nepalese rupee', 'ba-book-everything' ),
                    'NZD' => __( 'New Zealand dollar', 'ba-book-everything' ),
                    'OMR' => __( 'Omani rial', 'ba-book-everything' ),
                    'PAB' => __( 'Panamanian balboa', 'ba-book-everything' ),
                    'PEN' => __( 'Peruvian nuevo sol', 'ba-book-everything' ),
                    'PGK' => __( 'Papua New Guinean kina', 'ba-book-everything' ),
                    'PHP' => __( 'Philippine peso', 'ba-book-everything' ),
                    'PKR' => __( 'Pakistani rupee', 'ba-book-everything' ),
                    'PLN' => __( 'Polish z&#x142;oty', 'ba-book-everything' ),
                    'PRB' => __( 'Transnistrian ruble', 'ba-book-everything' ),
                    'PYG' => __( 'Paraguayan guaran&iacute;', 'ba-book-everything' ),
                    'QAR' => __( 'Qatari riyal', 'ba-book-everything' ),
                    'RON' => __( 'Romanian leu', 'ba-book-everything' ),
                    'RSD' => __( 'Serbian dinar', 'ba-book-everything' ),
                    'RUB' => __( 'Russian ruble', 'ba-book-everything' ),
                    'RWF' => __( 'Rwandan franc', 'ba-book-everything' ),
                    'SAR' => __( 'Saudi riyal', 'ba-book-everything' ),
                    'SBD' => __( 'Solomon Islands dollar', 'ba-book-everything' ),
                    'SCR' => __( 'Seychellois rupee', 'ba-book-everything' ),
                    'SDG' => __( 'Sudanese pound', 'ba-book-everything' ),
                    'SEK' => __( 'Swedish krona', 'ba-book-everything' ),
                    'SGD' => __( 'Singapore dollar', 'ba-book-everything' ),
                    'SHP' => __( 'Saint Helena pound', 'ba-book-everything' ),
                    'SLL' => __( 'Sierra Leonean leone', 'ba-book-everything' ),
                    'SOS' => __( 'Somali shilling', 'ba-book-everything' ),
                    'SRD' => __( 'Surinamese dollar', 'ba-book-everything' ),
                    'SSP' => __( 'South Sudanese pound', 'ba-book-everything' ),
                    'STD' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'ba-book-everything' ),
                    'SYP' => __( 'Syrian pound', 'ba-book-everything' ),
                    'SZL' => __( 'Swazi lilangeni', 'ba-book-everything' ),
                    'THB' => __( 'Thai baht', 'ba-book-everything' ),
                    'TJS' => __( 'Tajikistani somoni', 'ba-book-everything' ),
                    'TMT' => __( 'Turkmenistan manat', 'ba-book-everything' ),
                    'TND' => __( 'Tunisian dinar', 'ba-book-everything' ),
                    'TOP' => __( 'Tongan pa&#x2bb;anga', 'ba-book-everything' ),
                    'TRY' => __( 'Turkish lira', 'ba-book-everything' ),
                    'TTD' => __( 'Trinidad and Tobago dollar', 'ba-book-everything' ),
                    'TWD' => __( 'New Taiwan dollar', 'ba-book-everything' ),
                    'TZS' => __( 'Tanzanian shilling', 'ba-book-everything' ),
                    'UAH' => __( 'Ukrainian hryvnia', 'ba-book-everything' ),
                    'UGX' => __( 'Ugandan shilling', 'ba-book-everything' ),
                    'USD' => __( 'United States dollar', 'ba-book-everything' ),
                    'UYU' => __( 'Uruguayan peso', 'ba-book-everything' ),
                    'UZS' => __( 'Uzbekistani som', 'ba-book-everything' ),
                    'VEF' => __( 'Venezuelan bol&iacute;var', 'ba-book-everything' ),
                    'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'ba-book-everything' ),
                    'VUV' => __( 'Vanuatu vatu', 'ba-book-everything' ),
                    'WST' => __( 'Samoan t&#x101;l&#x101;', 'ba-book-everything' ),
                    'XAF' => __( 'Central African CFA franc', 'ba-book-everything' ),
                    'XCD' => __( 'East Caribbean dollar', 'ba-book-everything' ),
                    'XOF' => __( 'West African CFA franc', 'ba-book-everything' ),
                    'XPF' => __( 'CFP franc', 'ba-book-everything' ),
                    'YER' => __( 'Yemeni rial', 'ba-book-everything' ),
                    'ZAR' => __( 'South African rand', 'ba-book-everything' ),
                    'ZMW' => __( 'Zambian kwacha', 'ba-book-everything' ),
                )
            )
        );
    }

    /////////////////////////////
    /**
     * Format decimal numbers ready for DB storage.
     * Sanitize, remove decimals, and optionally round + trim off zeros.
     * This function does not remove thousands - this should be done before passing a value to the function.
     *
     * @param  float|string $number     Expects either a float or a string with a decimal separator only (no thousands).
     * @param  mixed        $dp number  Number of decimal points to use, blank to use BA settings, or false to avoid all rounding.
     * @param  bool         $trim_zeros From end of string.
     * @return string
     */
    public static function format_decimal( $number, $dp = false, $trim_zeros = false ) {
        $locale   = localeconv();
        $decimals = array( BABE_Settings::$settings['price_decimal_separator'], $locale['decimal_point'], $locale['mon_decimal_point'] );

        // Remove locale from string.
        if ( ! is_float( $number ) ) {
            $number = str_replace( $decimals, '.', $number );
            // Convert multiple dots to just one.
            $number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', sanitize_text_field( $number ) );
        }

        if ( false !== $dp ) {

            $dp     = (int)('' === $dp ? BABE_Settings::$settings['price_decimals'] : $dp);
            $number = number_format( (float)$number, $dp, '.', '' );

        } elseif ( is_float( $number ) ) {
            // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
            $number = str_replace( $decimals, '.', sprintf( '%.' . ((int)BABE_Settings::$settings['price_decimals'] + 2) . 'f', $number ) );
            // We already had a float, so trailing zeros are not needed.
            $trim_zeros = true;
        }

        if ( $trim_zeros && strpos($number, '.') !== false) {
            $number = rtrim( rtrim( $number, '0' ), '.' );
        }

        return $number;
    }
        
////////////////////    
}
