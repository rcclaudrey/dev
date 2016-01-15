<?php

class SMDesign_ColorswatchProductView_GetController extends Mage_Core_Controller_Front_Action {

    function mainImageAction() {

        $selection = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('selection', '[]'));
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $optionId = $this->getRequest()->getParam('option_id');
        $productId = $this->getRequest()->getParam('product_id');
        $imageSelector = $this->getRequest()->getParam('image_selector', '.product-img-box img#image');

        $_product = Mage::getModel('catalog/product')->load($productId);
        if (!$_product->getId()) {
            $this->_forward('noRoute');
            return;
        }

        $selectedAttributeCode = $_product->getTypeInstance(true)->getAttributeById($attributeId, $_product)->getAttributeCode();

        $colorswatch = Mage::getModel('colorswatch/product_swatch')->setProduct($_product);
        $allProducts = $colorswatch->getAllowProducts();
		$products = array();

        foreach ($allProducts as $product) {
            if ($product->isSaleable() && $product->getIsInStock()) {
                if (Mage::getModel('colorswatch/attribute_settings')->getConfig($attributeId, 'allow_attribute_to_change_main_image') == 1) {
                    if ($product->getData($selectedAttributeCode) == $optionId) {
                        $products[] = $product;
                    }
                } else {
                    $products[] = $product;
                }
            }
        }

        $selected = array();
        foreach ($selection as $key => $val) {
            if ($val && Mage::getModel('colorswatch/attribute_settings')->getConfig($key, 'allow_attribute_to_change_main_image') == 1) {
                $selected[$key] = $val;
            }
        }

        $allAvialableAttributeCode = $colorswatch->getAllAttributeCodes();
        foreach ($colorswatch->getAllAttributeIds() as $aKey => $aId) {

            if (!isset($selected[$aId]) && Mage::getModel('colorswatch/attribute_settings')->getConfig($aId, 'allow_attribute_to_change_main_image') == 1) {
                $options = $colorswatch->getAttributeById($aId)->getColorswatchOptions()->getData();
                $optionCount = count($options);
                $optionIndex = 0;

                while ($optionIndex < $optionCount) {
                    $option = $options[$optionIndex];

                    if ($this->productExsist($products, $allAvialableAttributeCode[$aKey], $option['option_id'])) {
                        $selected[$aId] = $option['option_id'];
                        $optionIndex = count($options);
                    }
                    $optionIndex++;
                }
            }

            if (isset($selected[$aId])) {
                foreach ($products as $key => $simpleProduct) {
                    if ($simpleProduct->getData($allAvialableAttributeCode[$aKey]) != $selected[$aId]) {
                        unset($products[$key]);
                    }
                }
            }
        }

        if (count($selected) == 0) { // not have attribut who is allowed to change image
            echo "SMDesignColorswatchPreloader.removePerload($$('.product-image img')[0]);";
            echo "  //not have allowed attribute to change image.\n";
            exit();
        }

