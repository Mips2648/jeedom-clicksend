<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Mips\Http\HttpClient;

class clicksend extends eqLogic {

  public static $_encryptConfigKey = array('apikey');


  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
   }
   */

  private function getClient() {
    $host = 'https://rest.clicksend.com/v3';
    $client = new HttpClient($host, log::getLogger(__CLASS__));
    $username = config::byKey('username', __CLASS__);
    $apikey = config::byKey('apikey', __CLASS__);
    $auth = base64_encode("{$username}:{$apikey}");
    $client->getHttpHeaders()->setHeader('Authorization', "Basic {$auth}");
    return $client;
  }

  public function sendSms($to, $message) {

    $message = [
      "body" => $message,
      "to" =>  $to,
      "from" =>  $this->getName()
    ];

    $payload = [
      "messages" => [
        $message
      ]
    ];

    $this->getClient()->doPost('sms/send', $payload);
  }

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */
}

class clicksendCmd extends cmd {

  // Exécution d'une commande
  public function execute($_options = array()) {
    /** @var clicksend */
    $eqLogic = $this->getEqLogic();
    $eqLogic->sendSms($this->getConfiguration('phonenumber'), $_options['title'] . ' ' . $_options['message']);
  }

  /*     * **********************Getteur Setteur*************************** */
}
