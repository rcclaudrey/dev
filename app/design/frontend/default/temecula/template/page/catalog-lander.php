<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
?>
<?php
/**
 * Template for Mage_Page_Block_Html
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->getLang() ?>" lang="<?php echo $this->getLang() ?>">
<head>
<?php echo $this->getChildHtml('head') ?>
</head>
<body<?php echo $this->getBodyClass()?' class="cat-lander '.$this->getBodyClass().'"':'' ?> id="catalog">
<?php echo $this->getChildHtml('after_body_start') ?>
<?php echo $this->getChildHtml('global_notices') ?>
<?php echo $this->getChildHtml('header') ?>
<div class="wrapper">
    <div class="page site-width">
        <div class="main-container col2-left-layout">
            <div class="main">
                <?php echo $this->getChildHtml('breadcrumbs') ?>
                <?php echo $this->getChildHtml('global_messages') ?>
                <h1 class="category-title"><?php echo Mage::getSingleton('catalog/layer')->getCurrentCategory()->getName() ?></h1>
                <div class="col-main">
                    <?php echo $this->getChildHtml('content') ?>
                </div>
                <div class="col-left sidebar"><?php echo $this->getChildHtml('left') ?></div>
<div class="crystal"></div>                
<h2 class="hdr-under">Featured Brands</h2>
<div class="carouseled">
<div class="carrow carrow-left"></div>
<div class="carrow carrow-right"></div>
    <div class="carocontent">
  		<div class="caroitem"><a href="#"><img src="<?php echo $this->getSkinUrl('images/home/feat01.png');?>" alt=""/></a></div>
        <div class="caroitem"><a href="#"><img src="<?php echo $this->getSkinUrl('images/home/feat02.png');?>" alt=""/></a></div>
        <div class="caroitem"><a href="#"><img src="<?php echo $this->getSkinUrl('images/home/feat03.png');?>" alt=""/></a></div>
        <div class="caroitem"><a href="#"><img src="<?php echo $this->getSkinUrl('images/home/feat04.png');?>" alt=""/></a></div>
    </div>
<div class="crystal"></div>
</div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->getChildHtml('footer') ?>
<?php echo $this->getChildHtml('global_cookie_notice') ?>
<?php echo $this->getChildHtml('before_body_end') ?>
<?php echo $this->getAbsoluteFooter() ?>
</body>
</html>