        /*  calculate image sizes */
        if (Mage::getStoreConfig('smdesign_smdzoom/zoom/enabled') && $_product->getData('enable_zoom_plugin') == 1) {
            /* smdzoom plugin installed */
            $zoomConfig = array();
            $zoomConfig['image_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/image_width');
            $zoomConfig['image_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/image_height');
            $zoomConfig['thumbnail_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/thumbnail_width');
            $zoomConfig['thumbnail_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/thumbnail_height');
            $zoomConfig['wrapper_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_width');
            $zoomConfig['wrapper_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_height');
            $zoomConfig['wrapper_offset_left'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_offset_left');
            $zoomConfig['wrapper_offset_top'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_offset_top');
            $zoomConfig['zoom_type'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_type');
            $zoomConfig['zoom_ratio'] = intval(Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_ratio'));
            $zoomConfig['show_zoom_effect'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_zoom_effect');
            $zoomConfig['hide_zoom_effect'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/hide_zoom_effect');
            $zoomConfig['show_info_error'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_info_error');
            $zoomConfig['more_view'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/more_view_change_main_image');
            $zoomConfig['show_preloader'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_preloader');

            if ($zoomConfig['zoom_ratio'] == "" || $zoomConfig['zoom_ratio'] == 0 || $zoomConfig['zoom_ratio'] == 1) {
                $zoomConfig['zoom_ratio'] = 2;
            }

            switch ($zoomConfig['zoom_type']) {
                default:
                case 0:
                    /* outside */
                    $ratioModifierWidth = 0;
                    $ratioModifierHeight = 0;
                    if ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_width']) {
                        $ratioModifierWidth = intval($zoomConfig['wrapper_width'] / ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio']));
                    }
                    if ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_height']) {
                        $ratioModifierHeight = intval($zoomConfig['wrapper_height'] / ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio']));
                    }
                    $zoomConfig['zoom_ratio'] = $zoomConfig['zoom_ratio'] + max($ratioModifierWidth, $ratioModifierHeight);
                    break;
                case 1:
                    /* inside */
                    $zoomConfig['show_zoom_effect'] = "none";
                    $zoomConfig['hide_zoom_effect'] = "none";
                    $ratioModifierWidth = 0;
                    $ratioModifierHeight = 0;

                    if ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_width']) {
                        $ratioModifierWidth = intval($zoomConfig['wrapper_width'] / ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio']));
                    }
                    if ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_height']) {
                        $ratioModifierHeight = intval($zoomConfig['wrapper_height'] / ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio']));
                    }
                    $zoomConfig['zoom_ratio'] = $zoomConfig['zoom_ratio'] + max($ratioModifierWidth, $ratioModifierHeight);
                    break;
                case 2:
                    /* full */
                    $zoomConfig['show_zoom_effect'] = "none";
                    $zoomConfig['hide_zoom_effect'] = "none";
                    $zoomConfig['wrapper_offset_left'] = 0;
                    $zoomConfig['wrapper_offset_top'] = 0;
                    $zoomConfig['wrapper_width'] = $zoomConfig['image_width'];
                    $zoomConfig['wrapper_height'] = $zoomConfig['image_height'];
                    break;
            }

            $zoomConfig['upscale_image_width'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_width'];
            $zoomConfig['upscale_image_height'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_height'];
            $bigImageWidth = $zoomConfig['upscale_image_width'];
            $bigImageHeight = $zoomConfig['upscale_image_height'];
            $mainImageWidth = $zoomConfig['image_width'];
            $mainImageHeight = $zoomConfig['image_height'];
            $thumbImageWidth = $zoomConfig['thumbnail_width'];
            $thumbImageHeight = $zoomConfig['thumbnail_height'];
        } else {
            /* smdzoom not being used */
            $bigImageWidth = null;
            $bigImageHeight = null;
            $mainImageWidth = null; //$this->getRequest()->getParam('img_width', null);
            $mainImageHeight = null; //$this->getRequest()->getParam('img_height', null);
            $thumbImageWidth = 56;
            $thumbImageHeight = 56;
        }

        $images = array();
        if (count($products) > 0) {
            foreach ($products as $simpleProduct) {
                if (count($images) == 0) {
                    $simpleProduct->load($simpleProduct->getId());
                    $simpleProductImages = $simpleProduct->getMediaGalleryImages();
                    if (count($simpleProductImages)) {
                        foreach ($simpleProductImages as $_image) {
                            if ($simpleProduct->getImage() == $_image->getData('file')) { // Is base image if exsist go on top of array
                                array_unshift($images, array(
                                    'id' => $_image->getId(),
                                    'product_id' => $simpleProduct->getId(),
                                    'product' => $simpleProduct,
                                    'label' => $_image->getLabel(),
                                    'big_image' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($bigImageWidth, $bigImageHeight)),
                                    'image' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($mainImageWidth, $mainImageHeight)),
                                    'thumb' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($thumbImageWidth, $thumbImageHeight))
                                ));
                            } else {
                                array_push($images, array(
                                    'id' => $_image->getId(),
                                    'product_id' => $simpleProduct->getId(),
                                    'product' => $simpleProduct,
                                    'label' => $_image->getLabel(),
                                    'big_image' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($bigImageWidth, $bigImageHeight)),
                                    'image' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($mainImageWidth, $mainImageHeight)),
                                    'thumb' => sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($thumbImageWidth, $thumbImageHeight))
                                ));
                            }
                        }
                    }
                }
            }
        }

        if (count($images) == 0) {
            foreach ($_product->getMediaGalleryImages() as $_image) {
                $images[] = array(
                    'big_image' => sprintf(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($bigImageWidth, $bigImageHeight)),
                    'image' => sprintf(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($mainImageWidth, $mainImageHeight)),
                    'thumb' => sprintf(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($thumbImageWidth, $thumbImageHeight)),
                    'label' => $_image->getLabel(),
                    'id' => $_image->getId(),
                    'product_id' => $productId,
                    'product' => $_product
                );
            }
        }

        if (count($images) == 0) {
            echo "SMDesignColorswatchPreloader.removePerload($$('.product-image img')[0]);\n";
            if (Mage::getStoreConfig('smdesign_colorswatch/general/update_more_view')) {
                echo "$$('.more-views ul')[0].update('');";
            }
            $image = Mage::helper('catalog/image')->init($_product, 'image')->resize($mainImageWidth, $mainImageHeight);
            echo "$$('.product-image img')[0].src = '{$image}?rand=' + Math.random();";
            exit();
        }
        ?>
        <?php if (Mage::getStoreConfig('smdesign_smdzoom/zoom/enabled') && $_product->getData('enable_zoom_plugin') == 1) : ?>
            tempLink = new Object();
            tempLink.rel = '<?php echo $images[0]['image'] ?>?rand=' + Math.random();
            tempLink.href = '<?php echo $images[0]['big_image'] ?>?rand=' + Math.random();
            SMDUpdateMainImage(tempLink);
        <?php else : ?>
            $$('<?php echo $imageSelector ?>').first().src = '<?php echo $images[0]['image'] ?>?rand=' + Math.random();
        <?php endif; ?>
        <?php if (Mage::getStoreConfig('smdesign_colorswatch/general/update_more_view')) : ?>
            galleryView = $$('.more-views ul');
            if (galleryView.length == 0) {
            galleryViewContainer = document.createElement('div');
            galleryViewContainer.className = 'more-views';
            galleryView = document.createElement('ul');
            galleryViewContainer.appendChild(galleryView);
            if ($$('.product-img-box').length > 0) {
            $$('.product-img-box').first().appendChild(galleryViewContainer);
            }
            } else {
            galleryView = galleryView[0];
            }
            galleryView.update('');

            <?php foreach ($images as $key => $image) : ?>
                li = document.createElement('LI');
                <?php if (Mage::getStoreConfig('smdesign_smdzoom/zoom/enabled') && Mage::getStoreConfig('smdesign_smdzoom/zoom/more_view_change_main_image') && $_product->getData('enable_zoom_plugin') == 1 && ( 1 || $_product->getImage() != 'no_selection' && $_product->getImage() )) : ?>
                    $(li).update("<a href=\"<?php echo $image['big_image']; ?>\"  rel=\"<?php echo $image['image']; ?>\" onclick=\"SMDUpdateMainImage(this);return false;\"><img src=\"<?php echo $image['thumb'] ?>\" width=\"<?php echo $thumbImageWidth; ?>\" height=\"<?php echo $thumbImageHeight; ?>\" alt=\"<?php echo $image['label'] ?>\" /></a>");
                <?php else : ?>
                    $(li).update("<a href=\"#\" onclick=\"popWin('<?php echo Mage::getUrl('catalog/product/gallery', array('id' => $image['product_id'], 'image' => $image['id'], 'pid' => $productId)) ?>', 'gallery', 'width=300,height=300,left=0,top=0,location=no,status=yes,scrollbars=yes,resizable=yes'); return false;\" title=\"<?php echo $image['label'] ?>\"><img src=\"<?php echo $image['thumb'] ?>\" width=\"56\" height=\"56\" alt=\"<?php echo $image['label'] ?>\" /></a>");

                <?php endif; ?>
                galleryView.appendChild(li);
            <?php endforeach; ?>

        <?php endif; ?>
    <?php
    }

    private function productExsist($products, $aCode, $oId) {
        foreach ($products as $key => $product) {
            if ($product->getData($aCode) == $oId) {
                return true;
            }
        }
        return false;
    }

}
