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
  use MipsEqLogicTrait;

  public static $_encryptConfigKey = array('apikey');


  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
   }
   */

  public static function cronDaily() {
    /** @var clicksend */
    foreach (eqLogic::byType(__CLASS__, true) as $eqLogic) {
      $eqLogic->getAccount();
    }
  }

  private function getClient() {
    $host = 'https://rest.clicksend.com/v3';
    $client = new HttpClient($host, log::getLogger(__CLASS__));
    $username = trim($this->getConfiguration('username'));
    $apikey = trim($this->getConfiguration('apikey'));
    if (empty($username) || empty($apikey)) {
      throw new Exception(__("username or apikey missing", __FILE__));
    }
    $auth = base64_encode("{$username}:{$apikey}");
    $client->getHttpHeaders()->setHeader('Authorization', "Basic {$auth}");
    return $client;
  }

  public function getAccount() {
    $result = $this->getClient()->doGet('account');
    if ($result->isSuccess()) {
      $body = json_decode($result->getBody(), true);
      $data = $body['data'];
      $this->checkAndUpdateCmd('balance', round($data['balance'], 2));
    } else {
      log::add(__CLASS__, 'warning', "{$result->getError()} - {$result->getBody()}");
    }
  }

  public function sendSms($to, $message) {
    $message = [
      "body" => $message,
      "to" =>  $to,
      "source" => $this->getName()
    ];

    $payload = [
      "messages" => [
        $message
      ]
    ];

    $this->getClient()->doPost('sms/send', $payload);
  }

  public function sendVoice($to, $message) {
    $message = [
      "body" => $message,
      "to" =>  $to,
      "country" => 'BE',
      "voice" => "female",
      "source" => $this->getName(),
      "custom_string" => $message,
      "lang" => "fr-fr",
      "machine_detection" => 1,
      "require_input" => 0
    ];

    $payload = [
      "messages" => [
        $message
      ]
    ];

    $this->getClient()->doPost('voice/send', $payload);
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
    $this->createCommandsFromConfigFile(__DIR__ . '/../config/commands.json', 'common');
  }

  public function decrypt() {
    $this->setConfiguration('apikey', utils::decrypt($this->getConfiguration('apikey')));
  }
  public function encrypt() {
    $this->setConfiguration('apikey', utils::encrypt($this->getConfiguration('apikey')));
  }
}

class clicksendCmd extends cmd {

  public function dontRemoveCmd() {
    return in_array($this->getLogicalId(), ['refresh', 'balance', 'sendSms', 'sendVoice']);
  }

  public function preInsert() {
    log::add('clicksend', 'debug', "insert cmd {$this->getLogicalId()}");
    if ($this->getLogicalId() == '') {
      $this->setLogicalId('sendCustom');
      $this->setType('action');
      $this->setSubType('message');
      $this->setDisplay('title_disable', 1);
    }
  }

  // Exécution d'une commande
  public function execute($_options = array()) {
    /** @var clicksend */
    $eqLogic = $this->getEqLogic();

    switch ($this->getLogicalId()) {
      case 'refresh':
        $eqLogic->getAccount();
        return;
      case 'sendSms':
        $eqLogic->sendSms($_options['title'], $_options['message']);
        return;
      case 'sendVoice':
        $eqLogic->sendVoice($_options['title'], $_options['message']);
        return;
      default:
        switch ($this->getConfiguration('type')) {
          case 'sms':
            $eqLogic->sendSms($this->getConfiguration('phonenumber'), $_options['message']);
            break;
          case 'voice':
            $eqLogic->sendVoice($this->getConfiguration('phonenumber'), $_options['message']);
            break;
        }
    }
  }
}
