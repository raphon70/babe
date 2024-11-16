<?php
/**
 * Email Styles
 *
 * This template can be overridden by copying it to yourtheme/ba-book-everything/emails/email-styles.php.
 *
 * @author  Booking Algorithms
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

#template_container{
	vertical-align: middle;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    background-color: <?php echo BABE_Settings::$settings['email_color_background']; ?>;
    color: <?php echo BABE_Settings::$settings['email_color_font']; ?>;
}

a{
    text-decoration:none;
    color: <?php echo BABE_Settings::$settings['email_color_link']; ?>;
    border: none;
    outline: none;
}

.wrapper{
    background-color: <?php echo BABE_Settings::$settings['email_color_background']; ?>;
    max-width: 600px;
    font-size: 14px;
}

.wrapper_td {
    max-width: 600px;
    text-align:left;
    padding:10px 0;
    background-color: #ffffff;
    vertical-align: middle;
}

.wrapper_td_separator {
    max-width: 600px;
    text-align:center;
    padding:10px 0;
    background-color: #ffffff;
    vertical-align: middle;
}

.wrapper_td_logo{
    text-align:center;
    vertical-align: middle;
    padding:10px 10px;
}

.wrapper_td_button{
	max-width: 600px;
    vertical-align: middle;
    text-align:center;
    padding:10px 0;
    background-color: #ffffff;
}

.wrapper_td_header{
	max-width: 600px;
    text-align:left;
    background-color: #ffffff;
}

.wrapper_td_footer{
    max-width: 600px;
    text-align:center;
    padding:10px 0;
    background-color: #ffffff;
}

.wrapper_td_credit{
    max-width: 600px;
    text-align:center;
    font-size:12px;
    vertical-align: middle;
    padding:20px 0;
    background-color: <?php echo BABE_Settings::$settings['email_color_background']; ?>;
}

.wrapper_td_inner{
    font-size:14px;
    text-align:left;
}

.email_logo{
    border: none;
    outline: none;
    text-decoration: none;
}

.email_header_image{
    display: block;
    border: none;
    outline: none;
    text-decoration: none;
    max-width: 600px;
    width: 100%;
}

.button_table{
   min-width: 200px;
   max-width: 300px;
   width: 60%;
   text-align:center; 
}

.button_td{
   width: 100%;
   text-align: center;
   font-size: 16px;
   vertical-align: middle;
   background-color:<?php echo BABE_Settings::$settings['email_color_button']; ?>;
   color:#ffffff;
   font-weight:bold; 
}

.button_td1{
   width: 100%;
   text-align: center;
   font-size: 16px;
   vertical-align: middle;
   background-color:<?php echo BABE_Settings::$settings['email_color_button_yes']; ?>;
   color:#ffffff;
   font-weight:bold; 
}

.button_td2{
   width: 100%;
   text-align: center;
   font-size: 16px;
   vertical-align: middle;
   background-color:<?php echo BABE_Settings::$settings['email_color_button_no']; ?>;
   color:#ffffff;
   font-weight:bold; 
}

.button_a{
   text-decoration: none;
   color: #ffffff;
   display: block;
   padding: 14px 20px;
   text-align:center;
   text-decoration:none;
   font-weight:400;
}

.separator_td{
   border-top-width:1px;
   border-top-style:solid;
   border-top-color:#989898;
   font-weight:normal!important;
}

.title_td{
   font-size: 20px;
   color:<?php echo BABE_Settings::$settings['email_color_title']; ?>;
   text-align:center;
}

.title_td1{
   font-size: 18px;
   color:<?php echo BABE_Settings::$settings['email_color_title']; ?>;
   text-align:center;
}

<?php //Customer details table styles  ?>

.table_customer_details{
    text-align: left;
    font-size: 14px;
}

.customer_field_label{
    padding: 2px 5px;
    font-weight: 700;
    font-size: 14px;
}

.customer_field_content{
    padding: 2px 5px;
    font-weight: normal;
    font-size: 14px;
}


<?php //Order details table styles  ?>

.table_order_items_details{
    border: 1px solid #ffffff;
    background-color: #f2f2f2;
    border-bottom: none;
    font-size: 14px;
    width: 100%;
}

.table_order_items_details > tr, .table_order_items_details > tbody > tr > td{
   border-bottom: 1px solid #ffffff; 
}

.table_order_item_row_details, .table_order_item_row_details > tbody > tr{
    border: none;
    margin: 0;
}

.table_order_item_total_price, .table_order_item_total_price tr{
    border: none;
    margin: 0;
}

.table_order_item_row_details td{
    padding: 2px 5px;
}

.table_order_items_details td.order_item_info_title{
    font-size: 16px;
}

.table_order_items_details a{
    text-decoration:none;
    color: <?php echo BABE_Settings::$settings['email_color_link']; ?>;
}

.order_item_row_image{
    width: 150px;
    padding: 8px;
}

.order_item_row_main_details{
    padding: 4px;
}
.order_item_row_image img{
    width: 100%;
}

.order_item_td_label{
    padding-right: 5px;
    font-weight: 700;
    font-size: 14px;
}

.order_item_td_value{
    padding-right: 10px;
    font-size: 14px;
}

.table_order_item_total_price td{
    padding: 3px 10px 3px 10px;
}

.table_order_item_total_price td.order_item_total_price{
    font-size: 16px;
    color: #f7931e;
    font-weight: 700;
}

.order_item_row_price{
    width: 80px;
}    

.order_item_row_price .order_item_discount_note{
    font-style: italic;
    background-color: #f7931e;
    color: #fff;
    padding: 3px 7px;
    border-radius: 5px;
}

.order_item_age_prices, .order_item_services{
    border: none;
    margin: 0;
}

.order_item_age_prices td, .order_item_services td{
    font-size: 14px;
}

.order_items_row_total, table td.order_items_row_total{
    text-align: right;
    font-weight: 700;
    color: #777;
    background-color: #f2f2f2;
    padding: 0;
}

table td.order_items_row_total_amount{
    font-weight: 700;
    background-color: #f2f2f2;
    padding: 4px;
}

table td.order_items_row_total_amount.order_items_row_due{
    color: #f7931e;
}

.order_items_row_total_label{
   padding-right: 10px; 
}

table.order_item_age_prices td{
    padding: 3px;
}

.coupon-form-block-applied{
    text-align: center;
    margin-bottom: 10px;
    padding: 10px;
    background-color: #f3f3f3;
    border: 1px solid #1e73be;
    font-size: 16px;
    font-weight: 500;
    line-height: 1.15;
}


<?php
