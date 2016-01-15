<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Grid_Renderer_Pages extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhanced_Model_SitemapEnhanced */
        
        $html = '';

        $catPages    = $row->getSitemapCatLinks();
        $prodPages   = $row->getSitemapProdLinks();
        $prodOut     = $row->getSitemapOutLinks();
        $cmsPages    = $row->getSitemapCmsLinks();
        $tagPages    = $row->getSitemapTagLinks();
        $reviewPages = $row->getSitemapReviewLinks();
        $images      = $row->getSitemapMediaLinks();

        $tot = $row->getSitemapTotLinks();

        $html .= sprintf('<table style="border:0;">');

        $html .= sprintf('<tr><td>Category pages: </td><td style="text-align:right;">%1$s</td></tr>', $catPages);
        $html .= sprintf('<tr><td>Product pages: </td><td style="text-align:right;">%1$s</td></tr>', $prodPages);
        $html .= sprintf('<tr><td>Out Of Stock pages: </td><td style="text-align:right;">%1$s</td></tr>', $prodOut);

        $html .= sprintf('<tr><td>CMS pages: </td><td style="text-align:right;">%1$s</td></tr>', $cmsPages);
        $html .= sprintf('<tr><td>Tag pages: </td><td style="text-align:right;">%1$s</td></tr>', $tagPages);
        $html .= sprintf('<tr><td>Review pages: </td><td style="text-align:right;">%1$s</td></tr>', $reviewPages);
        $html .= sprintf('<tr style="font-weight: bold;"><td>Total pages: </td><td style="text-align:right;">%1$s</td></tr>', $tot);


        $html .= sprintf('<table style="border:0;width:90%%;">');
        $html .= sprintf('<tr style="font-style: italic;"><td>Product images: </td><td style="text-align:right;">%1$s</td></tr>', $images);
        $html .= sprintf('</table>');
//        $html .= sprintf('<div>%1$s</div>', $tot);

        return $html;
    }

}
