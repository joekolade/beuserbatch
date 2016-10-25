<?php
namespace SCW\Beuserbatch\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

// use TYPO3\CMS\Backend\Utility\BackendUtility;
// use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
// use TYPO3\CMS\Core\Database\DatabaseConnection;
// use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
// use TYPO3\CMS\Lang\LanguageService;

/**
 * Backend module user administration controller
 */
class BackendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

  /**
   * downloads: download
   *
   * @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository
   * @inject
   */
  protected $beusergroupRepository = NULL;

  /**
   * downloads: download
   *
   * @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository
   * @inject
   */
  protected $beuserRepository = NULL;

  /**
   * downloads: download
   *
   * @var \SCW\Beuserbatch\Domain\Repository\ImportuserRepository
   * @inject
   */
  protected $importUserRepository = NULL;

  /**
   * Create backendUsers-form
   *
   * @return void
   */
  public function overviewAction()
  {
      // Init
  }

  public function initializeCheckAction()
  {
    // Clear table
    $GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('tx_beuserbatch_domain_model_importuser');
  }

  /**
   * Check the given data
   *
   * @return void
   */
  public function checkAction()
  {

    # Todo: Check if user exists (E-Mail)

    if($this->request->hasArgument('file')) {
      $file = $this->request->getArgument('file')['tmp_name'];
      $arrResult = $this->getInfoFromCSV($file);
    }
    else {
      $this->redirect(
          'overview'
      );
    }

    $this->view->assign('file', $this->request->getArgument('file'));
    $this->view->assign('data', $arrResult);

  }

  /**
   * Create BackendUsers
   *
   * @return void
   */
  public function createAction()
  {
    $user = $this->importUserRepository->findAll();
    $persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
    $data = array();

    // Create users
    // 
    foreach ($user as $key => $value) {
      // $u = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser;
      $u = new \TYPO3\CMS\Beuser\Domain\Model\BackendUser;
      $u->setRealName($value->getFirstname() . ' ' . $value->getLastname());
      $u->setUserName($value->getUsername());
      $u->setEmail($value->getEmail());

      // Group
      if($value->getBegrouip() > 0){
        // Set Group!
        $g = $this->beusergroupRepository->findByUid($value->getBegrouip());
        $u->setBackendUserGroups($g);
      }
      else {
        $u->setIsAdministrator(TRUE);
      }
      $u->setPid(261);

      \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($u);
      // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($u->getBackendUser());
      $this->beuserRepository->add($u);
      $persistenceManager->persistAll();

      \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($u->getUid(), 'UID');
      
      // Set pwd
      $into_table  = 'be_users';
      $where_clause= 'uid='.$u->getUid();
      $field_values = array(
        'password' => $value->getUsername().'_pwd_0102'
        ,'tstamp' => time()
      );
          
      $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        $into_table      
        , $where_clause    
        , $field_values    
      );

      $data[] = array($value->getUsername(), $value->getUsername().'_pwd_0102');
    }
    
    $this->view->assign('data', $data);

    // Truncate Table
    // $GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('tx_beuserbatch_domain_model_importuser');
    
  }

  protected function getInfoFromCSV($file){
    $arrResult  = array();
    $user  = array();
    $handle     = fopen($file, "r");
    if(empty($handle) === false) {
        while(($data = fgetcsv($handle, 1000, ";")) !== FALSE){
            $arrResult[] = $data;
        }
        fclose($handle);
    }

    // delete title column
    array_shift($arrResult);

    foreach ($arrResult as $key => &$value) {
      $u = new \SCW\Beuserbatch\Domain\Model\Importuser();
      $u->setFirstname($value[0]);
      $u->setLastname($value[1]);
      $u->setEmail($value[2]);
      $value[3] = intval($value[3]);
      $u->setBegrouip($value[3]);

      if(is_int($value[3]) && $value[3] > 0){
        $g = $this->beusergroupRepository->findByUid($value[3]);
        if(count($g)){
          $value[4] = $g->getTitle() . ' (#' . $value[3] . ')';
        }
      }
      else {
        $value[4] = 'Admin';
      }
      $value[5] = $this->buildUsername($value);
      
      $u->setGroupname($value[4]);
      $u->setUsername($value[5]);

      $this->importUserRepository->add($u);

      $user[] = $u;
    }

    return $user;
  }

  protected function buildUsername($arr)
  {
    $n = $this->settings['usernamePrefix'];
    $n .= '_';
    $n .= strtolower($arr[0][0]);

    $find = array('/ä/','/ö/','/ü/','/ß/','/Ä/','/Ö/','/Ü/','/ /','/[:;]/');
    $replace = array('ae','oe','ue','ss','Ae','Oe','Ue','_','');

    $n .= strtolower(preg_replace($find , $replace, $arr[1]));

    return $n;
  }
}
