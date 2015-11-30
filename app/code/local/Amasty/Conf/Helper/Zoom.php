<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Helper_Zoom extends Mage_Core_Helper_Abstract
{
   public function jsParam()
   {
       $param['general'] = array(
           'zoom_enable'    =>  Mage::getStoreConfig('amconf/zoom/enable'),
           'lightbox_enable' =>     Mage::getStoreConfig('amconf/lightbox/enable'),
           'carousel_enable' =>     Mage::getStoreConfig('amconf/carousel/enable'),
           'change_image' =>     Mage::getStoreConfig('amconf/zoom/change_main_img_with'),
           'thumbnail_lignhtbox' =>     Mage::getStoreConfig('amconf/lightbox/thumbnail_lignhtbox'),
       );       
       
       /*Zoom configuration start*/
       $param['zoom']['zoomType'] = Mage::getStoreConfig('amconf/zoom/type');
       $param['zoom']['responsive'] = true;
       $param['zoom']['preloading'] = Mage::getStoreConfig('amconf/zoom_settings/preloading');
       $param['zoom']['loadingIcon'] = Mage::getDesign()->getSkinUrl('js/amasty/amconf/images/preloader.gif');
       switch(Mage::getStoreConfig('amconf/zoom/type')) {
           case "lens":
              $param['zoom']["lensShape"] = "round";
              $param['zoom']["lensSize"] = Mage::getStoreConfig('amconf/zoom_settings/lens_size');
              $param['zoom']["borderSize"] = 1;
               break; 
           case "inner":
                $param['zoom']["cursor"] = "crosshair";
               break;
           case "window":
           default:
              $param['zoom']["zoomWindowOffetx"] = (int)Mage::getStoreConfig('amconf/zoom_settings/offset_x');
              $param['zoom']["zoomWindowOffety"] = (int)Mage::getStoreConfig('amconf/zoom_settings/offset_y');     
              $param['zoom']["zoomWindowPosition"] = (int)Mage::getStoreConfig('amconf/zoom_settings/viewer_position');     
              $param['zoom']["zoomWindowWidth"] = (int)Mage::getStoreConfig('amconf/zoom_settings/viewer_width');     
              $param['zoom']["zoomWindowHeight"] = (int)Mage::getStoreConfig('amconf/zoom_settings/viewer_height');   
              if(Mage::getStoreConfig('amconf/zoom_settings/use_tint_effect')) {
                  $param['zoom']["tint"] = true;
                  $param['zoom']["tintOpacity"] = 0.5;
                  $param['zoom']["tintColour"] = Mage::getStoreConfig('amconf/zoom_settings/tint_color');    
              }  
       }
       
       if (Mage::getStoreConfig('amconf/zoom_settings/fadein')) {
            $param['zoom']["zoomWindowFadeIn"] = 500;
            $param['zoom']["zoomWindowFadeOut"] = 500;
            $param['zoom']["lensFadeIn"] = 630;
       }
       if (Mage::getStoreConfig('amconf/zoom_settings/easing')) {
            $param['zoom']["easing"] = true;
       }
       if (Mage::getStoreConfig('amconf/zoom_settings/scroll')) {
            $param['zoom']["scrollZoom"] = true;
       }  
       
       /*Lightbox configuration start*/  
        if(Mage::getStoreConfig('amconf/lightbox/enable')) {  
             $param['zoom']["gallery"] = 'amasty_gallery';
            $param['zoom']["cursor"] = 'pointer';
            $param['zoom']["galleryActiveClass"] = 'active';
            $param['zoom']["imageCrossfade"] = false;
              
             $param['lightbox']['loop'] = intval(Mage::getStoreConfig('amconf/lightbox/circular_lightbox'));
             $param['lightbox']['prevEffect'] = Mage::getStoreConfig('amconf/lightbox/effect');
             $param['lightbox']['nextEffect'] = Mage::getStoreConfig('amconf/lightbox/effect');
             $param['lightbox']['helpers'] = array(
                 "title" => array("type" => Mage::getStoreConfig('amconf/lightbox/title_position')), 
             );
             if(Mage::getStoreConfig('amconf/lightbox/thumbnail_helper')) {
                 $param['lightbox']['helpers']['thumbs'] =   array("width" => 50,
                                   "height" => 50
                  ); 
             }
       }
       /*Carusel configuration end*/
       
       /*Zoom configuration end*/
       
       
       /*Carusel configuration start*/
       $param['carousel']['items'] = (int)Mage::getStoreConfig('amconf/carousel/visible_items');
       $param['carousel']['circular'] = Mage::getStoreConfig('amconf/carousel/circular')? true: false;
       $param['carousel']['height'] = (int)Mage::getStoreConfig('amconf/size/thumb');
       $param['carousel']['prev'] = array("button" => "#prevGallery", "key" => "left");
       $param['carousel']['next'] = array("button" => "#nextGallery", "key" => "right");
       $param['carousel']['auto'] = Mage::getStoreConfig('amconf/carousel/auto')? true: false;
       if(Mage::getStoreConfig('amconf/carousel/swipe')) {
            $param['carousel']['swipe'] = array("onTouch" => true, "onMouse" => true);   
       }    
       //$param['carousel']['mousewheel'] = Mage::getStoreConfig('amconf/carousel/mousewheel')? true: false;    
       if(Mage::getStoreConfig('amconf/carousel/pagination')) {
           $param['carousel']['pagination'] = "#ampagination";    
       }
       $param['carousel']['responsive'] = false;
       $param['carousel']['infinite'] = false;
       /*Carusel configuration end*/
       
       return Zend_Json::encode($param);
   }
  
}
