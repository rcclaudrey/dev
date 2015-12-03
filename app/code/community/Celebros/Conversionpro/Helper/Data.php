<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author      Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    const QUERY_VAR_NAME = 'q';
    const ICONV_CHARSET = 'UTF-8';
    
    /**
     * Conversionpro_Answer object
     *
     * @var Celebros_Conversionpro_Model_Answer
     */
    protected $_answers = array();
    
    /**
     * A mapping of answer ids to answer texts.
     */
    protected $_answersMapping = array();
    
    /**
     * A mapping of attribute codes to question SideTexts
     */
    protected $_questionTextsMapping = array();
    
    /**
     * Stored mapping of question names to attribute names.
     *
     */ 
    protected $_questionTexts = array();
    
    /**
     * Stored list of answer names to answer objects.
     *
     */ 
    protected $_answersByQuestion = array();
    
    /**
     * QwiserSearchApi object
     *
     * @var Mage_CatalogSearch_Model_Query
     */
    protected $_api;
    
    /**
     * Stored results of Conversionpro health monitors.
     */
    protected $_monitors = array();
    
    /**
     * Query object
     *
     * @var Mage_CatalogSearch_Model_Query
     */
    protected $_query;

    /**
     * Query string
     *
     * @var string
     */
    protected $_queryText;
    
    /**
     * Define if engine is available for layered navigation
     *
     * @var bool|null
     */
    protected $_isEngineAvailableForNavigation  = null;
    
    /**
     * Is a maximum length cut
     *
     * @var bool
     */
    protected $_isMaxLength = false;
    
    public function getCurrentLayer()
    {
        $layer = Mage::registry('current_layer');
        
        if (!isset($layer)) {
            if (!$this->getIsEngineAvailable()) {
                return Mage::getModel('catalogsearch/layer');
            }
            Mage::register('current_layer', Mage::getSingleton('conversionpro/search_layer'), true);
        }
        return Mage::registry('current_layer');
    }
        
    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        return Mage::getUrl($route, $params);
    }
    
    public function getSalespersonCrossSellApi()
    {
        return Mage::getModel('conversionpro/salespersonCrossSellApi');
    }
        
    /**
     * Retrieve search query parameter name
     *
     * @return string
     */
    public function getQueryParamName()
    {
        return self::QUERY_VAR_NAME;
    }
    
    public function getPriceFrom($priceAnswerId) { 
        $dashPos = strpos($priceAnswerId, '_', 2);
        return substr($priceAnswerId, 2, $dashPos - 2);
    }
    
    public function getPriceTo($priceAnswerId) {
        $dashPos = strpos($priceAnswerId, '_', 2);
        return substr($priceAnswerId, $dashPos + 1, strlen($priceAnswerId) - $dashPos - 1);
    }
    
    public function isHierarchical($fieldName)
    {
        $question = $this->getQuestionByAttributeCode($fieldName);
        if ($question && $question->DynamicProperties['IsHierarchical'] == 'True') {
            return true;
        }
        
        return false;
    }
    
    public function getCategoryRewriteQuery($category)
    {
        $query = $category->getName();
    
        switch(Mage::getStoreConfig('conversionpro/nav_to_search_settings/nav_to_search_use_full_category_path')) {
    
            case "category":
                break;
    
            case "full_path":
    
                $categories = $category->getParentCategories();
                $aParentIds = $category->getParentIds();
                $aParentIds = array_reverse($aParentIds);
    
                for($i=0; $i < count($aParentIds) - 2; $i++) {
                    $parentId  = $aParentIds[$i];
                    $category = $categories[$parentId];

                    if (!isset($category))
                        continue;

                    $categorySearchPhrase = $category->getName();
                    $query =  $categorySearchPhrase . " " . $query;
                }
    
                break;
    
            case "category_and_parent":
    
                $categories = $category->getParentCategories();
    
                if(count($categories) < 3) break;
    
                $parentId = $category->getParentId();
                $category = $categories[$parentId];
                $categorySearchPhrase = $category->getName();
                $query =  $categorySearchPhrase . " " . $query;
    
                break;
    
            case "category_and_root":
    
                $aParentIds = $category->getParentIds();
                
                if(count($aParentIds ) < 2) break;
                
                $branchRootId = $aParentIds[1];
                $category = Mage::getModel('catalog/category')->load($branchRootId);
                $categorySearchPhrase = $category->getName();
                $query =  $categorySearchPhrase . " " . $query;
    
                break;
        }
    
        return $query;
    }
    
    #####################################################################################################
    ## Search Registers
    #####################################################################################################
    
    /*
     * Storing returned search results from Quiser in the registry for this HTTP request.
     */
    public function registerSearchResults($searchResults)
    {
        $bSearchResultsWereRegisteredBefore = (bool) Mage::registry("current_conversionpro_search_results");
        Mage::unregister("conversionpro_search_results_were_registered_before");
        Mage::register("conversionpro_search_results_were_registered_before", $bSearchResultsWereRegisteredBefore);
        
        Mage::unregister("current_conversionpro_search_results");
        Mage::register("current_conversionpro_search_results", $searchResults);
        
        //Re-registering the questions with the new search results data.
        $this->refreshQuestions();
    }
        
    public function getSearchResults() {
        return Mage::registry("current_conversionpro_search_results");
    }
    
    public function hasSearchResults() {
        $searchResults = $this->getSearchResults();
        return isset($searchResults);
    }
    
    /*
     * Storing the search handle in the session storage, so it'll be available on succeeding HTTP requests as well.
     */
    public function persistSearchHandle($value)
    {
        Mage::getSingleton('conversionpro/session')->setSearchHandle($value);
    }
    
    public function getSearchHandle()
    {
        return Mage::getSingleton('conversionpro/session')->getSearchHandle();
    }
    
    /*
     * Storing the search session id in the session storage.
     */
    public function persistSearchSessionId($value)
    {
        if ($value != '') {
            Mage::getSingleton('conversionpro/session')->setSearchSessionId($value);
        }
    }
    
    /**
     * This holds the search query and params that were previously used.
     * This is used to compare search queries and see if we're in a new or existing search,
     *  and also to fetch existing filters for the state tags.
     */
    public function persistPreviousSearch($query, $filters)
    {
        Mage::getSingleton('conversionpro/session')->setPreviousSearchQuery(array('query' => $query, 'filters' => $filters));
    }
    
    public function getPreviousSearch()
    {
        return Mage::getSingleton('conversionpro/session')->getPreviousSearchQuery();
    }
    
    public function isSearchResultsWasRegisteredBefore() {
        return (bool)Mage::registry("conversionpro_search_results_were_registered_before");
    }
    
    public function registerImageBanner($src, $link) {
        $aLinks = Mage::registry("banner_images");
        if(!isset($aLinks)) $aLinks = array();
        $aLinks[] = array('src' => $src, 'link' => $link);
        Mage::unregister("banner_images");
        Mage::register("banner_images", $aLinks);
    }
    
    public function registerFlashBanner($link) {
        $aLinks = Mage::registry("banner_flashes");
        if(!isset($aLinks)) $aLinks = array();
        $aLinks[] = $link;
        Mage::unregister("banner_flashes");
        Mage::register("banner_flashes", $aLinks);
    }
    
    public function getBannerImage()
    {
        $aLinks = Mage::registry("banner_images");
        if(isset($aLinks)) {
            return $aLinks;
        }
        return false;
    }
    
    public function getBannerFlash()
    {
        $aLinks = Mage::registry("banner_flashes");
        if(isset($aLinks)) {
            return $aLinks;
        }
        return false;
    }
    
    public function setPriceQuestion($question) {
        Mage::getSingleton('conversionpro/session')->setPriceQuestion($question);
        return $this;
    }
    
    public function getPriceQuestion() {
        return Mage::getSingleton('conversionpro/session')->getPriceQuestion();
    }
    
    public function getSearchPathEntries()
    {
        $searchPathEntries = Mage::registry('searchPathEntries');
        
        if(!isset($searchPathEntries))
        {
            $searchPathEntries = array();
            $results = $this->getSearchResults();
            if($results && isset($results->SearchPath->Items)){
                foreach($results->SearchPath->Items as $searchPathEntry) {
                    $searchPathEntries[$searchPathEntry->QuestionId] = $searchPathEntry;
                }
            }
            
            Mage::register('searchPathEntries', $searchPathEntries);
        }
        return Mage::registry('searchPathEntries');
    }
    
    //These two functions (isNewCategorySearch and setNewCategorySearch) were implemented
    // to solve a bug that had to do with nav2search that uses the answer ids method.
    // In such a scenario, switching between category pages doesn't modify the query string,
    // so the engine doesn't know it's a new search and doesn't reset the price filter.
    // Using these functions, the engine can tell when the category page changed, and act accordingly.
    public function isNewCategorySearch()
    {
        return (bool)Mage::registry("conversionpro_new_category_search");
    }
    
    public function setNewCategorySearch()
    {
        Mage::unregister("conversionpro_new_category_search");
        Mage::register("conversionpro_new_category_search", true);
    }
    
    //Old gift finder code.
    /*public function setIsInGiftFinderRedirect($value)
    {
        Mage::unregister("conversionpro_is_in_gift_finder_redirect");
        Mage::register("conversionpro_is_in_gift_finder_redirect", $value);
    }
    
    public function getIsInGiftFinderRedirect()
    {
        return (bool)Mage::registry("conversionpro_is_in_gift_finder_redirect");
    }
    */
    
    #####################################################################################################
    ## Search Engine Status
    #####################################################################################################
    
    /**
     * Check if search engine can be used for either categories or search.
     *
     * @return  bool
     *
     */
    public function getIsEngineAvailable()
    {
        //This section takes care of returning a false value in case the disabler was activated.
        //The Conversionpro disabler is hardcoded to always be disabled, unless someone will find a use for it in the future.
        $status = Mage::getSingleton('conversionpro/session')->getConversionproDisabler();
        if ($status && $status == true) {
            $this->disableEngine();
            return $this->_isEngineAvailableForNavigation;
        }
        
        if (is_null($this->_isEngineAvailableForNavigation)) {
            $this->_isEngineAvailableForNavigation = false;
            //First, check if conversionpro is the chosen search engine.
            if ($this->isActiveEngine()) {
                //Next, check if this is a category page.
                if (Mage::app()->getRequest()->getModuleName() == 'catalog'
                    && Mage::app()->getRequest()->getControllerName() == 'category') {
                    //If so, check whether nav2search is enabled.
                    if ($this->getCelebrosConfigData('nav_to_search_settings/nav_to_search')) {
                        //If so, check whether nav2search blacklisting is enabled.
                        if ($this->getCelebrosConfigData('nav_to_search_settings/nav_to_search_enable_blacklist')) {
                            //If so, check whether this category is in nav2search's blacklist.
                            $categoryId = (int) Mage::app()->getRequest()->getParam('id', false);
                            if ($categoryId) {
                                $blacklist = Mage::helper('conversionpro')->getCelebrosConfigData('nav_to_search_settings/nav_to_search_blacklist');
                                
                                //If the category is not blacklisted, mark conversionpro as available.
                                if (!in_array($categoryId, explode(',',$blacklist))) {
                                    $this->_isEngineAvailableForNavigation = true;
                                }
                            }
                        //If blacklisting is disabled, mark conversionpro as available.
                        } else {
                            $this->_isEngineAvailableForNavigation = true;
                        }
                    }
                
                //Check if this is a search page.
                } elseif (Mage::app()->getRequest()->getModuleName() == 'catalogsearch') {
                    $this->_isEngineAvailableForNavigation = TRUE;
                } else {
                    $this->_isEngineAvailableForNavigation = FALSE;
                }
            }
        }
        
        return $this->_isEngineAvailableForNavigation;
    }
    
    public function disableEngine()
    {
        $this->_isEngineAvailableForNavigation = false;
    }
    
    /**
     * Check if Celebros engine is available
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        //In case the Conversionpro Disabler is on, always return false (We're not using the disabler right now).
        $status = Mage::getSingleton('conversionpro/session')->getConversionproDisabler();
        if (isset($status) && $status == true) {
            return false;
        }
        
        //In case we're disabled conversionpro manually in the administrative menu, always return false.
        if ($this->isConversionproEnabled()) {
            return true;
        }

        return false;
    }
    
    /**
     * Wrapper for getSearchEngine().
     */
    public function getEngine()
    {
        return $this->getSearchEngine();
    }
    
    public function getSearchEngine()
    {
        return Mage::getResourceSingleton('conversionpro/fulltext_engine');
    }
    
    /*
     * The Conversionpro disabler is used for reverting to default Magento search, in case the Quiser API indicates that we
     *  should do so.
     * Currently, this feature isn't activated under any circumstances, but we're leaving it here for future use.
     */
    public function enableConversionproDisabler()
    {
        Mage::getSingleton('conversionpro/session')->setConversionproDisabler(true);
    }
    
    /*
     * When a new keyword is used in the search, we'll set the disabler back to false, and get the results from Conversionpro again.
     */
    public function resetConversionproDisabler()
    {
        Mage::getSingleton('conversionpro/session')->setConversionproDisabler(false);
    }
    
    #####################################################################################################
    ## Config Data Getters
    #####################################################################################################
    
    /**
     * Retrieve information from Celebros search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getCelebrosConfigData($field, $storeId = null)
    {
        $path = 'conversionpro/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }
    
    public function getHost($storeId = null)
    {
        return $this->getCelebrosConfigData('general_settings/host', $storeId);
    }
    
    public function getSiteKey($storeId = null)
    {
        return $this->getCelebrosConfigData('general_settings/sitekey', $storeId);
    }
    
    public function isAddScripts($storeId = null)
    {
        return $this->getCelebrosConfigData('general_settings/addscripts', $storeId);
    }
    
    public function isAddDiv($storeId = null)
    {
        return $this->getCelebrosConfigData('general_settings/adddiv', $storeId);
    }
    
    public function isHideContent($storeId = null)
    {
        return $this->getCelebrosConfigData('general_settings/hidecontent', $storeId);
    }
    
    public function getJsScripts()
    {
        $protocol = Mage::app()->getFrontController()->getRequest()->isSecure()?'https':'http';
        $html = '<script src="' . $protocol . '://' . $this->getHost() . DS . $this->getJqueryPath() . DS . Mage::getConfig()->getNode('conversionpro/jquery_filename') . '" type="text/javascript"></script>';
        $html .= '<script src="' . $protocol . '://' . $this->getHost() . DS . $this->getClientConfigPath() . DS . $this->getSiteKey() . DS . Mage::getConfig()->getNode('conversionpro/client_config_path') . DS . $this->getClientConfigFilename() . '" type="text/javascript" ></script>';
        return $html;
    }
    
    public function getJqueryPath()
    {
        return $this->getCelebrosConfigData('advanced/scripts_path');
    }
    
    public function getClientConfigPath()
    {
        return $this->getCelebrosConfigData('advanced/client_config_path');
    }
    
    public function getClientConfigFilename()
    {
        return $this->getCelebrosConfigData('advanced/client_config_js_filename');
    }
    
    /*public function isMultiselectEnabled() {
        if ($this->getCelebrosConfigData('display_settings/enable_multiselect')) {
            return true;
        }
        return false;
    }
    
    public function isPriceSliderEnabled() {
        if ($this->getCelebrosConfigData('display_settings/price_selector') == 'slider') {
            return true;
        }
        return false;
    }
    
    public function isSliderAjaxified()
    {
        return $this->getCelebrosConfigData('display_settings/price_slider_ajax_enabled');
    }
    
    public function getExportChunkSize()
    {
        $chunk_size = $this->getCelebrosConfigData('advanced/export_chunk_size');
        if ($chunk_size == '') {
            $chunk_size = 1000;
        }
        return $chunk_size;
    }
    
    public function getExportProcessLimit()
    {
        $limit =  $this->getCelebrosConfigData('advanced/export_process_limit');
        if ($limit == '') {
            $limit = 3;
        }
        return $limit;
    }*/
    
    public function isCampaignsEnabled()
    {
        return $this->getCelebrosConfigData('display_settings/campaigns_enabled');
    }
    
    public function isConversionproEnabled()
    {
        return $this->getCelebrosConfigData('general_settings/conversionpro_enabled');
    }

    public function isLivesightEnabled()
    {
        return ($this->getCelebrosConfigData('livesight_settings/livesight_enabled')
                && $this->isConversionproEnabled());
    }
    
    public function getProfileName()
    {
        return $this->getCelebrosConfigData('display_settings/profile_name');
    }
    
    public function getSingleResultRedirect()
    {
        return $this->getCelebrosConfigData('display_settings/go_to_product_on_one_result');
    }
    
    #####################################################################################################
    ## Layered Navigation (Questions & Answers)
    #####################################################################################################
    
    public function getQuestionByAttributeCode($att_code)
    {
        $questions = $this->getQuestions();

        if ($att_code == 'cat') {
            $att_code = 'Category';
        }
        
        if(isset($questions[$att_code])) {
            return $questions[$att_code];
        }

        return false;
    }
    
    public function refreshQuestions()
    {
        $searchResults = $this->getSearchResults();
        
        $orig_questions = ($searchResults && isset($searchResults->Questions)) ?
            $searchResults->Questions->GetAllQuestions()
            : array();
        //replace questions id keys with questions text
        $questions = array();
        foreach($orig_questions as $question) {
            if ($question->Id == 'PriceQuestion') {
                $questions['price'] = $question;
            } else {
                $questions[$question->Text] = $question;
            }
            //In case this is a question we don't know yet, save it to the list of questions and their names we store in the db.
            //This is used later when during refinements we don't get these questions from the XML any more, but we still want 
            // to make sure that a given url parameter is a question, so we need a list of question to base that decision on.
            if ($question->Id == 'PriceQuestion') {
                $this->addQuestionText('price');
            } else {
                $this->addQuestionText($question->Text);
            }
        }

        Mage::unregister("current_questions");
        Mage::register("current_questions", $questions);
    }
    
    public function getQuestions() {
        $questions = Mage::registry("current_questions");
        if(!isset($questions) || (is_array($questions) && count($questions) == 0)){
            $this->refreshQuestions();
            $questions = Mage::registry("current_questions");
        }

        return $questions;
    }
    
    /**
     * Adds a question name to the list.
     * See getQuestionTexts() for an explanation of why we do this.
     */
    public function addQuestionText($questionText)
    {
        $this->_questionTexts = $this->getQuestionTexts();

        if (!array_key_exists($questionText, $this->_questionTexts)) {
            $this->_questionTexts[$questionText] = $questionText;
            Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'questions')
                ->getFirstItem()
                ->setName('questions')
                ->setContent(serialize($this->_questionTexts))
                ->save();
        }
    }
    
    /**
     * Gets a list of Conversionpro question names.
     * This enables us to tell which GET request parameters are search filters and which are something else.
     */
    public function getQuestionTexts()
    {
        //Load the answers array from the database.
        if (!isset($this->_questionTexts) || !count($this->_questionTexts)) {
            $item = Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'questions')
                ->getFirstItem();
            $this->_questionTexts = unserialize($item->getContent());
        }
        
        //Create the array if it doesn't exist yet.
        if (!is_array($this->_questionTexts)) {
            $this->_questionTexts = array();
        }
        
        return $this->_questionTexts;
    }
    
    #####################################################################################################
    ## Auto Complete Section
    #####################################################################################################
    
    public function isAutoComplete() {
        $res = $this->isConversionproEnabled() &&
        $this->getACFrontServerAddress()!="" && 
        $this->getACScriptServerAddress()!="" && 
        $this->getACCustomerName()!="";
        return $res;
    }
    
    public function getACFrontServerAddress() {
        return Mage::getStoreConfig('conversionpro/autocomplete_settings/ac_frontend_address');
    }

    public function getACScriptServerAddress() {
        return Mage::getStoreConfig('conversionpro/autocomplete_settings/ac_scriptserver_address');
    }
    
    public function getACCustomerName() {
        return Mage::getStoreConfig('conversionpro/autocomplete_settings/ac_customer_name');
    }
    
    #####################################################################################################
    ## Category Id to Conversionpro Answer Id Mapping
    #####################################################################################################
    
    public function addCategoryMapping($categoryId, $answerId)
    {
        $this->_answers = $this->getCategoryMapping();
            
        if (!array_key_exists($answerId, $this->_answers)) {
            $this->_answers[$answerId] = $categoryId;
            Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'categories')
                ->getFirstItem()
                ->setName('categories')
                ->setContent(serialize($this->_answers))
                ->save();
        }
    }
    
    public function getCategoryMapping($answer_id = null)
    {
        //Load the answers array from the database.
        if (!isset($this->_answers) || !count($this->_answers)) {
            $item = Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'categories')
                ->getFirstItem();
            $this->_answers = unserialize($item->getContent());
        }
        
        //Create the array if it doesn't exist yet.
        if (!is_array($this->_answers)) {
            $this->_answers = array();
        }
        
        return $this->_answers;
    }
    
    public function getCategoryIdByAnswerId($answer_id)
    {
        $this->_answers = $this->getCategoryMapping();
        
        //If the answer id doesn't exist yet, get the list of ids from conversionpro
        if (!array_key_exists($answer_id, $this->_answers)) {
            $this->_answers = $this->resetCategoryMapping();
        }
        
        return $this->_answers[$answer_id];
    }
    
    public function getAnswerIdByCategoryId($expected_category_id, $reset = false)
    {
        if ($reset) {
            $this->_answers = $this->resetCategoryMapping();
        }
        
        $this->_answers = $this->getCategoryMapping();
        foreach ($this->_answers as $answerId => $categoryId) {
            if ($categoryId == $expected_category_id) {
                return $answerId;
            }
        }
        
        //If this didn't work, run it again but refresh the list of answer ids first.
        if (!$reset) {
            $this->getAnswerIdByCategoryId($expected_category_id, true);
        }
    }
    
    public function resetCategoryMapping()
    {
        $this->_answers = $this->getCategoryMapping();
        $salespersonApi = Mage::getModel('conversionpro/salespersonSearchApi');
        $salespersonApi->GetAllQuestions();
        
        if (isset($salespersonApi->results)) {
            $categoryQuestionId = null;
            foreach ($salespersonApi->results->Items as $question) {
                if ($question->Text == 'Category') {
                    $categoryQuestionId = $question->Id;
                }
            }
            if (isset($categoryQuestionId)) {
                $salespersonApi->GetQuestionAnswers($categoryQuestionId);
                if (isset($salespersonApi->results)) {
                    foreach ($salespersonApi->results->Items as $answer) {
                        $category = Mage::getModel('catalog/category')->loadByAttribute('name', $answer->Text);
                        if ($category) {
                            $this->_answers[$answer->Id] = $category->getId();
                        } else {
                            $this->_answers[$answer->Id] = $answer->Text;
                        }
                    }
                    Mage::getModel('conversionpro/cache')
                        ->getCollection()
                        ->addFieldToFilter('name', 'categories')
                        ->getFirstItem()
                        ->setName('categories')
                        ->setContent(serialize($this->_answers))
                        ->save();
                }
            }
        }
        return $this->_answers;
    }
    
    #####################################################################################################
    ## Answer Text to Answer Id Mapping
    #####################################################################################################
    
    public function getAnswerTextByAnswerId($att_code, $answer_id)
    {
        $mapping = $this->getAnswersMapping($att_code);
        if (!array_key_exists($answer_id, $mapping)) {
            $this->_answersMapping = $this->resetAnswersMapping();
            $mapping = $this->getAnswersMapping($att_code);
        }
        
        return $mapping[$answer_id];
    }
    
    public function getAnswersMapping($att_code)
    {
        //Load the answers array from the database.
        if (!isset($this->_answersMapping) || !count($this->_answersMapping)) {
            $item = Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'answers')
                ->getFirstItem();
            $this->_answersMapping = unserialize($item->getContent());
        }
        
        //Fill in the values for this attribute code, if it doesn't exist yet.
        if (!is_array($this->_answersMapping) || !array_key_exists($att_code, $this->_answersMapping)) {
            $this->_answersMapping = $this->resetAnswersMapping();
        }
        return $this->_answersMapping[$att_code];
    }
    
    public function resetAnswersMapping()
    {
        $salespersonApi = Mage::getModel('conversionpro/salespersonSearchApi');
        $salespersonApi->GetAllQuestions();
    
        if (isset($salespersonApi->results)) {
            $categoryQuestionId = null;
            foreach ($salespersonApi->results->Items as $question) {
                $salespersonApi->GetQuestionAnswers($question->Id);
                $answers = array();
                if (isset($salespersonApi->results)) {
                    foreach ($salespersonApi->results->Items as $answer) {
                        $answers[$answer->Id] = $answer->Text;
                    }
                }
                if ($question->Id == 'PriceQuestion') {
                    $this->_answersMapping['price'] = $answers;
                } else {
                    $this->_answersMapping[$question->Text] = $answers;
                }
            }
            Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'answers')
                ->getFirstItem()
                ->setName('answers')
                ->setContent(serialize($this->_answersMapping))
                ->save();
        }
        return $this->_answersMapping;
    }
    
    
    #####################################################################################################
    ## Question Text to Question SideText Mapping
    #####################################################################################################
    
    public function getQuestionSideText($question_text)
    {
        $sidetext = $this->getQuestionTextsMapping($question_text);
        if (!$sidetext) {
            $this->_questionTextsMapping = $this->resetQuestionTextsMapping();
            $sidetext = $this->getQuestionTextsMapping($question_text);
        }
        
        return $sidetext;
    }
    
    public function getQuestionTextsMapping($question_text)
    {
        //Load the question texts array from the database.
        if (!isset($this->_questionTextsMapping) || !count($this->_questionTextsMapping)) {
            $item = Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'question_texts')
                ->getFirstItem();
            $this->_questionTextsMapping = unserialize($item->getContent());
        }
        
        //Fill in the values for this attribute code, if it doesn't exist yet.
        if (!is_array($this->_questionTextsMapping) || !array_key_exists($question_text, $this->_questionTextsMapping)) {
            $this->_questionTextsMapping = $this->resetQuestionTextsMapping();
        }
        return $this->_questionTextsMapping[$question_text];
    }
    
    public function resetQuestionTextsMapping()
    {
        $salespersonApi = Mage::getModel('conversionpro/salespersonSearchApi');
        $salespersonApi->GetAllQuestions();
    
        if (isset($salespersonApi->results)) {
            foreach ($salespersonApi->results->Items as $question) {
                $this->_questionTextsMapping[$question->Text] = $question->SideText;
            }
            Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'question_texts')
                ->getFirstItem()
                ->setName('question_texts')
                ->setContent(serialize($this->_questionTextsMapping))
                ->save();
        }
        return $this->_questionTextsMapping;
    }
    
    #####################################################################################################
    ## Conversionpro Monitoring
    #####################################################################################################
    
    public function isMonitoringEnabled()
    {
        return $this->getCelebrosConfigData('advanced/enable_monitoring');
    }
    
    public function isConnectivityMonitoringEnabled()
    {
        return $this->getCelebrosConfigData('advanced/enable_connectivity');
    }
    
    public function getConnectivityAttempts()
    {
        return $this->getCelebrosConfigData('advanced/connectivity_attempts');
    }
    
    public function getConnectivityFailures()
    {
        return $this->getCelebrosConfigData('advanced/connectivity_failures');
    }
    
    public function pushResultToMonitor($type, $result)
    {
        $monitors = $this->getMonitors();
        
        if (!array_key_exists($type, $monitors))  {
            $monitors[$type] = array();
        }
        
        if (count($monitors[$type]) > $this->getConnectivityAttempts()) {
            $count = count($monitors[$type]) - $this->getConnectivityAttempts();
            for ($i = 0; $i <= $count; ++$i) {
                array_shift($monitors[$type]);
            }
        }
        
        if (count($monitors[$type]) == $this->getConnectivityAttempts()) {
            array_shift($monitors[$type]);
        }
        
        array_push($monitors[$type], $result);
        $this->setMonitor($type, $monitors[$type]);
    }
    
    public function getMonitors()
    {
        if (!count($this->_monitors)) {
            $item = Mage::getModel('conversionpro/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'monitors')
                ->getFirstItem();
            $this->_monitors = unserialize($item->getContent());
        }
        
        if (!isset($this->_monitors) || !$this->_monitors) {
            $this->_monitors = array();
        }
        
        return $this->_monitors;
    }
    
    public function setMonitor($type, $monitor)
    {
        $this->_monitors[$type] = $monitor;
        
        Mage::getModel('conversionpro/cache')
            ->getCollection()
            ->addFieldToFilter('name', 'monitors')
            ->getFirstItem()
            ->setName('monitors')
            ->setContent(serialize($this->_monitors))
            ->save();
    }
    
    public function checkConversionproPulse()
    {
        $this->_monitors = $this->getMonitors();
        foreach ($this->_monitors as $monitor) {
            $errorCount = 0;
            foreach ($monitor as $result) {
                if (!$result) {
                    $errorCount++;
                }
            }
            
            if ($errorCount >= $this->getConnectivityFailures()) {
                Mage::getModel('core/config')->saveConfig('catalog/search/engine', 'mysql_fulltext');
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
            }
        }
    }
    
    #####################################################################################################
    ## Conversionpro Export
    #####################################################################################################
    
    public function setStoreExportStatus($store_export_status)
    {
        Mage::unregister("conversionpro_store_export_status");
        Mage::register("conversionpro_store_export_status", $store_export_status);
    }
    
    public function getStoreExportStatus()
    {
        return Mage::registry("conversionpro_store_export_status");
    }
    
    public function setCronJobCode($cron_execution_time)
    {
        Mage::unregister("conversionpro_cron_execution_time");
        Mage::register("conversionpro_cron_execution_time", $cron_execution_time);
    }
    
    public function getCronJobCode()
    {
        return Mage::registry("conversionpro_cron_execution_time");
    }
    
    
    public function getUrlParam($param)
    {
        return strip_tags(Mage::app()->getRequest()->getParam($param));
    }
    
    public function sanitizeOutput($text)
    {
        return htmlentities($text , ENT_QUOTES, 'UTF-8');
    }
    
}