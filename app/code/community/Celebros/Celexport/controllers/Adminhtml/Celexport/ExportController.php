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
 * @package     Celebros_Celexport
 */
class Celebros_Celexport_Adminhtml_Celexport_ExportController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function export_celebrosAction()
    {
        $model = Mage::getModel('celexport/exporter');
        
        $isWebRun = $this->getRequest()->getParam('webadmin');
        $model->export_celebros($isWebRun);
    }
    
    public function export_orders_celebrosAction()
    {
        $model = Mage::getModel('celexport/exporter');
        $model->export_orders_celebros();
    }   
    
    public function schedule_exportAction()
    {
        $SCHEDULE_EVERY_MINUTES = 30;
        
        //Flooring the minutes
        $startTimeSeconds = ((int)(time()/60))*60;
        //Ceiling to the next 5 minutes
        $startTimeMinutes = $startTimeSeconds/60;
        $startTimeMinutes = ((int)($startTimeMinutes/5))*5 + 5;
        $startTimeSeconds = $startTimeMinutes * 60;
        
        $bAddedFromXml = FALSE;
        $config = Mage::getConfig()->getNode('crontab/jobs');
        if ($config instanceof Mage_Core_Model_Config_Element) {
            $jobs = $config->children();
            
            $i = 0;
            foreach ($jobs as $jobCode => $jobConfig) {
                if (strpos($jobCode, 'celexport') === FALSE) {
                    continue;
                }
                
                $timecreated   = strftime('%Y-%m-%d %H:%M:%S', time());
                $timescheduled = strftime('%Y-%m-%d %H:%M:%S', $startTimeSeconds + $i * 60 * $SCHEDULE_EVERY_MINUTES);
                
                try {
                    $schedule = Mage::getModel('cron/schedule');
                    $schedule->setJobCode($jobCode)
                    ->setCreatedAt($timecreated)
                    ->setScheduledAt($timescheduled)
                    ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                    ->save();
                    echo "{$jobCode} cron job is scheduled at $timescheduled <br/>";
                
                } catch (Exception $e) {
                    throw new Exception(Mage::helper('cron')->__('Unable to schedule Cron'));
                }
                
                $bAddedFromXml = TRUE;
                $i++;
            }
        }
        
        if (!$bAddedFromXml) {
            $config = Mage::getConfig()->getNode('default/crontab/jobs');
            if ($config instanceof Mage_Core_Model_Config_Element) {
                $jobs = $config->children();
                $i = 0;
                foreach ($jobs as $jobCode => $jobConfig) {
                    if (strpos($jobCode, 'celexport') === false) {
                        continue;
                    }
                    
                    $timecreated   = strftime('%Y-%m-%d %H:%M:%S', time());
                    $timescheduled = strftime('%Y-%m-%d %H:%M:%S', $startTimeSeconds + $i * 60 * $SCHEDULE_EVERY_MINUTES);
                    
                    try {
                        $schedule = Mage::getModel('cron/schedule');
                        $schedule->setJobCode($jobCode)
                        ->setCreatedAt($timecreated)
                        ->setScheduledAt($timescheduled)
                        ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                        ->save();
                        echo "{$jobCode} cron job is scheduled at $timescheduled <br/>";
                        
                    } catch (Exception $e) {
                        throw new Exception(Mage::helper('cron')->__('Unable to schedule Cron'));
                    }
                    
                    $i++;
                }
            }
        }
    }
    
}
