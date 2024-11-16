<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_html_emails Class.
 * 
 * @class 		BABE_html_emails
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_html_emails {
    
//////////////email_array_wrapper//////////////
    /**
	 * Put the content to the table tags for html email
     * @param array $content 
     * @return string
	 */
    public static function email_array_wrapper($content){
        
        $output = '';
        
        $output .= '<table class="table_customer_details" cellpadding="0" cellspacing="0"><tbody>';
            
        foreach($content as $field_name => $field_content){
            $output .= '
            <tr>
            <td class="customer_field_label">'.$field_name.'</td>
            <td class="customer_field_content">'.$field_content.'</td>
            </tr>
            ';
        }
        
        $output .= '</tbody></table>';
        
        return $output; 
    }    
        
//////////////email_row_wrapper//////////////
    /**
	 * Put the content to the table tags for html email
     * @param string $content
     * @param string $wrapper_type - could be '', logo, button, header, footer, credit, separator 
     * @return string
	 */
    public static function email_row_wrapper($content, $wrapper_type = ''){
        
        $output = '';
        
        $wrapper_type = $wrapper_type ? '_'.$wrapper_type : '';
        
        if ($content){
        
        $output .= '
        
                <table width="100%" class="wrapper" align="center" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                        <tr>
                            
                            <td class="wrapper_td'.$wrapper_type.'" width="100%">
                                '.$content.'
                            </td>
                        </tr>
                    </tbody>
                </table>
                ';
        }
        
        return $output; 
    }
    
//////////////email_row_wrapper_inner//////////////
    /**
	 * Put the content to the table tags for html email
     * @param string $content
     * @return string
	 */
    public static function email_row_wrapper_inner($content){
        
        $output = '';
        
        if ($content){
        
        $output .= '
                <table width="85%" align="center" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                           <tr>
                                <td class="wrapper_td_inner" width="100%">
                                '.$content.'
                                </td>
                           </tr>     
                    </tbody>
                </table>
                ';
        }
        
        return $output; 
    }

//////////////email_template_wrapper_header//////////////
    /**
	 * Put the content to the table tags for html email
     * @return string
	 */
    public static function email_template_wrapper_header(){
        
        $output = '<!DOCTYPE html>
<html dir="'.(is_rtl() ? 'rtl' : 'ltr').'">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.get_bloginfo( 'charset' ).'">
<title>'.get_bloginfo( 'name', 'display' ).'</title>
<style type="text/css">
  table {border-collapse:separate;}
  a, a:link, a:visited {text-decoration: none; color: #00788a;} 
  a:hover {text-decoration: underline;}
  h2,h2 a,h2 a:visited,h3,h3 a,h3 a:visited,h4,h5,h6,.t_cht {color:#000 !important;}
  .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td {line-height: 100%;}
  .ExternalClass {width: 100%;}
</style>
</head>
<body '.(is_rtl() ? 'rightmargin' : 'leftmargin').'="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">  
        
     <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" id="template_container">
        <tbody>
          <tr>
            <td>
             ';
        
        return $output; 
    }
    
///////////////email_template_wrapper_footer/////////////
    /**
	 * Put the content to the table tags for html email
     * @return string
	 */
    public static function email_template_wrapper_footer(){
        
        $output = '
             </td>
           </tr>     
         </tbody>
      </table>
</body>
</html>';
        
        return $output; 
    }

//////////////email_get_row_logo//////////////
    /**
	 * Get logo html for email header
     * @return string
	 */
    public static function email_get_row_logo(){
        
        $output = '';
        
        $img_src = BABE_Settings::$settings['email_logo'];
        
        if ($img_src){
        
        $output .= '
                <a href="'.home_url().'" target="_blank"><img src="'.$img_src.'" border="0" class="email_logo"></a>
                ';
        
        $output = self::email_row_wrapper($output, 'logo');
        }        
        
        return $output; 
    }
    
//////////////email_get_row_header_image//////////////
    /**
	 * Get header image html for email header, placed under the logo
     * @param string $img_src
     * @param string $url
     * @return string
	 */
    public static function email_get_row_header_image($img_src = '', $url = ''){
        
        $output = '';
        
        $img_src = $img_src ? $img_src : BABE_Settings::$settings['email_header_image'];
        
        if ($img_src){
        
        $output = '
        <img src="'.$img_src.'" border="0" class="email_header_image">
        ';
                
        $output = $url ? '<a href="'.esc_url($url).'" target="_blank">'.$output.'</a>' : $output;
        
        $output = self::email_row_wrapper($output, 'header');
        
        }     
        
        return $output; 
    }
    
//////////////email_get_row_button//////////////
    /**
	 * Get button html for email
     * @param string $title
     * @param string $url
     * @param string $color_type - '' - default, 1 - for "Yes" buttons, 2 - for "No/Delete" buttons 
     * @return string
	 */
    public static function email_get_row_button($title = '', $url = '', $color_type = ''){
        
        $output = '';
        
        if ($title && $url){
                
        $output = '
        <table class="button_table" align="center" border="0" cellpadding="0" cellspacing="0">
            <tbody>
               <tr>
                 <td align="center" valign="middle" class="button_td'.$color_type.'">
                     <a href="'.esc_url($url).'" target="_blank" class="button_a">'.$title.'</a>
                  </td>
                </tr>
            </tbody>
        </table>
        ';
        
        $output = self::email_row_wrapper($output, 'button');
        
        }     
        
        return $output; 
    }
    
//////////////email_get_row_separator//////////////
    /**
	 * Get separator html for email
     * @return string
	 */
    public static function email_get_row_separator(){
        
        $output = '';
                
        $output .= '
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="85%" style="font-weight:normal!important">
          <tbody>
           <tr>
             <td align="center" valign="top" class="separator_td"></td>
           </tr>
          </tbody>
        </table>
        ';
        
        $output = self::email_row_wrapper($output, 'separator');    
        
        return $output; 
    }
    
//////////////email_get_row_footer_message//////////////
    /**
	 * Get footer message html for email
     * @param string $content
     * @return string
	 */
    public static function email_get_row_footer_message($content = ''){
                
        $output = $content ? $content : BABE_Settings::$settings['email_footer_message'];
        
        $output = wpautop( do_shortcode($output) );
        
        $output = self::email_row_wrapper_inner($output);
        
        $output = self::email_row_wrapper($output, 'footer');   
        
        return $output; 
    }
    
//////////////email_get_row_footer_credit//////////////
    /**
	 * Get footer credit html for email
     * @param string $content
     * @return string
	 */
    public static function email_get_row_footer_credit($content = ''){
                
        $output = $content ? $content : BABE_Settings::$settings['email_footer_credit'];
        
        $output = wpautop( do_shortcode($output) );
        
        $output = self::email_row_wrapper($output, 'credit');   
        
        return $output; 
    }
    
//////////////email_get_row_content//////////////
    /**
	 * Get content html row for email
     * @param string $content
     * @return string
	 */
    public static function email_get_row_content($content = ''){
        
        $output = self::email_row_wrapper_inner( wpautop( do_shortcode($content) ) );
        
        $output = self::email_row_wrapper($output);   
        
        return $output; 
    }
    
//////////////email_get_row_title//////////////
    /**
	 * Get title html row for email
     * @param string $content
     * @return string
	 */
    public static function email_get_row_title($content = '', $sub_title = ''){
        
        $content = '
        <table width="100%" cellpadding="0" cellspacing="0">
          <tbody>
            <tr>
              <td align="center" valign="middle" class="title_td'.$sub_title.'">'.esc_html( $content ).'</td>
            </tr>
          </tbody>
        </table>
        ';
        
        $output = self::email_row_wrapper_inner( do_shortcode($content) );
        
        $output = self::email_row_wrapper($output);   
        
        return $output; 
    }
    
//////////////email_body_wrap//////////////
    /**
	 * Wrap email body with headers and footers
     * @param string $body
     * @return string
	 */
    public static function email_body_wrap($body = ''){
        
        $output = self::email_template_wrapper_header().self::email_get_row_logo().$body.self::email_get_row_separator().self::email_get_row_footer_message().self::email_get_row_footer_credit().self::email_template_wrapper_footer();   
        
        return $output; 
    }                                                             

////////////////////////////
    
}
