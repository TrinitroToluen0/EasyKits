<?php
/**
 *    _____                         _  __  _   _         
 *   | ____|   __ _   ___   _   _  | |/ / (_) | |_   ___ 
 *   |  _|    / _` | / __| | | | | | ' /  | | | __| / __|
 *   | |___  | (_| | \__ \ | |_| | | . \  | | | |_  \__ \
 *   |_____|  \__,_| |___/  \__, | |_|\_\ |_|  \__| |___/
 *                           |___/                        
 *          by AndreasHGK and fernanACM 
 */
declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use pocketmine\Server;
use pocketmine\player\Player;

use AndreasHGK\EasyKits\EasyKits;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;

use onebone\economyapi\EconomyAPI;
use Twisted\MultiEconomy\MultiEconomy;

class EconomyManager{

    /**
     * @var null|EconomyAPI|BedrockEconomy
     */
    public static $economy = null;

    /**
     * @param Player $player
     * @return float
     */
    public static function getMoney(Player $player): float
    {
        $economy = self::getEconomy();
        switch (true) {
            case $economy instanceof EconomyAPI:
                return $economy->myMoney($player);
            case $economy instanceof MultiEconomy:
                $currency = DataManager::getKey(DataManager::CONFIG, "multieconomy-currency");
                return $economy->getAPI()->getBalance($player->getName(), $currency);
            case $economy instanceof BedrockEconomy:
                $balance = 0;
                $economy->getPlayerBalance($player->getName(), ClosureContext::create(static function (?int $balance) use (&$balance): void {
                    $balance = $balance ?? 0;
                }));
                return $balance;
        }
        return 0;
    }

    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function setMoney(Player $player, float $money, bool $force = false): void
    {
        switch (true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->setMoney($player, $money);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->setBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case self::getEconomy() instanceof BedrockEconomy:
                self::getEconomy()->setPlayerBalance($player->getName(), (int)$money, ClosureContext::create(static function (bool $success): void {
                    // handle success or failure
                }));
                break;
        }
    }

    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function reduceMoney(Player $player, float $money, bool $force = false): void
    {
        switch (true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->reduceMoney($player, $money, $force);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->takeFromBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case self::getEconomy() instanceof BedrockEconomy:
                self::getEconomy()->subtractFromPlayerBalance($player->getName(), (int)$money, ClosureContext::create(static function (bool $success): void {
                    // handle success or failure
                }));
                break;
        }
    }

    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function addMoney(Player $player, float $money, bool $force = false): void
    {
        switch (true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->addMoney($player, $money, $force);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->addToBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case self::getEconomy() instanceof BedrockEconomy:
                self::getEconomy()->addToPlayerBalance($player->getName(), (int)$money, ClosureContext::create(static function (bool $success): void {
                    // handle success or failure
                }));
                break;
        }
    }

    /**
     * @return void
     */
    public static function loadEconomy(): void{
        $plugins = Server::getInstance()->getPluginManager();
        $economyAPI = $plugins->getPlugin("EconomyAPI");
        if($economyAPI instanceof EconomyAPI) {
            self::$economy = $economyAPI;
            EasyKits::get()->getLogger()->info("loaded EconomyAPI");
            return;
        }
        $bedrockEconomy = $plugins->getPlugin("BedrockEconomy");
        if($bedrockEconomy instanceof BedrockEconomy){
            self::$economy = $bedrockEconomy;
            EasyKits::get()->getLogger()->info("loaded BedrockEconomy");
            return;
        }
    }

    /**
     * @return boolean
     */
    public static function isEconomyLoaded(): bool{
        return self::getEconomy() !== null;
    }

    /**
     * @return void
     */
    public static function getEconomy(){
        return self::$economy;
    }
}